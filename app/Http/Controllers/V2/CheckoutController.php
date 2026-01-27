<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\CheckoutRequest;
use App\Models\Coupon;
use App\Models\PaymentMethod;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Traits\ApiResponses;
use App\Traits\V2\GetTotalAmountInCart;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    use ApiResponses, GetTotalAmountInCart;

    public function applyCoupon(Request $request)
    {
        $validated = $request->validate([
            'coupon_code' => 'required|string',
            'event_id' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $code = trim((string)$validated['coupon_code']);
        $eventId = (string)$validated['event_id'];
        $amount = (float)$validated['amount'];

        $coupon = Coupon::query()
            ->where('code', $code)
            ->where('event_id', $eventId)
            ->first();

        if (!$coupon) {
            return $this->error('Invalid coupon code for this event', 422);
        }

        if ($coupon->status !== 'active') {
            return $this->error('Coupon is inactive', 422);
        }

        if (!$coupon->is_active) {
            return $this->error('Coupon is not active', 422);
        }

        // Limit: -1 = unlimited, otherwise must be > 0
        if ($coupon->limit === 0) {
            return $this->error('Coupon has been used up', 422);
        }

        $rawValue = $coupon->value;

        if ($coupon->type === 'percentage' && $rawValue > 100) {
            // Prevent negative totals when a bad value is stored.
            return $this->error('Coupon percentage value is invalid', 422);
        }

        $discount = $coupon->calculateValue($amount);
        if ($discount < 0) {
            $discount = 0;
        }

        // Never discount more than the amount
        $discount = min($discount, $amount);

        $total = max(0, $amount - $discount);

        return $this->success([
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'amount' => $amount,
            'discount' => $discount,
            'total' => $total,
        ], 'Coupon applied');
    }

    public function processCheckout(CheckoutRequest $request)
    {
        $payload = $request->validated();

        $cartItems = $this->transformTicketIdsToCartItems($payload['tickets']);

        $this->checkSellingDateFromCartItems($cartItems);

        $ticketsAmount = $this->getTotalAmount($cartItems);

        if ($payload['is_free_checkout'] && $ticketsAmount == 0) {
            $data = [
                'customer' => $payload['customer'],
                'send_to_different_email' => $payload['send_to_different_email'],
                'attendees' => $payload['attendees'] ?? [],
                'meta' => [
                    'tickets_amount' => $ticketsAmount,
                    'gateway_fees' => 0,
                    'platform_fee' => 0,
                    'tickets_count' => count($payload['tickets']),
                    'tickets' => $payload['tickets']
                ],
            ];

            $transaction = Transaction::create([
                'amount' => 0,
                'charged_amount' => 0,
                'gateway' => 'free',
                'cart_items' => $cartItems,
                'reference' => $reference = Str::uuid()->toString(),
                'user_id' => $request->user()?->id,
                'data' => $data,
            ]);

            \DB::beginTransaction();
            PaymentWebhookController::finishUp($transaction);

            return $this->success(['reference' => $reference], 'Free checkout successful');
        }

        $gateway = $payload['gateway'];

        $paymentMethod = PaymentMethod::where('gateway', $gateway)->firstOrFail();

        $testKeyAttr = $gateway . '_test_key';
        $liveKeyAttr = $gateway . '_live_key';
        $secretKey = config('app.env') === 'local'
            ? ($paymentMethod->{$testKeyAttr} ?? null)
            : ($paymentMethod->{$liveKeyAttr} ?? null);

        if (!$secretKey) {
            return $this->error(ucfirst($gateway) . ' payment method is disabled', 500);
        }

        // I need an event context for coupon validation
        $firstTicket = Ticket::query()->where('id', $cartItems[0]['id'])->first();
        if (!$firstTicket) {
            return $this->error('Invalid ticket selected', 422);
        }

        $platformFee = $this->getPlatformFee($ticketsAmount);

        $coupon = null;
        $couponAmount = 0.0;

        if (!empty($payload['coupon_applied'])) {
            $couponCode = $payload['coupon_code'];

            $coupon = Coupon::where('code', $couponCode)
                ->where('event_id', $firstTicket->event_id)
                ->where('status', 'active')
                ->first();

            if (!$coupon) {
                return $this->error('Invalid coupon code for this event', 422);
            }

            if (!$coupon->is_active) {
                return $this->error('Coupon is no longer valid', 422);
            }

            if ($coupon->limit === 0) {
                return $this->error('Coupon has been used up', 422);
            }

            $couponAmount = $coupon->calculateValue($ticketsAmount + $platformFee);
            $couponAmount = max(0, min($couponAmount, $ticketsAmount));
        }

        $total = max(0, $ticketsAmount + $platformFee - $couponAmount);

        $gatewayFees = $this->getGatewayFees($total, $gateway);

        $chargedAmount = $total + $gatewayFees;

        $reference = Str::uuid()->toString();

        $data = [
            'customer' => $payload['customer'],
            'send_to_different_email' => $payload['send_to_different_email'],
            'attendees' => $payload['attendees'] ?? [],
            'meta' => [
                'tickets_amount' => $ticketsAmount,
                'gateway_fees' => $gatewayFees,
                'platform_fee' => $platformFee,
                'tickets_count' => count($payload['tickets']),
                'tickets' => $payload['tickets']
            ],
        ];

        $gatewayResponse = $this->initializeGatewayPayment(
            gateway: $gateway,
            secretKey: $secretKey,
            email: $payload['customer']['email'],
            amount: $gateway === 'paystack' ? $chargedAmount : $total,
            reference: $reference
        );

        if (!$gatewayResponse['ok']) {
            return $this->error($gatewayResponse['message'], 502);
        }

        Transaction::create([
            'amount' => $total - $platformFee,
            'charged_amount' => $chargedAmount,
            'gateway' => $gateway,
            'cart_items' => $cartItems,
            'reference' => $reference,
            'coupon_id' => $coupon?->id,
            'coupon_amount' => $couponAmount,
            'user_id' => $request->user()?->id,
            'data' => $data,
        ]);

        return response()->json($gatewayResponse['data']);
    }

    private function transformTicketIdsToCartItems(array $ticketIds): array
    {
        $counts = [];
        foreach ($ticketIds as $ticketId) {
            $id = (string)$ticketId;
            $counts[$id] = ($counts[$id] ?? 0) + 1;
        }

        $cartItems = [];
        foreach ($counts as $id => $qty) {
            $cartItems[] = ['id' => $id, 'quantity' => $qty];
        }

        return $cartItems;
    }

    private function checkSellingDateFromCartItems(array $cartItems): void
    {
        foreach ($cartItems as $item) {
            $ticket = Ticket::where('id', $item['id'])->first();

            if (!$ticket) {
                abort(422, 'Invalid ticket selected');
            }

            $now = Carbon::now();
            $start = Carbon::parse($ticket->selling_start_date_time);
            $end = Carbon::parse($ticket->selling_end_date_time);

            if ($now->lt($start)) {
                abort(403, 'Ticket selling date has not begun');
            }

            if ($now->gt($end)) {
                abort(403, 'Ticket selling date has ended');
            }
        }
    }

    private function getPlatformFee(float $amount): float
    {
        return round($amount * 0.03, 2);
    }

    private function getGatewayFees(float $amount, string $gateway): float
    {
        if ($gateway === 'flutterwave') {
            return round($amount * 0.02, 2);
        }

        if ($gateway === 'paystack') {
            $flatFee = $amount >= 2500 ? 100 : 0;
            $feeCap = 2000;
            $decFee = 0.015;

            $appFee = round((($decFee * $amount) + $flatFee), 2);

            if ($appFee > $feeCap) {
                return (float)$feeCap;
            }

            $finalPrice = round((($amount + $flatFee) / (1 - $decFee)) + 0.01, 2);

            if ($amount < 2500 && $finalPrice >= 2500) {
                $finalPrice += 120;
            }

            return round($finalPrice - $amount, 2);
        }

        // chainpal or unknown gateway (not implemented yet)
        return 0;
    }

    private function initializeGatewayPayment(string $gateway, string $secretKey, string $email, float $amount, string $reference): array
    {
        $callbackUrl = config('app.client_url') . "/payment-complete?reference=$reference";

        if ($gateway === 'paystack') {
            $serviceUrl = config('services.paystack.url');
            $url = rtrim((string)$serviceUrl, '/') . '/transaction/initialize';

            $res = Http::withHeaders([
                'Authorization' => 'Bearer ' . $secretKey,
            ])->post($url, [
                'email' => $email,
                'amount' => $amount * 100,
                'reference' => $reference,
                'callback_url' => $callbackUrl
            ]);

            if (!$res->successful()) {
                return [
                    'ok' => false,
                    'message' => 'Paystack initialization failed',
                    'data' => $res->json(),
                ];
            }

            return ['ok' => true, 'data' => $res->json()];
        }

        if ($gateway === 'flutterwave') {
            $serviceUrl = config('services.flutterwave.url');
            $url = rtrim((string)$serviceUrl, '/') . '/v3/payments';

            $res = Http::withHeaders([
                'Authorization' => 'Bearer ' . $secretKey,
            ])->post($url, [
                'amount' => $amount,
                'tx_ref' => $reference,
                'redirect_url' => $callbackUrl,
                'customer' => [
                    'email' => $email,
                ],
            ]);

            if (!$res->successful()) {
                return [
                    'ok' => false,
                    'message' => 'Flutterwave initialization failed',
                    'data' => $res->json(),
                ];
            }

            return ['ok' => true, 'data' => $res->json()];
        }

        if ($gateway === 'chainpal') {
            return [
                'ok' => false,
                'message' => 'Chainpal gateway is not implemented yet',
            ];
        }

        return [
            'ok' => false,
            'message' => 'Unsupported payment gateway',
        ];
    }
}

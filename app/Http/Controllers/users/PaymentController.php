<?php

namespace App\Http\Controllers\users;

use App\Events\TicketPurchaseCompleted;
use App\Http\Requests\PaymentRequest;
use App\Models\Attendee;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use App\Models\Ticket;
use App\Traits\ApiResponses;
use App\Traits\GetTotalAmountInCart;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Mockery\Exception;

class PaymentController extends Controller
{
    use GetTotalAmountInCart, HttpResponses, ApiResponses;

    public function paystackRedirectToGateway(PaymentRequest $request)
    {
        return $this->gatewayRedirectToGateway($request, 'paystack');
    }

    public function flutterwaveRedirectToGateway(PaymentRequest $request)
    {
        return $this->gatewayRedirectToGateway($request, 'flutterwave');
    }

    private function gatewayRedirectToGateway(PaymentRequest $request, string $gateway)
    {
        try {
            $this->checkSellingDate($request->tickets);
            $this->validateCoupon($request->coupon_code);
        } catch (Exception $e) {
            if ($e->getCode() === 403) {
                return $this->failed(403, null, $e->getMessage());
            }
            return $this->failed(500, null, $e->getMessage());
        }

        $payment = PaymentMethod::where('gateway', $gateway)->first();

        $testKeyAttr = $gateway . '_test_key';
        $liveKeyAttr = $gateway . '_live_key';

        $secretKey = config('app.env') === 'local'
            ? ($payment->{$testKeyAttr} ?? null)
            : ($payment->{$liveKeyAttr} ?? null);

        if (!$secretKey) {
            return $this->failed(500, null, ucfirst($gateway) . ' payment method is disabled');
        }

        $ticketsAmount = $this->getTotalAmount($request->tickets);

        $ticket = Ticket::where('id', $request->tickets[0]['id'])->first();

        $coupon = Coupon::where('code', $request->coupon_code)
            ->where('event_id', $ticket->event_id)
            ->where('status', 'active')
            ->first();

        if ($coupon && $coupon->is_expired) {
            return $this->failed(403, null, 'Coupon is not active');
        }

        if ($coupon && $coupon->has_reached_limit) {
            return $this->failed(403, null, 'Coupon has been used up');
        }

        $couponAmount = $coupon?->calculateValue($ticketsAmount) ?? 0;

        $total = $ticketsAmount - $couponAmount;

        $chargedAmount = $total + $this->getFees($total, $gateway);

        $customer = Customer::create([
            "first_name" => $request->customer_first_name,
            "last_name" => $request->customer_last_name,
            "email" => $request->customer_email,
            "phone_dial_code" => $request->customer_phone_dial_code,
            "phone_number" => $request->customer_phone_number
        ]);

        $email = $customer->email;
        $reference = Str::uuid()->toString();

        $data = [
            'email' => $email,
            'amount' => $chargedAmount * 100, // paystack wants it in kobo,
            'reference' => $reference,
            'callback_url' => config('app.url') . '/api/v1/payments/callback/' . $reference
        ];

        $serviceUrl = config('services.' . $gateway . '.url') ?? config('services.paystack.url');

        if ($gateway === 'paystack') {
            $url = $serviceUrl . '/transaction/initialize';
        } elseif ($gateway === 'flutterwave') {
            $data['amount'] = $total;

            $url = $serviceUrl . '/v3/payments';

            // rename callback_url to redirect_url for flutterwave
            $data['redirect_url'] = $data['callback_url'];
            unset($data['callback_url']);

            // rename reference to tx_ref for flutterwave
            $data['tx_ref'] = $data['reference'];
            unset($data['reference']);

            // move email inside customer object for flutterwave
            $data['customer'] = [
                'email' => $email,
            ];
            unset($data['email']);
        } else {
            return $this->failed(500, null, 'Unsupported payment gateway');
        }

        $headers = [
            'Authorization' => 'Bearer ' . $secretKey
        ];

        $res = Http::withHeaders($headers)->post($url, $data);

        if ($res->successful()) {
            $attendees = array_map(function ($a) use ($customer) {
                return [...$a, 'customer_id' => $customer->id];
            }, $request->attendees);
            Attendee::insert($attendees);

            Transaction::create([
                'customer_id' => $customer->id,
                'organizer_id' => $ticket->event->user_id,
                'amount' => $total,
                'charged_amount' => $chargedAmount,
                'gateway' => $gateway,
                'cart_items' => $request->tickets,
                'reference' => $reference,
                'status' => 'pending',
                'coupon_id' => $coupon?->id,
                'coupon_amount' => $couponAmount,
                'user_id' => $request->user()?->id
            ]);

            return $res->json();
        }

        return $this->failed(500);
    }


    public function freePayment(PaymentRequest $request)
    {
        try {
            $this->checkSellingDate($request->tickets);
        } catch (Exception $e) {
            if ($e->getCode() === 403) {
                return $this->failed(403, null, $e->getMessage());
            }
            return $this->failed(500, null, $e->getMessage());
        }


        $amount = $this->getTotalAmount($request->tickets);

        if ($amount > 0) {
            return $this->failed(403);
        }
        $customer = Customer::create([
            "first_name" => $request->customer_first_name,
            "last_name" => $request->customer_last_name,
            "email" => $request->customer_email,
            "phone_dial_code" => $request->customer_phone_dial_code,
            "phone_number" => $request->customer_phone_number
        ]);
        $attendees = array_map(function ($a) use ($customer) {
            return [
                'full_name' => $a['first_name'] . ' ' . $a['last_name'],
                'email' => $a['email'],
                'ticket_id' => $a['ticket_id'],
                'customer_id' => $customer->id
            ];
        }, $request->attendees);
        Attendee::insert($attendees);

        $reference = $customer->id;
        $ticket = Ticket::where('id', $request->tickets[0]['id'])
            ->first();

        $invoice = Transaction::create([
            'customer_id' => $customer->id,
            'organizer_id' => $ticket->event->user_id,
            'gateway' => 'paystack',
            'cart_items' => $request->tickets,
            'reference' => $reference,
            'status' => 'success',
            'user_id' => $request->user()?->id
        ]);

        event(new TicketPurchaseCompleted($invoice, $customer));

        return $this->success(null, 'TotalAmount successfull');
    }

    private function checkSellingDate($tickets)
    {
        foreach ($tickets as $ticket) {
            $ticket = Ticket::where('id', $ticket['id'])
                ->first();
            $now = Carbon::now();
            $ticketSellingEndDate = Carbon::parse($ticket->selling_end_date_time);
            $ticketSellingStartDate = Carbon::parse($ticket->selling_start_date_time);


            if ($now->lt($ticketSellingStartDate)) {
                throw new Exception('Ticket selling date has not begun', 403);
            }

            if ($now->gt($ticketSellingEndDate)) {
                throw new Exception('Ticket selling date has ended', 403);
            }
        }
    }

    public function callback($reference)
    {
        $invoice = Transaction::where('reference', $reference)->first();

        if (!$invoice) {
            return $this->failed(404);
        }
        return redirect()->away(config('app.client_url') . '/events/payment-complete');
    }

    private function validateCoupon($code)
    {
        $ticket = Ticket::where('id', request()->tickets[0]['id'])
            ->first();

        $coupon = Coupon::where('code', $code)
            ->where('status', 'active')
            ->where('event_id', $ticket->event_id)
            ->first();

        if ($coupon && $coupon->is_expired) {
            throw new \Exception('Coupon is not active', 403);
        }
    }

    private function getFees($amount, $gateway)
    {
        if ($gateway === 'flutterwave') {
            return $amount * 0.02;
        }

        if ($gateway === 'paystack') {
            $flatFee = $amount >= 2500 ? 100 : 0;
            $feeCap = 2000;
            $decFee = 0.015;

            $appFee = round((($decFee * $amount) + $flatFee), 2);

            if ($appFee > $feeCap) {
                return $feeCap;
            }

            $finalPrice = round((($amount + $flatFee) / (1 - $decFee)) + 0.01, 2);

            if ($amount < 2500 && $finalPrice >= 2500) {
                $finalPrice += 120;
            }

            return round($finalPrice - $amount, 2);
        }

        return 0;
    }
}

<?php

namespace App\Http\Controllers\users;

use App\Events\TicketPurchaseCompleted;
use App\Models\Invoice;
use App\Traits\GetTotalAmountInCart;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentWebhook extends Controller
{
    use GetTotalAmountInCart;

    private array $paystackSupportedEvents = [
        'charge.success',
    ];
    private array $flutterwaveSupportedEvents = [
        "charge.completed"
    ];

    public function paystackWebhook(Request $request)
    {
        $event = $request->event;
        $eventIsSupported = in_array($event, $this->paystackSupportedEvents);
        if (!$eventIsSupported) {
            return response(null, 400);
        }

        $data = $request->data;
        $reference = $data['reference'];
        $invoice = Invoice::where('transaction_reference', '=', $reference)->first();
        $amount = (float)$data['amount'];


        if (!$invoice) {
            return response(null, 400);
        }

        if ($invoice->payment_status === 'success') {
            return response(null, 200);
        }

        if (round($invoice->charged_amount * 100, 2) !== $amount) {
            return response(null, 200);
        }


        return $this->finishUp($invoice);
    }

    public function flutterwaveWebhook(Request $request)
    {
        $event = $request->event;

        if (!in_array($event, $this->flutterwaveSupportedEvents)) {
            return response(null, 200);
        }

        $data = $request->data;
        $reference = $data['tx_ref'];
        $invoice = Invoice::where('transaction_reference', '=', $reference)->first();
        $amount = (float) $data['amount'];


        if (!$invoice) {
            return response(null, 200);
        }

        if ($invoice->payment_status === 'success') {
            return response(null, 200);
        }


        if ($invoice->amount !== $amount || $data['status'] !== 'successful') {
            return response(null, 200);
        }

        return $this->finishUp($invoice);
    }

    public function finishUp(Invoice $invoice)
    {
        $invoice->update([
            'payment_status' => 'success'
        ]);

        if ($invoice?->coupon && $invoice?->coupon->limit) {
            $invoice?->coupon->update([
                'limit' => $invoice?->coupon->limit - 1
            ]);
        }

        try {
            event(new TicketPurchaseCompleted($invoice, $invoice->customer));
        } catch (Exception $e) {
            error_log($e->getMessage());
        }

        return response(null, 200);
    }

    public function manualVerifyPayment($reference)
    {
        $invoice = Invoice::where('transaction_reference', $reference)->first();

        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        if ($invoice->payment_status === 'success') {
            return response()->json(['message' => 'Payment already verified']);
        }

        if ($invoice->payment_status === 'pending') {
            $gateway = $invoice->payment_method;

            if ($gateway === 'paystack') {
                $secret = config('services.paystack.secret', env('PAYSTACK_SECRET'));
                $verifyUrl = "https://api.paystack.co/transaction/verify/{$reference}";

                try {
                    $res = Http::withToken($secret)
                        ->acceptJson()
                        ->get($verifyUrl);
                } catch (\Exception $e) {
                    return response()->json(['message' => 'Verification request failed: ' . $e], 502);
                }

                if (!$res->successful()) {
                    return response()->json(['message' => 'Verification failed', 'data' => $res->json()], 400);
                }

                $payload = $res->json();

                if ($payload['data']['status'] === 'success') {
                    echo "success";
                    return $this->finishUp($invoice);
                }
            } elseif ($gateway === 'flutterwave') {
                $secret = config('services.flutterwave.secret', env('FLUTTERWAVE_SECRET'));
                $verifyUrl = "https://api.flutterwave.com/v3/transactions/verify_by_reference?tx_ref=$reference";
                try {
                    $res = Http::withToken($secret)
                        ->acceptJson()
                        ->get($verifyUrl);
                } catch (\Exception $e) {
                    return response()->json(['message' => 'Verification request failed: ' . $e], 502);
                }
                if (!$res->successful()) {
                    return response()->json(['message' => 'Verification failed', 'data' => $res->json()], 400);
                }
                $payload = $res->json();
                if ($payload['data']['status'] === 'successful') {
                    echo "success";
                    return $this->finishUp($invoice);
                }
            }
        }
    }
}

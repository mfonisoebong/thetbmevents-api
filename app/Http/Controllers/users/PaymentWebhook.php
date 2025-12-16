<?php

namespace App\Http\Controllers\users;

use App\Events\TicketPurchaseCompleted;
use App\Models\Invoice;
use App\Traits\GetTotalAmountInCart;
use Exception;
use Illuminate\Http\Request;

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
        $amountInCart = ($this->getTotalAmount($invoice->cart_items) - (float)$invoice->coupon_amount) * 100;

        if ($amountInCart !== $amount) {
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

        $amountInCart = $this->getTotalAmount($invoice->cart_items) - (float)$invoice->coupon_amount;

        if ($amountInCart !== $amount || $data['status'] !== 'successful') {
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
}

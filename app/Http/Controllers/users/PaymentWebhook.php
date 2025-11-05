<?php

namespace App\Http\Controllers\users;

use App\Events\InvoiceGenerated;
use App\Models\Invoice;
use App\Traits\GetTotalAmountInCart;
use Exception;
use Illuminate\Http\Request;

class PaymentWebhook extends Controller
{
    use GetTotalAmountInCart;

    private $paystackSupportedEvents = [
        'charge.success',
    ];
    private $vellaSupportedEvents = [
        'transaction.completed'
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


        $invoice->update([
            'payment_status' => 'success'
        ]);

        if ($invoice?->coupon && $invoice?->coupon->limit) {
            $invoice?->coupon->update([
                'limit' => $invoice?->coupon->limit - 1
            ]);
        }

        try {

            event(new InvoiceGenerated($invoice, $invoice->customer));
        } catch (Exception $e) {
            error_log($e->getMessage());
        }


        return response(null, 200);
    }

    public function vellaWebhook(Request $request)
    {

        $type = $request->type;
        $typeIsSupported = in_array($type, $this->vellaSupportedEvents);
        if (!$typeIsSupported) {
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
        $amountInCart = $this->getTotalAmount($invoice->cart_items);
        $parsedAmountInCart = (float)$amountInCart;

        if ($parsedAmountInCart !== $amount) {
            return response(null, 200);
        }
        $invoice->update([
            'payment_status' => 'success'
        ]);
        event(new InvoiceGenerated($invoice, $invoice->customer));

        return response(null, 200);
    }
}

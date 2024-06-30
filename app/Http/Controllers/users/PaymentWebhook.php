<?php

namespace App\Http\Controllers\users;

use App\Events\InvoiceGenerated;
use App\Mail\InvoiceMail;
use App\Mail\TestMail;
use App\Models\Invoice;
use App\Models\PurchasedTicket;
use App\Models\Sale;
use App\Traits\GetTotalAmountInCart;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
        $amountInCart = $this->getTotalAmount($invoice->cart_items) * 100;
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

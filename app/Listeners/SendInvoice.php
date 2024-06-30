<?php

namespace App\Listeners;

use App\Events\InvoiceGenerated;
use App\Mail\InvoiceMail;
use App\Models\Ticket;
use App\Traits\GetTotalAmountInCart;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendInvoice
{
    use GetTotalAmountInCart;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(InvoiceGenerated $event): void
    {

         $pdfPath= public_path('storage/pdf/'.Str::uuid());
         $invoiceCartItems= json_decode($event->invoice->cart_items);

         $pdfCartItems= array_map(function ($item){
             $ticket= Ticket::where('id', $item->ticket_id)
                 ->first();
            $itemQuantity= (int)$item->quantity;
             return [
                 'name'=> $ticket->name.' - '.$ticket->event->title,
                 'quantity'=> $itemQuantity,
                 'amount'=> $itemQuantity * $ticket->price
              ];

         }, $invoiceCartItems);

         $pdfData= [
             'cartItems'=> $pdfCartItems,
             'total'=> $this->getTotalAmount($event->invoice->cart_items)
         ];
         $pdf = Pdf::loadView('pdf.invoice', $pdfData);
         $pdf->save($pdfPath);

         Mail::to($event->invoice->user)
             ->send(new InvoiceMail($event->invoice, $pdfPath));
         unlink($pdfPath);
    }
}

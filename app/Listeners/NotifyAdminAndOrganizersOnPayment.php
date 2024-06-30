<?php

namespace App\Listeners;

use App\Events\InvoiceGenerated;
use App\Mail\NotifyOnPayment;
use App\Models\Notification;
use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class NotifyAdminAndOrganizersOnPayment
{
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
        $invoice = $event->invoice;
        $userCart = json_decode($invoice->cart_items);


        foreach ($userCart as $ticketData) {
            $ticket = Ticket::where('id', '=', $ticketData->id)
                ->first();
            $organizerId = $ticket->organizer->id;
            $organizerMail = $ticket->organizer->email;
            $name = $ticket->name . ' - ' . $ticket->event->title;

            $data = [
                'name' => $name,
                'quantity' => $ticketData->quantity
            ];

            Mail::to($organizerMail)
                ->send(new NotifyOnPayment($ticket->organizer, [$data], false));
            Mail::to(env('ADMIN_MAIL_ADDRESS'))
                ->send(new NotifyOnPayment($ticket->organizer, [$data], true));
            Notification::create([
                'body' => 'A ticket purchase has been made for ' . $name . ' (' . $ticketData->quantity . ')',
                'user_id' => $organizerId
            ]);
        }


    }
}

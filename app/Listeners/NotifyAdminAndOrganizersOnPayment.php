<?php

namespace App\Listeners;

use App\Events\TicketPurchaseCompleted;
use App\Mail\NotifyOrganizerOnPayment;
use App\Models\Notification;
use App\Models\Ticket;
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
    public function handle(TicketPurchaseCompleted $event): void
    {
        $invoice = $event->invoice;
        $userCart = $invoice->cart_items;

        foreach ($userCart as $ticketData) {
            $ticket = Ticket::where('id', '=', $ticketData->id)->first();
            $organizerId = $ticket->organizer->id;
            $organizerMail = $ticket->organizer->email;
            $name = $ticket->name . ' - ' . $ticket->event->title;

            $data = [
                'name' => $name,
                'quantity' => $ticketData->quantity
            ];

            Mail::to($organizerMail)->send(new NotifyOrganizerOnPayment($ticket->organizer, [$data], false));

            Mail::to(config('mail.admin_email'))->send(new NotifyOrganizerOnPayment($ticket->organizer, [$data], true));

            Notification::create([
                'body' => 'A ticket purchase has been made for ' . $name . ' (' . $ticketData->quantity . ')',
                'user_id' => $organizerId
            ]);
        }
    }
}

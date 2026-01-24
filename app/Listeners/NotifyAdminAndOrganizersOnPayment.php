<?php

namespace App\Listeners;

use App\Events\TicketPurchaseCompleted;
use App\Mail\NotifyAdminOnPayment;
use App\Mail\NotifyOrganizerOnPayment;
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
        $transaction = $event->transaction;
        $customer = $transaction->customer;
        $ticket = $customer->attendees()->first()->ticket;
        $organizerMail = $ticket->organizer->email;

        Mail::to($organizerMail)->send(new NotifyOrganizerOnPayment($transaction, $customer, $ticket->event, $ticket));

        Mail::to(config('mail.admin_email'))->send(new NotifyAdminOnPayment($transaction, $customer, $ticket->event, $ticket));
    }
}

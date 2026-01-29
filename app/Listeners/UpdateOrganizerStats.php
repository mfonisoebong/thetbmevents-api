<?php

namespace App\Listeners;

use App\Events\TicketPurchaseCompleted;
use App\Models\Event;
use App\Models\Sale;
use App\Models\Ticket;

class UpdateOrganizerStats
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
        $userCart = $transaction->cart_items;

        foreach ($userCart as $item) {
            $ticket = Ticket::where('id', $item['id'])->firstOrFail();
            $itemQuantity = $item['quantity'];

            $organizerEvent = Event::where('id', $ticket->event_id)->first();

            Sale::create([
                'invoice_id' => $transaction->id,
                'organizer_id' => $organizerEvent->user_id,
                'customer_id' => $event->customer->id,
                'ticket_id' => $ticket->id,
                'tickets_bought' => $itemQuantity,
                'total' => $itemQuantity * $ticket->price,
                'event_id' => $organizerEvent->id
            ]);

        }
    }
}

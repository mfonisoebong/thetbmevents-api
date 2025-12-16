<?php

namespace App\Listeners;

use App\Events\TicketPurchaseCompleted;
use App\Models\Event;
use App\Models\Sale;
use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
        $invoice = $event->invoice;
        $userCart = json_decode($invoice->cart_items);

        foreach ($userCart as $item) {
            $ticket = Ticket::where('id', $item->id)
                ->first();
            $itemQuantity = (int)$item->quantity;

            $organizerEvent = Event::where('id', '=', $ticket->event_id)
                ->first();
            $organizerEvent->update([
                'attendees' => $itemQuantity
            ]);

            Sale::create([
                'invoice_id' => $invoice->id,
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

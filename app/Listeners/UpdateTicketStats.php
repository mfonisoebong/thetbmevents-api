<?php

namespace App\Listeners;

use App\Events\TicketPurchaseCompleted;
use App\Models\PurchasedTicket;
use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateTicketStats
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
        $cartItems = json_decode($event->invoice->cart_items);
        $attendees = $event->customer->attendees;

        foreach ($cartItems as $item) {
            $ticket = Ticket::where('id', '=', $item->id)
                ->first();
            $itemQuantity = (int)$item->quantity;

            $isUnlimited = $ticket->unlimited;

            if (!$isUnlimited) {
                $ticket->quantity = $ticket->quantity - $itemQuantity;
                $ticket->save();
            }

            foreach ($attendees as $attendee){
                PurchasedTicket::create([
                    'ticket_id' => $item->id,
                    'attendee_id' => $attendee->id,
                    'quantity' => $itemQuantity,
                    'price' => $itemQuantity * $ticket->price,
                    'invoice_id' => $event->invoice->id
                ]);
            }



        }
    }
}

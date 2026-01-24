<?php

namespace App\Listeners;

use App\Events\TicketPurchaseCompleted;
use App\Models\Ticket;

class UpdateTicketSoldListener
{
    public function __construct()
    {
    }

    public function handle(TicketPurchaseCompleted $event): void
    {
        $transaction = $event->transaction;
        foreach ($transaction->cart_items as $item) {
            $ticket = Ticket::find($item->id);
            $ticket->increment('sold', $item->quantity);
        }
    }
}

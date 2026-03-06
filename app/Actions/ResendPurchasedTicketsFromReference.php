<?php

namespace App\Actions;

use App\Events\TicketPurchaseCompleted;
use App\Listeners\SendPurchasedTickets;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ResendPurchasedTicketsFromReference
{
    /**
     * Resend purchased ticket emails for a given transaction reference.
     *
     * @throws ModelNotFoundException
     */
    public function handle(string $reference): void
    {
        $transaction = Transaction::where('reference', $reference)->firstOrFail();

        $invoiceGeneratedEvent = new TicketPurchaseCompleted($transaction, $transaction->customer);

        // Keep behavior consistent with the existing console command.
        $sendPurchasedTicketsListener = new SendPurchasedTickets();
        $sendPurchasedTicketsListener->handle($invoiceGeneratedEvent);
    }
}


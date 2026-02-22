<?php

namespace App\Console\Commands;

use App\Events\TicketPurchaseCompleted;
use App\Listeners\SendPurchasedTickets;
use App\Models\Transaction;
use Exception;
use Illuminate\Console\Command;

class ResendTicketEmailFromReference extends Command
{
    protected $signature = 'resend:purchased-ticket-from-ref';

    protected $description = 'Resend ticket emails to customers';

    public function handle(): void
    {
        $references = [''];

        for ($i = 0; $i < count($references); $i++) {
            $reference = $references[$i];
            try {
                $transaction = Transaction::where('reference', $reference)->firstOrFail();
                $invoiceGeneratedEvent = new TicketPurchaseCompleted($transaction, $transaction->customer);
                $sendPurchasedTicketsListener = new SendPurchasedTickets();
                $sendPurchasedTicketsListener->handle($invoiceGeneratedEvent);
            } catch (Exception $e) {
                $this->error("Failed to resend ticket email. Reference id $reference: " . $e->getMessage());
            }
            $this->info("Resent ticket email for Transaction Reference ID: $reference");
        }

        $this->info('Ticket emails resent successfully.');
    }
}

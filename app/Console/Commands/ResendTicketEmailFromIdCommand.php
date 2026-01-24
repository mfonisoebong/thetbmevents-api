<?php

namespace App\Console\Commands;

use App\Events\TicketPurchaseCompleted;
use App\Listeners\SendPurchasedTickets;
use App\Models\Transaction;
use Exception;
use Illuminate\Console\Command;

class ResendTicketEmailFromIdCommand extends Command
{
    protected $signature = 'resend:ticket-email-from-id';

    protected $description = 'Resend ticket emails to customers';

    public function handle(): void
    {
        /*
            PLAN:
            Get Transaction from id
            create invoice generated object
            create sendpurchase ticket and call handle() passing the previous created obj
        */
        $invoices = [''];

        for ($i = 0; $i < count($invoices); $i++) {
            $invoice = $invoices[$i];
            try {
                $_invoice = Transaction::where('reference', $invoice)->firstOrFail();
                $invoiceGeneratedEvent = new TicketPurchaseCompleted($_invoice, $_invoice->customer);
                $sendPurchasedTicketsListener = new SendPurchasedTickets();
                $sendPurchasedTicketsListener->handle($invoiceGeneratedEvent);
            } catch (Exception $e) {
                $this->error("Failed to resend ticket email. Reference id $invoice: " . $e->getMessage());
            }
            $this->info("Resent ticket email for Transaction Reference ID: $invoice");
        }

        $this->info('Ticket emails resent successfully.');
    }
}

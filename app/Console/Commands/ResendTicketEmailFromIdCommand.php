<?php

namespace App\Console\Commands;

use App\Events\TicketPurchaseCompleted;
use App\Listeners\SendPurchasedTickets;
use App\Models\Invoice;
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
            Get Invoice from id
            create invoice generated object
            create sendpurchase ticket and call handle() passing the previous created obj
        */
        $invoices = ['4855b3de-d068-4a33-b72e-d1efb3a151a3'];

        for ($i = 0; $i < count($invoices); $i++) {
            $invoice = $invoices[$i];
            try {
                $_invoice = Invoice::where('transaction_reference', $invoice)->firstOrFail();
                $invoiceGeneratedEvent = new TicketPurchaseCompleted($_invoice, $_invoice->customer);
                $sendPurchasedTicketsListener = new SendPurchasedTickets();
                $sendPurchasedTicketsListener->handle($invoiceGeneratedEvent);
            } catch (Exception $e) {
                $this->error("Failed to resend ticket email. Reference id $invoice: " . $e->getMessage());
            }
        }

        $this->info('Ticket emails resent successfully.');
    }
}

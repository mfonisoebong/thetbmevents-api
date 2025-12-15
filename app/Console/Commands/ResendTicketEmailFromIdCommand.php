<?php

namespace App\Console\Commands;

use App\Events\InvoiceGenerated;
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
        $invoices = ['49b864e5-7205-4627-94f6-8975ab84304c'];
        $correctEmails = ['heritageoluwole2@gmail.com'];

        for ($i = 0; $i < count($invoices); $i++) {
            $invoice = $invoices[$i];
            $email = $correctEmails[$i];
            try {
                $_invoice = Invoice::where('id', $invoice)->firstOrFail();
                $invoiceGeneratedEvent = new InvoiceGenerated($_invoice, $_invoice->customer);
                $sendPurchasedTicketsListener = new SendPurchasedTickets();
                $sendPurchasedTicketsListener->handle($invoiceGeneratedEvent, $email);
            } catch (Exception $e) {
                $this->error("Failed to resend ticket email to $email: " . $e->getMessage());
            }
        }

        $this->info('Ticket emails resent successfully.');
    }
}

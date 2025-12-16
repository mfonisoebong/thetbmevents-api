<?php

namespace App\Console\Commands;

use App\Events\TicketPurchaseCompleted;
use App\Listeners\SendPurchasedTickets;
use App\Models\Customer;
use Exception;
use Illuminate\Console\Command;

class ResendTicketEmailCommand extends Command
{
    protected $signature = 'resend:ticket-email';

    protected $description = 'Resend ticket emails to customers';

    public function handle(): void
    {
        /*
            PLAN:
            Get customer by email
            Get Invoice by customer_id
            create invoice geenrated object
            create sendpurchase ticket and call handle() passing the previous created obj
        */
        $emails = ['heritageoluwole2@gmail.com'];

        foreach ($emails as $email) {
            try {
                $customer = Customer::where('email', $email)->orderByDesc('id')->firstOrFail();
                $invoice = $customer->invoice;
                $invoiceGeneratedEvent = new TicketPurchaseCompleted($invoice, $customer);
                $sendPurchasedTicketsListener = new SendPurchasedTickets();
                $sendPurchasedTicketsListener->handle($invoiceGeneratedEvent);
            } catch (Exception $e) {
                $this->error("Failed to resend ticket email to {$email}: " . $e->getMessage());
            }
        }

        $this->info('Ticket emails resent successfully.');
    }
}

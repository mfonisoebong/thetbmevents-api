<?php

namespace App\Console\Commands;

use App\Actions\ResendPurchasedTicketsFromReference;
use Exception;
use Illuminate\Console\Command;

class ResendTicketEmailFromReference extends Command
{
    protected $signature = 'resend:purchased-tickets-from-ref';

    protected $description = 'Resend ticket emails to customers';

    public function handle(ResendPurchasedTicketsFromReference $resender): void
    {
        $references = [''];

        for ($i = 0; $i < count($references); $i++) {
            $reference = $references[$i];
            try {
                $resender->handle($reference);
            } catch (Exception $e) {
                $this->error("Failed to resend ticket email. Reference id $reference: " . $e->getMessage());
            }
            $this->info("Resent ticket email for Transaction Reference ID: $reference");
        }

        $this->info('Ticket emails resent successfully.');
    }
}

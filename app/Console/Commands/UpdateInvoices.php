<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        try {
            DB::transaction(function () {

                $invoices = Invoice::all();

                foreach ($invoices as $invoice) {

                    $salesAmount = (float)$invoice->sales()->sum('total');
                    $organizer = array_key_exists(0, $invoice->sales->toArray()) ? $invoice->sales[0]->organizer : null;

                    $couponAmount = (float)$invoice->coupon_amount;

                    $newAmount = $salesAmount - $couponAmount;

                    $invoice->amount = max($newAmount, 0);
                    $invoice->organizer_id = $organizer?->id;

                    $invoice->save();
                }
            });

            $this->info('Invoices amount updated successfully');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}

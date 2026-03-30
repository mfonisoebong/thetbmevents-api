<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\AdminTransactionResource;
use App\Models\Transaction;
use App\Traits\GetTopOrganizers;

class AdminFinanceController extends Controller
{
    use GetTopOrganizers;

    public function overview()
    {
        $allTimeRevenue = Transaction::where('status', 'success')->sum('amount');

        $recentTransactions = Transaction::with(['customer', 'newPurchasedTickets', 'newPurchasedTickets.ticket', 'newPurchasedTickets.ticket.event'])
        ->orderByDesc('created_at')->take(100)->get();

        return $this->success([
            'all_time_revenue' => $allTimeRevenue,
            'recent_transactions' => AdminTransactionResource::collection($recentTransactions),
            'top_organizers' => $this->computeTopOrganizers('total_sales'),
        ]);
    }

    public function verifyTransaction(string $reference)
    {
        $paymentWebhookController = new PaymentWebhookController();
        $response = $paymentWebhookController->manualVerifyPayment($reference);

        if ($response->status() !== 200) {
            return $response;
        }

        $transaction = Transaction::where('reference', $reference)->first();

        return $this->success(new AdminTransactionResource($transaction));
    }
}

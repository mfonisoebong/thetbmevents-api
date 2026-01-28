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
        $successfulTransactionsBuilder = Transaction::where('status', 'success');
        $allTimeRevenue = $successfulTransactionsBuilder->sum('amount');

        // 10 most recent transactions
        $recentTransactions = $successfulTransactionsBuilder
            ->orWhere('status', 'pending')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return $this->success([
            'all_time_revenue' => $allTimeRevenue,
            'recent_transactions' => AdminTransactionResource::collection($recentTransactions),
            'top_organizers' => $this->computeTopOrganizers(),
        ]);
    }

    public function verifyTransaction(string $reference)
    {
        $paymentWebhookController = new PaymentWebhookController();
        $paymentWebhookController->manualVerifyPayment($reference);

        $transaction = Transaction::where('reference', $reference)->first();

        return $this->success(new AdminTransactionResource($transaction));
    }
}

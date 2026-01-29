<?php

namespace App\Http\Controllers\V2;

use App\Events\TicketPurchaseCompleted;
use App\Http\Controllers\Controller;
use App\Models\Attendee;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Log;

class PaymentWebhookController extends Controller
{
    private array $paystackSupportedEvents = [
        'charge.success',
    ];

    private array $flutterwaveSupportedEvents = [
        'charge.completed',
    ];

    public function paystackWebhook(Request $request)
    {
        $event = $request->event;
        if (!in_array($event, $this->paystackSupportedEvents, true)) {
            Log::warning('Paystack webhook received unsupported event', ['event' => $event]);
            return response(null, 400);
        }

        $data = $request->data;
        $reference = $data['reference'];
        $amount = (float) $data['amount'];

        if (!$reference) {
            Log::warning('Paystack webhook received without reference', ['data' => $data]);
            return response(null, 400);
        }

        DB::beginTransaction();

        $transaction = Transaction::where('reference', $reference)->lockForUpdate()->first();

        if (!$transaction) {
            Log::warning('Paystack webhook received for non-existent transaction', ['reference' => $reference]);
            return response(null, 400);
        }

        if ($transaction->status === 'success') {
            Log::notice('Paystack webhook received for already successful transaction', ['reference' => $reference]);
            return response(null, 200);
        }

        if (round($transaction->charged_amount * 100, 2) !== $amount) {
            Log::warning('Paystack webhook amount mismatch', [
                'reference' => $reference,
                'expected_amount' => round($transaction->charged_amount * 100, 2),
                'received_amount' => $amount,
            ]);
            return response(null, 200);
        }

        return $this->finishUp($transaction);
    }

    public function flutterwaveWebhook(Request $request)
    {
        $event = $request->event;

        if (!in_array($event, $this->flutterwaveSupportedEvents, true)) {
            Log::warning('Flutterwave webhook received unsupported event', ['event' => $event]);
            return response(null, 200);
        }

        $data = $request->data;
        $reference = $data['tx_ref'];
        $amount = (float) $data['amount'];

        if (!$reference) {
            Log::warning('Flutterwave webhook received without reference', ['data' => $data]);
            return response(null, 200);
        }

        DB::beginTransaction();

        $transaction = Transaction::where('reference', $reference)->lockForUpdate()->first();

        if (!$transaction) {
            Log::warning('Flutterwave webhook received for non-existent transaction', ['reference' => $reference]);
            return response(null, 200);
        }

        if ($transaction->status === 'success') {
            Log::notice('Flutterwave webhook received for already successful transaction', ['reference' => $reference]);
            return response(null, 200);
        }

        $platformFee = $transaction->data['meta']['platform_fee'] ?? 0;

        $expectedAmount = $transaction->amount + $platformFee;

        if ($expectedAmount!== $amount || $data['status'] !== 'successful') {
            Log::warning('Flutterwave webhook amount/status mismatch', [
                'reference' => $reference,
                'expected_amount' => $expectedAmount,
                'received_amount' => $amount,
                'received_status' => $data['status'],
            ]);
            return response(null, 200);
        }

        return $this->finishUp($transaction);
    }


    public function flutterwaveWebhookFailed(Request $request)
    {
        $event = $request->event;

        if (!in_array($event, $this->flutterwaveSupportedEvents, true)) {
            Log::warning('Flutterwave webhook received unsupported event', ['event' => $event]);
            return response(null, 200);
        }

        $data = $request->data;
        $reference = $data['tx_ref'];
        $status = $data['status'];

        if ($transaction = Transaction::where('reference', $reference)->firstOrFail()) {
            $transaction->status = $status;
            $transaction->save();
        }

        return response(null, 200);
    }

    public function manualVerifyPayment(string $reference)
    {
        DB::beginTransaction();

        $transaction = Transaction::where('reference', $reference)->lockForUpdate()->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        if ($transaction->status === 'success') {
            return response()->json(['message' => 'Payment already verified']);
        }

        if ($transaction->status !== 'pending') {
            return response()->json(['message' => 'Transaction is not pending'], 400);
        }

        $gateway = $transaction->gateway;

        if ($gateway === 'paystack') {
            $secret = config('services.paystack.secret');
            $verifyUrl = "https://api.paystack.co/transaction/verify/{$reference}";

            try {
                $res = Http::withToken($secret)
                    ->acceptJson()
                    ->get($verifyUrl);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Verification request failed: ' . $e], 502);
            }

            if (!$res->successful()) {
                return response()->json(['message' => 'Verification failed', 'data' => $res->json()], 400);
            }

            $payload = $res->json();

            if ($payload['data']['status'] === 'success') {
                return $this->finishUp($transaction);
            }

            return response()->json(['message' => 'Payment not successful'], 400);
        }

        if ($gateway === 'flutterwave') {
            $secret = config('services.flutterwave.secret');
            $verifyUrl = "https://api.flutterwave.com/v3/transactions/verify_by_reference?tx_ref=$reference";

            try {
                $res = Http::withToken($secret)
                    ->acceptJson()
                    ->get($verifyUrl);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Verification request failed: ' . $e], 502);
            }

            if (!$res->successful()) {
                return response()->json(['message' => 'Verification failed', 'data' => $res->json()], 400);
            }

            $payload = $res->json();

            if ($payload['data']['status'] === 'successful') {
                return $this->finishUp($transaction);
            }

            return response()->json(['message' => 'Payment not successful'], 400);
        }

        if ($gateway === 'free') {
            return $this->finishUp($transaction);
        }

        return response()->json(['message' => 'Unsupported gateway'], 400);
    }

    /** Always start a transaction using DB::beginTransaction() before calling this method */
    public static function finishUp(Transaction $transaction)
    {
        $transaction->update([
            'status' => 'success',
        ]);

        // Decrement coupon usage if coupon is limited.
        if ($transaction->coupon && $transaction->coupon->limit !== -1 && $transaction->coupon->limit > 0) {
            $transaction->coupon->update([
                'limit' => $transaction->coupon->limit - 1,
            ]);
        }

        try {
            // create customer & attendees record from transaction data payload.
            $payload = $transaction->data;
            $customerData = $payload['customer'];
            $attendeesData = $payload['attendees'];
            $tickets = $payload['meta']['tickets'];
            $ticketsBoughtCount = $payload['meta']['tickets_count'];

            $customer = $transaction->customer()->create([
                'full_name' => $customerData['fullname'],
                'email' => $customerData['email'],
                'phone_number' => $customerData['phone'],
                'tickets_bought_count' => $ticketsBoughtCount
            ]);

            $transaction->update([
                'customer_id' => $customer->id,
            ]);

            if ($payload['send_to_different_email']) {
                for ($i = 0; $i < count($attendeesData); $i++) {
                    $attendee = $attendeesData[$i];
                    $ticket_id = $tickets[$i];

                    $attendee = $customer->attendees()->create([
                        'full_name' => $attendee['fullname'],
                        'email' => $attendee['email'],
                        'ticket_id' => $ticket_id,
                    ]);

                    static::createPurchasedTickets($attendee, $transaction, $ticket_id);
                }
            } else {
                $attendee = $customer->attendees()->create([
                    'full_name' => $customerData['fullname'],
                    'email' => $customerData['email'],
                    'ticket_id' => $tickets[0],
                    'tickets_bought_count' => $ticketsBoughtCount
                ]);

                for ($i = 0; $i < $ticketsBoughtCount; $i++) {
                    static::createPurchasedTickets($attendee, $transaction, $tickets[$i]);
                }
            }

            event(new TicketPurchaseCompleted($transaction, $customer));
        } catch (Exception $e) {
            Log::error('Error processing transaction after payment verification', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            DB::rollBack();
            return response(null, 500);
        }

        DB::commit();
        return response(null, 200);
    }

    private static function createPurchasedTickets(Attendee $attendee, Transaction $transaction, string $ticket_id)
    {
        $attendee->newPurchasedTickets()->create([
            'transaction_id' => $transaction->id,
            'ticket_id' => $ticket_id
        ]);
    }
}

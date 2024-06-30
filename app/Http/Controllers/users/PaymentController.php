<?php

namespace App\Http\Controllers\users;

use App\Events\InvoiceGenerated;
use App\Http\Controllers;
use App\Http\Requests\PaymentRequest;
use App\Models\Attendee;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Ticket;
use App\Traits\GetTotalAmountInCart;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Mockery\Exception;

class PaymentController extends Controller
{
    use GetTotalAmountInCart, HttpResponses;

    public function paystackRedirectToGateway(PaymentRequest $request)
    {
        try {
            $this->checkSellingDate($request->tickets);

        } catch (Exception $e) {
            if($e->getCode() === 403){
                return $this->failed(403, null, $e->getMessage());
            }
            return $this->failed(500, null, $e->getMessage());
        }

        $payment = PaymentMethod::where('gateway', 'paystack')
            ->first();
        $secretKey = env('APP_ENV') === 'development' ?
            $payment->paystack_test_key :
            $payment->paystack_live_key;

        if (!$secretKey) {
            return $this->failed(500, null, 'Paystack payment method is disabled');
        }
        $amount = $this->getTotalAmount($request->tickets) * 100;

        $customer = Customer::create([
            "first_name" => $request->customer_first_name,
            "last_name" => $request->customer_last_name,
            "email" => $request->customer_email,
            "phone_dial_code" => $request->customer_phone_dial_code,
            "phone_number" => $request->customer_phone_number
        ]);

        $email = $customer->email;
        $reference = Str::uuid()->toString();
        $data = [
            'email' => $email,
            'amount' => (string)$amount,
            'reference' => $reference,
            'callback_url' => env('APP_URL') . '/api/v1/payments/callback/' . $reference
        ];
        $url = env('PAYSTACK_API_URL') . '/transaction/initialize';
        $headers = [
            'Authorization' => 'Bearer ' . $secretKey
        ];

        $res = Http::withHeaders($headers)
            ->post($url, $data);
        if ($res->successful()) {


            $attendees = array_map(function ($a) use ($customer) {
                return [...$a, 'customer_id' => $customer->id];
            }, $request->attendees);
            Attendee::insert($attendees);

            Invoice::create([
                'customer_id' => $customer->id,
                'payment_method' => 'paystack',
                'cart_items' => json_encode($request->tickets),
                'transaction_reference' => $reference,
                'payment_status' => 'pending'
            ]);
            return $res->json();
        }
        return $this->failed(500);

    }

    public function vellaGenerateInvoice($reference, PaymentRequest $request)
    {
        try {
            $this->checkSellingDate($request->tickets);

        } catch (Exception $e) {
            if($e->getCode() === 403){
                return $this->failed(403, null, $e->getMessage());
            }
            return $this->failed(500, null, $e->getMessage());
        }
        $customer = Customer::create([
            "first_name" => $request->customer_first_name,
            "last_name" => $request->customer_last_name,
            "email" => $request->customer_email,
            "phone_dial_code" => $request->customer_phone_dial_code,
            "phone_number" => $request->customer_phone_number
        ]);
        $attendees = array_map(function ($a) use ($customer) {
            return [...$a, 'customer_id' => $customer->id];
        }, $request->attendees);
        Attendee::insert($attendees);


        Invoice::create([
            'customer_id' => $customer->id,
            'payment_method' => 'vella',
            'cart_items' => json_encode($request->tickets),
            'transaction_reference' => $reference,
            'payment_status' => 'pending'
        ]);


        return $this->success(null, 'Invoice generated', 200);

    }

    public function freePayment(PaymentRequest $request)
    {
        try {
            $this->checkSellingDate($request->tickets);

        } catch (Exception $e) {
            if($e->getCode() === 403){
                return $this->failed(403, null, $e->getMessage());
            }
            return $this->failed(500, null, $e->getMessage());
        }


        $amount = $this->getTotalAmount($request->tickets);

        if ($amount > 0) {
            return $this->failed(403);
        }
        $customer = Customer::create([
            "first_name" => $request->customer_first_name,
            "last_name" => $request->customer_last_name,
            "email" => $request->customer_email,
            "phone_dial_code" => $request->customer_phone_dial_code,
            "phone_number" => $request->customer_phone_number
        ]);
        $attendees = array_map(function ($a) use ($customer) {
            return [...$a, 'customer_id' => $customer->id];
        }, $request->attendees);
        Attendee::insert($attendees);

        $reference = $customer->id;

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'payment_method' => 'vella',
            'cart_items' => json_encode($request->tickets),
            'transaction_reference' => $reference,
            'payment_status' => 'success'
        ]);

        event(new InvoiceGenerated($invoice, $customer));

        return $this->success(null, 'TotalAmount successfull');

    }

    private function checkSellingDate($tickets){
        foreach ($tickets as $ticket){
            $ticket= Ticket::where('id', $ticket['id'])
                ->first();
            $now= Carbon::now();
            $ticketSellingEndDate= Carbon::parse($ticket->selling_end_date_time);
            $ticketSellingStartDate= Carbon::parse($ticket->selling_start_date_time);
            

            if($now->lt($ticketSellingStartDate)){
                throw new Exception('Ticket selling date has not begun', 403);

            }

            if($now->gt($ticketSellingEndDate)){
                throw new Exception('Ticket selling date has ended', 403);
            }

        }
    }
    public function callback($reference)
    {
        $invoice = Invoice::where('transaction_reference', $reference)
            ->first();

        if (!$invoice) {
            return $this->failed(404);
        }
        return redirect()->away(env('CLIENT_URL') . '/events/payment-complete');
    }


}

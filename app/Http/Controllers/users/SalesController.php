<?php

namespace App\Http\Controllers\users;

use App\Http\Resources\SalesResource;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\Sale;
use App\Traits\ApiResponses;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    use HttpResponses, ApiResponses;

    public function getSales(Request $request)
    {
        $user = $request->user();

        $sales = Sale::where('organizer_id', $user->id)
            ->paginate(20);

        $salesMetaData = $sales->toArray();

        $formattedSalesResource = SalesResource::collection($sales);

        $formattedSales = [...$salesMetaData, 'data' => $formattedSalesResource];
        return $this->success($formattedSales);
    }

    public function resendPurchasedTickets(Sale $sale)
    {

        $invoice = $sale->invoice;

        $invoice->sendInvoice();

        return $this->success(null, 'Transaction has been resent');
    }

    public function verifySalesEmail(Event $event, string $email)
    {
        $tickets = $event->tickets;

        $attendees = Attendee::where('email', $email)->whereIn('ticket_id', $tickets->pluck('id'))->orderByDesc('id')->get();

        if (!$attendees->count()) {
            return $this->failed(404, null, 'Attendee not found' );
        }

        $data = [];

        foreach ($attendees as $attendee) {
            $customer = $attendee->customer;
            $purchasedTicket = $attendee->ticket;
            $invoice = $customer->invoice;

            $item = collect($invoice->cart_items ?? [])->first(function ($cartItem) use ($purchasedTicket) {
                return isset($cartItem['id']) && $cartItem['id'] == $purchasedTicket->id;
            });
            $quantity = $item['quantity'] ?? 0;

            $data[] = [
                'attendee_name' => $attendee->first_name . ' ' . $attendee->last_name,
                'attendee_email' => $attendee->email,
                'customer_name' => $customer->first_name . ' ' . $customer->last_name,
                'customer_email' => $customer->email,
                'invoice' => [
                    'date' => $invoice->created_at->toDateTimeString(),
                    'status' => $invoice->payment_status,
                    'gateway' => $invoice->payment_method,
                    'amount_paid' => $invoice->amount,
                    'ticket_bought' => $purchasedTicket->name,
                    'quantity' => $quantity,
                ],
            ];
        }

        return $this->success($data);
    }
}

<?php

namespace App\Http\Controllers\users;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Requests\VerifyTicketRequest;
use App\Models\Event;
use App\Models\PurchasedTicket;
use App\Models\Ticket;
use App\Traits\GetModelIds;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class TicketsController extends Controller
{
    use HttpResponses, GetModelIds;

    public function store(StoreTicketRequest $request)
    {

        $request->validate($request->all());
        $user = $request->user();
        foreach ($request->tickets as $ticket) {
            Ticket::create([
                'event_id' => $ticket['event_id'],
                'name' => $ticket['name'],
                'price' => $ticket['price'],
                'quantity' => $ticket['quantity'],
                'selling_start_date_time' => $ticket['selling_start_date_time'],
                'selling_end_date_time' => $ticket['selling_end_date_time'],
                'description' => $ticket['description'] ?? null,
                'organizer_id' => $user->id
            ]);
        }


        return $this->success(null, 'Tickets created successfully');

    }

    public function update(Event $event, UpdateTicketRequest $request)
    {
        $request->validate($request->all());

        $user = $request->user();

        foreach ($request->tickets as $ticket) {
            $oldTicketId = $ticket['id'] ?? null;


            $oldTicket = Ticket::where('id', $oldTicketId)
                ->first();

            if (!$oldTicket) {
                Ticket::create([
                    'event_id' => $ticket['event_id'],
                    'name' => $ticket['name'],
                    'price' => $ticket['price'],
                    'quantity' => $ticket['quantity'],
                    'selling_start_date_time' => $ticket['selling_start_date_time'],
                    'selling_end_date_time' => $ticket['selling_end_date_time'],
                    'description' => $ticket['description'] ?? null,
                    'organizer_id' => $user->id
                ]);
            }
        }

        return $this->success(null, 'Tickets updated successfully');
    }

    public function destroy(Ticket $ticket, Request $request)
    {
        $user = $request->user();

        if ($ticket->organizer_id !== $user->id) {
            return $this->failed(401);
        }
        if (count($ticket->sales) > 0) {
            return $this->failed(403, null, 'Ticket has been purchased already');
        }

        if (count($ticket->event->tickets) < 2) {
            return $this->failed(403, null, 'Event must have at least one ticket');
        }


        $ticket->delete();

        return $this->success(null, 'Ticket deleted successfully');

    }

    public function verifyTicket(PurchasedTicket $ticket, VerifyTicketRequest $request)
    {

        $request->validated($request->all());

//        $eventId= $ticket->ticket->event_id;

//        $event= Event::where('id', $eventId)
//        ->first();

//        $expiryDate= Carbon::parse($event->end_date.' '.$event->end_time);
//        $hasExpired= Carbon::now()->gt($expiryDate);

        $ticketInvoice = $ticket->invoice;
        $unsettledPayment = $ticketInvoice->status !== 'success';

        if ($unsettledPayment) {
            return $this
                ->failed(403,
                    null,
                    'Your payment status for this ticket is/has ' . $ticketInvoice->status);
        }

//        if($hasExpired){
//            return $this->failed(400, null, 'Ticket has expired');
//        }


        if ($ticket->used) {
            return $this->failed(400, null, 'Ticket has been verified already');
        }

        $ticket->update([
            'used' => true
        ]);

        return $this->success(null, 'Ticket verified successfully');
    }
}

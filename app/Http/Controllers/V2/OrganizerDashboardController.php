<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\EventWithStatsResource;
use App\Http\Resources\V2\OrganizerAttendeeResource;
use App\Http\Resources\V2\OrganizerTransactionResource;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Transaction;

class OrganizerDashboardController extends Controller
{

    public function overview()
    {
        return $this->success(EventWithStatsResource::collection(Event::where('user_id', auth()->id())->get()));
    }

    public function eventOrdersAndAttendees(Event $event)
    {
        $tickets = Ticket::where('event_id', $event->id)->get();

        $transactions = Transaction::where(function ($query) use ($tickets) {
            foreach ($tickets as $ticket) {
                $query->orWhereJsonContains('cart_items', [['id' => $ticket->id]]);
            }
        })->get();

        $attendees = collect();

        foreach ($transactions as $transaction) {
            if ($transaction->status !== 'success') {
                continue;
            }

            $attendees = $attendees->merge($transaction->customer->attendees);
        }

        return $this->success([
            'orders' => OrganizerTransactionResource::collection($transactions),
            'attendees' => OrganizerAttendeeResource::collection($attendees),
        ]);
    }
}

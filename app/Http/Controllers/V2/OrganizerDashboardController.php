<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\EventWithStatsResource;
use App\Http\Resources\V2\OrganizerAttendeeResource;
use App\Http\Resources\V2\OrganizerTransactionResource;
use App\Models\Attendee;
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
        $tickets = Ticket::where('event_id', $event->id)->get(['id', 'name']);

        $ticketIds = $tickets->pluck('id')->values();
        $ticketNames = $tickets->pluck('name', 'id')->mapWithKeys(fn ($name, $id) => [(string) $id => $name])->all();

        // Only fetch successful transactions if you only use them for attendee listing.
        // If you need failed/pending orders too, remove the status clause.
        $transactions = Transaction::where('status', 'success')
            ->where(function ($query) use ($ticketIds) {
                foreach ($ticketIds as $ticketId) {
                    $query->orWhereJsonContains('cart_items', [['id' => $ticketId]]);
                }
            })
            ->with(['customer:id,full_name,email,phone_number'])
            ->get();

        // Fetch attendees in one query (instead of merging per-transaction) and eager-load what's needed by the resource.
        $attendees = Attendee::whereIn('ticket_id', $ticketIds)
            ->with(['ticket:id,name'])
            ->withExists(['newPurchasedTickets as new_purchased_tickets_used_exists' => function ($q) {
                $q->where('used', true);
            }])
            ->get();

        // Pass preloaded ticket names to the resource to avoid Ticket::find() inside resource serialization.
        request()->attributes->set('ticket_names', $ticketNames);

        return $this->success([
            'orders' => OrganizerTransactionResource::collection($transactions),
            'attendees' => OrganizerAttendeeResource::collection($attendees),
        ]);
    }
}

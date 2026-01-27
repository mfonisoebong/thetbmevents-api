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
use Illuminate\Support\Facades\DB;

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

    public function revenueByYear(string $year)
    {
        if (!preg_match('/^\d{4}$/', $year)) {
            return $this->failed(422, null, 'Invalid year format. Expected YYYY');
        }

        $ticketIds = auth()->user()->createdTickets->pluck('id')->all();

        // Note: months returned by MySQL are 1..12.
        $monthly = Transaction::where('status', 'success')
            ->where(function ($query) use ($ticketIds) {
                foreach ($ticketIds as $ticketId) {
                    $query->orWhereJsonContains('cart_items', [['id' => $ticketId]]);
                }
            })
            ->whereYear('created_at', (int) $year)
            ->selectRaw('MONTH(created_at) as month, SUM(amount) as total')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->pluck('total', 'month');

        $result = array_fill(0, 12, 0.0);
        foreach ($monthly as $month => $total) {
            $idx = ((int) $month) - 1;
            if ($idx >= 0 && $idx < 12) {
                $result[$idx] = (float) $total;
            }
        }

        return $this->success($result);
    }
}

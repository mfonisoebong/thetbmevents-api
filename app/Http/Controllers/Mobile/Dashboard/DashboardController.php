<?php

namespace App\Http\Controllers\Mobile\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\Event\EventListResource;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function eventSummary(Request $request)
    {
        $user = $request->user();

        $tickets = $user->createdTickets;

        $attendees = 0;
        $tickets->each(function ($ticket) use (&$attendees) {
            $attendees += $ticket->attendees->count();
        });

        $data = [
            'total_events' => $user->events()->count(),
            'total_sales' => $user->invoices()
                ->where('status', 'success')
                ->sum('amount'),
            'tickets_sold' => $user->sales->sum('tickets_bought'),
            'total_attendees' => $attendees,
        ];

        return $this->success($data);
    }

    public function eventsPreview(Request $request)
    {
        $events = $request->user()->events()->latest()->take(3)->get();
        $data = EventListResource::collection($events);

        return $this->success($data);
    }
}

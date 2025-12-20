<?php

namespace App\Http\Controllers\Mobile\Dashboard;

use App\Http\Controllers\Controller;
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
                ->where('payment_status', 'success')
                ->sum('amount'),
            'tickets_sold' => $user->sales->sum('tickets_bought'),
            'total_attendees' => $attendees,
        ];

        return $this->success($data);
    }
}

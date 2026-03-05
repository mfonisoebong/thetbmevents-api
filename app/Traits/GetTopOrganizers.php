<?php

namespace App\Traits;

use App\Models\Event;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;

trait GetTopOrganizers
{
    public function computeTopOrganizers($orderBy = 'tickets_sold'): array
    {
        $organizers = User::where('role', 'organizer')->get();

        return $organizers->map((function ($organizer) {
            $events = $organizer->events()->with('tickets')->get();

            $ticketsSold = $events->sum(function ($event) {
                return $event->tickets->sum('sold');
            });

            if ($ticketsSold == 0) {
                $ticketsSold = $events->sum(function ($event) {
                    return $event->tickets->sum(function ($ticket) {
                        return $ticket->newPurchasedTickets->count() + $ticket->purchasedTickets->count();
                    });
                });
            }

            return [
                'id' => $organizer->id,
                'organizer' => $organizer->business_name,
                'avatar' => $organizer->avatar,
                'email' => $organizer->email,
                'tickets_sold' => $ticketsSold,
                'total_sales' => $events->sum('total_revenue'),
            ];
        }))->sortByDesc($orderBy)->take(50)->values()->all();
    }
}

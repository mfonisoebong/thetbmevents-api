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
        $organizers = User::where('role', 'organizer')->with(['events', 'createdTickets', 'createdTickets.purchasedTickets', 'createdTickets.newPurchasedTickets'])->get();

        return $organizers->map((function ($organizer) {
            $events = $organizer->events;

            $ticketsSold = $organizer->createdTickets->sum(function ($ticket) {
                return $ticket->newPurchasedTickets->count() + $ticket->purchasedTickets->count();
            });

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

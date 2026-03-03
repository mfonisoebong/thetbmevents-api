<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;

class EventWithStatsResource extends EventResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'location' => $this->location,
            'total_tickets_sold' => $this->getTicketsSold(),
            'total_revenue' => $this->getTotalRevenue(),
        ]);
    }

    private function getTicketsSold(): int
    {
        $sumSoldQuantity = (int) $this->whenLoaded('tickets')->sum('sold');

        if ($sumSoldQuantity > 0) {
            return $sumSoldQuantity;
        }

        $purchasedCount = $this->whenLoaded('tickets')->sum(function ($ticket) {
            return $ticket->newPurchasedTickets->count();
        });

        if ($purchasedCount > 0) {
            return $purchasedCount;
        }

        return $this->whenLoaded('tickets')->sum(function ($ticket) {
            return $ticket->purchasedTickets->count();
        });
    }

    // TODO: Optimize this by caching the total revenue in the database and updating it whenever a purchase is made, instead of calculating it on the fly.
    private function getTotalRevenue(): float
    {
        $ticketIds = $this->whenLoaded('tickets')->pluck('id')->all();

        if (empty($ticketIds)) {
            return 0.0;
        }

        $total = \DB::table('transactions')
            ->where('status', 'success')
            ->where(function ($query) use ($ticketIds) {
                foreach ($ticketIds as $ticketId) {
                    $query->orWhereJsonContains('cart_items', [['id' => $ticketId]]);
                }
            })
            ->sum('amount');

        return (float) $total;
    }
}

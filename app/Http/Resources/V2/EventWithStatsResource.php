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

    private function getTotalRevenue(): float
    {
        if ($this->total_revenue != -1) {
            return $this->total_revenue;
        } else {
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

            $this->resource->forceFill(['total_revenue' => $total])->save();

            return (float) $total;
        }
    }
}

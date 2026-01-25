<?php

namespace App\Http\Resources\V2;

use App\Models\NewPurchasedTicket;
use App\Models\PurchasedTicket;
use Illuminate\Http\Request;

class EventWithStatsResource extends EventResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'total_tickets_sold' => $this->getTicketsSold(),
            'total_revenue' => $this->getTotalRevenue(),
        ]);
    }

    private function getTicketsSold(): int
    {
        $sumSoldQuantity = (int)$this->tickets()->sum('sold');

        if ($sumSoldQuantity > 0) {
            return $sumSoldQuantity;
        }

        $purchasedCount = NewPurchasedTicket::whereIn('ticket_id', $this->tickets->pluck('id'))->count();

        if ($purchasedCount > 0) {
            return $purchasedCount;
        }

        return PurchasedTicket::whereIn('ticket_id', $this->tickets->pluck('id'))->count();
    }

    private function getTotalRevenue(): float
    {
        $ticketIds = $this->tickets->pluck('id')->all();

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

<?php

namespace App\Http\Resources\Mobile\Sale;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Event
 */
class EventSaleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $totalTickets = (int) $this->tickets()->sum('quantity') + (int) $this->sales()->sum('tickets_bought');
        return [
            'id' => $this->id,
            'title' => $this->title,
            'logo' => $this->logo,
            'event_date' => Carbon::parse($this->event_date)->format('D, d M Y'),
            'categories' => $this->categories,
            'tickets_sold' => (int) $this->sales()->sum('tickets_bought'),
            'total_tickets' => $totalTickets,
            'percent_sold' => $totalTickets > 0 ? round(((int) $this->sales()->sum('tickets_bought') / $totalTickets) * 100, 2) : 0,
        ];
    }
}

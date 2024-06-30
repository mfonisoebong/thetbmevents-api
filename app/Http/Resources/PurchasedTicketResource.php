<?php

namespace App\Http\Resources;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchasedTicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       $event= Event::where('id', '=', $this->ticket->event_id)
           ->first();

        return [
            'id'=> (string)$this->id,
            'event'=> $event->title,
            'date_purchased'=> $this->created_at,
            'ticket'=> $this->ticket->name,
            'used'=> (bool)$this->used,
            'event_location'=> $event?->location ?? $event?->event_link,
            'price'=> $this->price,
            'quantity'=> $this->quantity
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $event = Event::where('id', $this->ticket->event_id)
            ->first();

        return [
            'id' => $this->id,
            'ticket_no' => '#' . $this->id,
            'event_name' => $event?->title ?? '',
            'ticket_name' => $this->ticket->name,
            'ticket_price' => $this->ticket->price,
            'organizer' => $event?->user->business_name ?? '',
            'created_at' => $this->created_at
        ];
    }
}

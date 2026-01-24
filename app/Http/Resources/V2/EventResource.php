<?php

namespace App\Http\Resources\V2;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->alias,
            'description' => $this->description,
            'date' => $this->event_date,
            'time' => $this->event_time,
            'location' => !$this->undisclose_location ? $this->location : 'Undisclosed',
            'category' => $this->category,
            'tags' => $this->tags,
            'image' => $this->logo,
            'isOnline' => $this->type === 'virtual',
            'tickets' => TicketResource::collection(Ticket::where('event_id', $this->id)->get()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

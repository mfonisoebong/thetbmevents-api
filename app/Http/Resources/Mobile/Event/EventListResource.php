<?php

namespace App\Http\Resources\Mobile\Event;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/** @mixin Event */
class EventListResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'logo' => $this->logo,
            'categories' => $this->category,
            'location' => $this->location,
            'status' => $this->status,
            'description' => $this->description,
            'organizer' => [
                'avatar' => $this->user->avatar,
                'name' => $this->user->full_name
            ],
            'has_liked' => $this->likes()->where('user_id', $request->user()?->id)->exists(),
            'event_date' => $this->event_date,
            'event_time' => $this->event_time,
            'tickets' => [
                'available_units' => (int) $this->tickets()->sum('quantity'),
            ],
            'formatted_date' => Carbon::parse($this->event_date)->format('M d'), // e.g., Nov 28
            'date' => Carbon::parse($this->event_date)->format('D, d F Y') // e.g., Fri, 28 November 2026
        ];
    }
}

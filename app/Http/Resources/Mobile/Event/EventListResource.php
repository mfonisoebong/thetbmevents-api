<?php

namespace App\Http\Resources\Mobile\Event;

use Illuminate\Http\Request;
use App\Models\Event;
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
            'categories' => $this->categories,
            'location' => $this->location,
            'event_date' => $this->event_date,
            'event_time' => $this->event_time,
            'formatted_date' => Carbon::parse($this->event_date)->format('M d')
        ];
    }
}

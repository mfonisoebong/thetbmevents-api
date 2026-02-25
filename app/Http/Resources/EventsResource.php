<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class EventsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $location = $this->event_link ?? Str::of($this->location)->limit(80);

        return [
            'id' => $this->id,
            'alias' => $this->slug,
            'status' => $this->status,
            'title' => Str::of($this->title)->limit(25) ?? $this->title,
            'event_date' => $this->event_date,
            'location' => $this->undisclose_location ? 'Undisclosed' : $location,
            'logo' => $this->image_url,
            'undisclose_location' => $this->undisclose_location === 1 ? true : false
        ];
    }
}


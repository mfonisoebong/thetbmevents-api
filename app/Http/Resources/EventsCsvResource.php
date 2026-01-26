<?php

namespace App\Http\Resources;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventsCsvResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return

            [
             Str::of($this->title)->limit(25) ?? $this->title,
             $this->image_url,
           $this->created_at,
            $this->tickets[0]->price,
           $this->type,
            $this->event_date,
            $this->timezone,
        ];
    }
}

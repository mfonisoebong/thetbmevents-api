<?php

namespace App\Http\Resources;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SliderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $event= Event::where('id', $this->event_id)
        ->first();
        $ticket= $event
            ->tickets
            ->first();
        $startDate= Carbon::parse($event->start_date_time);
        $endDate= Carbon::parse($event->end_date_time);
        $expireIn= $endDate->diff($startDate);

        return [
            'id'=> $this->id,
            'event_id'=> $this->event_id,
            'event_logo'=> $this->event->logo,
            'event_alias'=> $this->event->alias,
            'expire_in'=> $expireIn,
            'ticket'=> [
                'name'=> $ticket?->name,
                'price'=> $ticket?->price,
            ]
        ];
    }
}

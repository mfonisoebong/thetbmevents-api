<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

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
            'alias' => $this->slug,
            'title' => Str::of($this->title)->limit(25) ?? $this->title,
            'logo' => $this->image_url,
            'created_at' => $this->created_at,
            'ticket_price' => count($this->tickets) < 1 ? null : $this->tickets[0]->price,
            'status' => $this->status
        ];
    }
}

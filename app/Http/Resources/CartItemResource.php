<?php

namespace App\Http\Resources;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $ticket= Ticket::where('id', '=', $this->ticket_id)->first();
        $event= Event::where('id', '=', $ticket->event_id)->first();
        return [
            'id'=> $this->id,
            'quantity'=> $this->quantity,
            'event'=>[
                'id'=> $event->id,
                'logo'=> $event->logo,
                'category'=> $event->categories,
                'title'=> $event->title
            ],
            'ticket'=> [
                'id'=> $this->ticket_id,
                'price'=> $ticket->price,
                'name'=> $ticket->name,
                'quantity'=> $ticket->quantity,
                'unlimited'=> $ticket->unlimited ? true: false
            ]
        ];
    }
}

<?php

namespace App\Http\Resources\Mobile\Invoice;

use App\Models\PurchasedTicket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PurchasedTicket */
class TicketResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => (string)$this->id,
            'event' => [
                'id' => $this->ticket->event->id,
                'title' => $this->ticket->event->title,
                'logo' => $this->ticket->event->logo,
                'event_date' => $this->ticket->event->event_date,
                'event_time' => $this->ticket->event->event_time,
                'location' => $this->ticket->event->location,
                'organizer' => $this->ticket->event->user->business_name,
            ],
            'ticket' => [
                'name' => $this->ticket->name,
            ],
            'quantity' => $this->quantity,
            'total_paid' => $this->price,
            'status' => $this->invoice->status,
            'customer' => [
                'name' => $this->attendee->customer->full_name
            ]

        ];
    }
}

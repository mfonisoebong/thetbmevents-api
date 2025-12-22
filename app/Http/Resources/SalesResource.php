<?php

namespace App\Http\Resources;

use App\Models\Customer;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $customer = Customer::where('id', $this->customer_id)->first();
        $ticket = Ticket::where('id', $this->ticket_id)->first();
        $event = Event::where('id', '=', $ticket->event_id)->first();

        return [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'tickets_bought' => $this->tickets_bought,
            'price' => $this->total,
            'price_formatted' => 'NGN ' . number_format($this->total, 2),
            'ticket' => $ticket->name . '-' . $event?->title,
            'customer' => [
                'name' => $customer->full_name,
                'email' => $customer->email
            ]
        ];
    }
}

<?php

namespace App\Http\Resources\V2;

use App\Http\Resources\Mobile\Auth\ProfileResource;
use App\Http\Resources\Mobile\Invoice\TicketResource as InvoiceTicketResource;
use App\Http\Resources\Mobile\Sale\EventSaleResource;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Ticket */
class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'currency' => $this->currency,
            'start_selling_date' => $this->selling_start_date_time,
            'end_selling_date' => $this->selling_end_date_time,
            'quantity' => $this->quantity,
            'sold' => $this->sold,
            'organizer_id' => $this->organizer_id,
            'event_id' => $this->event_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

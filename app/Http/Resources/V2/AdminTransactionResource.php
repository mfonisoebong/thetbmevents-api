<?php

namespace App\Http\Resources\V2;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'event_name' => Event::find($this->cart_items[0]['id'])->name ?? 'N/A',
            'email' => $this->customer?->email,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'created_at' => $this->created_at->format('j M Y h:iA'),
        ];
    }
}

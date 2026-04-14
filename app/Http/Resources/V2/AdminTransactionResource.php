<?php

namespace App\Http\Resources\V2;

use App\Models\Event;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Redis;

/** @mixin Transaction*/
class AdminTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $eventName = Redis::get("event_name_$this->id");

        if (!$eventName) {
            if ($newPurchasedTicket = $this->newPurchasedTickets->first()) {
                $eventName = ($newPurchasedTicket->ticket->event->title . ' (' . $newPurchasedTicket->ticket->name . ')') ?? 'N/A';
                Redis::set("event_name_$this->id", $eventName);
            }
        }

        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'event_name' => $eventName,
            'customer' => $this->data['customer'],
            'amount' => $this->amount,
            'gateway' => $this->gateway,
            'currency' => $this->currency,
            'status' => $this->status,
            'created_at' => $this->created_at->format('j M Y h:iA'),
        ];
    }
}

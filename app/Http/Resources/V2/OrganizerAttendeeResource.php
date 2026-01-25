<?php

namespace App\Http\Resources\V2;

use App\Models\NewPurchasedTicket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizerAttendeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'ticket_name' => $this->ticket->name,
            'checked_in' => $this->hasCheckedIn(),
        ];
    }

    private function hasCheckedIn(): bool
    {
        return $this->newPurchasedTickets()->where('used', true)->first() !== null;
    }
}

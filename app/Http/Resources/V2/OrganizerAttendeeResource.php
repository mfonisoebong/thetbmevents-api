<?php

namespace App\Http\Resources\V2;

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
            'ticket_name' => $this->ticket?->name,
            'checked_in' => $this->hasCheckedIn(),
        ];
    }

    private function hasCheckedIn(): bool
    {
        // If the controller used withExists(), Laravel will hydrate a boolean-ish attribute.
        if (array_key_exists('new_purchased_tickets_used_exists', $this->getAttributes())) {
            return (bool) $this->getAttribute('new_purchased_tickets_used_exists');
        }

        // Fallback: still avoid loading a full model.
        return $this->newPurchasedTickets()->where('used', true)->exists();
    }
}

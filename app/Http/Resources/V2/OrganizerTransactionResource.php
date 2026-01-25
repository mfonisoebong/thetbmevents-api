<?php

namespace App\Http\Resources\V2;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizerTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'items' => $this->cartItemsTicketNames($this->cart_items),
            'amount' => $this->amount,
            'status' => $this->status,
            'customer' => [
                'full_name' => $this->customer->full_name,
                'email' => $this->customer->email,
                'phone_number' => $this->customer->phone_number,
            ],
            'quantity' => $this->getTotalQuantityFromCartItems($this->cart_items),
        ];
    }

    private function cartItemsTicketNames(array $cart_items): array
    {
        $ticketNames = [];

        foreach ($cart_items as $item) {
            $ticketNames[] = Ticket::find($item['id'])->name ?? 'Unknown Ticket';
        }

        return $ticketNames;
    }

    private function getTotalQuantityFromCartItems(array $cart_items): int
    {
        $totalQuantity = 0;

        foreach ($cart_items as $item) {
            $totalQuantity += $item['quantity'] ?? 0;
        }

        return $totalQuantity;
    }
}

<?php

namespace App\Http\Resources\V2;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizerTransactionResource extends JsonResource
{
    /**
     * Simple per-request cache to avoid hitting the DB for every cart item.
     *
     * @var array<string,string>
     */
    private static array $ticketNameCache = [];

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'items' => $this->cartItemsTicketNames($this->cart_items, $request),
            'amount' => $this->amount,
            'status' => $this->status,
            'customer' => [
                'full_name' => $this->customer?->full_name,
                'email' => $this->customer?->email,
                'phone_number' => $this->customer?->phone_number,
            ],
            'quantity' => $this->getTotalQuantityFromCartItems($this->cart_items),
        ];
    }

    private function cartItemsTicketNames(array $cart_items, Request $request): array
    {
        $ticketNames = [];

        // injected by the controller: [ticketId => ticketName]
        /** @var array<string,string> $preloadedTicketNames */
        $preloadedTicketNames = (array) $request->attributes->get('ticket_names', []);

        foreach ($cart_items as $item) {
            $ticketId = $item['id'];

            if ($ticketId === '') {
                $ticketNames[] = 'Unknown Ticket';
                continue;
            }

            if (isset($preloadedTicketNames[$ticketId])) {
                $ticketNames[] = $preloadedTicketNames[$ticketId];
                continue;
            }

            if (!isset(self::$ticketNameCache[$ticketId])) {
                self::$ticketNameCache[$ticketId] = Ticket::query()
                        ->whereKey($ticketId)
                        ->value('name')
                    ?? 'Unknown Ticket';
            }

            $ticketNames[] = self::$ticketNameCache[$ticketId];
        }

        return $ticketNames;
    }

    private function getTotalQuantityFromCartItems(array $cart_items): int
    {
        $totalQuantity = 0;

        foreach ($cart_items as $item) {
            $totalQuantity += $item['quantity'];
        }

        return $totalQuantity;
    }
}

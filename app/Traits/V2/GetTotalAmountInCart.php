<?php

namespace App\Traits\V2;

use App\Models\Ticket;

trait GetTotalAmountInCart
{
    /**
     * Calculate cart total for V2 checkout.
     *
     * Supported item shapes:
     * - ["ticket_uuid", "ticket_uuid", ...] (each occurrence counts as quantity 1)
     * - [["id" => "ticket_uuid", "quantity" => 2], ...]
     * - JSON string containing either of the above shapes
     */
    public function getTotalAmount(array|string $cartItems): float
    {
        $items = is_string($cartItems)
            ? (json_decode($cartItems, true) ?: [])
            : $cartItems;

        // Case 1: list of string ticket ids
        if (!empty($items) && is_string($items[array_key_first($items)] ?? null)) {
            $counts = [];
            foreach ($items as $ticketId) {
                $id = (string)$ticketId;
                $counts[$id] = ($counts[$id] ?? 0) + 1;
            }

            $total = 0.0;
            foreach ($counts as $id => $qty) {
                $ticket = Ticket::query()->where('id', $id)->first();
                if (!$ticket) {
                    continue;
                }
                $total += $ticket->price * $qty;
            }

            return $total;
        }

        // Case 2: list of objects/arrays with id + quantity
        $total = 0.0;
        foreach ($items as $item) {
            $id = is_array($item) ? ($item['id'] ?? null) : ($item->id ?? null);
            $qty = is_array($item) ? ($item['quantity'] ?? 1) : ($item->quantity ?? 1);

            if (!$id) {
                continue;
            }

            $ticket = Ticket::where('id', $id)->first();
            if (!$ticket) {
                continue;
            }

            $total += $ticket->price * $qty;
        }

        return $total;
    }
}

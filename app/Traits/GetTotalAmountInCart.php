<?php
namespace App\Traits;

use App\Models\Ticket;


trait GetTotalAmountInCart
{
    public function getTotalAmount($cartItems)
    {
        $parsedCartItems = gettype($cartItems) === 'string' ?
            json_decode($cartItems, true) :
            json_decode(json_encode($cartItems), true);
        $totalAmount = 0;

        foreach ($parsedCartItems as $item) {
            if (gettype($item) === 'array') {
                $ticket = Ticket::where('id', '=', $item['id'])->first();
                $totalAmount = $totalAmount + (float)$ticket->price * (int)$item['quantity'];
            } else {
                $ticket = Ticket::where('id', '=', $item->id)->first();
                $totalAmount = $totalAmount + (float)$ticket->price * (int)$item->quantity;
            }

        }
        return $totalAmount;
    }
}

?>

<?php


namespace App\Http\Controllers\users;

use App\Events\InvoiceGenerated;
use App\Http\Requests\VerifyPaymentRequest;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\PurchasedTicket;
use App\Traits\GetModelIds;
use App\Traits\GetTotalAmountInCard;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers;
use App\Http\Requests\StoreCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use App\Models\Ticket;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use PHPUnit\Event\Exception;


class CartController extends Controller
{
    use HttpResponses, GetModelIds;



    public function store(StoreCartRequest $request){

        $request->validated($request->all());
        $user= $request->user();
        $ticketId= $request->ticket_id;
        $quantity= $request->quantity;

        $ticket= $this->getTicket($ticketId);

        try{
            $this->compareQuantity($ticket, $quantity);
        } catch (Exception $e){
            return $this->failed(400, null, $e->getMessage());
        }

        $cartItem= CartItem::create([
            'user_id'=> $user->id,
            'ticket_id'=> $ticketId,
            'quantity'=> $quantity
        ]);


        return $this->success(new CartItemResource($cartItem), 'MainContent item added succesfully');

    }

    public function destroy(CartItem $cartItem, Request $request){

        try {
            $this->checkCartItemOwner($cartItem, $request);
        } catch (\Exception $e){
            return $this->failed(401, null, 'Unauthorized');
        }

        $cartItem->delete();
        return $this->success(null, 'MainContent item deleted successfully');

    }

    public function destroyAll(Request $request){
        $user= $request->user();
        $cartItems= $user->cartItems ?? [];
        $cartItemsArr= json_decode($cartItems);


        $cartIds= array_map(function($item){
            return $item->id;
        }, $cartItemsArr);

        CartItem::destroy($cartIds);

        return $this->success(null, 'All cart items deleted succesfully');
    }
    public function getCartItems(Request $request){
        $cartItems= $request->user()->cartItems ?? [];

        return $this->success(CartItemResource::collection($cartItems));

    }

    public function update(CartItem $cartItem, UpdateCartRequest $request){
        $request->validated($request->all());
        $cartItem->quantity= $request->quantity;
        $cartItem->save();

        return $this->success($cartItem, 'MainContent item updated successfully');
    }
    private function getTicket($id){
        $ticket= Ticket::where('id', '=', $id)->first();
       return $ticket;
    }

    private function compareQuantity($ticket, $quantity){
        $surpassedLimit= !$ticket->unlimited && ($quantity > $ticket->quantity);

        if($surpassedLimit){
            throw new \Exception('Surpassed quantity limit');
        }

    }

    private function checkCartItemOwner(CartItem $cartItem, Request $request){
        $user= $request->user();
        $notOwner= $user->id !==$cartItem->user->id;
        if($notOwner){
            throw new \Exception('Unauthorized');
        }
    }

}

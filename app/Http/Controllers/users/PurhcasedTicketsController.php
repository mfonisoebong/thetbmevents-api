<?php

namespace App\Http\Controllers\users;

use App\Http\Resources\PurchasedTicketResource;
use App\Models\PurchasedTicket;
use App\Traits\ApiResponses;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PurhcasedTicketsController extends Controller
{
    use HttpResponses, ApiResponses;
    public function getPurhcasedTickets(Request $request){
        $user= $request->user();
        $tickets= PurchasedTicketResource::collection($user->purchasedTickets ?? []);

        return $this->success($tickets);
    }

    public function getTicket(PurchasedTicket $ticket, Request $request){
        $isNotOwner = $ticket->user_id!== $request->user()->id;
        $isAdmin= $request->user()->role==='admin';
        if($isNotOwner && !$isAdmin){
            return $this->failed(403);
        }
        $ticketResource= new PurchasedTicketResource($ticket);
        return $this->success($ticketResource);

    }

    public function getQrCode(PurchasedTicket $ticket, Request $request){
        $isNotOwner = $ticket->user_id!== $request->user()->id;
        $isAdmin= $request->user()->role==='admin';

        if($isNotOwner && !$isAdmin){
            return $this->failed(403);
        }

        $qrCodeData= json_encode([
            'id'=> $ticket->id,
            'event_id'=> $ticket->ticket->event_id,
            'quantity'=> $ticket->quantity,
            'price'=> $ticket->price
        ]);

        $qrcode= QrCode::size(200)->generate($qrCodeData);

        $dataUrl= base64_encode($qrcode);


        return response($dataUrl, 200);
    }
}

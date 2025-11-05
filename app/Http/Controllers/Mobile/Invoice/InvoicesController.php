<?php

namespace App\Http\Controllers\Mobile\Invoice;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\Invoice\TicketResource;
use App\Models\PurchasedTicket;
use App\Traits\HttpResponses;
use App\Traits\Pagination;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InvoicesController extends Controller
{
    use HttpResponses, Pagination;

    public function viewUserTickets(Request $request)
    {
        $tickets = PurchasedTicket::whereHas('invoice', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->latest()->paginate(12);

        $list = TicketResource::collection($tickets);
        $data = $this->paginatedData($tickets, $list);

        return $this->success($data);
    }

    public function viewBookingDetails(PurchasedTicket $ticket)
    {
        $qrCodeData = json_encode([
            'id' => $ticket->id,
            'event_id' => $ticket->ticket->event_id,
            'quantity' => $ticket->quantity,
            'price' => $ticket->price
        ]);
        $qrCode = QrCode::format('png')
            ->size(150)
            ->generate($qrCodeData);


        $data = [
            'booking_details' => new TicketResource($ticket),
            'qr_code' => 'data:image/png;base64,' . base64_encode($qrCode),
        ];

        return $this->success($data);
    }

    public function downloadBookingDetails(PurchasedTicket $ticket)
    {

        $qrCodeData = json_encode([
            'id' => $ticket->id,
            'event_id' => $ticket->ticket->event_id,
            'quantity' => $ticket->quantity,
            'price' => $ticket->price
        ]);

        $qrCode = QrCode::format('png')
            ->size(150)
            ->generate($qrCodeData);
        
        $pdf = Pdf::loadView('pdf.ticket', [
            'ticket' => $ticket,
            'qrCode' => 'data:image/png;base64,' . base64_encode($qrCode),
        ]);

        return $pdf->download('booking-details-' . $ticket->id . '.pdf');

    }
}

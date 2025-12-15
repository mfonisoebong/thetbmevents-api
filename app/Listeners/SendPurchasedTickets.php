<?php

namespace App\Listeners;

use App\Events\InvoiceGenerated;
use App\Mail\PurchasedTicketMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SendPurchasedTickets
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(InvoiceGenerated $event, string $email = null): void
    {
        $purchasedTickets = $event->invoice->tickets;
        $customer = $event->customer;
        $attendees = $customer->attendees;

        foreach ($attendees as $attendee) {

            $ticket = $attendee->purchasedTicket;
            $datePurchased = Carbon::parse($ticket->invoice->created_at)
                ->format('d/m/y');
            $timePurchased = Carbon::parse($ticket->invoice->created_at)
                ->format('H:i:s');
            $qrCodeData = json_encode([
                'id' => $ticket->id,
                'event_id' => $ticket->ticket->event_id,
                'quantity' => $ticket->quantity,
                'price' => $ticket->price
            ]);
            $eventLink = $ticket->ticket->event?->event_link;
            $eventLocation = $ticket->ticket->event?->location;
            $eventLocationTips = $ticket->ticket->event?->location_tips;
            $qrCode = QrCode::format('png')
                ->size(150)
                ->generate($qrCodeData);
            $ticketPath = 'tickets/' . Str::uuid()->toString() . '.png';

            Storage::disk('public')
                ->put($ticketPath, $qrCode);
            $qrCodeUrl = config('app.url') . '/storage/' . $ticketPath;


            $data = [
                'id' => $ticket->id,
                'event_title' => $ticket->ticket->event->title,
                'organizer' => $ticket->ticket->organizer->full_name,
                'event_logo' => $ticket->ticket->event->logo,
                'name' => $ticket->ticket->name . ' - ' . $ticket->ticket->event->title,
                'price' => $ticket->quantity * $ticket->ticket->price,
                'event_link' => $eventLink,
                'event_location' => $eventLocation,
                'event_location_tips' => $eventLocationTips,
                'quantity' => $ticket->quantity,
                'payment_method' => Str::upper($ticket->invoice->payment_method),
                'date_purchased' => $datePurchased,
                'time_purchased' => $timePurchased,
                'qr_code' => $qrCodeUrl
            ];

            Mail::to($email ?: $attendee->email)
                ->send(new PurchasedTicketMail($data, $ticket->attendee));


        }


    }
}

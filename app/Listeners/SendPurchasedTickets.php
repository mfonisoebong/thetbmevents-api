<?php

namespace App\Listeners;

use App\Events\TicketPurchaseCompleted;
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
    public function handle(TicketPurchaseCompleted $event): void
    {
        $customer = $event->customer;
        $attendees = $customer->attendees;

        foreach ($attendees as $attendee) {
            $tickets = $attendee->newPurchasedTickets;

            foreach ($tickets as $ticket) {
                $dateTimePurchased = Carbon::parse($ticket->transaction->updated_at)->format('d/m/y H:i:s');

                $qrCodeData = base64_encode($ticket->id);

                $eventLink = $ticket->ticket->event?->event_link;
                $eventLocation = $ticket->ticket->event?->location;
                $eventLocationTips = $ticket->ticket->event?->location_tips;

                $qrCode = QrCode::format('png')->size(200)->generate($qrCodeData);
                $ticketPath = 'tickets/' . Str::uuid()->toString() . '.png';

                Storage::disk('public')->put($ticketPath, $qrCode);
                $qrCodeUrl = config('app.url') . '/storage/' . $ticketPath;

                $data = [
                    'id' => $ticket->id,
                    'event_title' => $ticket->ticket->event->title,
                    'event_id' => $ticket->ticket->event->id,
                    'organizer' => $ticket->ticket->organizer->full_name,
                    'event_logo' => $ticket->ticket->event->logo,
                    'ticket_name' => $ticket->ticket->name,
                    'event_name' => $ticket->ticket->event->title,
                    'price' => $ticket->ticket->price,
                    'event_link' => $eventLink,
                    'event_location' => $eventLocation,
                    'event_location_tips' => $eventLocationTips,
                    'gateway' => Str::upper($ticket->transaction->gateway),
                    'date_time_purchased' => $dateTimePurchased,
                    'qr_code' => $qrCodeUrl
                ];

                Mail::to($attendee->email)->send(new PurchasedTicketMail($data, $attendee));
            }
        }
    }
}

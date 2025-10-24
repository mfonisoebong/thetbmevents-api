<?php

namespace App\Http\Controllers\Mobile\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use League\Csv\Writer;

class EventsController extends Controller
{
    public function exportCsv(Request $request)
    {

        $user = $request->user();

        $events = $user->events;


        try {
            $csv = Writer::createFromString('');

            $csv->insertOne([
                'Title',
                'Logo',
                'Ticket price',
                'Event type',
                'Commence date',
                'Commence time',
                'End date',
                'End time',
                'Timezone',
            ]);

            foreach ($events as $event) {
                $csv->insertOne([
                    $event->title,
                    $event->logo,
                    $event->tickets[0]->price,
                    $event->type,
                    $event->commence_date,
                    $event->commence_time,
                    $event->end_date,
                    $event->end_time,
                    $event->timezone,
                ]);
            }

            $date = now()->toString();
            $filename = 'events_' . $date . 'csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            // Convert the CSV to a string
            $csvString = $csv->getContent();
            return response($csvString, 200, $headers);
        } catch (\Exception $e) {
            return $this->failed(500, null, $e->getMessage());
        }
    }

    public function exportAttendeesCsv(Event $event)
    {
        try {
            $attendees = $this->getEventattendees($event);
            $csv = Writer::createFromString('');

            $csv->insertOne([
                'Attendee',
                'Customer',
                'Customer email',
                'Customer phone',
                'Ticket ID',
                'Ticket name',
                'Event title',
            ]);

            foreach ($attendees as $attendee) {
                $csv->insertOne([
                    $attendee->customer,
                    $attendee->customer_email,
                    $attendee->customer_phone,
                    $attendee->purchased_ticket_id,
                    $attendee->ticket_name,
                    $attendee->event_title,
                ]);
            }
            $date = now()->toString();
            $filename = 'attendees_' . $event->title . '_' . $date . 'csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            // Convert the CSV to a string
            $csvString = $csv->getContent();
            return response($csvString, 200, $headers);
        } catch (\Exception $e) {
            return $this->failed(500, null, $e->getMessage());
        }
    }

}

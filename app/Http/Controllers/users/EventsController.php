<?php

namespace App\Http\Controllers\users;

use App\Http\Requests\SendBlastEmailRequest;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventListResource;
use App\Http\Resources\EventsResource;
use App\Http\Resources\TicketResource;
use App\Mail\BlastMail;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Ticket;
use App\Traits\HttpResponses;
use App\Traits\StoreImage;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use League\Csv\Writer;
use PHPUnit\Exception;
use Stevebauman\Location\Facades\Location;

class EventsController extends Controller
{

    use HttpResponses, StoreImage;


    public function getLatestEvents()
    {
        $topEvents = Event::where('attendees', '>=', 400)->limit(3)->get();
        $freeEvents = Event::where('categories', 'like', '%free%')->limit(8)->get();
        $paidEvents = Event::where('categories', 'like', '%paid%')->limit(8)->get();
        $onlineEvents = Event::where('categories', 'like', '%online%')->limit(8)->get();
        $latestEvents = Event::latest()->limit(12)->get();
        return $this->success([
            'top_events' => EventsResource::collection($topEvents),
            'latest_events' => EventsResource::collection($latestEvents),
            'popular' => [
                'free' => EventsResource::collection($freeEvents),
                'paid' => EventsResource::collection($paidEvents),
                'online' => EventsResource::collection($onlineEvents),
            ],

        ]);
    }

    public function filterEvents(Request $request)
    {
        $requiredFields = ['search', 'category', 'location', 'date'];
        $hasFilter = Arr::hasAny($request->all(), $requiredFields);
        if (!$hasFilter) {
            return $this->success([]);
        }

        $events = Event::latest()->filter()->paginate(10);
        return $this->success([
            'events' => EventsResource::collection($events),
            'perPage' => $events->perPage(),
            'currentPage' => $events->currentPage(),
            'total' => $events->total(),
            'lastPage' => $events->lastPage(),
        ]);
    }

    public function getEventsInCategory(Request $request)
    {

        $category = $request->get('category') ?? null;
        $exludeEventId = $request->get('exclude') ?? null;

        if (!$category) {
            return $this->failed(400, [], 'No category was provided');
        }

        $eventsQuery = Event::where('categories', 'like', $category)
            ->limit(20);

        if (!$exludeEventId) {
            $events = $eventsQuery->get();
            return $this->success($events);
        }

        $events = $eventsQuery
            ->where('id', '!=', $exludeEventId)
            ->get();
        return $this->success($events);
    }

    public function getCategories()
    {
        $categories = EventCategory::all(['category', 'id']);

        return $this->success($categories);
    }

    public function getEventsByLocation(Request $request)
    {
        $ip = App::environment('production') ? $request->ip() : '41.203.78.171';
        $userInfo = Location::get($ip);
        $userCountry = $userInfo ? $userInfo->countryName : "";
        $topEventsInCountry = Event::where('location', 'like', '%' . $userCountry . '%')->limit(8)->get();

        return $this->success([
            'city' => [],
            'country' => EventsResource::collection($topEventsInCountry),
            'user_info' => [
                'city' => '',
                'country' => $userCountry
            ]
        ]);
    }

    public function getUserEvent(Event $event, Request $request)
    {
        try {
            $this->checkEventAuth($event, $request);

            $event->undisclose_location = $event->undisclose_location ? true : false;
            return $this->success(
                ['event' => $event, 'tickets' => TicketResource::collection($event->tickets)],
                null
            );
        } catch (Exception $e) {
            return $this
                ->failed(401, null, $e->getMessage());
        }
    }

    public function getEventsSlugs()
    {
        error_log('Here');
        $events = Event::all(['alias', 'id']);
        return $this->success($events);
    }

    public function getEvent($alias)
    {
        $event = Event::where('alias', $alias)
            ->first();

        if (!$event) {
            return $this->failed(404);
        }

        $event->undisclose_location = $event->undisclose_location ? true : false;
        return $this->success(['event' => $event, 'tickets' => TicketResource::collection($event->tickets)], null);
    }

    public function getUserEvents(Request $request)
    {

        $user = $request->user();
        $events = EventListResource::collection($user->events);

        return $this->success(['events' => $events]);
    }

    public function store(StoreEventRequest $request)
    {

        $user = $request->user();

        DB::beginTransaction();

        try {
            $logoFilepath = 'storage/events-logos/' . Str::uuid()->toString() . '.webp';

            $this->storeImage($logoFilepath, null, $request->file('logo'));
            $event = Event::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'event_date' => $request->event_date,
                'event_time' => Str::length($request->event_time ?? "") === 0 ? null : $request->event_time,
                'location_tips' => $request->location_tips,
                'timezone' => $request->timezone,
                'currency' => $request->currency,
                'event_link' => $request->event_link ?? null,
                'categories' => $request->categories,
                'location' => $request->location,
                'logo' => $logoFilepath,
                'type' => $request->type,
                'undisclose_location' => $request->undisclose_location === 'true' ? true : false,
                'links_instagram' => $request->links_instagram ?? null,
                'links_twitter' => $request->links_twitter ?? null,
                'links_facebook' => $request->links_facebook ?? null,
            ]);
            foreach ($request->tickets as $ticket) {
                Ticket::create([
                    'event_id' => $event->id,
                    'name' => $ticket['name'],
                    'price' => (float) $ticket['price'],
                    'unlimited' => $ticket['unlimited'] === "true" ? true : false,
                    'quantity' => (int) $ticket['quantity'],
                    'selling_start_date_time' => $ticket['selling_start_date_time'],
                    'selling_end_date_time' => $ticket['selling_end_date_time'],
                    'description' => $ticket['description'] ?? null,
                    'organizer_id' => $user->id
                ]);
            }
            DB::commit();
            $event->refresh();
            return $this->success([
                'event' => $event
            ], 'Event created successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->failed(500, null, $e->getMessage());
        }
    }

    public function update(Event $event, UpdateEventRequest $request)
    {
        DB::beginTransaction();

        try {

            $uploadedEventImage = $request->hasFile('logo');
            if ($uploadedEventImage) {
                $logoFilepath = 'storage/events-logos/' . Str::uuid()->toString() . '.webp';
                $this->storeImage($logoFilepath, $event->logo, $request->file('logo'));
                $event->update([
                    ...$request->except(['logo', 'event_time']),
                    'event_time' => Str::length($request->event_time ?? "") === 0 ? null : $request->event_time,

                    ...($request->type === 'physical' ? [
                        'location' => $request->location,
                        'event_link' => null,
                    ] : [
                        'location' => null,
                        'event_link' => $request->event_link,
                        'location_tips' => null,
                    ]),
                    'logo' => $logoFilepath,
                    'undisclose_location' => $request->undisclose_location === 'true' ? true : false,
                ]);
            } else {
                $event->update([
                    ...$request->except(['logo', 'event_time']),
                    'event_time' => Str::length($request->event_time ?? "") === 0 ? null : $request->event_time,

                    ...($request->type === 'physical' ? [
                        'location' => $request->location,
                        'event_link' => null,
                    ] : [
                        'location' => null,
                        'event_link' => $request->event_link,
                        'location_tips' => null,
                    ]),
                    'undisclose_location' => $request->undisclose_location === 'true' ? true : false,
                ]);
            }


            foreach ($request->tickets as $ticket) {
                $oldTicketId = $ticket['id'] ?? null;


                $oldTicket = Ticket::where('id', $oldTicketId)
                    ->first();

                if (!$oldTicket) {
                    Ticket::create([
                        'event_id' => $event->id,
                        'name' => $ticket['name'],
                        'price' => (float) $ticket['price'],
                        'unlimited' => $ticket['unlimited'] === "true" ? true : false,
                        'quantity' => (int) $ticket['quantity'],
                        'selling_start_date_time' => $ticket['selling_start_date_time'],
                        'selling_end_date_time' => $ticket['selling_end_date_time'],
                        'description' => $ticket['description'] ?? null,
                        'organizer_id' => $request->user()->id
                    ]);
                }
            }

            DB::commit();

            return $this->success(['event' => $event], 'Event updated successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this
                ->failed(500, null, $e->getMessage());
        }
    }

    public function destroy(Event $event, Request $request)
    {

        try {
            $this->checkEventAuth($event, $request);
            $event->delete();
            File::delete(public_path($event->logo));
        } catch (Exception $e) {
            return $this
                ->failed(401, null, $e->getMessage());
        }


        return $this
            ->success(null, 'Event has been deleted successfully');
    }

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

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="example.csv"',
            ];

            // Convert the CSV to a string
            $csvString = $csv->getContent();
            return response($csvString, 200, $headers);
        } catch (Exception $e) {
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

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="example.csv"',
            ];

            // Convert the CSV to a string
            $csvString = $csv->getContent();
            return response($csvString, 200, $headers);
        } catch (Exception $e) {
            return $this->failed(500, null, $e->getMessage());
        }
    }

    public function sendBlastEmail(SendBlastEmailRequest $request)
    {
        $events = Event::whereIn('id', $request->event_ids)->get();

        foreach ($events as $event) {
            $attendees = $this->getEventattendees($event);
            $emails = array_map(fn($attendee) => $attendee->customer_email, $attendees);
            if (count($emails) === 0) {
                continue;
            }
            Mail::to($emails, 'Organizer')
                ->send(new BlastMail($request->subject, $request->email_content));
        }

        return $this->success(null, "Blast email send successfully");
    }

    private function getEventattendees(Event $event)
    {
        $attendees = DB::select('
        SELECT
    purchased_tickets.id as purchased_ticket_id,
    CONCAT(
     customers.first_name,
        " ",
    customers.last_name
    ) AS customer,

    customers.email AS customer_email,
    CONCAT(
        customers.phone_dial_code,
        " ",
        customers.phone_number
    ) AS customer_phone,
    tickets.id AS ticket_id,
    tickets.name AS ticket_name,
    events.title AS event_title
FROM
    sales
INNER JOIN events ON sales.event_id = events.id
INNER JOIN invoices ON sales.invoice_id = invoices.id
INNER JOIN purchased_tickets ON invoices.id = purchased_tickets.invoice_id
INNER JOIN customers ON sales.customer_id = customers.id
INNER JOIN tickets ON sales.ticket_id = tickets.id

WHERE events.id = :event_id
        ', [
            'event_id' => $event->id
        ]);

        return $attendees;
    }

    private function checkEventAuth(Event $event, Request $request)
    {
        $user = $request->user();
        $notOwner = $user->id !== $event->user->id;
        if ($notOwner) {
            throw new \Exception('Unauthorized');
        }
    }
}

<?php

namespace App\Http\Controllers\Mobile\Event;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\Event\EventListResource;
use App\Http\Resources\TicketResource;
use App\Models\Event;
use App\Models\Like;
use App\Models\PurchasedTicket;
use App\Traits\HttpResponses;
use App\Traits\Pagination;
use Illuminate\Http\Request;
use League\Csv\Writer;
use Stevebauman\Location\Facades\Location;

class EventsController extends Controller
{
    use Pagination, HttpResponses;

    // Radius in kilometers
    private const HARVESINE_RADIUS = 100;
    private const HARVESINE = '*, ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance';

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

    public function getFeaturedEvents()
    {
        $featuredEvents = Event::where('is_featured', true)->paginate(10);
        $list = EventListResource::collection($featuredEvents);
        $data = $this->paginatedData($featuredEvents, $list);

        return $this->success($data);
    }

    public function getPopularEvents()
    {
        $popularEvents = Event::withCount('sales')
            ->orderByDesc('sales_count')
            ->paginate(10);
        $list = EventListResource::collection($popularEvents);
        $data = $this->paginatedData($popularEvents, $list);

        return $this->success($data);
    }

    public function getRecommendations(Request $request)
    {
        $userInfo = $this->getUserInfo($request);

        $events = Event::query();

        if ($userInfo && $userInfo->latitude && $userInfo->longitude) {
            $userLat = $userInfo->latitude;
            $userLon = $userInfo->longitude;

            // Haversine formula to calculate distance and find events within the radius.
            $events->selectRaw(
                self::HARVESINE,
                [$userLat, $userLon, $userLat]
            )
                ->whereNotNull(['latitude', 'longitude'])
                ->having('distance', '<', self::HARVESINE_RADIUS)
                ->orderBy('distance');
        } else {
            // Fallback for when location is not available
            $events->latest();
        }

        $paginatedData = $events->inRandomOrder()->paginate(10);


        if (!$paginatedData->total()) {
            // If no events are found nearby, get random recent events as a fallback.
            $events = Event::inRandomOrder()->latest();
            $paginatedData = $events->paginate(10);
        }

        $list = EventListResource::collection($paginatedData);
        $data = $this->paginatedData($paginatedData, $list);

        return $this->success($data);
    }

    public function getUserEvents(Request $request)
    {
        $user = $request->user();
        $events = $user->events()->latest()->paginate(10);
        $list = EventListResource::collection($events);
        $data = $this->paginatedData($events, $list);

        return $this->success($data);
    }

    public function getUserRecommendations(Request $request)
    {
        $user = $request->user();
        $userInfo = $this->getUserInfo($request);
        $userPreferences = $user->preferences->map(fn($item) => $item->category->category)->toArray();


        $events = Event::query();

        $events->where('categories', 'like', '%' . implode(',', $userPreferences) . '%');

        // Add location to events builder
        if ($userInfo && $userInfo->latitude && $userInfo->longitude) {
            $userLat = $userInfo->latitude;
            $userLon = $userInfo->longitude;

            // Haversine formula to calculate distance and find events within the radius.
            $events->selectRaw(
                self::HARVESINE,
                [$userLat, $userLon, $userLat]
            )
                ->whereNotNull(['latitude', 'longitude'])
                ->having('distance', '<', self::HARVESINE_RADIUS)
                ->orderBy('distance');
        } else {
            // Fallback for when location is not available
            $events->latest();
        }

        // Add previously bought event categories to builder
        $purchasedTicketsCategories = PurchasedTicket::whereHas('invoice', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->latest()
            ->take(12)
            ->get()
            ->map(fn($item) => $item->ticket->event->categories)
            ->toArray();


        $events->orWhere(
            'categories',
            'like',
            '%' . implode(',', $purchasedTicketsCategories) . '%'
        );
        $paginatedData = $events->paginate(10);

        if (!$paginatedData->total()) {
            $events = Event::inRandomOrder()->latest();
            $paginatedData = $events->paginate(10);
        }

        $list = EventListResource::collection($paginatedData);
        $data = $this->paginatedData($paginatedData, $list);

        return $this->success($data);
    }

    public function viewAll(Request $request)
    {
        $perPage = $request->get('per_page') ?? '10';

        $events = Event::latest()->filter()->paginate((int) $perPage);
        $list = EventListResource::collection($events);
        $data = $this->paginatedData($events, $list);

        return $this->success($data);
    }

    public function view(Event $event)
    {
        $eventData = [
            'event' => [
                'title' => $event->title,
                'event_date' => $event->event_date,
                'event_time' => $event->event_time,
                'description' => $event->description,
                'categories' => $event->categories,
                'location' => $event->location,
                'logo' => $event->logo,
                'type' => $event->type,
                'event_link' => $event->event_link,
                'links_instagram' => $event->links_instagram,
                'links_twitter' => $event->links_twitter,
                'links_facebook' => $event->links_facebook,
                'links_instagram_formatted' => $event->links_instagram ? 'https://www.instagram.com/' . ltrim($event->links_instagram, '@') : null,
                'links_twitter_formatted' => $event->links_twitter ? 'https://x.com/' . ltrim($event->links_twitter, '@') : null,
                'links_facebook_formatted' => $event->links_facebook ? 'https://www.facebook.com/' . ltrim($event->links_facebook, '@') : null,
                'timezone' => $event->timezone,
                'undisclose_location' => $event->undisclose_location,
                'alias' => $event->alias,
                'location_tips' => $event->location_tips,
                'is_featured' => $event->is_featured,
                'longitude' => $event->longitude,
                'latitude' => $event->latitude,
                'organizer' => [
                    'business_name' => $event->user->buisness_name,
                    'avatar' => $event->user->avatar,
                    'is_verified' => $event->user->account_state === 'active'
                ]
            ],
            'tickets' => TicketResource::collection($event->tickets)
        ];

        return $this->success($eventData);
    }

    public function toggleLike(Event $event, Request $request)
    {
        $like = Like::where('user_id', $request->user()->id)
            ->where('event_id', $event->id)
            ->first();

        if ($like) {
            $like->delete();
            return $this->success(null, 'Like removed successfully');
        }
        Like::create([
            'user_id' => $request->user()->id,
            'event_id' => $event->id
        ]);
        return $this->success(null, 'Liked successfully');
    }

    private function getUserInfo(Request $request)
    {
        $ip = config('app.env') === 'production' ? $request->ip() : config('app.test_ip');
        return Location::get($ip);

    }

}

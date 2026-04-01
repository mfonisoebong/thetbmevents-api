<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\EventResource;
use App\Models\Event;

class EventController extends Controller
{
    public function listRecentEvents()
    {
        $events = Event::where('status', 'published')
            ->with(['user', 'tickets'])
            ->orderByDesc('created_at')
            ->take(50)
            ->get();

        return $this->success(EventResource::collection($events), 'Recent events fetched successfully');
    }

    public function listRecentEventsByCategory(string $category)
    {
        $events = Event::where('category', 'like', "%$category%")
            ->where('status', 'published')
            ->with(['user', 'tickets'])
            ->orderByDesc('created_at')
            ->take(50)
            ->get();

        return $this->success(EventResource::collection($events), 'Recent events in category fetched successfully');
    }

    public function getEventDetails(string $event)
    {
        $event = Event::where('id', $event)->orWhere('slug', $event)->firstOrFail();

        return $this->success(new EventResource($event), 'Event details fetched successfully');
    }
}

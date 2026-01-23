<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\EventResource;
use App\Models\Event;

class EventController extends Controller
{
    public function listRecentEvents()
    {
        // list most recent 24 events
        $events = Event::orderBy('created_at', 'desc')->take(24)->get();

        return $this->success(EventResource::collection($events), 'Recent events fetched successfully');
    }

    public function listRecentEventsByCategory(string $category)
    {
        // list most recent 24 events by category
        $events = Event::where('category', 'like', "%$category%")->orderBy('created_at', 'desc')->take(24)->get();

        return $this->success(EventResource::collection($events), 'Recent events in category fetched successfully');
    }
}

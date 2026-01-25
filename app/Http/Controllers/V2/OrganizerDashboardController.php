<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\EventWithStatsResource;
use App\Models\Event;

class OrganizerDashboardController extends Controller
{

    public function overview()
    {
        return $this->success(EventWithStatsResource::collection(Event::where('user_id', auth()->id())->get()));
    }
}

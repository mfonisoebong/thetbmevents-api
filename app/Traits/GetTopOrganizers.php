<?php

namespace App\Traits;

use App\Models\Event;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use function App\Http\Controllers\admin\isSameOrganizer;

trait GetTopOrganizers
{
    public function computeTopOrganizers(): array
    {
        $topEvents = Sale::filter()
            ->select('event_id', DB::raw('SUM(tickets_bought) as tickets_sold'))
            ->groupBy('event_id')
            ->orderByDesc('tickets_sold')
            ->limit(10)
            ->get()
            ->toArray();


        $topOrganizersList = array_map(function ($event) {
            $organizerEvent = Event::where('id', $event['event_id'])
                ->first();
            return [
                'title' => $organizerEvent->title,
                'organizer' => $organizerEvent->user->business_name,
                'avatar' => $organizerEvent->user->avatar,
                'email' => $organizerEvent->user->email,
                'tickets_sold' => $event['tickets_sold'],
                'id' => $organizerEvent->user->id
            ];

        }, $topEvents);

        $topOrganizersList = array_values(array_filter($topOrganizersList, function ($organizer, $index) use ($topOrganizersList, $topEvents) {
            for ($i = 0; $i < $index; $i++) {
                if ($this->isSameOrganizer($organizer, $topOrganizersList[$i])) {
                    return false;
                }
            }
            return true;
        }, ARRAY_FILTER_USE_BOTH));

        return $topOrganizersList;
    }

    private function isSameOrganizer($organizer1, $organizer2)
    {
        return $organizer1['id'] === $organizer2['id'];
    }
}

<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\CreateEventRequest;
use App\Http\Resources\V2\EventResource;
use App\Models\Event;
use App\Models\Ticket;
use App\Traits\StoreImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrganizerEventController extends Controller
{
    use StoreImage;

    public function index()
    {
        $myEvents = Event::where('user_id', auth()->id())->get();
        return $this->success(EventResource::collection($myEvents));
    }

    public function createEvent(CreateEventRequest $request)
    {
        $user = $request->user();

        DB::beginTransaction();
        try {
            if ($request->has('image_url')) {
                $logoFilepath = $request->input('image_url');
            } else {
                $logoFilepath = 'storage/events-logos/' . Str::uuid()->toString() . '.webp';

                $this->storeImage($logoFilepath, null, $request->file('image'));
            }

            $event = Event::create([
                'user_id' => $user->id,
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'category' => $request->input('category'),
                'type' => $request->input('type'),
                'tags' => $request->input('tags', []),
                'event_date' => $request->input('date'),
                'event_time' => $request->input('time'),
                'timezone' => $request->input('timezone'),
                'location' => $request->input('location'),
                'event_link' => $request->input('virtual_link'),
                'image_url' => $logoFilepath,
                'undisclose_location' => $request->input('undisclosed'),
            ]);

            if (empty($request->tickets) || !is_array($request->tickets)) {
                throw new \Exception('At least one ticket type is required');
            }

            foreach ($request->tickets as $ticket) {
                $ticket['start_selling_date'] = date('Y-m-d H:i:s', strtotime($ticket['start_selling_date']));
                $ticket['end_selling_date'] = date('Y-m-d H:i:s', strtotime($ticket['end_selling_date']));

                Ticket::create([
                    'event_id' => $event->id,
                    'organizer_id' => $user->id,
                    'name' => $ticket['name'],
                    'price' => $ticket['price'],
                    'quantity' => $ticket['quantity'],
                    'selling_start_date_time' => $ticket['start_selling_date'],
                    'selling_end_date_time' => $ticket['end_selling_date'],
                    'description' => $ticket['description'] ?? null,
                    'currency' => $ticket['currency'] ?? 'NGN'
                ]);
            }
            DB::commit();
            return $this->success(null, 'Event created successfully');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), 500);
        }
    }

    public function updateEvent(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
//            'category' => 'required|string|max:100',
            'date' => 'required|date|after:now',
            'time' => 'required',
//            'location' => 'required|string|max:255',
//            'virtual_link' => 'required|url|max:255',
//            'undisclosed' => 'required|boolean',
        ]);

        $event->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'event_date' => $request->input('date'),
            'event_time' => $request->input('time'),
        ]);

        return $this->success(null, 'Event updated successfully');
    }
}

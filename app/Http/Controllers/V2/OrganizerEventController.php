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
        $myEvents = auth()->user()->events()->with('tickets')->latest()->get();
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
            return $this->error($e->getMessage() . $e->getTraceAsString(), 500);
        }
    }

    public function updateEvent(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date|after:now',
            'time' => 'required',
            'type' => 'required|string|in:physical,virtual',
            'category' => 'required|string|max:100',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:100',
            'image_url' => 'nullable|string',
            'image' => 'nullable|image',
            'location' => 'required_if:type,physical|string|max:255',
            'virtual_link' => 'required_if:type,virtual|url|max:255',
            'undisclosed' => 'required|boolean'
        ]);

        DB::beginTransaction();
        try {
            $oldImageToDelete = null;
            $newUploadedImagePath = null;

            $data = [
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'event_date' => $request->input('date'),
                'event_time' => $request->input('time'),
                'type' => $request->input('type'),
                'category' => $request->input('category'),
                'tags' => $request->input('tags', []),
            ];

            if ($request->input('type') === 'virtual') {
                $data['event_link'] = $request->input('virtual_link');
                $data['location'] = null;
            } else {
                $data['location'] = $request->input('location');
                $data['event_link'] = null;
            }

            if ($request->has('image_url')) {
                $data['image_url'] = $request->input('image_url');
            } elseif ($request->hasFile('image')) {
                $newUploadedImagePath = 'storage/events-logos/' . Str::uuid()->toString() . '.webp';
                $this->storeImage($newUploadedImagePath, null, $request->file('image')); // we are not deleting the old image here so we can rollback if the update fails
                $data['image_url'] = $newUploadedImagePath;
                $oldImageToDelete = $event->getRawOriginal('image_url');
            }

            $event->update($data);

            $event->undisclose_location = $request->input('undisclosed');
            $event->save();

            DB::commit();

            if ($oldImageToDelete) {
                $this->removeFile($oldImageToDelete);
            }

            return $this->success(null, 'Event updated successfully');
        } catch (\Throwable $e) {
            DB::rollBack();

            if (!empty($newUploadedImagePath)) {
                $this->removeFile($newUploadedImagePath);
            }

            return $this->error($e->getMessage(), 500);
        }
    }

    public function deleteEvent(Event $event)
    {
        $this->authorize('delete', $event);

        $this->removeFile($event->image_url);

        $event->delete();

        return $this->success(null, 'Event deleted successfully');
    }
}

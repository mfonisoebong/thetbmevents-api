<?php

namespace App\Jobs;

use App\Mail\BlastMailV2;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBlastEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $subject, public string $content, public string $eventId)
    {
        $this->onQueue('emails'); // default tho
    }

    public function handle(): void
    {
        $event = Event::findOrFail($this->eventId);
        $organizerName = $event->user->business_name ?? 'Organizer';

        $attendeeEmails = Attendee::whereHas('ticket', function ($query) use ($event) {
            $query->where('event_id', $event->id);
        })->pluck('email')->unique();

        foreach ($attendeeEmails as $email) {
            Mail::to($email)->send(new BlastMailV2($this->subject, $this->content, eventName: $event->title, organizerName: $organizerName));
        }
    }
}

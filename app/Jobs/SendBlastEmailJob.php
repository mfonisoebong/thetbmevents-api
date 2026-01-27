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

    /**
     * @param  array<int, string>|null  $recipientEmails
     */
    public function __construct(
        public string $subject,
        public string $content,
        public string $eventId,
        public ?array $recipientEmails = null,
    ) {
        $this->onQueue('emails');
    }

    public function handle(): void
    {
        $event = Event::findOrFail($this->eventId);
        $organizerName = $event->user->business_name ?? 'Organizer';

        $attendeeEmails = collect($this->recipientEmails)
            ->filter()
            ->unique()
            ->values();

        if ($attendeeEmails->isEmpty()) {
            $attendeeEmails = Attendee::whereHas('ticket', function ($query) use ($event) {
                $query->where('event_id', $event->id);
            })->pluck('email')->unique()->values();
        }

        foreach ($attendeeEmails as $email) {
            Mail::to($email)->send(new BlastMailV2(
                $this->subject,
                $this->content,
                eventName: $event->title,
                organizerName: $organizerName
            ));
        }
    }
}

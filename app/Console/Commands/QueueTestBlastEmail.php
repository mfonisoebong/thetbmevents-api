<?php

namespace App\Console\Commands;

use App\Jobs\SendBlastEmailJob;
use Illuminate\Console\Command;

class QueueTestBlastEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage:
     *  php artisan queue:test-blast-email --event-id=123
     */
    protected $signature = 'queue:test-blast-email
                            {--event-id= : Event ID used for mail template context (required)}
                            {--subject=Queue Test Blast Email : Subject for the blast email}
                            {--content=<p>This is a queued test blast email.</p> : HTML content for the blast email}';

    /**
     * The console command description.
     */
    protected $description = 'Dispatch SendBlastEmailJob to the emails queue to verify queue workers are processing jobs.';

    public function handle(): int
    {
        $eventId = (string) ($this->option('event-id') ?? '');

        if ($eventId === '') {
            $this->error('Missing required option: --event-id');
            $this->line('Example: php artisan queue:test-blast-email --event-id=1');
            return self::FAILURE;
        }

        $subject = (string) $this->option('subject');
        $content = (string) $this->option('content');

        $recipients = [
            'ajayimarvellous777@gmail.com',
            'admin@thetbmevents.com',
            'freah235@yahoo.com',
        ];

        SendBlastEmailJob::dispatch($subject, $content, $eventId, $recipients);

        $this->info('Dispatched SendBlastEmailJob to queue "emails" for 3 test recipients.');
        $this->line('Next: run a worker to process it, e.g. `php artisan queue:work --queue=emails`');

        return self::SUCCESS;
    }
}

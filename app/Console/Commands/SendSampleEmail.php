<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\SampleMail;
use Exception;

class SendSampleEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:sample-email {--queue : Queue the email instead of sending it immediately}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a sample email to myself to test SMTP credentials';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recipient = 'ajayimarvellous777@gmail.com';

        $this->info("Preparing to send a test email to {$recipient}");

        try {
            $mail = new SampleMail();

            if ($this->option('queue')) {
                Mail::to($recipient)->queue($mail);
                $this->info('Mail queued. Run `php artisan queue:work` to process the job.');
            } else {
                Mail::to($recipient)->send($mail);
                $this->info('Mail sent synchronously. Check the recipient inbox or mail logs.');
            }

            return 0;
        } catch (Exception $e) {
            $this->error('Failed to send mail: ' . $e->getMessage());
            logger()->error('send:sample-email error', ['exception' => $e]);
            return 1;
        }
    }
}


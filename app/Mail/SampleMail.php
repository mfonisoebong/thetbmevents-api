<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SampleMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The body text for the email.
     *
     * @var string
     */
    public $body;

    /**
     * Create a new message instance.
     *
     * @param string|null $body
     */
    public function __construct(?string $body = null)
    {
        $this->body = $body ?? 'This is a test email to verify SMTP settings.';
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('SMTP Test Email')
                    ->view('emails.sample')
                    ->with(['body' => $this->body]);
    }
}


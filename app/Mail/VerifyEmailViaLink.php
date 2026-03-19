<?php

namespace App\Mail;

use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyEmailViaLink extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public User $user, public string $hash)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            // to: $this->user->email,
            subject: 'Verify your Email',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $verifyUrl = config('app.client_url') . '/verify-email/' . rawurlencode($this->hash);

        return new Content(
            view: 'emails.verify-email-link',
            with: [
                'user' => $this->user,
                'hash' => $this->hash,
                'verifyUrl' => $verifyUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeUser extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public User $user)
    {
    }

    /**
     * Get the message envelope.
     */
        public function envelope(): Envelope
    {
        return new Envelope(
            to: $this->user->email,
            subject: 'Welcome to TBM - Your Journey Begins Here!',
        );
    }

    /**
     * Get the message content definition.
     */
        public function content(): Content
    {
        return new Content(
            view: $this->user->role === 'organizer' ? 'emails.welcome_organizer' : 'emails.welcome_customer',
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

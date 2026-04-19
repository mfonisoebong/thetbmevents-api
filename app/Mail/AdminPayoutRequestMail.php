<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminPayoutRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $organizer,
        public string $bankName,
        public string $accountName,
        public string $accountNumber
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Organizer payout request'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-payout-request'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}


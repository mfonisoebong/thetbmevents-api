<?php

namespace App\Mail;

use App\Models\Payout;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayoutUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Payout $payout, public ?string $reason = null)
    {
    }

    public function envelope(): Envelope
    {
        $subjectMap = [
            'pending' => 'Your payout request is pending approval',
            'approved' => 'Your payout request has been approved',
            'declined' => 'Your payout request has been declined',
            'paid' => 'Your payout request has been paid'
        ];

        return new Envelope(
            subject: $subjectMap[$this->payout->status],
        );
    }

    public function content(): Content
    {

        return new Content(
            view: 'emails.payout-updated',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

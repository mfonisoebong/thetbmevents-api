<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $pdfPath;
    /**
     * Create a new message instance.
     */
    public function __construct(public Transaction $invoice, $pdfPath)
    {
        $this->pdfPath= $pdfPath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'TBM Tickets Transaction',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $date= now();
        $pdfName= 'invoice_'.$date.'pdf';
        return [
            Attachment::fromPath($this->pdfPath)
                ->as($pdfName)
                ->withMime('application/pdf')
        ];
    }
}

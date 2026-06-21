<?php

namespace App\Mail;

use App\Models\Admission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdmissionRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Admission $admission,
        public ?string $reason = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'CMPI - Admission Application Update',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admission-rejected',
        );
    }
}

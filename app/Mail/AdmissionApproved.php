<?php

namespace App\Mail;

use App\Models\Admission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdmissionApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Admission $admission,
        public ?string $studentId = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'CMPI - Your Admission Has Been Approved!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admission-approved',
        );
    }
}

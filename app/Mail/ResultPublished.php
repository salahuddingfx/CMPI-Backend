<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResultPublished extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $studentName,
        public string $studentEmail,
        public string $semester,
        public float $sgpa
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'CMPI - New Results Published!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.result-published',
        );
    }
}

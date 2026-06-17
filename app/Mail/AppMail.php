<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $mailSubject,   // 'subject' se rename kiya
        public string $title,
        public array $lines = [],
        public ?array $action = null,
        public string $type = 'info',
        public ?string $footer = null,
        public array $attachments_list = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailSubject,   // yahan use karo
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.generic',
        );
    }

    public function attachments(): array
    {
        return $this->attachments_list;
    }
}
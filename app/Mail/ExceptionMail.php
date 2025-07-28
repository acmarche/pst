<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExceptionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private readonly string $htmlString)
    {

    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'A critical error occurred'
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->htmlString
        );
    }

}

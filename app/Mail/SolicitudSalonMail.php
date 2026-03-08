<?php

namespace App\Mail;

use App\Models\Schedule;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SolicitudSalonMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Schedule $schedule)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Solicitud de salón registrada',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.solicitud-salon-html',
            with: [
                's' => $this->schedule,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

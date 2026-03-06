<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Schedule;

class SolicitudSalonActualizadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public Schedule $schedule;

    /**
     * Create a new message instance.
     */
    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
    }
 public function build()
    {
        return $this->subject('Actualización de solicitud de salón')
            ->markdown('emails.solicitud-salon-actualizada')
            ->with([
                's' => $this->schedule, // ✅ ESTA LÍNEA ES LA CLAVE
            ]);
    }
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Solicitud Salon Actualizada Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.solicitud-salon-actualizada',
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

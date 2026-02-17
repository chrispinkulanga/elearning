<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Alumni;

class AlumniInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $alumni;
    public $message;

    /**
     * Create a new message instance.
     */
    public function __construct(Alumni $alumni, $message = null)
    {
        $this->alumni = $alumni;
        $this->message = $message;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitation to Join Our Alumni Network',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.alumni-invitation',
            with: [
                'alumni' => $this->alumni,
                'message' => $this->message,
                'invitationUrl' => url('/alumni/invitation/' . $this->alumni->id),
            ]
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

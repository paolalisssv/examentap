<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordRecoveryMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $name,
        public readonly string $temporaryPassword,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recuperación de contraseña - ExamenTAP',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-recovery',
            with: [
                'name' => $this->name,
                'temporaryPassword' => $this->temporaryPassword,
            ],
        );
    }
}

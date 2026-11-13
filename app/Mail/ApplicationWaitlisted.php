<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ApplicationWaitlisted extends Mailable
{
    public function __construct(public Application $application)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre candidature est sur liste d’attente — GCD+');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.application-waitlisted');
    }
}

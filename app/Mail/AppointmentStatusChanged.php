<?php

namespace App\Mail;

use App\Models\AppointmentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900];

    public function __construct(public AppointmentRequest $appointment) { $this->afterCommit(); }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Update to your Aurelia appointment request '.$this->appointment->reference);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.appointment-status-changed');
    }
}

<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InquiryReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900];

    public function __construct(public Inquiry $inquiry) { $this->afterCommit(); }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New Aurelia contact inquiry');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.inquiry-received');
    }
}

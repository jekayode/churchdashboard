<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class PublicEventRegistrationThankYouMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public string $recipientName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'re registered: '.$this->event->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-registration-thank-you',
            with: [
                'appName' => config('app.name', 'Church Dashboard'),
                'recipientName' => $this->recipientName,
                'eventName' => $this->event->name,
                'eventPageUrl' => $this->event->public_detail_url ?? route('public.events'),
            ],
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

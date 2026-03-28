<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Support\EventIcsGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class PublicEventRegistrationConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public EventRegistration $registration,
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
        $this->event->loadMissing(['branch:id,name,logo,venue,public_code']);

        return new Content(
            view: 'emails.event-registration-confirmation',
            with: [
                'appName' => config('app.name', 'Church Dashboard'),
                'recipientName' => $this->recipientName,
                'eventName' => $this->event->name,
                'eventDescription' => $this->event->description,
                'scheduleLine' => $this->scheduleLine(),
                'locationLine' => $this->locationLine(),
                'eventPageUrl' => $this->event->public_detail_url ?? route('public.events'),
                'checkInUrl' => url('/check-in/'.$this->registration->id),
                'branchName' => $this->event->branch?->name,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $ics = EventIcsGenerator::forEvent($this->event, $this->registration->id);

        return [
            Attachment::fromData(static fn (): string => $ics, 'event.ics')
                ->withMime('text/calendar; charset=utf-8'),
        ];
    }

    private function scheduleLine(): string
    {
        $tz = (string) config('app.timezone');
        $start = $this->event->start_date?->copy()->timezone($tz);
        if ($start === null) {
            return '';
        }

        $end = $this->event->end_date?->copy()->timezone($tz);

        if ($end === null) {
            return $start->format('l j F Y \a\t g:i A T');
        }

        return $start->format('l j F Y, g:i A').' – '.$end->format('l j F Y, g:i A T');
    }

    private function locationLine(): string
    {
        $parts = array_filter([
            $this->event->location,
            $this->event->venue,
            $this->event->address,
        ], static fn ($v) => $v !== null && $v !== '');

        return implode(', ', array_unique($parts));
    }
}

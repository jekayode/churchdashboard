<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class WorkforceDriveThankYouMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $teamName,
        public ?string $whatsappUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to the '.$this->teamName.' team',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.workforce-drive-thank-you',
            with: [
                'appName' => config('app.name', 'Church Dashboard'),
                'recipientName' => $this->recipientName,
                'teamName' => $this->teamName,
                'whatsappUrl' => $this->whatsappUrl,
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

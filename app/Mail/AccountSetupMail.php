<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class AccountSetupMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $recipientName,
        public readonly string $resetUrl,
    ) {}

    public function build(): self
    {
        $appName = config('app.name', 'Church Dashboard');

        return $this->subject('Set up your '.$appName.' password')
            ->view('emails.account-setup')
            ->with([
                'appName' => $appName,
                'recipientName' => $this->recipientName,
                'resetUrl' => $this->resetUrl,
            ]);
    }
}

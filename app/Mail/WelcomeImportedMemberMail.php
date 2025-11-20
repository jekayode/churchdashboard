<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class WelcomeImportedMemberMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $recipientName,
        public readonly string $email,
        public readonly string $temporaryPassword,
        public readonly string $resetUrl,
        public readonly string $branchName
    ) {}

    public function build(): self
    {
        $appName = config('app.name', 'Church Dashboard');

        return $this->subject("Welcome to {$this->branchName} - Set Up Your Account")
            ->view('emails.welcome-imported-member')
            ->with([
                'appName' => $appName,
                'recipientName' => $this->recipientName,
                'email' => $this->email,
                'temporaryPassword' => $this->temporaryPassword,
                'resetUrl' => $this->resetUrl,
                'branchName' => $this->branchName,
            ]);
    }
}

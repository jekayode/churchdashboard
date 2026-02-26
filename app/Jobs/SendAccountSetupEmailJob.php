<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\AccountSetupMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

final class SendAccountSetupEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public int $backoff = 30;

    public function __construct(
        public User $user
    ) {}

    public function handle(): void
    {
        try {
            // Skip if email is temporary (doesn't contain @church.local)
            if (str_contains($this->user->email, '@church.local')) {
                Log::info('Skipping account setup email for temporary email address', [
                    'user_id' => $this->user->id,
                    'email' => $this->user->email,
                ]);

                return;
            }

            // Generate password reset token
            $token = Password::createToken($this->user);
            $resetUrl = url(route('password.reset', ['token' => $token, 'email' => $this->user->email], false));

            // Send account setup email
            Mail::to($this->user->email)->send(
                new AccountSetupMail(
                    $this->user->name,
                    $resetUrl
                )
            );

            Log::info('Account setup email sent successfully', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send account setup email', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Account setup email job failed permanently', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }
}

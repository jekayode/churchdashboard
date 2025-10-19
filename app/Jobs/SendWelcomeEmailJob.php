<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\WelcomeImportedMemberMail;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

final class SendWelcomeEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public int $backoff = 30;

    public function __construct(
        public User $user,
        public string $temporaryPassword,
        public Branch $branch
    ) {}

    public function handle(): void
    {
        try {
            // Generate password reset token
            $token = Password::createToken($this->user);
            $resetUrl = url("/reset-password/{$token}?email=".urlencode($this->user->email));

            // Send welcome email
            Mail::to($this->user->email)->send(
                new WelcomeImportedMemberMail(
                    $this->user->name,
                    $this->user->email,
                    $this->temporaryPassword,
                    $resetUrl,
                    $this->branch->name
                )
            );

            Log::info('Welcome email sent successfully', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'branch_id' => $this->branch->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'branch_id' => $this->branch->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Welcome email job failed permanently', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'branch_id' => $this->branch->id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }
}


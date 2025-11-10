<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\WelcomeImportedMemberMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

final class EnvironmentAwareEmailService
{
    /**
     * Send email with environment-aware handling.
     */
    public function sendWelcomeEmail(
        string $recipientEmail,
        string $recipientName,
        string $password,
        string $branchName,
        ?string $resetUrl = null
    ): bool {
        try {
            if (app()->environment('local', 'testing')) {
                return $this->handleLocalEmail($recipientEmail, $recipientName, $password, $branchName, $resetUrl);
            }

            return $this->handleProductionEmail($recipientEmail, $recipientName, $password, $branchName, $resetUrl);

        } catch (\Exception $e) {
            Log::error('Email sending failed', [
                'recipient' => $recipientEmail,
                'environment' => app()->environment(),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Handle email in local/testing environment.
     */
    private function handleLocalEmail(
        string $recipientEmail,
        string $recipientName,
        string $password,
        string $branchName,
        ?string $resetUrl = null
    ): bool {
        // Log to application log
        Log::info('LOCAL EMAIL - Welcome Email', [
            'recipient' => $recipientEmail,
            'recipient_name' => $recipientName,
            'password' => $password,
            'branch_name' => $branchName,
            'reset_url' => $resetUrl,
            'timestamp' => now()->toISOString(),
        ]);

        // Save to dedicated email log file
        $emailLog = [
            'timestamp' => now()->toISOString(),
            'type' => 'welcome_email',
            'recipient' => $recipientEmail,
            'recipient_name' => $recipientName,
            'password' => $password,
            'branch_name' => $branchName,
            'reset_url' => $resetUrl,
            'environment' => app()->environment(),
        ];

        Storage::append('logs/local-emails.log', json_encode($emailLog, JSON_PRETTY_PRINT));

        // Also save to a more readable format
        $readableLog = sprintf(
            "\n=== LOCAL EMAIL LOG ===\n".
            "Time: %s\n".
            "Type: Welcome Email\n".
            "Recipient: %s (%s)\n".
            "Password: %s\n".
            "Branch: %s\n".
            "Reset URL: %s\n".
            "Environment: %s\n".
            "========================\n",
            now()->toISOString(),
            $recipientEmail,
            $recipientName,
            $password,
            $branchName,
            $resetUrl ?? 'N/A',
            app()->environment()
        );

        Storage::append('logs/local-emails-readable.log', $readableLog);

        return true;
    }

    /**
     * Handle email in production environment.
     */
    private function handleProductionEmail(
        string $recipientEmail,
        string $recipientName,
        string $password,
        string $branchName,
        ?string $resetUrl = null
    ): bool {
        // Send actual email using Laravel Mail
        Mail::to($recipientEmail)->send(
            new WelcomeImportedMemberMail(
                $recipientName,
                $recipientEmail,
                $password,
                $resetUrl,
                $branchName
            )
        );

        Log::info('PRODUCTION EMAIL - Welcome Email Sent', [
            'recipient' => $recipientEmail,
            'recipient_name' => $recipientName,
            'branch_name' => $branchName,
            'environment' => app()->environment(),
        ]);

        return true;
    }

    /**
     * Get local email logs for testing purposes.
     */
    public function getLocalEmailLogs(): array
    {
        if (! app()->environment('local', 'testing')) {
            return [];
        }

        try {
            $logContent = Storage::get('logs/local-emails-readable.log');

            return array_filter(explode('=== LOCAL EMAIL LOG ===', $logContent));
        } catch (\Exception $e) {
            Log::error('Failed to read local email logs', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Clear local email logs.
     */
    public function clearLocalEmailLogs(): bool
    {
        if (! app()->environment('local', 'testing')) {
            return false;
        }

        try {
            Storage::put('logs/local-emails.log', '');
            Storage::put('logs/local-emails-readable.log', '');

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to clear local email logs', ['error' => $e->getMessage()]);

            return false;
        }
    }
}




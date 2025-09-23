<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Config;
use Resend\Laravel\Facades\Resend;

final class SendAccountSetupLinks extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'users:send-setup-links
        {--role= : Role name to filter users (e.g., branch_pastor)}
        {--all : Send to all users}
        {--dry : Dry run (show counts only)}';

    /**
     * The console command description.
     */
    protected $description = 'Send password setup/reset links to users (production-only).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! App::environment('production')) {
            $this->error('This command can only be executed in the production environment.');
            return self::FAILURE;
        }

        $role = (string) ($this->option('role') ?? '');
        $sendToAll = (bool) $this->option('all');
        $isDryRun = (bool) $this->option('dry');

        if (! $sendToAll && $role === '') {
            $this->error('You must specify --all or --role=<role_name>.');
            return self::INVALID;
        }

        $usersQuery = User::query()->whereNotNull('email');

        if (! $sendToAll) {
            $usersQuery->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        // Exclude soft deleted users
        $usersQuery->whereNull('deleted_at');

        $total = $usersQuery->count();
        if ($total === 0) {
            $this->info('No users matched the criteria.');
            return self::SUCCESS;
        }

        $this->info(($isDryRun ? '[DRY RUN] ' : '') . "Preparing to send setup links to {$total} user(s).");

        if ($isDryRun) {
            return self::SUCCESS;
        }

        $useResend = Config::get('mail.default') === 'resend';
        $fromAddress = Config::get('mail.from.address');
        $fromName = Config::get('mail.from.name');

        $sent = 0;
        $failed = 0;

        $usersQuery->chunkById(200, function ($users) use (&$sent, &$failed, $useResend, $fromAddress, $fromName) {
            foreach ($users as $user) {
                try {
                    $token = app('auth.password.broker')->createToken($user);
                    $resetUrl = url(route('password.reset', ['token' => $token, 'email' => $user->email], false));

                    if ($useResend) {
                        $response = Resend::emails()->send([
                            'from' => sprintf('%s <%s>', $fromName, $fromAddress),
                            'to' => [$user->email],
                            'subject' => 'Set up your Church Dashboard password',
                            'html' => $this->buildSetupEmailHtml($user->name, $resetUrl),
                        ]);
                        if (! empty($response->id)) {
                            $sent++;
                        } else {
                            $failed++;
                            $this->warn("Resend failed for {$user->email}");
                        }
                    } else {
                        // Fallback to Laravel password broker notification
                        $status = \Illuminate\Support\Facades\Password::sendResetLink(['email' => $user->email]);
                        if ($status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
                            $sent++;
                        } else {
                            $failed++;
                            $this->warn("Failed to send to {$user->email}: {$status}");
                        }
                    }
                } catch (\Throwable $e) {
                    $failed++;
                    $this->warn("Exception for {$user->email}: {$e->getMessage()}");
                }
            }
        });

        $this->info("Completed. Sent: {$sent}, Failed: {$failed}.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function buildSetupEmailHtml(string $recipientName, string $resetUrl): string
    {
        $appName = config('app.name', 'Church Dashboard');

        return <<<HTML
<!doctype html>
<html>
  <body style="font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; line-height:1.6; color:#111827;">
    <h2 style="margin:0 0 12px 0;">Welcome to {$appName}</h2>
    <p style="margin:0 0 12px 0;">Hi {$recipientName},</p>
    <p style="margin:0 0 12px 0;">Use the button below to set your password and access your account.</p>
    <p style="margin:0 0 24px 0;"><a href="{$resetUrl}" style="background:#2563eb;color:#fff;padding:10px 16px;border-radius:6px;text-decoration:none;">Set Your Password</a></p>
    <p style="margin:0 0 8px 0; font-size:14px; color:#374151;">If the button does not work, copy and paste this link into your browser:</p>
    <p style="word-break:break-all; font-size:12px; color:#374151;">{$resetUrl}</p>
    <p style="margin-top:24px; font-size:14px; color:#374151;">Blessings,<br>{$appName} Team</p>
  </body>
</html>
HTML;
    }
}

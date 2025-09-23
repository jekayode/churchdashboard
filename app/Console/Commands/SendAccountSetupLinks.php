<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\AccountSetupMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
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

        $this->info(($isDryRun ? '[DRY RUN] ' : '')."Preparing to send setup links to {$total} user(s).");

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

                    // Build a shared Blade-rendered HTML using the mailable
                    $mailable = new AccountSetupMail($user->name, $resetUrl);
                    $html = view('emails.account-setup', [
                        'appName' => config('app.name', 'Church Dashboard'),
                        'recipientName' => $user->name,
                        'resetUrl' => $resetUrl,
                    ])->render();

                    if ($useResend) {
                        $response = Resend::emails()->send([
                            'from' => sprintf('%s <%s>', $fromName, $fromAddress),
                            'to' => [$user->email],
                            'subject' => $mailable->subject ?? ('Set up your '.config('app.name', 'Church Dashboard').' password'),
                            'html' => $html,
                        ]);
                        if (! empty($response->id)) {
                            $sent++;
                        } else {
                            $failed++;
                            $this->warn("Resend failed for {$user->email}");
                        }
                    } else {
                        // Send via Laravel Mail using the same mailable
                        Mail::to($user->email)->send($mailable);
                        $sent++;
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

    // Shared Blade template is used; no inline HTML builder needed
}

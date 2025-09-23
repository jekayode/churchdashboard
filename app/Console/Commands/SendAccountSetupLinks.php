<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Password;

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

        $sent = 0;
        $failed = 0;

        $usersQuery->chunkById(200, function ($users) use (&$sent, &$failed) {
            foreach ($users as $user) {
                try {
                    // Uses built-in password broker to send a reset/setup link
                    $status = Password::sendResetLink(['email' => $user->email]);
                    if ($status === Password::RESET_LINK_SENT) {
                        $sent++;
                    } else {
                        $failed++;
                        $this->warn("Failed to send to {$user->email}: {$status}");
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
}

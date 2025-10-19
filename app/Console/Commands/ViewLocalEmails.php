<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\EnvironmentAwareEmailService;
use Illuminate\Console\Command;

final class ViewLocalEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:view-local 
                            {--clear : Clear the local email logs}
                            {--latest=10 : Number of latest emails to show}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View or manage local email logs (only works in local/testing environment)';

    public function __construct(
        private readonly EnvironmentAwareEmailService $emailService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! app()->environment('local', 'testing')) {
            $this->error('This command only works in local or testing environment.');

            return 1;
        }

        if ($this->option('clear')) {
            if ($this->emailService->clearLocalEmailLogs()) {
                $this->info('Local email logs cleared successfully.');
            } else {
                $this->error('Failed to clear local email logs.');

                return 1;
            }

            return 0;
        }

        $this->info('=== Local Email Logs ===');
        $this->newLine();

        $logs = $this->emailService->getLocalEmailLogs();
        $latest = (int) $this->option('latest');

        if (empty($logs)) {
            $this->warn('No local email logs found.');

            return 0;
        }

        // Show only the latest emails
        $logs = array_slice($logs, -$latest);

        foreach ($logs as $index => $log) {
            if (trim($log)) {
                $this->line($log);
                $this->newLine();
            }
        }

        $this->info("Showing latest {$latest} emails. Use --latest=N to show more.");
        $this->newLine();
        $this->comment('Use --clear to clear all logs.');

        return 0;
    }
}

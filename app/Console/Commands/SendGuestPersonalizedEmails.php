<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\GuestPersonalizedEmailService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class SendGuestPersonalizedEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guests:send-personalized-emails 
                            {--start-date= : Start date for guest registration period (YYYY-MM-DD)}
                            {--end-date= : End date for guest registration period (YYYY-MM-DD)}
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send personalized follow-up emails to guests who registered in the previous week (runs on Mondays)';

    public function __construct(
        private readonly GuestPersonalizedEmailService $emailService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Sending personalized emails to recent guests...');

        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No emails will be sent');
        }

        // Determine date range
        $startDate = null;
        $endDate = null;

        if ($this->option('start-date') && $this->option('end-date')) {
            $startDate = Carbon::parse($this->option('start-date'));
            $endDate = Carbon::parse($this->option('end-date'));
        } else {
            // Default to previous week (Monday to Sunday)
            $endDate = now()->startOfWeek()->subDay(); // Last Sunday
            $startDate = $endDate->copy()->startOfWeek(); // Last Monday
        }

        $this->info("Processing guests who registered between {$startDate->format('Y-m-d')} and {$endDate->format('Y-m-d')}");

        if ($dryRun) {
            // In dry run, just show what would be sent
            $this->showDryRunResults($startDate, $endDate);

            return Command::SUCCESS;
        }

        try {
            $results = $this->emailService->sendPersonalizedEmailsToRecentGuests($startDate, $endDate);

            $this->info("Total guests found: {$results['total']}");
            $this->info("Emails sent successfully: {$results['sent']}");
            $this->info("Failed: {$results['failed']}");

            if (! empty($results['errors'])) {
                $this->warn("\nErrors encountered:");
                foreach ($results['errors'] as $error) {
                    $this->error("  - {$error['guest']} ({$error['email']}): {$error['error']}");
                }
            }

            Log::info('Guest personalized emails sent', [
                'total' => $results['total'],
                'sent' => $results['sent'],
                'failed' => $results['failed'],
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to send personalized emails: '.$e->getMessage());
            Log::error('Failed to send guest personalized emails', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Show dry run results without actually sending emails.
     */
    private function showDryRunResults(Carbon $startDate, Carbon $endDate): void
    {
        $guests = \App\Models\Member::where('member_status', 'visitor')
            ->where('registration_source', 'guest-form')
            ->whereBetween('date_joined', [$startDate, $endDate])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->with('branch')
            ->get();

        $this->info("\nWould send emails to {$guests->count()} guests:");
        $this->table(
            ['Name', 'Email', 'Branch', 'Date Joined', 'Prayer Request'],
            $guests->map(function ($guest) {
                return [
                    $guest->name,
                    $guest->email,
                    $guest->branch?->name ?? 'N/A',
                    $guest->date_joined?->format('Y-m-d') ?? 'N/A',
                    $guest->prayer_request ? (strlen($guest->prayer_request) > 50 ? substr($guest->prayer_request, 0, 50).'...' : $guest->prayer_request) : 'None',
                ];
            })->toArray()
        );
    }
}

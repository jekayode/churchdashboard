<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Member;
use Illuminate\Console\Command;

final class PopulateBirthdayMonthDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'members:populate-birthday-month-day 
                            {--dry-run : Run without making changes}
                            {--batch-size=100 : Number of members to process at a time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate birthday_month and birthday_day columns for existing members from date_of_birth';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch-size');

        $this->info('Starting birthday month/day population...');
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Count members with date_of_birth but missing birthday_month/day
        $totalMembers = Member::whereNotNull('date_of_birth')
            ->where(function ($query) {
                $query->whereNull('birthday_month')
                    ->orWhereNull('birthday_day');
            })
            ->count();

        if ($totalMembers === 0) {
            $this->info('No members need birthday month/day population.');

            return Command::SUCCESS;
        }

        $this->info("Found {$totalMembers} members to process.");

        $bar = $this->output->createProgressBar($totalMembers);
        $bar->start();

        $processed = 0;
        $skipped = 0;
        $errors = 0;

        Member::whereNotNull('date_of_birth')
            ->where(function ($query) {
                $query->whereNull('birthday_month')
                    ->orWhereNull('birthday_day');
            })
            ->chunk($batchSize, function ($members) use (&$processed, &$skipped, &$errors, $dryRun, $bar) {
                foreach ($members as $member) {
                    try {
                        if (! $member->date_of_birth) {
                            $skipped++;
                            $bar->advance();

                            continue;
                        }

                        $month = (int) $member->date_of_birth->format('n'); // 1-12
                        $day = (int) $member->date_of_birth->format('j'); // 1-31

                        // Validate day is valid for the month
                        if (! $this->isValidDayForMonth($day, $month)) {
                            $this->warn("\nInvalid date for member ID {$member->id}: {$member->date_of_birth->format('Y-m-d')}");
                            $errors++;
                            $bar->advance();

                            continue;
                        }

                        if (! $dryRun) {
                            $member->update([
                                'birthday_month' => $month,
                                'birthday_day' => $day,
                            ]);
                        }

                        $processed++;
                        $bar->advance();
                    } catch (\Exception $e) {
                        $this->error("\nError processing member ID {$member->id}: {$e->getMessage()}");
                        $errors++;
                        $bar->advance();
                    }
                }
            });

        $bar->finish();
        $this->newLine(2);

        $this->info('Processing complete!');
        $this->table(
            ['Status', 'Count'],
            [
                ['Processed', $processed],
                ['Skipped', $skipped],
                ['Errors', $errors],
                ['Total', $totalMembers],
            ]
        );

        if ($dryRun) {
            $this->warn('This was a dry run. Run without --dry-run to apply changes.');
        }

        return Command::SUCCESS;
    }

    /**
     * Validate that a day is valid for a given month.
     */
    private function isValidDayForMonth(int $day, int $month): bool
    {
        if ($month < 1 || $month > 12) {
            return false;
        }

        if ($day < 1 || $day > 31) {
            return false;
        }

        // Days in each month (non-leap year)
        $daysInMonth = [
            1 => 31, 2 => 28, 3 => 31, 4 => 30, 5 => 31, 6 => 30,
            7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31,
        ];

        $maxDay = $daysInMonth[$month];

        // Handle leap year for February
        if ($month === 2 && $day === 29) {
            // Allow Feb 29 - it's valid in leap years
            return true;
        }

        return $day <= $maxDay;
    }
}

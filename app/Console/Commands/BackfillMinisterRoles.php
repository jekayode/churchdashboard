<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Ministry;
use Illuminate\Console\Command;

final class BackfillMinisterRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Note: --dry will not persist changes, only report.
     */
    protected $signature = 'roles:backfill-ministers {--dry : Dry run, do not persist changes}';

    /**
     * The console command description.
     */
    protected $description = 'Ensure all ministry leaders have the ministry_leader role scoped to their ministry\'s branch';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dry = (bool) $this->option('dry');

        $updated = 0;
        $skipped = 0;

        $this->info('Scanning ministries for leader role backfill...');

        Ministry::with(['leader.user'])->chunkById(200, function ($ministries) use (&$updated, &$skipped, $dry) {
            foreach ($ministries as $ministry) {
                if (! $ministry->leader || ! $ministry->leader->user) {
                    $skipped++;

                    continue;
                }

                $user = $ministry->leader->user;
                $branchId = $ministry->branch_id;

                if ($user->hasRole('ministry_leader', $branchId)) {
                    $skipped++;

                    continue;
                }

                if ($dry) {
                    $this->line("[DRY] would assign ministry_leader to user {$user->id} for branch {$branchId}");
                } else {
                    $user->assignRole('ministry_leader', $branchId);
                    $updated++;
                    $this->line("Assigned ministry_leader to user {$user->id} for branch {$branchId}");
                }
            }
        });

        $this->info("Backfill complete. Updated: {$updated}, Skipped: {$skipped}");

        return self::SUCCESS;
    }
}

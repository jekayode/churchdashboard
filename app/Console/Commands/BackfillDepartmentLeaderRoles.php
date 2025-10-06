<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Department;
use Illuminate\Console\Command;

final class BackfillDepartmentLeaderRoles extends Command
{
    protected $signature = 'roles:backfill-department-leaders {--dry : Dry run, do not persist changes}';

    protected $description = 'Ensure department leaders have department_leader role scoped to the department\'s branch';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry');
        $updated = 0;
        $skipped = 0;

        $this->info('Scanning departments for leader role backfill...');

        Department::with(['leader.user', 'ministry'])->chunkById(200, function ($departments) use (&$updated, &$skipped, $dry) {
            foreach ($departments as $department) {
                if (! $department->leader || ! $department->leader->user || ! $department->ministry) {
                    $skipped++;

                    continue;
                }

                $user = $department->leader->user;
                $branchId = $department->ministry->branch_id;

                if ($user->hasRole('department_leader', $branchId)) {
                    $skipped++;

                    continue;
                }

                if ($dry) {
                    $this->line("[DRY] would assign department_leader to user {$user->id} for branch {$branchId}");
                } else {
                    $user->assignRole('department_leader', $branchId);
                    $updated++;
                    $this->line("Assigned department_leader to user {$user->id} for branch {$branchId}");
                }
            }
        });

        $this->info("Backfill complete. Updated: {$updated}, Skipped: {$skipped}");

        return self::SUCCESS;
    }
}

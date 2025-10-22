<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Projection;
use App\Models\User;
use Illuminate\Database\Seeder;

final class ProjectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all branches
        $branches = Branch::all();

        if ($branches->isEmpty()) {
            $this->command->warn('No branches found. Please run BranchSeeder first.');

            return;
        }

        // Get super admin users for approval
        $superAdmins = User::whereHas('roles', function ($query) {
            $query->where('name', 'super_admin');
        })->get();

        if ($superAdmins->isEmpty()) {
            $this->command->warn('No super admin users found. Some projections may not have approval data.');
        }

        $currentYear = now()->year;

        // Create projections for each branch
        foreach ($branches as $branch) {
            // Create current year projection (approved)
            Projection::factory()
                ->currentYear()
                ->create([
                    'branch_id' => $branch->id,
                    'year' => $currentYear,
                    'is_global' => false,
                    'is_current_year' => true,
                    'status' => 'approved',
                    'created_by' => $branch->pastor_id ?? User::factory()->create()->id,
                    'approved_by' => $superAdmins->isNotEmpty() ? $superAdmins->random()->id : null,
                    'approved_at' => now()->subDays(rand(30, 60)),
                    'approval_notes' => 'Approved for current year operations.',
                ]);

            // Create next year projection (in review or draft)
            $nextYearStatus = collect(['draft', 'in_review'])->random();
            Projection::factory()
                ->state([
                    'status' => $nextYearStatus,
                    'approved_by' => null,
                    'approved_at' => null,
                    'approval_notes' => null,
                    'rejection_reason' => null,
                ])
                ->create([
                    'branch_id' => $branch->id,
                    'year' => $currentYear + 1,
                    'is_global' => false,
                    'is_current_year' => false,
                    'created_by' => $branch->pastor_id ?? User::factory()->create()->id,
                ]);

            // Create previous year projection (approved)
            Projection::factory()
                ->approved()
                ->create([
                    'branch_id' => $branch->id,
                    'year' => $currentYear - 1,
                    'is_global' => false,
                    'is_current_year' => false,
                    'created_by' => $branch->pastor_id ?? User::factory()->create()->id,
                    'approved_by' => $superAdmins->isNotEmpty() ? $superAdmins->random()->id : null,
                    'approved_at' => now()->subYear()->addDays(rand(1, 30)),
                ]);

            // Occasionally create a rejected projection for a different year
            if (rand(1, 3) === 1) {
                Projection::factory()
                    ->rejected()
                    ->create([
                        'branch_id' => $branch->id,
                        'year' => $currentYear + 2, // Use a different year to avoid conflicts
                        'is_global' => false,
                        'is_current_year' => false,
                        'created_by' => $branch->pastor_id ?? User::factory()->create()->id,
                        'rejection_reason' => collect([
                            'Targets appear unrealistic for branch capacity.',
                            'Quarterly distribution needs adjustment.',
                            'Please provide more detailed planning notes.',
                            'Budget considerations need to be addressed.',
                        ])->random(),
                    ]);
            }
        }

        // Create some additional historical projections for variety
        foreach ($branches->take(3) as $branch) {
            for ($year = $currentYear - 3; $year < $currentYear - 1; $year++) {
                Projection::factory()
                    ->approved()
                    ->create([
                        'branch_id' => $branch->id,
                        'year' => $year,
                        'is_global' => false,
                        'is_current_year' => false,
                        'created_by' => $branch->pastor_id ?? User::factory()->create()->id,
                        'approved_by' => $superAdmins->isNotEmpty() ? $superAdmins->random()->id : null,
                        'approved_at' => now()->subYears($currentYear - $year)->addDays(rand(1, 60)),
                    ]);
            }
        }

        $this->command->info('Projection seeder completed successfully!');
        $this->command->info('Created projections for '.$branches->count().' branches.');
        $this->command->info('Generated data for years '.($currentYear - 3).' to '.($currentYear + 1).'.');
    }
}

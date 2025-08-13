<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Member;
use App\Models\Ministry;
use Illuminate\Database\Seeder;

final class MinistrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = Branch::all();
        $ministriesData = [
            [
                'name' => 'Worship Ministry',
                'description' => 'Handles music, choir, and worship sessions.',
            ],
            [
                'name' => 'Youth Ministry',
                'description' => 'Focuses on youth engagement and programs.',
            ],
            [
                'name' => 'Outreach Ministry',
                'description' => 'Coordinates evangelism and community outreach.',
            ],
            [
                'name' => 'Children Ministry',
                'description' => 'Oversees children\'s church and activities.',
            ],
        ];

        foreach ($branches as $branch) {
            foreach ($ministriesData as $ministryData) {
                // Assign a random member as leader if available
                $leader = Member::where('branch_id', $branch->id)->inRandomOrder()->first();
                Ministry::firstOrCreate([
                    'branch_id' => $branch->id,
                    'name' => $ministryData['name'],
                ], [
                    'description' => $ministryData['description'],
                    'leader_id' => $leader?->id,
                    'status' => 'active',
                ]);
            }
        }

        $this->command->info('Ministries seeded successfully!');
    }
}

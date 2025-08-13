<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Member;
use App\Models\SmallGroup;
use Illuminate\Database\Seeder;

final class SmallGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groupsData = [
            [
                'name' => 'Downtown Connect',
                'description' => 'Small group for downtown residents.',
                'meeting_day' => 'Wednesday',
                'meeting_time' => '18:00:00',
                'location' => 'Downtown Community Hall',
            ],
            [
                'name' => 'Young Adults',
                'description' => 'Small group for young adults and singles.',
                'meeting_day' => 'Friday',
                'meeting_time' => '19:00:00',
                'location' => 'Youth Center',
            ],
            [
                'name' => 'Family Fellowship',
                'description' => 'Small group for families.',
                'meeting_day' => 'Sunday',
                'meeting_time' => '17:00:00',
                'location' => 'Main Campus',
            ],
        ];

        $branches = Branch::all();
        foreach ($branches as $branch) {
            foreach ($groupsData as $groupData) {
                // Assign a random member as leader if available
                $leader = Member::where('branch_id', $branch->id)->inRandomOrder()->first();
                SmallGroup::firstOrCreate([
                    'branch_id' => $branch->id,
                    'name' => $groupData['name'],
                ], [
                    'description' => $groupData['description'],
                    'leader_id' => $leader?->id,
                    'meeting_day' => $groupData['meeting_day'],
                    'meeting_time' => $groupData['meeting_time'],
                    'location' => $groupData['location'],
                    'status' => 'active',
                ]);
            }
        }

        $this->command->info('Small groups seeded successfully!');
    }
}

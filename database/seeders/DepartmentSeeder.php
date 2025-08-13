<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Member;
use App\Models\Ministry;
use Illuminate\Database\Seeder;

final class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departmentsData = [
            [
                'name' => 'Music Team',
                'description' => 'Handles music and instruments.',
            ],
            [
                'name' => 'Ushering Team',
                'description' => 'Welcomes and assists members during services.',
            ],
            [
                'name' => 'Media Team',
                'description' => 'Manages sound, video, and livestream.',
            ],
            [
                'name' => 'Prayer Team',
                'description' => 'Coordinates prayer sessions and chains.',
            ],
        ];

        $ministries = Ministry::all();
        foreach ($ministries as $ministry) {
            foreach ($departmentsData as $deptData) {
                // Assign a random member as leader if available
                $leader = Member::where('branch_id', $ministry->branch_id)->inRandomOrder()->first();
                Department::firstOrCreate([
                    'ministry_id' => $ministry->id,
                    'name' => $deptData['name'],
                ], [
                    'description' => $deptData['description'],
                    'leader_id' => $leader?->id,
                    'status' => 'active',
                ]);
            }
        }

        $this->command->info('Departments seeded successfully!');
    }
}

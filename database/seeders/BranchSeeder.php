<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample pastors first
        $pastors = [
            [
                'name' => 'Pastor John Smith',
                'email' => 'john.smith@churchdashboard.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Pastor Sarah Johnson',
                'email' => 'sarah.johnson@churchdashboard.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Pastor Michael Brown',
                'email' => 'michael.brown@churchdashboard.com',
                'password' => Hash::make('password'),
            ],
        ];

        $createdPastors = [];
        foreach ($pastors as $pastorData) {
            $pastor = User::firstOrCreate(
                ['email' => $pastorData['email']],
                $pastorData
            );
            $createdPastors[] = $pastor;
        }

        // Create sample branches
        $branches = [
            [
                'name' => 'Main Campus',
                'venue' => 'Victory Christian Center',
                'service_time' => 'Sunday 9:00 AM & 11:00 AM',
                'phone' => '+1-555-0101',
                'email' => 'main@churchdashboard.com',
                'map_embed_code' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3024.123456789!2d-74.0059413!3d40.7127753!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDDCsDQyJzQ2LjAiTiA3NMKwMDAnMjEuNCJX!5e0!3m2!1sen!2sus!4v1234567890123!5m2!1sen!2sus" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>',
                'pastor_id' => $createdPastors[0]->id,
                'status' => 'active',
            ],
            [
                'name' => 'North Campus',
                'venue' => 'Community Center North',
                'service_time' => 'Sunday 10:00 AM',
                'phone' => '+1-555-0102',
                'email' => 'north@churchdashboard.com',
                'map_embed_code' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3024.123456789!2d-74.0059413!3d40.7127753!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDDCsDQyJzQ2LjAiTiA3NMKwMDAnMjEuNCJX!5e0!3m2!1sen!2sus!4v1234567890123!5m2!1sen!2sus" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>',
                'pastor_id' => $createdPastors[1]->id,
                'status' => 'active',
            ],
            [
                'name' => 'Youth Campus',
                'venue' => 'Youth Center Downtown',
                'service_time' => 'Sunday 6:00 PM',
                'phone' => '+1-555-0103',
                'email' => 'youth@churchdashboard.com',
                'map_embed_code' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3024.123456789!2d-74.0059413!3d40.7127753!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDDCsDQyJzQ2LjAiTiA3NMKwMDAnMjEuNCJX!5e0!3m2!1sen!2sus!4v1234567890123!5m2!1sen!2sus" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>',
                'pastor_id' => $createdPastors[2]->id,
                'status' => 'active',
            ],
        ];

        foreach ($branches as $branchData) {
            $branch = Branch::firstOrCreate(
                ['name' => $branchData['name']],
                $branchData
            );

            // Assign branch pastor role to the pastor
            $pastor = User::find($branchData['pastor_id']);
            $branchPastorRole = Role::where('name', 'branch_pastor')->first();
            
            if ($pastor && $branchPastorRole) {
                $pastor->roles()->syncWithoutDetaching([
                    $branchPastorRole->id => ['branch_id' => $branch->id]
                ]);
            }
        }

        $this->command->info('Branches and pastors seeded successfully!');
    }
}

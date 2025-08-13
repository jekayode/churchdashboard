<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Member;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = Branch::all();
        
        if ($branches->isEmpty()) {
            $this->command->error('No branches found. Please run BranchSeeder first.');
            return;
        }

        $memberRole = Role::where('name', 'church_member')->first();
        
        // Sample members data
        $membersData = [
            // Main Campus Members
            [
                'name' => 'David Wilson',
                'email' => 'david.wilson@example.com',
                'phone' => '+1-555-1001',
                'date_of_birth' => '1985-03-15',
                'anniversary' => '2010-06-20',
                'gender' => 'male',
                'marital_status' => 'married',
                'occupation' => 'Software Engineer',
                'nearest_bus_stop' => 'Main Street Station',
                'date_joined' => '2020-01-15',
                'date_attended_membership_class' => '2020-02-01',
                'teci_status' => '300_level',
                'growth_level' => 'growing',
                'leadership_trainings' => ['ELP', 'MLCC'],
                'member_status' => 'member',
                'branch_name' => 'Main Campus',
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emily.davis@example.com',
                'phone' => '+1-555-1002',
                'date_of_birth' => '1990-07-22',
                'gender' => 'female',
                'marital_status' => 'single',
                'occupation' => 'Teacher',
                'nearest_bus_stop' => 'Central Park',
                'date_joined' => '2021-03-10',
                'date_attended_membership_class' => '2021-04-05',
                'teci_status' => '200_level',
                'growth_level' => 'new_believer',
                'leadership_trainings' => ['ELP'],
                'member_status' => 'volunteer',
                'branch_name' => 'Main Campus',
            ],
            [
                'name' => 'Robert Martinez',
                'email' => 'robert.martinez@example.com',
                'phone' => '+1-555-1003',
                'date_of_birth' => '1978-11-08',
                'anniversary' => '2005-09-12',
                'gender' => 'male',
                'marital_status' => 'married',
                'occupation' => 'Business Manager',
                'nearest_bus_stop' => 'Downtown Terminal',
                'date_joined' => '2018-05-20',
                'date_attended_membership_class' => '2018-06-15',
                'teci_status' => 'graduated',
                'growth_level' => 'pastor',
                'leadership_trainings' => ['ELP', 'MLCC', 'MLCP Basic', 'MLCP Advanced'],
                'member_status' => 'leader',
                'branch_name' => 'Main Campus',
            ],
            // North Campus Members
            [
                'name' => 'Lisa Thompson',
                'email' => 'lisa.thompson@example.com',
                'phone' => '+1-555-2001',
                'date_of_birth' => '1992-04-18',
                'gender' => 'female',
                'marital_status' => 'single',
                'occupation' => 'Nurse',
                'nearest_bus_stop' => 'North Plaza',
                'date_joined' => '2022-01-08',
                'date_attended_membership_class' => '2022-02-12',
                'teci_status' => '100_level',
                'growth_level' => 'growing',
                'leadership_trainings' => [],
                'member_status' => 'member',
                'branch_name' => 'North Campus',
            ],
            [
                'name' => 'James Anderson',
                'email' => 'james.anderson@example.com',
                'phone' => '+1-555-2002',
                'date_of_birth' => '1980-12-03',
                'anniversary' => '2008-08-15',
                'gender' => 'male',
                'marital_status' => 'married',
                'occupation' => 'Accountant',
                'nearest_bus_stop' => 'North Station',
                'date_joined' => '2019-09-14',
                'date_attended_membership_class' => '2019-10-20',
                'teci_status' => '400_level',
                'growth_level' => 'core',
                'leadership_trainings' => ['ELP', 'MLCC', 'MLCP Basic'],
                'member_status' => 'minister',
                'branch_name' => 'North Campus',
            ],
            // Youth Campus Members
            [
                'name' => 'Ashley Garcia',
                'email' => 'ashley.garcia@example.com',
                'phone' => '+1-555-3001',
                'date_of_birth' => '2000-06-25',
                'gender' => 'female',
                'marital_status' => 'single',
                'occupation' => 'Student',
                'nearest_bus_stop' => 'University Ave',
                'date_joined' => '2023-02-18',
                'date_attended_membership_class' => '2023-03-25',
                'teci_status' => 'not_started',
                'growth_level' => 'new_believer',
                'leadership_trainings' => [],
                'member_status' => 'member',
                'branch_name' => 'Youth Campus',
            ],
            [
                'name' => 'Tyler Rodriguez',
                'email' => 'tyler.rodriguez@example.com',
                'phone' => '+1-555-3002',
                'date_of_birth' => '1998-09-14',
                'gender' => 'male',
                'marital_status' => 'single',
                'occupation' => 'Graphic Designer',
                'nearest_bus_stop' => 'Youth Center Stop',
                'date_joined' => '2021-11-07',
                'date_attended_membership_class' => '2021-12-12',
                'teci_status' => '200_level',
                'growth_level' => 'growing',
                'leadership_trainings' => ['ELP'],
                'member_status' => 'volunteer',
                'branch_name' => 'Youth Campus',
            ],
        ];

        foreach ($membersData as $memberData) {
            // Find the branch
            $branch = $branches->where('name', $memberData['branch_name'])->first();
            if (!$branch) {
                continue;
            }

            // Create user account if email is provided
            $user = null;
            if (!empty($memberData['email'])) {
                $user = User::firstOrCreate(
                    ['email' => $memberData['email']],
                    [
                        'name' => $memberData['name'],
                        'password' => Hash::make('password'),
                    ]
                );

                // Assign church member role
                if ($memberRole) {
                    $user->roles()->syncWithoutDetaching([
                        $memberRole->id => ['branch_id' => $branch->id]
                    ]);
                }
            }

            // Remove branch_name from member data
            unset($memberData['branch_name']);

            // Create member record
            $memberData['branch_id'] = $branch->id;
            $memberData['user_id'] = $user?->id;

            Member::firstOrCreate(
                [
                    'email' => $memberData['email'] ?? null,
                    'branch_id' => $branch->id,
                ],
                $memberData
            );
        }

        $this->command->info('Members seeded successfully!');
    }
}

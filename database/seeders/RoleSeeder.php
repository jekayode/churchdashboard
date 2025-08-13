<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

final class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Admin',
                'description' => 'Full system access across all branches and features',
            ],
            [
                'name' => 'branch_pastor',
                'display_name' => 'Branch Pastor',
                'description' => 'Full access to manage a specific branch including members, ministries, and reports',
            ],
            [
                'name' => 'ministry_leader',
                'display_name' => 'Ministry Leader',
                'description' => 'Manage ministries and departments within their assigned ministry',
            ],
            [
                'name' => 'department_leader',
                'display_name' => 'Department Leader',
                'description' => 'Manage members and activities within their assigned department',
            ],
            [
                'name' => 'church_member',
                'display_name' => 'Church Member',
                'description' => 'Basic member access to view events and update personal information',
            ],
            [
                'name' => 'public_user',
                'display_name' => 'Public User',
                'description' => 'Limited access to public events and registration',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        $this->command->info('Roles seeded successfully!');
    }
}

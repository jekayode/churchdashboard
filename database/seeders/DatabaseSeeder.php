<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a super admin user first
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@churchdashboard.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );

        // Create a test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Run seeders in dependency order
        $this->call([
            RoleSeeder::class,
            BranchSeeder::class,
            MemberSeeder::class,
            MinistrySeeder::class,
            DepartmentSeeder::class,
            SmallGroupSeeder::class,
            EventSeeder::class,
            ExpenseSeeder::class,
            ProjectionSeeder::class,
        ]);

        // Assign super admin role to the admin user
        $superAdminRole = \App\Models\Role::where('name', 'super_admin')->first();
        if ($superAdmin && $superAdminRole) {
            $superAdmin->roles()->syncWithoutDetaching([
                $superAdminRole->id => ['branch_id' => null]
            ]);
        }

        $this->command->info('All seeders completed successfully!');
    }
}

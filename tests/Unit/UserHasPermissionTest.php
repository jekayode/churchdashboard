<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserHasPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
    }

    public function test_super_admin_bypasses_all_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->assertTrue($user->hasPermission('members.delete'));
        $this->assertTrue($user->hasPermission('roles.manage'));
    }

    public function test_user_inherits_permissions_from_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('church_member');

        $this->assertTrue($user->hasPermission('members.view'));
        $this->assertFalse($user->hasPermission('members.delete'));
    }

    public function test_branch_scoped_role_only_counts_for_matching_branch(): void
    {
        $branch = Branch::factory()->create();
        $otherBranch = Branch::factory()->create();
        $user = User::factory()->create();

        $role = Role::query()->where('name', 'ministry_leader')->first();
        $permission = Permission::query()->where('name', 'ministries.update')->first();
        $role->permissions()->syncWithoutDetaching([$permission->id]);

        $user->assignRole('ministry_leader', $branch->id);

        $this->assertTrue($user->hasPermission('ministries.update', $branch->id));
        $this->assertFalse($user->hasPermission('ministries.update', $otherBranch->id));
    }

    public function test_all_permissions_collects_unique_names(): void
    {
        $user = User::factory()->create();
        $user->assignRole('church_member');

        $names = $user->allPermissions();

        $this->assertTrue($names->contains('members.view'));
        $this->assertFalse($names->contains('roles.manage'));
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Permissions;

use App\Models\Permission;
use App\Models\Role;
use App\Support\PermissionCatalog;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_catalog_is_seeded(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);

        $this->assertSame(count(PermissionCatalog::names()), Permission::query()->count());

        $pastor = Role::query()->where('name', 'branch_pastor')->first();
        $this->assertTrue($pastor->hasPermission('members.view'));
        $this->assertTrue($pastor->hasPermission('users.assign_role'));
        $this->assertFalse($pastor->hasPermission('roles.manage'));

        $member = Role::query()->where('name', 'church_member')->first();
        $this->assertTrue($member->hasPermission('members.view'));
        $this->assertFalse($member->hasPermission('members.delete'));
    }
}

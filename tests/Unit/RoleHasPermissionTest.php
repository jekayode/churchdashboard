<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RoleHasPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_has_permission_when_attached(): void
    {
        $role = Role::factory()->create(['name' => 'test_role', 'display_name' => 'Test']);
        $permission = Permission::query()->create([
            'name' => 'members.view',
            'group' => 'members',
            'label' => 'View members',
        ]);

        $role->permissions()->attach($permission->id);

        $this->assertTrue($role->fresh()->hasPermission('members.view'));
        $this->assertFalse($role->fresh()->hasPermission('members.delete'));
    }
}

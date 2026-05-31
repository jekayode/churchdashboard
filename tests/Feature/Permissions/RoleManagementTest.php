<?php

declare(strict_types=1);

namespace Tests\Feature\Permissions;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
    }

    public function test_super_admin_can_sync_role_permissions(): void
    {
        $admin = $this->superAdmin();
        $role = Role::query()->where('name', 'church_member')->first();
        $permissionIds = \App\Models\Permission::query()->limit(3)->pluck('id')->all();

        $response = $this->actingAs($admin)->putJson("/api/admin/roles/{$role->id}/permissions", [
            'permissions' => $permissionIds,
        ]);

        $response->assertOk();
        $this->assertCount(3, $role->fresh()->permissions);
    }

    public function test_branch_pastor_cannot_sync_role_permissions(): void
    {
        $branch = \App\Models\Branch::factory()->create();
        $pastor = User::factory()->create();
        $pastor->assignRole('branch_pastor', $branch->id);

        $role = Role::query()->where('name', 'church_member')->first();

        $response = $this->actingAs($pastor)->putJson("/api/admin/roles/{$role->id}/permissions", [
            'permissions' => [],
        ]);

        $response->assertForbidden();
    }

    public function test_system_role_cannot_be_deleted(): void
    {
        $admin = $this->superAdmin();
        $role = Role::query()->where('name', 'church_member')->first();

        $response = $this->actingAs($admin)->deleteJson("/api/admin/roles/{$role->id}");

        $response->assertStatus(422);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }
}

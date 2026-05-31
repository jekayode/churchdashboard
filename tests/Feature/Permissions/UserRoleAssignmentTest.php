<?php

declare(strict_types=1);

namespace Tests\Feature\Permissions;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserRoleAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
    }

    public function test_branch_pastor_can_assign_role_in_own_branch(): void
    {
        $branch = Branch::factory()->create();
        $pastor = User::factory()->create();
        $pastor->assignRole('branch_pastor', $branch->id);

        $target = User::factory()->create();
        $role = Role::query()->where('name', 'church_member')->first();

        $response = $this->actingAs($pastor)->postJson("/api/admin/users/{$target->id}/roles", [
            'role_id' => $role->id,
            'branch_id' => $branch->id,
        ]);

        $response->assertOk();
        $this->assertTrue($target->fresh()->hasRole('church_member', $branch->id));
    }

    public function test_branch_pastor_cannot_assign_super_admin_role(): void
    {
        $branch = Branch::factory()->create();
        $pastor = User::factory()->create();
        $pastor->assignRole('branch_pastor', $branch->id);

        $target = User::factory()->create();
        $role = Role::query()->where('name', 'super_admin')->first();

        $response = $this->actingAs($pastor)->postJson("/api/admin/users/{$target->id}/roles", [
            'role_id' => $role->id,
            'branch_id' => $branch->id,
        ]);

        $response->assertForbidden();
    }

    public function test_branch_pastor_cannot_assign_role_for_other_branch(): void
    {
        $branch = Branch::factory()->create();
        $other = Branch::factory()->create();
        $pastor = User::factory()->create();
        $pastor->assignRole('branch_pastor', $branch->id);

        $target = User::factory()->create();
        $role = Role::query()->where('name', 'church_member')->first();

        $response = $this->actingAs($pastor)->postJson("/api/admin/users/{$target->id}/roles", [
            'role_id' => $role->id,
            'branch_id' => $other->id,
        ]);

        $response->assertForbidden();
    }
}

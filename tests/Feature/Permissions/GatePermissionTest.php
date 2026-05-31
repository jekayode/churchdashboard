<?php

declare(strict_types=1);

namespace Tests\Feature\Permissions;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

final class GatePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
    }

    public function test_gate_before_allows_seeded_permission_for_church_member(): void
    {
        $user = User::factory()->create();
        $user->assignRole('church_member');

        $this->assertTrue(Gate::forUser($user)->allows('members.view'));
        $this->assertFalse(Gate::forUser($user)->allows('members.delete'));
    }

    public function test_super_admin_passes_any_permission_ability(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->assertTrue(Gate::forUser($user)->allows('roles.manage'));
    }
}

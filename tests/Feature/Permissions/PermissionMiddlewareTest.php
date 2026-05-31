<?php

declare(strict_types=1);

namespace Tests\Feature\Permissions;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class PermissionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);

        Route::middleware(['web', 'auth', 'permission:roles.manage'])->get('/test-permission-gate', fn () => response()->json(['ok' => true]));
    }

    public function test_user_with_permission_can_access_route(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->actingAs($admin)->getJson('/test-permission-gate')->assertOk();
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $user = User::factory()->create();
        $user->assignRole('church_member');

        $this->actingAs($user)->getJson('/test-permission-gate')->assertForbidden();
    }
}

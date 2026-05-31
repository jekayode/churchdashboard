<?php

declare(strict_types=1);

namespace Tests\Feature\Directory;

use App\Enums\BusinessStatus;
use App\Models\Business;
use App\Models\DirectorySetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_gets_json_403_on_stats(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/admin/biz/stats')->assertForbidden();
    }

    public function test_directory_admin_can_view_stats_and_manage_changelog(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        DirectorySetting::instance();

        Business::factory()->create([
            'status' => BusinessStatus::PendingReview,
        ]);
        Business::factory()->active()->create();

        $admin = User::factory()->create();
        $admin->assignRole('directory_admin');
        $this->actingAs($admin, 'sanctum');

        $stats = $this->getJson('/api/admin/biz/stats')->assertOk()->json();
        $this->assertTrue($stats['success']);
        $this->assertIsArray($stats['data']);

        $this->postJson('/api/admin/biz/changelog', [
            'version' => '1.2.3',
            'title' => 'Directory Update',
            'body' => 'Changelog entry for directory.',
            'published_at' => now()->toDateString(),
        ])->assertCreated();

        $this->get(route('biz.changelog'))
            ->assertOk()
            ->assertSee('Directory Update')
            ->assertSee('v1.2.3');
    }

    public function test_branch_pastor_has_directory_admin_access(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        DirectorySetting::instance();

        $branchPastor = User::factory()->create();
        $branchPastor->assignRole('branch_pastor');
        $this->actingAs($branchPastor, 'sanctum');

        $this->getJson('/api/admin/biz/stats')->assertOk();
    }
}

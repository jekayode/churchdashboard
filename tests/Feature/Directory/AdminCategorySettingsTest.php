<?php

declare(strict_types=1);

namespace Tests\Feature\Directory;

use App\Models\DirectorySetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminCategorySettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        DirectorySetting::instance();
        $this->admin = User::factory()->create();
        $this->admin->assignRole('directory_admin');
        $this->actingAs($this->admin, 'sanctum');
    }

    public function test_directory_admin_can_create_category(): void
    {
        $response = $this->postJson('/api/admin/biz/categories', [
            'name' => 'Beauty & Wellness',
            'icon' => 'sparkles',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('directory_categories', ['name' => 'Beauty & Wellness']);
    }

    public function test_non_admin_cannot_create_category(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/admin/biz/categories', ['name' => 'Test'])
            ->assertForbidden();
    }

    public function test_directory_admin_can_update_settings(): void
    {
        $response = $this->putJson('/api/admin/biz/settings', [
            'tagline' => 'Find church businesses',
            'primary_color' => '#336699',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('directory_settings', ['tagline' => 'Find church businesses']);
    }
}

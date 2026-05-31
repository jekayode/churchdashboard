<?php

declare(strict_types=1);

namespace Tests\Feature\Directory;

use App\Enums\BusinessStatus;
use App\Models\Business;
use App\Models\DirectorySetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class BusinessCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_business_pending_approval(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/biz/businesses', [
            'name' => 'Test Shop',
            'city' => 'Lagos',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('businesses', [
            'name' => 'Test Shop',
            'owner_user_id' => $user->id,
            'status' => BusinessStatus::Draft->value,
        ]);
    }

    public function test_user_cannot_update_another_owners_business(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $business = Business::factory()->for($owner, 'owner')->create();
        $this->actingAs($other, 'sanctum');

        $this->putJson("/api/biz/businesses/{$business->slug}", ['name' => 'Hacked'])
            ->assertForbidden();
    }

    public function test_admin_can_approve_business(): void
    {
        Notification::fake();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        DirectorySetting::instance();

        $admin = User::factory()->create();
        $admin->assignRole('directory_admin');
        $owner = User::factory()->create();
        $business = Business::factory()->for($owner, 'owner')->create(['status' => BusinessStatus::PendingReview]);

        $this->actingAs($admin, 'sanctum');

        $this->postJson("/api/admin/biz/businesses/{$business->slug}/approve")
            ->assertOk();

        $this->assertSame(BusinessStatus::Active, $business->fresh()->status);
    }

    public function test_reserved_slug_is_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/biz/businesses', [
            'name' => 'Search Shop',
            'slug' => 'search',
        ])->assertUnprocessable();
    }
}

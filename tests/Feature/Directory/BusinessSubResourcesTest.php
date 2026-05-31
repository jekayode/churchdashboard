<?php

declare(strict_types=1);

namespace Tests\Feature\Directory;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BusinessSubResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_manage_services(): void
    {
        $owner = User::factory()->create();
        $business = Business::factory()->for($owner, 'owner')->create();
        $this->actingAs($owner, 'sanctum');

        $this->postJson("/api/biz/businesses/{$business->slug}/services", [
            'name' => 'Consultation',
            'duration_text' => '1 hour',
            'price_text' => 'from $50',
        ])->assertCreated();

        $this->assertDatabaseHas('business_services', ['name' => 'Consultation']);
    }

    public function test_non_owner_cannot_add_service(): void
    {
        $business = Business::factory()->create();
        $this->actingAs(User::factory()->create(), 'sanctum');

        $this->postJson("/api/biz/businesses/{$business->slug}/services", [
            'name' => 'Hack',
        ])->assertForbidden();
    }
}

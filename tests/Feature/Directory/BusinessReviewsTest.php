<?php

declare(strict_types=1);

namespace Tests\Feature\Directory;

use App\Enums\ReviewStatus;
use App\Models\Business;
use App\Models\BusinessReview;
use App\Models\DirectorySetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BusinessReviewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_one_review_per_business(): void
    {
        DirectorySetting::instance()->update(['reviews_require_approval' => false]);

        $user = User::factory()->create();
        $business = Business::factory()->active()->create();
        $this->actingAs($user, 'sanctum');

        $this->postJson("/api/biz/businesses/{$business->slug}/reviews", [
            'rating' => 5,
            'body' => 'Great!',
        ])->assertCreated();

        $this->assertEquals(1, BusinessReview::count());
        $this->assertEquals(5.0, (float) $business->fresh()->average_rating);

        $this->postJson("/api/biz/businesses/{$business->slug}/reviews", [
            'rating' => 3,
            'body' => 'Updated',
        ])->assertCreated();

        $this->assertEquals(1, BusinessReview::count());
        $this->assertEquals(3.0, (float) $business->fresh()->average_rating);
    }

    public function test_resubmitting_approved_review_recalculates_average_when_approval_required(): void
    {
        DirectorySetting::instance()->update(['reviews_require_approval' => true]);

        $user = User::factory()->create();
        $business = Business::factory()->active()->create();

        BusinessReview::factory()->for($business)->for($user)->create([
            'status' => ReviewStatus::Approved,
            'rating' => 5,
        ]);

        // Manually set counters as if the approved review had been counted.
        $business->update(['reviews_count' => 1, 'average_rating' => 5.0]);

        $this->actingAs($user, 'sanctum');

        $this->postJson("/api/biz/businesses/{$business->slug}/reviews", [
            'rating' => 1,
            'body' => 'Changed my mind',
        ])->assertCreated();

        $business->refresh();

        // Re-submitted review is now Pending → previously-approved rating must no longer count.
        $this->assertEquals(0, $business->reviews_count);
        $this->assertEquals(0.0, (float) $business->average_rating);
    }

    public function test_admin_moderation_recalculates_average(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        DirectorySetting::instance();

        $admin = User::factory()->create();
        $admin->assignRole('branch_pastor');

        $business = Business::factory()->active()->create();
        $review = BusinessReview::factory()->for($business)->create([
            'status' => ReviewStatus::Pending,
            'rating' => 4,
        ]);

        $this->actingAs($admin, 'sanctum');

        $this->putJson("/api/admin/biz/reviews/{$review->id}/moderate", [
            'status' => 'approved',
        ])->assertOk();

        $this->assertEquals(4.0, (float) $business->fresh()->average_rating);
    }
}

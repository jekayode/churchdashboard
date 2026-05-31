<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BusinessReviewReplyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_reply_to_a_review_once(): void
    {
        $owner = User::factory()->create();
        $business = Business::factory()->active()->for($owner, 'owner')->create();

        $reviewer = User::factory()->create();
        $review = BusinessReview::factory()
            ->approved()
            ->create([
                'business_id' => $business->id,
                'user_id' => $reviewer->id,
            ]);

        $this->actingAs($owner, 'sanctum');

        $this->postJson("/api/biz/businesses/{$business->slug}/reviews/{$review->id}/reply", [
            'body' => 'Thank you for your feedback!',
        ])->assertOk();

        $this->assertDatabaseHas('business_review_replies', [
            'business_review_id' => $review->id,
            'body' => 'Thank you for your feedback!',
        ]);

        $this->postJson("/api/biz/businesses/{$business->slug}/reviews/{$review->id}/reply", [
            'body' => 'Second reply (should fail).',
        ])->assertStatus(409);
    }

    public function test_non_owner_cannot_reply_to_a_review(): void
    {
        $owner = User::factory()->create();
        $business = Business::factory()->active()->for($owner, 'owner')->create();

        $nonOwner = User::factory()->create();
        $reviewer = User::factory()->create();

        $review = BusinessReview::factory()
            ->approved()
            ->create([
                'business_id' => $business->id,
                'user_id' => $reviewer->id,
            ]);

        $this->actingAs($nonOwner, 'sanctum');

        $this->postJson("/api/biz/businesses/{$business->slug}/reviews/{$review->id}/reply", [
            'body' => 'Owner-only response.',
        ])->assertForbidden();
    }

    public function test_reply_to_review_not_belonging_to_business_returns_404(): void
    {
        $owner = User::factory()->create();
        $business = Business::factory()->active()->for($owner, 'owner')->create();

        $reviewer = User::factory()->create();
        $otherBusiness = Business::factory()->active()->create();

        $review = BusinessReview::factory()
            ->approved()
            ->create([
                'business_id' => $otherBusiness->id,
                'user_id' => $reviewer->id,
            ]);

        $this->actingAs($owner, 'sanctum');

        $this->postJson("/api/biz/businesses/{$business->slug}/reviews/{$review->id}/reply", [
            'body' => 'This should not be allowed.',
        ])->assertNotFound();
    }
}

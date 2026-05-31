<?php

declare(strict_types=1);

namespace Tests\Feature\Directory;

use App\Models\Business;
use App\Models\BusinessLike;
use App\Models\BusinessProduct;
use App\Models\ProductLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BusinessLikesTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_like_toggle_is_idempotent(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->active()->create(['likes_count' => 0]);
        $this->actingAs($user, 'sanctum');

        $this->postJson("/api/biz/businesses/{$business->slug}/like")->assertOk();
        $this->assertEquals(1, $business->fresh()->likes_count);
        $this->assertEquals(1, BusinessLike::count());

        $this->postJson("/api/biz/businesses/{$business->slug}/like")->assertOk();
        $this->assertEquals(0, $business->fresh()->likes_count);
        $this->assertEquals(0, BusinessLike::count());
    }

    public function test_product_like_toggle(): void
    {
        $user = User::factory()->create();
        $product = BusinessProduct::factory()->create(['likes_count' => 0]);
        $this->actingAs($user, 'sanctum');

        $this->postJson("/api/biz/products/{$product->id}/like")->assertOk();
        $this->assertEquals(1, ProductLike::count());
    }
}

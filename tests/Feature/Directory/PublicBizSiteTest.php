<?php

declare(strict_types=1);

namespace Tests\Feature\Directory;

use App\Models\Business;
use App\Models\DirectoryCategory;
use App\Models\DirectorySetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicBizSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_is_public(): void
    {
        DirectorySetting::instance();

        $this->get(route('biz.landing'))->assertOk();
    }

    public function test_business_profile_is_accessible_by_slug(): void
    {
        DirectorySetting::instance();
        $business = Business::factory()->active()->create([
            'slug' => 'demo-shop',
            'views_count' => 0,
        ]);

        $this->get(route('biz.show', $business))->assertOk();
        $this->assertEquals(1, $business->fresh()->views_count);
    }

    public function test_pending_business_is_not_public(): void
    {
        DirectorySetting::instance();
        $business = Business::factory()->create(['slug' => 'hidden-shop']);

        $this->get(route('biz.show', $business))->assertNotFound();
    }

    public function test_category_page_lists_businesses(): void
    {
        DirectorySetting::instance();
        $category = DirectoryCategory::factory()->create(['slug' => 'food']);
        $business = Business::factory()->active()->create();
        $business->categories()->attach($category);

        $this->get(route('biz.category', 'food'))->assertOk();
    }

    public function test_sitemap_includes_active_businesses(): void
    {
        Business::factory()->active()->create(['slug' => 'listed-shop']);

        $response = $this->get(route('biz.sitemap'));
        $response->assertOk();
        $response->assertSee('/biz/listed-shop');
    }
}

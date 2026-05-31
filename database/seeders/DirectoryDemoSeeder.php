<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\BusinessStatus;
use App\Enums\ReviewStatus;
use App\Models\Business;
use App\Models\BusinessReview;
use App\Models\BusinessService;
use App\Models\DirectoryCategory;
use App\Models\DirectoryChangelogEntry;
use App\Models\DirectorySetting;
use App\Models\User;
use Illuminate\Database\Seeder;

final class DirectoryDemoSeeder extends Seeder
{
    public function run(): void
    {
        DirectorySetting::instance()->update([
            'tagline' => 'Support our church family businesses',
            'primary_color' => '#F1592A',
            'secondary_color' => '#1e293b',
            'announcement_title' => 'Welcome to our Business Directory',
            'announcement_body' => 'Discover trusted services from members of our community.',
            'announcement_active' => true,
            'business_approval_required' => true,
        ]);

        $categories = collect([
            ['Beauty & Wellness', 'beauty-wellness'],
            ['Food & Catering', 'food-catering'],
            ['Professional Services', 'professional-services'],
            ['Home Services', 'home-services'],
        ])->map(fn ($c) => DirectoryCategory::query()->firstOrCreate(
            ['slug' => $c[1]],
            ['name' => $c[0], 'icon' => 'building-storefront', 'sort_order' => 0, 'is_active' => true]
        ));

        $owner = User::factory()->create(['name' => 'Demo Business Owner', 'email' => 'biz-owner@example.com']);
        if (\App\Models\Role::where('name', 'church_member')->exists()) {
            $owner->assignRole('church_member');
        }

        $business = Business::factory()->for($owner, 'owner')->create([
            'name' => 'Bella Glow Beauty Salon',
            'slug' => 'bella-glow-beauty-salon',
            'tagline' => 'Your beauty, our passion',
            'description' => 'Full-service beauty salon offering hair, nails, facials, and bridal packages.',
            'address' => '123 Queen Street',
            'city' => 'Sydney',
            'state' => 'New South Wales',
            'country' => 'Australia',
            'whatsapp_number' => '61400000000',
            'status' => BusinessStatus::Active,
            'is_featured' => true,
            'approved_at' => now(),
            'average_rating' => 4.8,
            'reviews_count' => 2,
        ]);

        $business->categories()->sync($categories->take(2)->pluck('id'));

        BusinessService::factory()->count(4)->for($business)->create();
        BusinessService::factory()->for($business)->create([
            'name' => 'Haircuts and Styling',
            'duration_text' => '1 hour',
            'price_text' => 'from ₦100,000',
        ]);

        $reviewer = User::factory()->create();
        BusinessReview::factory()->for($business)->for($reviewer)->create([
            'rating' => 5,
            'status' => ReviewStatus::Approved,
            'title' => 'Excellent service',
        ]);

        DirectoryChangelogEntry::factory()->create([
            'version' => '1.0.0',
            'title' => 'Business Directory launch',
            'body' => 'Initial release of the church business directory.',
            'published_at' => now(),
        ]);

        $this->command?->info('Directory demo data seeded.');
    }
}

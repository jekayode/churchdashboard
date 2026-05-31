<?php

declare(strict_types=1);

namespace Tests\Feature\Directory;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class BusinessImageScopingTest extends TestCase
{
    use RefreshDatabase;

    public function test_gallery_delete_endpoint_cannot_remove_logo_media(): void
    {
        Storage::fake('public');
        config()->set('media-library.disk_name', 'public');

        $owner = User::factory()->create();
        $business = Business::factory()->for($owner, 'owner')->active()->create();

        $business->addMedia(UploadedFile::fake()->image('logo.jpg'))
            ->toMediaCollection('logo');

        $logoMediaId = $business->getFirstMedia('logo')->id;

        $this->actingAs($owner, 'sanctum');

        $this->deleteJson("/api/biz/businesses/{$business->slug}/gallery/{$logoMediaId}")
            ->assertNotFound();

        $this->assertNotNull($business->fresh()->getFirstMedia('logo'));
    }
}

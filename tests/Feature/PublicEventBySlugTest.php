<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class PublicEventBySlugTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('Full migrations require MySQL (SQLite fails on MODIFY in guest_follow_ups migration). Run with DB_CONNECTION=mysql to execute these tests.');
        }
    }

    public function test_guest_can_view_public_event_html_by_branch_code_and_slug(): void
    {
        $branch = Branch::factory()->create([
            'public_code' => 'gl',
            'status' => 'active',
        ]);

        $event = Event::factory()->create([
            'branch_id' => $branch->id,
            'public_slug' => 'spring-picnic',
            'is_public' => true,
            'status' => 'active',
            'start_date' => now()->addWeek(),
            'name' => 'Spring Picnic Public',
        ]);

        $response = $this->get('/event/gl/spring-picnic');

        $response->assertOk();
        $response->assertSee('Spring Picnic Public', false);
    }

    public function test_public_event_resolver_loads_branch_status_so_is_active_works(): void
    {
        $branch = Branch::factory()->create([
            'public_code' => 'gl',
            'status' => 'active',
        ]);

        Event::factory()->create([
            'branch_id' => $branch->id,
            'public_slug' => 'easter-potluck',
            'is_public' => true,
            'status' => 'active',
            'start_date' => now()->addWeek(),
        ]);

        $event = Event::findPubliclyVisibleByBranchCodeAndSlug('gl', 'easter-potluck');

        $this->assertNotNull($event->branch);
        $this->assertTrue($event->branch->isActive(), 'Branch status must be selected so public registration isAllowed checks work.');
    }

    public function test_welcome_api_json_detail_uses_branch_code_and_slug(): void
    {
        $branch = Branch::factory()->create([
            'public_code' => 'ojo',
            'status' => 'active',
        ]);

        Event::factory()->create([
            'branch_id' => $branch->id,
            'public_slug' => 'youth-night',
            'is_public' => true,
            'status' => 'active',
            'start_date' => now()->addDays(3),
            'name' => 'Youth Night',
        ]);

        $response = $this->getJson('/api/welcome/event/ojo/youth-night');

        $response->assertOk();
        $response->assertJsonPath('public_slug', 'youth-night');
        $response->assertJsonPath('branch_code', 'ojo');
    }

    public function test_super_admin_can_fetch_public_page_qr_svg(): void
    {
        Role::factory()->create(['name' => 'super_admin']);
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $branch = Branch::factory()->create([
            'public_code' => 'yaba',
            'status' => 'active',
        ]);

        $event = Event::factory()->create([
            'branch_id' => $branch->id,
            'public_slug' => 'test-qr-event',
            'is_public' => true,
            'status' => 'active',
            'start_date' => now()->addWeek(),
        ]);

        Sanctum::actingAs($admin);

        $response = $this->get('/api/events/'.$event->id.'/public-page-qr');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/svg+xml');
    }

    public function test_super_admin_can_download_public_page_qr_high_res(): void
    {
        Role::factory()->create(['name' => 'super_admin']);
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $branch = Branch::factory()->create([
            'public_code' => 'yaba',
            'status' => 'active',
        ]);

        $event = Event::factory()->create([
            'branch_id' => $branch->id,
            'public_slug' => 'test-qr-download',
            'is_public' => true,
            'status' => 'active',
            'start_date' => now()->addWeek(),
            'name' => 'QR Download Event',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->get('/api/events/'.$event->id.'/public-page-qr/download?pixels=512');

        $response->assertOk();
        $contentType = (string) $response->headers->get('Content-Type');
        $this->assertTrue(
            str_starts_with($contentType, 'image/png')
            || str_starts_with($contentType, 'image/svg+xml'),
            'Expected PNG (imagick) or SVG fallback, got: '.$contentType
        );
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertIsString($disposition);
        $this->assertStringContainsString('attachment', $disposition);
        $this->assertTrue(
            str_contains($disposition, '-public-qr.png') || str_contains($disposition, '-public-qr.svg'),
            'Expected downloadable PNG or SVG filename in Content-Disposition: '.$disposition
        );
        $this->assertNotEmpty($response->getContent());
    }
}

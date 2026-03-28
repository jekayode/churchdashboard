<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Event;
use App\Support\PublicEventPayload;
use Carbon\Carbon;
use Tests\TestCase;

final class PublicEventPayloadTest extends TestCase
{
    public function test_for_event_includes_expected_keys(): void
    {
        $branch = Branch::make([
            'id' => 1,
            'name' => 'Greater Lekki',
            'public_code' => 'gl',
            'logo' => null,
            'venue' => 'Main venue',
            'phone' => '0800',
            'email' => 'hi@example.test',
            'service_time' => '10:00',
        ]);

        $event = Event::make([
            'name' => 'Easter Potluck',
            'public_slug' => 'easter-potluck',
            'description' => 'Bring a dish',
            'type' => 'social',
            'service_type' => null,
            'location' => 'Corner Bus Stop',
            'registration_type' => 'simple',
            'registration_link' => null,
            'custom_form_fields' => null,
            'max_capacity' => 50,
        ]);
        $event->setAttribute('start_date', Carbon::parse('2026-04-04 14:00:00'));
        $event->setAttribute('end_date', Carbon::parse('2026-04-04 16:00:00'));
        $event->setRelation('branch', $branch);

        $payload = PublicEventPayload::forEvent($event);

        $this->assertSame('Easter Potluck', $payload['name']);
        $this->assertSame('Bring a dish', $payload['description']);
        $this->assertSame('simple', $payload['registration_type']);
        $this->assertArrayHasKey('cover_image_url', $payload);
        $this->assertIsArray($payload['branch']);
        $this->assertSame('Greater Lekki', $payload['branch']['name']);
        $this->assertSame('Main venue', $payload['branch']['venue']);
        $this->assertSame('easter-potluck', $payload['public_slug']);
        $this->assertSame('gl', $payload['branch_code']);
        $this->assertNotNull($payload['public_url']);
        $this->assertStringContainsString('/event/gl/easter-potluck', (string) $payload['public_url']);
    }
}

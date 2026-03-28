<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Event;
use App\Support\EventIcsGenerator;
use Carbon\Carbon;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EventIcsGeneratorTest extends TestCase
{
    #[Test]
    public function it_builds_ics_with_uid_dtstart_dtend_and_summary(): void
    {
        config(['app.url' => 'https://church.example.test']);

        $event = Event::make([
            'name' => 'Easter Potluck',
            'start_date' => Carbon::parse('2026-04-04 11:00:00', 'UTC'),
            'end_date' => Carbon::parse('2026-04-04 13:00:00', 'UTC'),
            'location' => 'Main Hall',
        ]);
        $event->id = 42;

        $ics = EventIcsGenerator::forEvent($event, 7);

        $this->assertStringContainsString('BEGIN:VCALENDAR', $ics);
        $this->assertStringContainsString('VERSION:2.0', $ics);
        $this->assertStringContainsString('METHOD:PUBLISH', $ics);
        $this->assertStringContainsString('UID:event-42-reg-7@church.example.test', $ics);
        $this->assertStringContainsString('DTSTART:20260404T110000Z', $ics);
        $this->assertStringContainsString('DTEND:20260404T130000Z', $ics);
        $this->assertStringContainsString('SUMMARY:Easter Potluck', $ics);
        $this->assertStringContainsString('LOCATION:Main Hall', $ics);
        $this->assertStringContainsString('END:VCALENDAR', $ics);
    }

    #[Test]
    public function it_uses_one_hour_end_when_end_date_missing(): void
    {
        config(['app.url' => 'https://app.test']);

        $event = Event::make([
            'name' => 'Solo',
            'start_date' => Carbon::parse('2026-01-15 14:00:00', 'UTC'),
            'end_date' => null,
        ]);
        $event->id = 1;

        $ics = EventIcsGenerator::forEvent($event, 1);

        $this->assertStringContainsString('DTSTART:20260115T140000Z', $ics);
        $this->assertStringContainsString('DTEND:20260115T150000Z', $ics);
    }

    #[Test]
    public function it_throws_when_start_date_missing(): void
    {
        $event = Event::make([
            'name' => 'Broken',
            'start_date' => null,
        ]);
        $event->id = 1;

        $this->expectException(InvalidArgumentException::class);

        EventIcsGenerator::forEvent($event, 1);
    }

    #[Test]
    public function it_escapes_special_characters_in_summary(): void
    {
        config(['app.url' => 'https://x.test']);

        $event = Event::make([
            'name' => 'Meet; Discuss, Plan',
            'start_date' => Carbon::parse('2026-06-01 10:00:00', 'UTC'),
        ]);
        $event->id = 3;

        $ics = EventIcsGenerator::forEvent($event, 1);

        $this->assertStringContainsString('SUMMARY:Meet\; Discuss\, Plan', $ics);
    }
}

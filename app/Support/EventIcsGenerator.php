<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Event;
use Carbon\CarbonInterface;
use InvalidArgumentException;

final class EventIcsGenerator
{
    /**
     * Build a minimal RFC 5545 iCalendar document for a public event (UTC times).
     *
     * @throws InvalidArgumentException when the event has no start date
     */
    public static function forEvent(Event $event, int $registrationId): string
    {
        $start = $event->start_date;
        if (! $start instanceof CarbonInterface) {
            throw new InvalidArgumentException('Event start date is required to build a calendar invite.');
        }

        $startUtc = $start->copy()->utc();
        $end = $event->end_date;
        if ($end instanceof CarbonInterface) {
            $endUtc = $end->copy()->utc();
        } else {
            $endUtc = $startUtc->copy()->addHour();
        }

        if ($endUtc->lessThanOrEqualTo($startUtc)) {
            $endUtc = $startUtc->copy()->addHour();
        }

        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost';
        $uid = 'event-'.$event->id.'-reg-'.$registrationId.'@'.$host;
        $dtStamp = now()->utc()->format('Ymd\THis\Z');
        $dtStart = $startUtc->format('Ymd\THis\Z');
        $dtEnd = $endUtc->format('Ymd\THis\Z');

        $summary = self::escapeText($event->name);
        $location = self::escapeText(self::eventLocationLine($event));

        $descriptionParts = [];
        if ($event->description) {
            $descriptionParts[] = trim((string) $event->description);
        }
        $publicUrl = $event->public_detail_url;
        if ($publicUrl) {
            $descriptionParts[] = $publicUrl;
        }
        $description = self::escapeText(implode("\n", array_filter($descriptionParts)));

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//'.self::prodIdVendor().'//Event Registration//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:'.$uid,
            'DTSTAMP:'.$dtStamp,
            'DTSTART:'.$dtStart,
            'DTEND:'.$dtEnd,
            'SUMMARY:'.$summary,
        ];

        if ($location !== '') {
            $lines[] = 'LOCATION:'.$location;
        }

        if ($description !== '') {
            $lines[] = 'DESCRIPTION:'.$description;
        }

        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines)."\r\n";
    }

    private static function prodIdVendor(): string
    {
        $name = (string) config('app.name', 'App');

        return (string) preg_replace('/[^A-Za-z0-9.\-]+/', '-', $name);
    }

    private static function eventLocationLine(Event $event): string
    {
        $parts = array_filter([
            $event->location,
            $event->venue,
            $event->address,
        ], static fn ($v) => $v !== null && $v !== '');

        return implode(', ', array_unique($parts));
    }

    /**
     * Escape TEXT value per RFC 5545.
     */
    private static function escapeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r", "\n"], '\n', $text);

        return str_replace(
            ['\\', ';', ','],
            ['\\\\', '\\;', '\\,'],
            $text
        );
    }
}

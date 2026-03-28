<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Event;

final class PublicEventPayload
{
    /**
     * @return array<string, mixed>
     */
    public static function forEvent(Event $event): array
    {
        $event->loadMissing(['branch:id,name,logo,venue,phone,email,service_time,public_code']);

        $branch = $event->branch;

        return [
            'id' => $event->id,
            'name' => $event->name,
            'public_slug' => $event->public_slug,
            'branch_code' => $branch?->public_code,
            'public_url' => $event->public_detail_url,
            'description' => $event->description,
            'type' => $event->type,
            'service_type' => $event->service_type,
            'location' => $event->location,
            'start_date' => $event->start_date?->toIso8601String(),
            'end_date' => $event->end_date?->toIso8601String(),
            'start_time' => $event->service_time?->format('H:i'),
            'end_time' => $event->service_end_time?->format('H:i'),
            'cover_image_url' => $event->cover_image_url,
            'registration_type' => $event->registration_type,
            'registration_link' => $event->registration_link,
            'custom_form_fields' => $event->custom_form_fields,
            'max_capacity' => $event->max_capacity,
            'is_upcoming' => $event->isUpcoming(),
            'branch' => $branch ? [
                'id' => $branch->id,
                'name' => $branch->name,
                'public_code' => $branch->public_code,
                'logo_url' => $branch->logo_url,
                'venue' => $branch->venue,
                'phone' => $branch->phone,
                'email' => $branch->email,
                'service_time' => $branch->service_time,
            ] : null,
        ];
    }
}

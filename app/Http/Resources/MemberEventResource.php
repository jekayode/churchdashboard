<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * An event as presented to a member in the mobile app.
 *
 * @property-read \App\Models\Event $resource
 */
final class MemberEventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->public_slug,
            'description' => $this->resource->description,
            'type' => $this->resource->type,
            'location' => $this->resource->location,
            'start_date' => $this->resource->start_date?->toIso8601String(),
            // Pre-formatted so the app doesn't have to know about second services.
            'time_label' => $this->resource->time_label,
            'end_date' => $this->resource->end_date?->toIso8601String(),
            'max_capacity' => $this->resource->max_capacity,
            // null when the event is uncapped; the app shows "N spots left".
            'spots_remaining' => $this->resource->spots_remaining,
            'cover_url' => $this->resource->cover_url,
            'registration_type' => $this->resource->registration_type,
            // The pastor's form-builder definition, so the app can render the
            // same custom fields natively.
            'custom_form_fields' => $this->resource->custom_form_fields,
            'registrations_count' => $this->whenCounted('registrations'),
            'is_registered' => $this->when(
                isset($this->resource->is_registered),
                fn (): bool => (bool) $this->resource->is_registered,
            ),
            'branch' => $this->whenLoaded('branch', fn (): ?array => $this->resource->branch === null ? null : [
                'id' => $this->resource->branch->id,
                'name' => $this->resource->branch->name,
            ]),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A small group as presented to a member in the mobile app.
 *
 * @property-read \App\Models\SmallGroup $resource
 */
final class MemberSmallGroupResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'meeting_day' => $this->resource->meeting_day,
            // Time-of-day only; the column is a TIME, so avoid leaking today's date.
            'meeting_time' => $this->resource->meeting_time?->format('H:i'),
            'location' => $this->resource->location,
            'cover_url' => $this->resource->cover_url,
            'status' => $this->resource->status,
            'members_count' => $this->whenCounted('members'),
            'leader' => $this->whenLoaded('leader', fn (): ?array => $this->resource->leader === null ? null : [
                'id' => $this->resource->leader->id,
                'name' => $this->resource->leader->name,
            ]),
            // Only exposed for groups the member actually belongs to.
            'members' => $this->whenLoaded('members', fn () => $this->resource->members->map(fn ($member): array => [
                'id' => $member->id,
                'name' => $member->name,
            ])),
            'join_request_status' => $this->when(
                isset($this->resource->join_request_status),
                fn () => $this->resource->join_request_status,
            ),
        ];
    }
}

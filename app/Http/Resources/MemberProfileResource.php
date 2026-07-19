<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The authenticated member's own profile.
 *
 * @property-read \App\Models\Member $resource
 */
final class MemberProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'first_name' => $this->resource->first_name,
            'surname' => $this->resource->surname,
            'email' => $this->resource->email,
            'phone' => $this->resource->phone,
            'gender' => $this->resource->gender,
            'date_of_birth' => $this->resource->date_of_birth?->toDateString(),
            'marital_status' => $this->resource->marital_status,
            'occupation' => $this->resource->occupation,
            'home_address' => $this->resource->home_address,
            'nearest_bus_stop' => $this->resource->nearest_bus_stop,
            'member_status' => $this->resource->member_status,
            'growth_level' => $this->resource->growth_level,
            'teci_status' => $this->resource->teci_status,
            'profile_completion_percentage' => $this->resource->profile_completion_percentage,
            // Drives the app's "Member since" line.
            'date_joined' => $this->resource->date_joined?->toDateString(),
            'branch' => $this->whenLoaded('branch', fn (): ?array => $this->resource->branch === null ? null : [
                'id' => $this->resource->branch->id,
                'name' => $this->resource->branch->name,
                'venue' => $this->resource->branch->venue,
                'service_time' => $this->resource->branch->service_time,
            ]),
        ];
    }
}

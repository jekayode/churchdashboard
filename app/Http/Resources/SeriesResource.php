<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read \App\Models\Series $resource
 */
final class SeriesResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'tone' => $this->resource->tone,
            'cover_url' => $this->resource->cover_url,
            'starts_on' => $this->resource->starts_on?->toDateString(),
            'ends_on' => $this->resource->ends_on?->toDateString(),
            'sermons_count' => $this->whenCounted('sermons'),
        ];
    }
}

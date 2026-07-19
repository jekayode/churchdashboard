<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A sermon as presented to a member in the mobile app.
 *
 * @property-read \App\Models\Sermon $resource
 */
final class SermonResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'speaker' => $this->resource->speaker,
            'preached_on' => $this->resource->preached_on?->toDateString(),
            'duration_seconds' => $this->resource->duration_seconds,
            'duration_label' => $this->resource->duration_label,
            'tone' => $this->resource->tone,
            'is_live' => $this->resource->is_live,
            'live_url' => $this->when($this->resource->is_live, fn () => $this->resource->live_url),
            'cover_url' => $this->resource->cover_url,
            'video_url' => $this->resource->video_url,
            // Non-null when the video is on YouTube, so the app can embed it.
            'youtube_id' => $this->resource->youtube_id,
            'recording_url' => $this->resource->recording_url,
            'is_saved' => $this->when(
                isset($this->resource->is_saved),
                fn (): bool => (bool) $this->resource->is_saved,
            ),
            'series' => $this->whenLoaded('series', fn (): ?array => $this->resource->series === null ? null : [
                'id' => $this->resource->series->id,
                'name' => $this->resource->series->name,
                'slug' => $this->resource->series->slug,
                'tone' => $this->resource->series->tone,
                'cover_url' => $this->resource->series->cover_url,
            ]),
            'passages' => $this->whenLoaded('passages', fn () => $this->resource->passages->map(fn ($passage): array => [
                'id' => $passage->id,
                'reference' => $passage->reference,
                'book' => $passage->book,
                'chapter' => $passage->chapter,
                'verses' => $passage->verses,
            ])),
            'slides' => $this->when(
                $this->resource->relationLoaded('media'),
                fn () => $this->resource->getMedia('slides')->map(fn ($media): array => [
                    'id' => $media->id,
                    'name' => $media->name,
                    'url' => $media->getUrl(),
                    'mime_type' => $media->mime_type,
                ])->values(),
            ),
        ];
    }
}

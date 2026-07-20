<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class Sermon extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\SermonFactory> */
    use HasFactory, InteractsWithMedia, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'branch_id',
        'series_id',
        'title',
        'slug',
        'description',
        'speaker',
        'speaker_member_id',
        'preached_on',
        'duration_seconds',
        'tone',
        'is_live',
        'live_url',
        'video_url',
        'is_published',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'preached_on' => 'date',
        'duration_seconds' => 'integer',
        'is_live' => 'boolean',
        'is_published' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $sermon): void {
            if (blank($sermon->slug)) {
                $sermon->slug = self::uniqueSlug($sermon->title);
            }
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'sermon';
        $slug = $base;
        $suffix = 1;

        while (self::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$suffix);
        }

        return $slug;
    }

    /**
     * Recording and slides are large files, so they live on the media disk
     * (Cloudflare R2 in production) rather than the app server.
     */
    public function registerMediaCollections(): void
    {
        $disk = config('filesystems.media_disk', 'public');

        $this->addMediaCollection('recording')->singleFile()->useDisk($disk);
        $this->addMediaCollection('slides')->useDisk($disk);
        $this->addMediaCollection('cover')->singleFile()->useDisk($disk);
    }

    /**
     * YouTube video id, from any of the URL shapes a pastor might paste:
     * watch?v=, youtu.be/, /embed/ and /live/.
     */
    public function getYoutubeIdAttribute(): ?string
    {
        $url = $this->video_url;

        if (blank($url)) {
            return null;
        }

        $patterns = [
            '~youtu\.be/([A-Za-z0-9_-]{11})~',
            '~[?&]v=([A-Za-z0-9_-]{11})~',
            '~/embed/([A-Za-z0-9_-]{11})~',
            '~/live/([A-Za-z0-9_-]{11})~',
            '~/shorts/([A-Za-z0-9_-]{11})~',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches) === 1) {
                return $matches[1];
            }
        }

        return null;
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->getFirstMedia('cover')?->getUrl();
    }

    public function getRecordingUrlAttribute(): ?string
    {
        return $this->getFirstMedia('recording')?->getUrl();
    }

    /**
     * Duration as mm:ss (or h:mm:ss), matching the app's sermon cards.
     */
    public function getDurationLabelAttribute(): ?string
    {
        if ($this->duration_seconds === null || $this->duration_seconds <= 0) {
            return null;
        }

        $seconds = $this->duration_seconds;
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remaining = $seconds % 60;

        return $hours > 0
            ? sprintf('%d:%02d:%02d', $hours, $minutes, $remaining)
            : sprintf('%d:%02d', $minutes, $remaining);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function speakerMember(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'speaker_member_id');
    }

    public function passages(): HasMany
    {
        return $this->hasMany(SermonPassage::class)->orderBy('position');
    }

    public function savedByMembers(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'member_saved_sermons')
            ->withPivot('saved_at')
            ->withTimestamps();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeForBranch(Builder $query, ?int $branchId): Builder
    {
        // Sermons without a branch are network-wide and visible to everyone.
        return $query->where(function (Builder $inner) use ($branchId): void {
            $inner->whereNull('branch_id');

            if ($branchId !== null) {
                $inner->orWhere('branch_id', $branchId);
            }
        });
    }
}

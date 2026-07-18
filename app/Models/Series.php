<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class Series extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\SeriesFactory> */
    use HasFactory, InteractsWithMedia, SoftDeletes;

    /**
     * "series" is both singular and plural, so Laravel's inflector guess
     * ("serie") has to be corrected.
     */
    protected $table = 'series';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'branch_id',
        'name',
        'slug',
        'description',
        'tone',
        'starts_on',
        'ends_on',
        'is_published',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
        'is_published' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $series): void {
            if (blank($series->slug)) {
                $series->slug = self::uniqueSlug($series->name);
            }
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'series';
        $slug = $base;
        $suffix = 1;

        while (self::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$suffix);
        }

        return $slug;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->singleFile()
            ->useDisk(config('filesystems.media_disk', 'public'));
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->getFirstMedia('cover')?->getUrl();
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function sermons(): HasMany
    {
        return $this->hasMany(Sermon::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BusinessStatus;
use App\Services\BusinessHoursService;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class Business extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\BusinessFactory> */
    use HasFactory, InteractsWithMedia, SoftDeletes;

    public const RESERVED_SLUGS = [
        'search',
        'categories',
        'changelog',
        'owner',
        'account',
        'messages',
        'favorites',
        'reviews',
        'admin',
        'api',
    ];

    protected $fillable = [
        'owner_user_id',
        'name',
        'slug',
        'tagline',
        'description',
        'phone',
        'whatsapp_number',
        'email',
        'website',
        'address',
        'city',
        'state',
        'country',
        'latitude',
        'longitude',
        'social_facebook',
        'social_instagram',
        'social_twitter',
        'social_tiktok',
        'social_youtube',
        'social_linkedin',
        'working_hours',
        'status',
        'is_featured',
        'featured_until',
        'rejection_reason',
        'views_count',
        'likes_count',
        'reviews_count',
        'average_rating',
        'approved_at',
        'approved_by_user_id',
        'owner_deactivated',
    ];

    protected function casts(): array
    {
        return [
            'working_hours' => 'array',
            'status' => BusinessStatus::class,
            'is_featured' => 'boolean',
            'featured_until' => 'date',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'average_rating' => 'decimal:2',
            'approved_at' => 'datetime',
            'owner_deactivated' => 'boolean',
        ];
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(128)
            ->height(128)
            ->nonQueued();

        $this->addMediaConversion('medium')
            ->width(800)
            ->height(600)
            ->nonQueued();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(DirectoryCategory::class, 'business_category');
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(BusinessTeamMember::class)->orderBy('sort_order');
    }

    public function services(): HasMany
    {
        return $this->hasMany(BusinessService::class)->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(BusinessProduct::class)->orderBy('sort_order');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(BusinessPost::class)->latest('published_at');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(BusinessReview::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(BusinessLike::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(BusinessMessage::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', BusinessStatus::Active)
            ->where('owner_deactivated', false);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
            ->where(function ($q) {
                $q->whereNull('featured_until')
                    ->orWhere('featured_until', '>=', now()->toDateString());
            });
    }

    public function scopePubliclyVisible($query)
    {
        return $query->active();
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_user_id === $user->id;
    }

    public function isPubliclyVisible(): bool
    {
        return $this->status === BusinessStatus::Active && ! $this->owner_deactivated;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getWhatsappUrlAttribute(): ?string
    {
        if (! $this->whatsapp_number) {
            return null;
        }

        $number = preg_replace('/[^0-9]/', '', $this->whatsapp_number);

        return 'https://wa.me/'.$number;
    }

    public static function generateSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (self::slugTaken($slug, $excludeId)) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public static function slugTaken(string $slug, ?int $excludeId = null): bool
    {
        if (in_array($slug, self::RESERVED_SLUGS, true)) {
            return true;
        }

        $query = self::query()->where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * @return array{
     *     is_open_now: bool,
     *     status_label: string,
     *     hours_summary: string|null,
     *     closed_all_day: bool
     * }|null
     */
    public function openingStatus(?CarbonInterface $at = null): ?array
    {
        return app(BusinessHoursService::class)->statusForBusiness($this, $at);
    }
}

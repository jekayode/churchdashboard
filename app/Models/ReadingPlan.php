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

final class ReadingPlan extends Model
{
    /** @use HasFactory<\Database\Factories\ReadingPlanFactory> */
    use HasFactory, SoftDeletes;

    public const TYPE_PASSAGES = 'passages';

    public const TYPE_DEVOTIONAL = 'devotional';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'branch_id', 'name', 'slug', 'description', 'type', 'is_annual',
        'length_days', 'tone', 'is_published', 'is_default', 'attribution', 'source_url',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_annual' => 'boolean',
        'is_published' => 'boolean',
        'is_default' => 'boolean',
        'length_days' => 'integer',
    ];

    protected static function booted(): void
    {
        self::creating(function (self $plan): void {
            if (blank($plan->slug)) {
                $plan->slug = self::uniqueSlug($plan->name);
            }
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'plan';
        $slug = $base;
        $suffix = 1;

        while (self::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$suffix);
        }

        return $slug;
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(ReadingDay::class)->orderBy('day_number');
    }

    public function enrolments(): HasMany
    {
        return $this->hasMany(MemberPlanEnrolment::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function isAnnual(): bool
    {
        return $this->is_annual;
    }

    /**
     * The day a member should be on today.
     *
     * Annual plans map to the calendar (29 February falls back to 28 February
     * so leap years do not lose a day). Finite plans count from the start date.
     */
    public function dayForDate(\DateTimeInterface $date, ?\DateTimeInterface $startedOn = null): ?ReadingDay
    {
        if ($this->isAnnual()) {
            $monthDay = $date->format('md');

            return $this->days()->where('month_day', $monthDay)->first()
                ?? ($monthDay === '0229' ? $this->days()->where('month_day', '0228')->first() : null);
        }

        if ($startedOn === null) {
            return $this->days()->where('day_number', 1)->first();
        }

        $start = \Carbon\CarbonImmutable::instance(\Carbon\Carbon::instance($startedOn))->startOfDay();
        $today = \Carbon\CarbonImmutable::instance(\Carbon\Carbon::instance($date))->startOfDay();
        $dayNumber = $start->diffInDays($today) + 1;

        return $this->days()->where('day_number', $dayNumber)->first();
    }
}

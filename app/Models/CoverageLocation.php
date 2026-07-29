<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A "closest location" option — the areas a branch's LifeGroups cover.
 *
 * Was a list hardcoded across half a dozen Blade files, so changing it meant
 * editing views and it drifted between them. Now one branch-scoped table the
 * pastor manages, read by every closest-location dropdown.
 */
final class CoverageLocation extends Model
{
    /** @use HasFactory<\Database\Factories\CoverageLocationFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = ['branch_id', 'name', 'sort_order', 'is_active'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * @param  Builder<CoverageLocation>  $query
     */
    public function scopeForBranch(Builder $query, int $branchId): void
    {
        $query->where('branch_id', $branchId);
    }

    /**
     * @param  Builder<CoverageLocation>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * The active option names for a branch, in order — what a dropdown shows.
     *
     * @return list<string>
     */
    public static function optionsForBranch(?int $branchId): array
    {
        if ($branchId === null) {
            return [];
        }

        return self::query()
            ->forBranch($branchId)
            ->where('is_active', true)
            ->ordered()
            ->pluck('name')
            ->all();
    }
}

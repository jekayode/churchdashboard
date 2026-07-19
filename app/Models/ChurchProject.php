<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ChurchProject extends Model
{
    /** @use HasFactory<\Database\Factories\ChurchProjectFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'branch_id', 'name', 'description', 'period',
        'giving_account_id', 'sort_order', 'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function givingAccount(): BelongsTo
    {
        return $this->belongsTo(GivingAccount::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Branch extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'logo',
        'venue',
        'service_time',
        'phone',
        'email',
        'map_embed_code',
        'pastor_id',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the pastor for this branch.
     */
    public function pastor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pastor_id');
    }

    /**
     * Get the members for this branch.
     */
    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    /**
     * Get the ministries for this branch.
     */
    public function ministries(): HasMany
    {
        return $this->hasMany(Ministry::class);
    }

    /**
     * Get the small groups for this branch.
     */
    public function smallGroups(): HasMany
    {
        return $this->hasMany(SmallGroup::class);
    }

    /**
     * Get the events for this branch.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get the expenses for this branch.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the projections for this branch.
     */
    public function projections(): HasMany
    {
        return $this->hasMany(Projection::class);
    }

    /**
     * Scope to get active branches.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if the branch is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}

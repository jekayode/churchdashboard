<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Department extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ministry_id',
        'name',
        'description',
        'leader_id',
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
     * Get the ministry this department belongs to.
     */
    public function ministry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class);
    }

    /**
     * Get the leader of this department.
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'leader_id');
    }

    /**
     * Get the members assigned to this department.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'member_departments')
            ->withPivot('assigned_at')
            ->withTimestamps();
    }

    /**
     * Scope to get active departments.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get departments by ministry.
     */
    public function scopeByMinistry($query, int $ministryId)
    {
        return $query->where('ministry_id', $ministryId);
    }

    /**
     * Check if the department is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}

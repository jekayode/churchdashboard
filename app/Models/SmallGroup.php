<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class SmallGroup extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'branch_id',
        'name',
        'description',
        'leader_id',
        'meeting_day',
        'meeting_time',
        'location',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'meeting_time' => 'datetime:H:i',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the branch this small group belongs to.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the leader of this small group.
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'leader_id');
    }

    /**
     * Get the members in this small group.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'member_small_groups')
            ->withPivot('joined_at')
            ->withTimestamps();
    }

    /**
     * Get the meeting reports for this small group.
     */
    public function meetingReports(): HasMany
    {
        return $this->hasMany(SmallGroupMeetingReport::class);
    }

    /**
     * Scope to get active small groups.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get small groups by branch.
     */
    public function scopeByBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Check if the small group is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}

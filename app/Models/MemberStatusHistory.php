<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MemberStatusHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'changed_by',
        'previous_status',
        'new_status',
        'reason',
        'notes',
        'changed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'changed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the member this status change belongs to.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the user who made this status change.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Scope to get recent status changes.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('changed_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to get status changes by member.
     */
    public function scopeForMember($query, int $memberId)
    {
        return $query->where('member_id', $memberId);
    }

    /**
     * Scope to get status changes by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('new_status', $status);
    }

    /**
     * Get formatted status change description.
     */
    public function getDescriptionAttribute(): string
    {
        $previous = $this->previous_status ?? 'none';
        return "Changed from '{$previous}' to '{$this->new_status}'";
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class GuestFollowUp extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_id',
        'assigned_to',
        'follow_up_type',
        'contact_date',
        'contact_status',
        'notes',
        'next_follow_up_date',
        'outcome',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'contact_date' => 'datetime',
        'next_follow_up_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('contact_status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('contact_status', 'completed');
    }

    public function scopeByBranch($query, int $branchId)
    {
        return $query->whereHas('member', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        });
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByFollowUpType($query, string $type)
    {
        return $query->where('follow_up_type', $type);
    }

    public function scopeByOutcome($query, string $outcome)
    {
        return $query->where('outcome', $outcome);
    }
}

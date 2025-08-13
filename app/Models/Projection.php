<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Projection extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'branch_id',
        'year',
        'attendance_target',
        'converts_target',
        'leaders_target',
        'volunteers_target',
        'quarterly_breakdown',
        'monthly_breakdown',
        'quarterly_attendance',
        'quarterly_converts',
        'quarterly_leaders',
        'quarterly_volunteers',
        'quarterly_actual_attendance',
        'quarterly_actual_converts',
        'quarterly_actual_leaders',
        'quarterly_actual_volunteers',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'is_current_year',
        'submitted_at',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quarterly_breakdown' => 'array',
        'monthly_breakdown' => 'array',
        'quarterly_attendance' => 'array',
        'quarterly_converts' => 'array',
        'quarterly_leaders' => 'array',
        'quarterly_volunteers' => 'array',
        'quarterly_actual_attendance' => 'array',
        'quarterly_actual_converts' => 'array',
        'quarterly_actual_leaders' => 'array',
        'quarterly_actual_volunteers' => 'array',
        'is_current_year' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the branch this projection belongs to.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who created this projection.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this projection.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who rejected this projection.
     */
    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Scope to get projections by branch.
     */
    public function scopeByBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to get projections by year.
     */
    public function scopeByYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Get current year projection for a branch.
     */
    public function scopeCurrentYear($query)
    {
        return $query->where('year', now()->year);
    }

    /**
     * Scope to get approved projections.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get pending review projections.
     */
    public function scopePendingReview($query)
    {
        return $query->where('status', 'in_review');
    }

    /**
     * Scope to get draft projections.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to get current year designated projections.
     */
    public function scopeCurrentYearDesignated($query)
    {
        return $query->where('is_current_year', true);
    }

    /**
     * Check if projection is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if projection is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if projection is in review.
     */
    public function isInReview(): bool
    {
        return $this->status === 'in_review';
    }

    /**
     * Check if projection is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Submit projection for review.
     */
    public function submitForReview(): bool
    {
        if ($this->status !== 'draft' && $this->status !== 'rejected') {
            return false;
        }

        $updateData = [
            'status' => 'in_review',
            'submitted_at' => now(),
        ];

        // Clear rejection data if resubmitting a rejected projection
        if ($this->status === 'rejected') {
            $updateData['rejected_by'] = null;
            $updateData['rejected_at'] = null;
            $updateData['rejection_reason'] = null;
        }

        return $this->update($updateData);
    }

    /**
     * Approve projection.
     */
    public function approve(User $approver, ?string $notes = null): bool
    {
        if ($this->status !== 'in_review') {
            return false;
        }

        return $this->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    /**
     * Reject projection.
     */
    public function reject(User $rejector, string $reason): bool
    {
        if ($this->status !== 'in_review') {
            return false;
        }

        return $this->update([
            'status' => 'rejected',
            'rejected_by' => $rejector->id,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Set as current year projection.
     */
    public function setAsCurrentYear(): bool
    {
        if (!$this->isApproved()) {
            return false;
        }

        // Unset all other current year projections for this branch
        static::where('branch_id', $this->branch_id)
            ->where('id', '!=', $this->id)
            ->update(['is_current_year' => false]);

        return $this->update(['is_current_year' => true]);
    }
}

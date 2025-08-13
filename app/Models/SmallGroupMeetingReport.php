<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class SmallGroupMeetingReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'small_group_id',
        'reported_by',
        'meeting_date',
        'meeting_time',
        'meeting_location',
        'male_attendance',
        'female_attendance',
        'children_attendance',
        'first_time_guests',
        'converts',
        'total_attendance',
        'meeting_notes',
        'prayer_requests',
        'testimonies',
        'attendee_names',
        'status',
        'rejection_reason',
        'submitted_at',
        'approved_at',
        'approved_by',
    ];

    protected $appends = [
        'guests_count',
        'first_time_visitors', 
        'converts_count',
        'meeting_topic'
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'meeting_time' => 'datetime:H:i',
        'attendee_names' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'male_attendance' => 'integer',
        'female_attendance' => 'integer',
        'children_attendance' => 'integer',
        'first_time_guests' => 'integer',
        'converts' => 'integer',
        'total_attendance' => 'integer',
    ];

    /**
     * Get the small group that this report belongs to.
     */
    public function smallGroup(): BelongsTo
    {
        return $this->belongsTo(SmallGroup::class);
    }

    /**
     * Get the user who reported this meeting.
     */
    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Get the user who approved this report.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('meeting_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by small group.
     */
    public function scopeForSmallGroup($query, $smallGroupId)
    {
        return $query->where('small_group_id', $smallGroupId);
    }

    /**
     * Scope to filter by branch through small group relationship.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->whereHas('smallGroup', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        });
    }

    /**
     * Calculate total attendance automatically before saving.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($report) {
            $report->total_attendance = $report->male_attendance + 
                                      $report->female_attendance + 
                                      $report->children_attendance;
        });
    }

    /**
     * Get the adult attendance count.
     */
    public function getAdultAttendanceAttribute(): int
    {
        return $this->male_attendance + $this->female_attendance;
    }

    /**
     * Get guests count (alias for first_time_guests).
     */
    public function getGuestsCountAttribute(): int
    {
        return $this->first_time_guests ?? 0;
    }

    /**
     * Get first time visitors (alias for first_time_guests).
     */
    public function getFirstTimeVisitorsAttribute(): int
    {
        return $this->first_time_guests ?? 0;
    }

    /**
     * Get converts count (alias for converts).
     */
    public function getConvertsCountAttribute(): int
    {
        return $this->converts ?? 0;
    }

    /**
     * Get meeting topic (from meeting_notes for now).
     */
    public function getMeetingTopicAttribute(): ?string
    {
        // For now, we'll extract from meeting_notes or return null
        // In a future update, we could add a dedicated meeting_topic column
        return null;
    }

    /**
     * Check if the report is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the report is pending approval.
     */
    public function isPending(): bool
    {
        return $this->status === 'submitted';
    }

    /**
     * Check if the report is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Member extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'branch_id',
        'name',
        'email',
        'phone',
        'date_of_birth',
        'anniversary',
        'gender',
        'marital_status',
        'occupation',
        'nearest_bus_stop',
        'date_joined',
        'date_attended_membership_class',
        'teci_status',
        'growth_level',
        'leadership_trainings',
        'member_status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'anniversary' => 'date',
        'date_joined' => 'date',
        'date_attended_membership_class' => 'date',
        'leadership_trainings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user account for this member.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the branch this member belongs to.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the departments this member is assigned to.
     */
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'member_departments')
            ->withPivot('assigned_at')
            ->withTimestamps();
    }

    /**
     * Get the small groups this member belongs to.
     */
    public function smallGroups(): BelongsToMany
    {
        return $this->belongsToMany(SmallGroup::class, 'member_small_groups')
            ->withPivot('joined_at')
            ->withTimestamps();
    }

    /**
     * Get the ministries this member leads.
     */
    public function ledMinistries(): HasMany
    {
        return $this->hasMany(Ministry::class, 'leader_id');
    }

    /**
     * Get the departments this member leads.
     */
    public function ledDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'leader_id');
    }

    /**
     * Get the small groups this member leads.
     */
    public function ledSmallGroups(): HasMany
    {
        return $this->hasMany(SmallGroup::class, 'leader_id');
    }

    /**
     * Get the event registrations for this member.
     */
    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Get the status history for this member.
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(MemberStatusHistory::class)->orderBy('changed_at', 'desc');
    }

    /**
     * Scope to get members by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('member_status', $status);
    }

    /**
     * Scope to get members by branch.
     */
    public function scopeByBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Check if member is a leader.
     */
    public function isLeader(): bool
    {
        return in_array($this->member_status, ['leader', 'minister']);
    }

    /**
     * Check if member is a volunteer.
     */
    public function isVolunteer(): bool
    {
        return in_array($this->member_status, ['volunteer', 'leader', 'minister']);
    }

    /**
     * Get the member's age.
     */
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    /**
     * Update member status based on assignments.
     */
    public function updateStatusBasedOnAssignments(): void
    {
        // Don't change status if member is a visitor
        if ($this->member_status === 'visitor') {
            return;
        }

        $oldStatus = $this->member_status;

        if ($this->ledMinistries()->exists()) {
            $this->member_status = 'minister';
        } elseif ($this->ledDepartments()->exists() || $this->ledSmallGroups()->exists()) {
            $this->member_status = 'leader';
        } elseif ($this->departments()->exists() || $this->smallGroups()->exists()) {
            $this->member_status = 'volunteer';
        } else {
            // If no assignments, default to 'member' (unless they were already a visitor)
            $this->member_status = 'member';
        }
        
        // Only save and log if status actually changed
        if ($oldStatus !== $this->member_status) {
            $this->save();
            
            // Log the status change automatically
            $this->logStatusChange(
                $oldStatus,
                $this->member_status,
                'Automatic status update based on role assignments',
                null,
                auth()->id() ?? 1 // Use system user if no authenticated user
            );
        }
    }

    /**
     * Change member status with history tracking.
     */
    public function changeStatus(
        string $newStatus,
        ?string $reason = null,
        ?string $notes = null,
        ?int $changedBy = null
    ): bool {
        $previousStatus = $this->member_status;
        
        if ($previousStatus === $newStatus) {
            return false; // No change needed
        }

        $this->member_status = $newStatus;
        $saved = $this->save();

        if ($saved) {
            $this->logStatusChange($previousStatus, $newStatus, $reason, $notes, $changedBy);
        }

        return $saved;
    }

    /**
     * Log status change to history.
     */
    private function logStatusChange(
        ?string $previousStatus,
        string $newStatus,
        ?string $reason = null,
        ?string $notes = null,
        ?int $changedBy = null
    ): void {
        MemberStatusHistory::create([
            'member_id' => $this->id,
            'changed_by' => $changedBy ?? auth()->id() ?? 1,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'reason' => $reason,
            'notes' => $notes,
            'changed_at' => now(),
        ]);
    }
}

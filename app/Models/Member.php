<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class Member extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'branch_id',
        'name',
        'first_name',
        'surname',
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
        // Guest form fields
        'preferred_call_time',
        'home_address',
        'age_group',
        'prayer_request',
        'discovery_source',
        'staying_intention',
        'closest_location',
        'additional_info',
        'consent_given_at',
        'consent_ip',
        'profile_completion_percentage',
        'registration_source',
        'spouse_id',
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
        'consent_given_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Media Library: register conversions and collections
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(128)
            ->height(128)
            ->nonQueued();

        $this->addMediaConversion('medium')
            ->width(512)
            ->height(512)
            ->nonQueued();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_image')->singleFile();
        $this->addMediaCollection('couple_image')->singleFile();
    }

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

    public function spouse(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'spouse_id');
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
     * Get the ministries this member belongs to (through departments).
     */
    public function ministries()
    {
        return Ministry::whereHas('departments.members', function ($query) {
            $query->where('members.id', $this->id);
        });
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
     * Get the full name from first_name and surname.
     */
    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '').' '.($this->surname ?? ''));
    }

    /**
     * Mutator to auto-populate name field when first_name changes.
     */
    public function setFirstNameAttribute($value): void
    {
        $this->attributes['first_name'] = $value;
        $this->updateNameField();
    }

    /**
     * Mutator to auto-populate name field when surname changes.
     */
    public function setSurnameAttribute($value): void
    {
        $this->attributes['surname'] = $value;
        $this->updateNameField();
    }

    /**
     * Update the name field based on first_name and surname.
     */
    private function updateNameField(): void
    {
        $firstName = $this->attributes['first_name'] ?? '';
        $surname = $this->attributes['surname'] ?? '';

        if (empty($surname)) {
            $this->attributes['name'] = trim($firstName);
        } else {
            $this->attributes['name'] = trim($firstName.' '.$surname);
        }
    }

    /**
     * Calculate profile completion percentage.
     */
    public function calculateProfileCompletion(): int
    {
        $totalFields = 15; // Total number of profile fields (excluding system fields)
        $filledFields = 0;

        $fields = [
            'first_name', 'surname', 'email', 'phone', 'date_of_birth',
            'gender', 'marital_status', 'home_address', 'age_group',
            'prayer_request', 'discovery_source', 'staying_intention',
            'closest_location', 'additional_info', 'preferred_call_time',
        ];

        foreach ($fields as $field) {
            if (! empty($this->$field)) {
                $filledFields++;
            }
        }

        return (int) round(($filledFields / $totalFields) * 100);
    }

    /**
     * Update profile completion percentage.
     */
    public function updateProfileCompletion(): void
    {
        $this->profile_completion_percentage = $this->calculateProfileCompletion();
        $this->save();
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
     * Get the guest follow-ups for this member.
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(GuestFollowUp::class);
    }

    /**
     * Get the guest prayer requests for this member.
     */
    public function prayerRequests(): HasMany
    {
        return $this->hasMany(GuestPrayerRequest::class);
    }

    /**
     * Get the guest membership pipeline for this member.
     */
    public function membershipPipeline(): HasOne
    {
        return $this->hasOne(GuestMembershipPipeline::class);
    }

    /**
     * Scope to get only guest members.
     */
    public function scopeGuests($query)
    {
        return $query->where('member_status', 'visitor')
            ->where('registration_source', 'guest-form');
    }

    /**
     * Scope to get recent guests.
     */
    public function scopeRecentGuests($query, int $days = 7)
    {
        return $query->guests()->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to get guests by branch.
     */
    public function scopeGuestsByBranch($query, int $branchId)
    {
        return $query->guests()->where('branch_id', $branchId);
    }

    /**
     * Scope to get guests by staying intention.
     */
    public function scopeGuestsByStayingIntention($query, string $intention)
    {
        return $query->guests()->where('staying_intention', $intention);
    }

    /**
     * Check if this member is a guest.
     */
    public function isGuest(): bool
    {
        return $this->member_status === 'visitor' && $this->registration_source === 'guest-form';
    }

    /**
     * Check if this guest has high membership interest.
     */
    public function hasHighMembershipInterest(): bool
    {
        return $this->staying_intention === 'yes-for-sure';
    }

    /**
     * Check if this guest has a prayer request.
     */
    public function hasPrayerRequest(): bool
    {
        return ! empty($this->prayer_request);
    }
}

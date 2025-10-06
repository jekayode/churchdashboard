<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

final class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'email_verified_at',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_enabled' => 'boolean',
        'two_factor_confirmed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the roles for this user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('branch_id')
            ->withTimestamps();
    }

    /**
     * Get the member profile for this user.
     */
    public function member(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    /**
     * Get the branches this user pastors.
     */
    public function pastoredBranches(): HasMany
    {
        return $this->hasMany(Branch::class, 'pastor_id');
    }

    /**
     * Get the event registrations for this user.
     */
    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Get the event reports created by this user.
     */
    public function eventReports(): HasMany
    {
        return $this->hasMany(EventReport::class, 'reported_by');
    }

    /**
     * Get the expenses created by this user.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'created_by');
    }

    /**
     * Get the projections created by this user.
     */
    public function projections(): HasMany
    {
        return $this->hasMany(Projection::class, 'created_by');
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleName, ?int $branchId = null): bool
    {
        $query = $this->roles()->where('name', $roleName);

        if ($branchId !== null) {
            $query->wherePivot('branch_id', $branchId);
        }

        return $query->exists();
    }

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Check if user is a branch pastor.
     */
    public function isBranchPastor(?int $branchId = null): bool
    {
        return $this->hasRole('branch_pastor', $branchId);
    }

    /**
     * Check if user is a ministry leader.
     */
    public function isMinistryLeader(?int $branchId = null): bool
    {
        return $this->hasRole('ministry_leader', $branchId);
    }

    /**
     * Check if user is a department leader.
     */
    public function isDepartmentLeader(?int $branchId = null): bool
    {
        return $this->hasRole('department_leader', $branchId);
    }

    /**
     * Check if user is a church member.
     */
    public function isChurchMember(?int $branchId = null): bool
    {
        return $this->hasRole('church_member', $branchId);
    }

    /**
     * Check if user is a public user.
     */
    public function isPublicUser(?int $branchId = null): bool
    {
        return $this->hasRole('public_user', $branchId);
    }

    /**
     * Check if user is a leader of any small group (optionally scoped to a branch).
     */
    public function isSmallGroupLeader(?int $branchId = null): bool
    {
        $member = $this->member;
        if (! $member) {
            return false;
        }

        $query = \App\Models\SmallGroup::query()->where('leader_id', $member->id);
        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        return $query->exists();
    }

    /**
     * Get user's branches based on roles.
     */
    public function getBranches(): BelongsToMany
    {
        return $this->roles()->whereNotNull('user_roles.branch_id')
            ->join('branches', 'user_roles.branch_id', '=', 'branches.id')
            ->select('branches.*');
    }

    /**
     * Get the branches this user belongs to (for testing and relationships).
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'user_roles', 'user_id', 'branch_id')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    /**
     * Assign role to user for a specific branch.
     */
    public function assignRole(string $roleName, ?int $branchId = null): void
    {
        $role = Role::where('name', $roleName)->first();

        if ($role && ! $this->hasRole($roleName, $branchId)) {
            $this->roles()->attach($role->id, [
                'branch_id' => $branchId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Remove role from user for a specific branch.
     */
    public function removeRole(string $roleName, ?int $branchId = null): void
    {
        $role = Role::where('name', $roleName)->first();

        if ($role) {
            $query = $this->roles()->where('role_id', $role->id);

            if ($branchId !== null) {
                $query->wherePivot('branch_id', $branchId);
            }

            $query->detach();
        }
    }

    /**
     * Get the user's primary role (highest priority role).
     */
    public function getPrimaryRole(): ?Role
    {
        // Define role hierarchy (highest to lowest priority)
        $roleHierarchy = [
            'super_admin',
            'branch_pastor',
            'ministry_leader',
            'department_leader',
            'church_member',
            'public_user',
        ];

        foreach ($roleHierarchy as $roleName) {
            $role = $this->roles()->where('name', $roleName)->first();
            if ($role) {
                return $role;
            }
        }

        return null;
    }

    /**
     * Get the user's primary branch (based on primary role).
     */
    public function getPrimaryBranch(): ?Branch
    {
        $primaryRole = $this->getPrimaryRole();

        if ($primaryRole) {
            $userRole = $this->roles()
                ->where('role_id', $primaryRole->id)
                ->first();

            if ($userRole && $userRole->pivot->branch_id) {
                return Branch::find($userRole->pivot->branch_id);
            }
        }

        return null;
    }

    /**
     * Get the user's active branch ID (for policies and authorization).
     */
    public function getActiveBranchId(): ?int
    {
        $primaryBranch = $this->getPrimaryBranch();

        return $primaryBranch?->id;
    }

    /**
     * Send the password reset notification with church-specific branding.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ChurchPasswordResetNotification($token));
    }

    /**
     * Get communication logs for this user.
     */
    public function communicationLogs(): HasMany
    {
        return $this->hasMany(CommunicationLog::class);
    }

    /**
     * Get email campaign enrollments for this user.
     */
    public function emailCampaignEnrollments(): HasMany
    {
        return $this->hasMany(EmailCampaignEnrollment::class);
    }

    /**
     * Get active email campaign enrollments.
     */
    public function activeCampaignEnrollments(): HasMany
    {
        return $this->emailCampaignEnrollments()->whereNull('completed_at');
    }

    /**
     * Enroll user in an email campaign.
     */
    public function enrollInCampaign(EmailCampaign $campaign): EmailCampaignEnrollment
    {
        // Check if already enrolled
        $existingEnrollment = $this->emailCampaignEnrollments()
            ->where('campaign_id', $campaign->id)
            ->first();

        if ($existingEnrollment) {
            return $existingEnrollment;
        }

        // Get first step
        $firstStep = $campaign->steps()->orderBy('step_order')->first();

        if (! $firstStep) {
            throw new \Exception('Campaign has no steps defined');
        }

        return $this->emailCampaignEnrollments()->create([
            'campaign_id' => $campaign->id,
            'current_step' => 1,
            'next_send_at' => now()->addDays($firstStep->delay_days),
        ]);
    }

    /**
     * Check if user is enrolled in a specific campaign.
     */
    public function isEnrolledInCampaign(EmailCampaign $campaign): bool
    {
        return $this->emailCampaignEnrollments()
            ->where('campaign_id', $campaign->id)
            ->exists();
    }
}

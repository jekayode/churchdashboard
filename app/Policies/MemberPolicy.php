<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Member;

final class MemberPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any members.
     */
    public function viewAny(User $user): bool
    {
        // Users with leadership privileges can view member lists
        return $this->hasLeadershipPrivileges($user);
    }

    /**
     * Determine whether the user can view the member.
     */
    public function view(User $user, mixed $member): bool
    {
        // Users can view their own member profile
        if ($user->member && $user->member->id === $member->id) {
            return true;
        }

        // Users with leadership privileges can view members in their branch
        if ($this->hasLeadershipPrivileges($user)) {
            return $this->belongsToSameBranch($user, $member);
        }

        return false;
    }

    /**
     * Determine whether the user can create members.
     */
    public function create(User $user): bool
    {
        // Users with administrative privileges can create member records
        return $this->hasAdminPrivileges($user);
    }

    /**
     * Determine whether the user can update the member.
     */
    public function update(User $user, mixed $member): bool
    {
        // Users can update their own member profile
        if ($user->member && $user->member->id === $member->id) {
            return true;
        }

        // Users with leadership privileges can update members in their branch
        if ($this->hasLeadershipPrivileges($user)) {
            return $this->belongsToSameBranch($user, $member);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the member.
     */
    public function delete(User $user, mixed $member): bool
    {
        // Users cannot delete their own member profile
        if ($user->member && $user->member->id === $member->id) {
            return false;
        }

        // Only users with admin privileges can delete members
        return $this->hasAdminPrivileges($user) && $this->belongsToSameBranch($user, $member);
    }

    /**
     * Determine whether the user can update member growth levels.
     */
    public function updateGrowthLevel(User $user, Member $member): bool
    {
        // Users with leadership privileges can update growth levels
        return $this->hasLeadershipPrivileges($user) && $this->belongsToSameBranch($user, $member);
    }

    /**
     * Determine whether the user can update TECI progress.
     */
    public function updateTeciProgress(User $user, Member $member): bool
    {
        // Users with leadership privileges can update TECI progress
        return $this->hasLeadershipPrivileges($user) && $this->belongsToSameBranch($user, $member);
    }

    /**
     * Determine whether the user can view member reports.
     */
    public function viewReports(User $user, Member $member): bool
    {
        // Users can view their own reports
        if ($user->member && $user->member->id === $member->id) {
            return true;
        }

        // Users with leadership privileges can view member reports
        return $this->hasLeadershipPrivileges($user) && $this->belongsToSameBranch($user, $member);
    }

    /**
     * Determine whether the user can view any guests.
     * Override for guest management access.
     */
    public function viewAnyGuests(User $user): bool
    {
        // Super Admins have full access
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Branch Pastors have access
        if ($this->isBranchPastor($user)) {
            return true;
        }

        // Eligible Ministry Leaders have access
        return $this->isEligibleMinistryLeader($user);
    }

    /**
     * Determine whether the user can view a guest.
     * Override for guest management access.
     */
    public function viewGuest(User $user, Member $member): bool
    {
        // Must be a guest (visitor status with guest-form registration source)
        if ($member->member_status !== 'visitor' || $member->registration_source !== 'guest-form') {
            return false;
        }

        // Super Admins have full access
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Branch Pastors can view guests in their branch(es)
        if ($this->isBranchPastor($user)) {
            return $this->belongsToSameBranch($user, $member);
        }

        // Eligible Ministry Leaders can view guests in their ministry's branch
        if ($this->isEligibleMinistryLeader($user)) {
            return $this->belongsToSameBranch($user, $member);
        }

        return false;
    }

    /**
     * Check if user is an eligible Ministry Leader (leads ministries matching criteria).
     */
    protected function isEligibleMinistryLeader(User $user): bool
    {
        // User must have a member record to lead ministries
        if (!$user->member) {
            return false;
        }

        // Keywords to match in ministry names (case-insensitive partial match)
        $keywords = [
            'life groups',
            'assimilation',
            'online church',
            'finance',
            'prayers',
        ];

        // Get all ministries led by this user's member
        $ledMinistries = $user->member->ledMinistries()
            ->where('status', 'active')
            ->get();

        // Check if any ministry name contains any of the keywords
        foreach ($ledMinistries as $ministry) {
            $ministryName = strtolower($ministry->name ?? '');
            foreach ($keywords as $keyword) {
                if (str_contains($ministryName, strtolower($keyword))) {
                    return true;
                }
            }
        }

        return false;
    }
} 
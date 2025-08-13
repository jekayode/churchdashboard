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
} 
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\GuestFollowUp;
use App\Models\User;

final class GuestFollowUpPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any guest follow-ups.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasFullManagementAccess($user) ||
               $this->hasLimitedAccess($user);
    }

    /**
     * Determine whether the user can view the guest follow-up.
     */
    public function view(User $user, GuestFollowUp $guestFollowUp): bool
    {
        // Full management access can view all
        if ($this->hasFullManagementAccess($user)) {
            return $this->belongsToSameBranch($user, $guestFollowUp->member);
        }

        // Limited access can only view assigned guests
        if ($this->hasLimitedAccess($user)) {
            return $this->belongsToSameBranch($user, $guestFollowUp->member) &&
                   $guestFollowUp->assigned_to === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create guest follow-ups.
     */
    public function create(User $user): bool
    {
        return $this->hasFullManagementAccess($user) ||
               $this->hasLimitedAccess($user);
    }

    /**
     * Determine whether the user can update the guest follow-up.
     */
    public function update(User $user, GuestFollowUp $guestFollowUp): bool
    {
        // Full management access can update all
        if ($this->hasFullManagementAccess($user)) {
            return $this->belongsToSameBranch($user, $guestFollowUp->member);
        }

        // Limited access can only update assigned guests
        if ($this->hasLimitedAccess($user)) {
            return $this->belongsToSameBranch($user, $guestFollowUp->member) &&
                   $guestFollowUp->assigned_to === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the guest follow-up.
     */
    public function delete(User $user, GuestFollowUp $guestFollowUp): bool
    {
        // Only full management access can delete
        return $this->hasFullManagementAccess($user) &&
               $this->belongsToSameBranch($user, $guestFollowUp->member);
    }

    /**
     * Determine whether the user can assign guests to team members.
     */
    public function assign(User $user): bool
    {
        // Only full management access can assign
        return $this->hasFullManagementAccess($user);
    }

    /**
     * Determine whether the user can export guest data.
     */
    public function export(User $user): bool
    {
        return $this->hasFullManagementAccess($user) ||
               $this->hasLimitedAccess($user);
    }

    /**
     * Check if user has full management access.
     * Branch Pastors and Ministry Leaders of authorized ministries.
     */
    protected function hasFullManagementAccess(User $user): bool
    {
        // Branch Pastors have full access to their branch
        if ($user->isBranchPastor()) {
            return true;
        }

        // Ministry Leaders of specific ministries have full access
        if ($user->isMinistryLeader() && $user->member) {
            $ministry = $user->member->leadingMinistry;
            $authorizedMinistries = [
                'Life Groups, Assimilation & Online Church Division',
                'Finance and Prayers',
            ];

            return $ministry && in_array($ministry->name, $authorizedMinistries);
        }

        return false;
    }

    /**
     * Check if user has limited access.
     * Department Leaders and volunteers in authorized ministries.
     */
    protected function hasLimitedAccess(User $user): bool
    {
        if ($user->isDepartmentLeader() && $user->member) {
            $department = $user->member->leadingDepartment;
            if ($department && $department->ministry) {
                $authorizedMinistries = [
                    'Life Groups, Assimilation & Online Church Division',
                    'Finance and Prayers',
                ];

                return in_array($department->ministry->name, $authorizedMinistries);
            }
        }

        return false;
    }
}

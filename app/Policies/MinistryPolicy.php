<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Ministry;

final class MinistryPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any ministries.
     */
    public function viewAny(User $user): bool
    {
        // Users with leadership privileges can view ministry lists
        return $this->hasLeadershipPrivileges($user);
    }

    /**
     * Determine whether the user can view the ministry.
     */
    public function view(User $user, mixed $ministry): bool
    {
        // Super Admins can view any ministry
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Users with leadership privileges can view ministries in their branch
        if ($this->hasLeadershipPrivileges($user) && $this->belongsToSameBranch($user, $ministry)) {
            return true;
        }

        // Ministry leaders can view their own ministry
        if ($this->isMinistryLeaderOf($user, $ministry)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create ministries.
     */
    public function create(User $user): bool
    {
        // Only users with administrative privileges can create ministries
        return $this->hasAdminPrivileges($user);
    }

    /**
     * Determine whether the user can update the ministry.
     */
    public function update(User $user, mixed $ministry): bool
    {
        // Super Admins can update any ministry
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Branch Pastors can update ministries in their branch
        if ($this->isBranchPastor($user)) {
            return $this->belongsToSameBranch($user, $ministry);
        }

        // Ministry Leaders can update their own ministry
        if ($this->isMinistryLeaderOf($user, $ministry)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the ministry.
     */
    public function delete(User $user, mixed $ministry): bool
    {
        // Only users with admin privileges can delete ministries
        return $this->hasAdminPrivileges($user) && $this->belongsToSameBranch($user, $ministry);
    }

    /**
     * Determine whether the user can assign ministry leaders.
     */
    public function assignLeader(User $user, Ministry $ministry): bool
    {
        // Only users with administrative privileges can assign ministry leaders
        return $this->hasAdminPrivileges($user) && $this->belongsToSameBranch($user, $ministry);
    }

    /**
     * Determine whether the user can manage ministry departments.
     */
    public function manageDepartments(User $user, Ministry $ministry): bool
    {
        // Super Admins can manage departments for any ministry
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Branch Pastors can manage departments for ministries in their branch
        if ($this->isBranchPastor($user)) {
            return $this->belongsToSameBranch($user, $ministry);
        }

        // Ministry Leaders can manage departments for their own ministry
        if ($this->isMinistryLeaderOf($user, $ministry)) {
            return true;
        }

        return false;
    }

    /**
     * Check if the user is the leader of the specific ministry.
     */
    private function isMinistryLeaderOf(User $user, mixed $ministry): bool
    {
        // Check if user has a member record and if that member is the leader of this ministry
        if ($user->member && isset($ministry->leader_id)) {
            return $user->member->id === $ministry->leader_id;
        }

        return false;
    }
} 
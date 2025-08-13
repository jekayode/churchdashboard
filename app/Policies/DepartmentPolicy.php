<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Department;

final class DepartmentPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any departments.
     */
    public function viewAny(User $user): bool
    {
        // Users with leadership privileges can view department lists
        return $this->hasLeadershipPrivileges($user);
    }

    /**
     * Determine whether the user can view the department.
     */
    public function view(User $user, mixed $department): bool
    {
        // Super Admins can view any department
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Users with leadership privileges can view departments in their branch
        if ($this->hasLeadershipPrivileges($user) && $this->belongsToSameBranch($user, $department)) {
            return true;
        }

        // Department leaders can view their own department
        if ($this->isDepartmentLeaderOf($user, $department)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create departments.
     */
    public function create(User $user): bool
    {
        // Users with administrative privileges and ministry leaders can create departments
        return $this->hasAdminPrivileges($user) || $this->isMinistryLeader($user);
    }

    /**
     * Determine whether the user can update the department.
     */
    public function update(User $user, mixed $department): bool
    {
        // Super Admins can update any department
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Branch Pastors can update departments in their branch
        if ($this->isBranchPastor($user)) {
            return $this->belongsToSameBranch($user, $department);
        }

        // Ministry Leaders can update departments in their ministry
        if ($this->isMinistryLeader($user) && isset($department->ministry)) {
            return $this->belongsToSameBranch($user, $department->ministry);
        }

        // Department Leaders can update their own department
        if ($this->isDepartmentLeaderOf($user, $department)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the department.
     */
    public function delete(User $user, mixed $department): bool
    {
        // Super Admins can delete any department
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Branch Pastors can delete departments in their branch
        if ($this->isBranchPastor($user)) {
            return $this->belongsToSameBranch($user, $department);
        }

        // Ministry Leaders can delete departments in their ministry
        if ($this->isMinistryLeader($user) && $department->ministry) {
            return $this->isMinistryLeaderOf($user, $department->ministry);
        }

        return false;
    }

    /**
     * Determine whether the user can assign department leaders.
     */
    public function assignLeader(User $user, Department $department): bool
    {
        // Super Admins can assign leaders to any department
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Branch Pastors can assign leaders to departments in their branch
        if ($this->isBranchPastor($user)) {
            return $this->belongsToSameBranch($user, $department);
        }

        // Ministry Leaders can assign leaders to departments in their ministry
        if ($this->isMinistryLeader($user) && $department->ministry) {
            return $this->isMinistryLeaderOf($user, $department->ministry);
        }

        return false;
    }

    /**
     * Determine whether the user can manage department members.
     */
    public function manageMembers(User $user, Department $department): bool
    {
        // Super Admins can manage members for any department
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Branch Pastors can manage members for departments in their branch
        if ($this->isBranchPastor($user)) {
            return $this->belongsToSameBranch($user, $department);
        }

        // Ministry Leaders can manage members for departments in their ministry
        if ($this->isMinistryLeader($user) && $department->ministry) {
            return $this->isMinistryLeaderOf($user, $department->ministry);
        }

        // Department Leaders can manage members for their own department
        if ($this->isDepartmentLeaderOf($user, $department)) {
            return true;
        }

        return false;
    }

    /**
     * Check if the user is the leader of the specific department.
     */
    private function isDepartmentLeaderOf(User $user, mixed $department): bool
    {
        // Check if user has a member record and if that member is the leader of this department
        if ($user->member && isset($department->leader_id)) {
            return $user->member->id === $department->leader_id;
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
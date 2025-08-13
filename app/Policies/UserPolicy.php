<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class UserPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        // Super Admins can view all users, Branch Pastors can view users in their branch
        return $this->hasAdminPrivileges($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, mixed $model): bool
    {
        // Users can view their own profile
        if ($user->id === $model->id) {
            return true;
        }

        // Super Admins can view any user
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Branch Pastors can view users in their branch
        if ($this->isBranchPastor($user)) {
            return $this->belongsToSameBranch($user, $model);
        }

        // Ministry and Department Leaders can view users in their branch
        if ($this->hasLeadershipPrivileges($user)) {
            return $this->belongsToSameBranch($user, $model);
        }

        return false;
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        // Only Super Admins and Branch Pastors can create users
        return $this->hasAdminPrivileges($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, mixed $model): bool
    {
        // Users can update their own profile
        if ($user->id === $model->id) {
            return true;
        }

        // Super Admins can update any user
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Branch Pastors can update users in their branch
        if ($this->isBranchPastor($user)) {
            return $this->belongsToSameBranch($user, $model);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, mixed $model): bool
    {
        // Users cannot delete their own profile
        if ($user->id === $model->id) {
            return false;
        }

        // Only Super Admins can delete users
        return $this->isSuperAdmin($user);
    }

    /**
     * Determine whether the user can assign roles to the model.
     */
    public function assignRole(User $user, User $model): bool
    {
        // Super Admins can assign any role except Super Admin
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Branch Pastors can assign roles below their level in their branch
        if ($this->isBranchPastor($user) && 
            !$this->isBranchPastor($model) && 
            !$this->isSuperAdmin($model)) {
            return $this->belongsToSameBranch($user, $model);
        }

        return false;
    }

    /**
     * Determine whether the user can manage branch assignments.
     */
    public function manageBranches(User $user, User $model): bool
    {
        // Only Super Admins can manage branch assignments
        return $this->isSuperAdmin($user);
    }

    /**
     * Determine whether the user can view reports.
     */
    public function viewReports(User $user): bool
    {
        // Super Admins can view all reports
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Branch Pastors can view reports for their branch
        if ($this->isBranchPastor($user)) {
            return true;
        }

        // Ministry and Department Leaders can view reports for their branch
        if ($this->hasLeadershipPrivileges($user)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create reports.
     */
    public function createReports(User $user): bool
    {
        // Super Admins and Branch Pastors can create reports
        return $this->hasAdminPrivileges($user);
    }

    /**
     * Determine whether the user can update reports.
     */
    public function updateReports(User $user, mixed $model = null): bool
    {
        // Super Admins can update any report
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Branch Pastors can update reports for their branch
        if ($this->isBranchPastor($user)) {
            // If no specific model is provided, allow general update permission
            if ($model === null) {
                return true;
            }
            
            // If a specific report model is provided, check branch ownership
            if (isset($model->event) && $model->event->branch_id === $user->getActiveBranchId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can delete reports.
     */
    public function deleteReports(User $user, mixed $model = null): bool
    {
        // Super Admins can delete any report
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Branch Pastors can delete reports for their branch
        if ($this->isBranchPastor($user)) {
            // If no specific model is provided, allow general delete permission
            if ($model === null) {
                return true;
            }
            
            // If a specific report model is provided, check branch ownership
            if (isset($model->event) && $model->event->branch_id === $user->getActiveBranchId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can view all branches (Super Admin only).
     */
    public function viewAllBranches(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }
} 
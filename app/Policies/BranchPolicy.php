<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Branch;

final class BranchPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any branches.
     */
    public function viewAny(User $user): bool
    {
        // Super Admins can view all branches
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Other users can view branches they belong to
        return $user->getBranches()->count() > 0;
    }

    /**
     * Determine whether the user can view the branch.
     */
    public function view(User $user, mixed $branch): bool
    {
        // Super Admins can view any branch
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Users can view branches they belong to
        return $this->belongsToSameBranch($user, $branch);
    }

    /**
     * Determine whether the user can create branches.
     */
    public function create(User $user): bool
    {
        // Only Super Admins can create branches
        return $this->isSuperAdmin($user);
    }

    /**
     * Determine whether the user can update the branch.
     */
    public function update(User $user, mixed $branch): bool
    {
        // Super Admins can update any branch
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Branch Pastors can update their own branch
        if ($this->isBranchPastor($user)) {
            return $this->belongsToSameBranch($user, $branch);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the branch.
     */
    public function delete(User $user, mixed $branch): bool
    {
        // Only Super Admins can delete branches
        return $this->isSuperAdmin($user);
    }

    /**
     * Determine whether the user can manage branch pastors.
     */
    public function managePastors(User $user, Branch $branch): bool
    {
        // Only Super Admins can manage branch pastors
        return $this->isSuperAdmin($user);
    }

    /**
     * Determine whether the user can view branch statistics.
     */
    public function viewStatistics(User $user, Branch $branch): bool
    {
        // Super Admins can view statistics for any branch
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Branch Pastors can view statistics for their branch
        if ($this->isBranchPastor($user)) {
            return $this->belongsToSameBranch($user, $branch);
        }

        return false;
    }

    /**
     * Determine whether the user can manage branch settings.
     */
    public function manageSettings(User $user, Branch $branch): bool
    {
        // Super Admins can manage settings for any branch
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Branch Pastors can manage settings for their branch
        if ($this->isBranchPastor($user)) {
            return $this->belongsToSameBranch($user, $branch);
        }

        return false;
    }
} 
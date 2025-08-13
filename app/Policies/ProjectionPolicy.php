<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Projection;
use App\Models\User;

final class ProjectionPolicy
{
    /**
     * Determine whether the user can view any projections.
     */
    public function viewAny(User $user): bool
    {
        // Super admins and branch pastors can view projections
        return $user->hasRole('super_admin') || $user->hasRole('branch_pastor');
    }

    /**
     * Determine whether the user can view the projection.
     */
    public function view(User $user, Projection $projection): bool
    {
        // Super admins can view all projections
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Branch pastors can only view projections for their branches
        if ($user->hasRole('branch_pastor')) {
            $userBranches = $user->pastoredBranches()->pluck('id');
            return $userBranches->contains($projection->branch_id);
        }

        return false;
    }

    /**
     * Determine whether the user can create projections.
     */
    public function create(User $user): bool
    {
        // Super admins and branch pastors can create projections
        return $user->hasRole('super_admin') || $user->hasRole('branch_pastor');
    }

    /**
     * Determine whether the user can update the projection.
     */
    public function update(User $user, Projection $projection): bool
    {
        // Super admins can update ALL projections regardless of status
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Branch pastors can only update draft or rejected projections for their branches
        if ($user->hasRole('branch_pastor')) {
            $userBranches = $user->pastoredBranches()->pluck('id');
            $canAccessBranch = $userBranches->contains($projection->branch_id);
            $canEditStatus = $projection->isDraft() || $projection->isRejected();
            
            return $canAccessBranch && $canEditStatus;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the projection.
     */
    public function delete(User $user, Projection $projection): bool
    {
        // Super admins can delete ALL projections regardless of status
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Branch pastors can only delete draft or rejected projections in their branches
        if ($user->isBranchPastor()) {
            $userBranches = $user->pastoredBranches()->pluck('id');
            $canAccessBranch = $userBranches->contains($projection->branch_id);
            $canDeleteStatus = $projection->isDraft() || $projection->isRejected();
            
            return $canAccessBranch && $canDeleteStatus;
        }
        
        return false;
    }

    /**
     * Determine whether the user can restore the projection.
     */
    public function restore(User $user, Projection $projection): bool
    {
        // Only super admins can restore deleted projections
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the projection.
     */
    public function forceDelete(User $user, Projection $projection): bool
    {
        // Only super admins can permanently delete projections
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can view projection statistics.
     */
    public function viewStatistics(User $user): bool
    {
        // Super admins and branch pastors can view statistics
        return $user->hasRole('super_admin') || $user->hasRole('branch_pastor');
    }

    /**
     * Determine whether the user can view projection comparisons.
     */
    public function viewComparisons(User $user): bool
    {
        // Super admins and branch pastors can view comparisons
        return $user->hasRole('super_admin') || $user->hasRole('branch_pastor');
    }

    /**
     * Determine whether the user can manage projections for a specific branch.
     */
    public function manageForBranch(User $user, int $branchId): bool
    {
        // Super admins can manage projections for all branches
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Branch pastors can only manage projections for their branches
        if ($user->hasRole('branch_pastor')) {
            $userBranches = $user->pastoredBranches()->pluck('id');
            return $userBranches->contains($branchId);
        }

        return false;
    }

    /**
     * Determine whether the user can submit projections for review.
     */
    public function submitForReview(User $user, Projection $projection): bool
    {
        // Only the creator or branch pastor can submit for review
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        if ($user->isBranchPastor()) {
            return $projection->branch_id === $user->getActiveBranchId() &&
                   ($projection->isDraft() || $projection->isRejected());
        }
        
        return $projection->created_by === $user->id && ($projection->isDraft() || $projection->isRejected());
    }

    /**
     * Determine whether the user can approve projections.
     */
    public function approve(User $user, Projection $projection): bool
    {
        // Only super admins can approve projections
        return $user->isSuperAdmin() && $projection->isInReview();
    }

    /**
     * Determine whether the user can reject projections.
     */
    public function reject(User $user, Projection $projection): bool
    {
        // Only super admins can reject projections
        return $user->isSuperAdmin() && $projection->isInReview();
    }

    /**
     * Determine whether the user can set projections as current year.
     */
    public function setCurrentYear(User $user, Projection $projection): bool
    {
        // Only super admins can set current year projections
        return $user->isSuperAdmin() && $projection->isApproved();
    }

    /**
     * Determine whether the user can view approval history.
     */
    public function viewApprovalHistory(User $user, Projection $projection): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Branch pastors can view approval history for their branch projections
        if ($user->isBranchPastor()) {
            return $projection->branch_id === $user->getActiveBranchId();
        }
        
        return false;
    }
} 
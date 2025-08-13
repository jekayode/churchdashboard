<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

abstract class BasePolicy
{
    use HandlesAuthorization;

    /**
     * Check if user is a Super Admin (has access to everything)
     */
    protected function isSuperAdmin(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Check if user is a Branch Pastor
     */
    protected function isBranchPastor(User $user): bool
    {
        return $user->isBranchPastor();
    }

    /**
     * Check if user is a Ministry Leader
     */
    protected function isMinistryLeader(User $user): bool
    {
        return $user->isMinistryLeader();
    }

    /**
     * Check if user is a Department Leader
     */
    protected function isDepartmentLeader(User $user): bool
    {
        return $user->isDepartmentLeader();
    }

    /**
     * Check if user is a Church Member
     */
    protected function isChurchMember(User $user): bool
    {
        return $user->isChurchMember();
    }

    /**
     * Check if user is a Public User
     */
    protected function isPublicUser(User $user): bool
    {
        return $user->isPublicUser();
    }

    /**
     * Check if user has administrative privileges (Super Admin or Branch Pastor)
     */
    protected function hasAdminPrivileges(User $user): bool
    {
        return $this->isSuperAdmin($user) || $this->isBranchPastor($user);
    }

    /**
     * Check if user has leadership privileges (Admin + Ministry/Department Leaders)
     */
    protected function hasLeadershipPrivileges(User $user): bool
    {
        return $this->hasAdminPrivileges($user) || 
               $this->isMinistryLeader($user) || 
               $this->isDepartmentLeader($user);
    }

    /**
     * Check if user belongs to the same branch as the resource
     */
    protected function belongsToSameBranch(User $user, mixed $model): bool
    {
        // If user is Super Admin, they can access all branches
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Get user's primary branch
        $userBranch = $user->getPrimaryBranch();
        
        if (!$userBranch) {
            return false;
        }

        // If model has a branch_id property
        if (isset($model->branch_id)) {
            return $userBranch->id === $model->branch_id;
        }

        // If model is a Branch itself
        if ($model instanceof \App\Models\Branch) {
            return $userBranch->id === $model->id;
        }

        // If model is a Department, check through its ministry's branch
        if ($model instanceof \App\Models\Department) {
            if ($model->ministry && isset($model->ministry->branch_id)) {
                return $userBranch->id === $model->ministry->branch_id;
            }
            // If ministry relationship is not loaded, load it
            if ($model->ministry_id) {
                $ministry = \App\Models\Ministry::find($model->ministry_id);
                if ($ministry && isset($ministry->branch_id)) {
                    return $userBranch->id === $ministry->branch_id;
                }
            }
        }

        // If model belongs to a user (check user's branch)
        if (isset($model->user_id)) {
            $modelUser = \App\Models\User::find($model->user_id);
            if ($modelUser) {
                $modelUserBranch = $modelUser->getPrimaryBranch();
                return $modelUserBranch && $userBranch->id === $modelUserBranch->id;
            }
        }

        // If model has a member relationship
        if (method_exists($model, 'member') && $model->member) {
            return $this->belongsToSameBranch($user, $model->member);
        }

        return false;
    }

    /**
     * Check if user can manage the resource (has appropriate role and branch access)
     */
    protected function canManage(User $user, mixed $model): bool
    {
        return $this->hasLeadershipPrivileges($user) && $this->belongsToSameBranch($user, $model);
    }

    /**
     * Check if user owns the resource
     */
    protected function ownsResource(User $user, mixed $model): bool
    {
        // If model has user_id property
        if (isset($model->user_id)) {
            return $user->id === $model->user_id;
        }

        // If model is the User itself
        if ($model instanceof User) {
            return $user->id === $model->id;
        }

        // If model has a member relationship and user has a member record
        if (method_exists($model, 'member') && $model->member && $user->member) {
            return $user->member->id === $model->member->id;
        }

        return false;
    }

    /**
     * Default implementation for viewAny - can be overridden in specific policies
     */
    public function viewAny(User $user): bool
    {
        // By default, authenticated users can view lists
        return true;
    }

    /**
     * Default implementation for view - can be overridden in specific policies
     */
    public function view(User $user, mixed $model): bool
    {
        // Users can view resources they own or manage, or if they're in the same branch
        return $this->ownsResource($user, $model) || 
               $this->canManage($user, $model) || 
               $this->belongsToSameBranch($user, $model);
    }

    /**
     * Default implementation for create - can be overridden in specific policies
     */
    public function create(User $user): bool
    {
        // By default, only users with leadership privileges can create
        return $this->hasLeadershipPrivileges($user);
    }

    /**
     * Default implementation for update - can be overridden in specific policies
     */
    public function update(User $user, mixed $model): bool
    {
        // Users can update resources they own or manage
        return $this->ownsResource($user, $model) || $this->canManage($user, $model);
    }

    /**
     * Default implementation for delete - can be overridden in specific policies
     */
    public function delete(User $user, mixed $model): bool
    {
        // Only users with management privileges can delete
        return $this->canManage($user, $model);
    }
} 
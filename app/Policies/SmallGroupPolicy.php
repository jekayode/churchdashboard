<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SmallGroup;
use App\Models\User;

final class SmallGroupPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any small groups.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view small groups
        return true;
    }

    /**
     * Determine whether the user can view the small group.
     */
    public function view(User $user, mixed $smallGroup): bool
    {
        // Users can view small groups in their branch
        return $this->belongsToSameBranch($user, $smallGroup);
    }

    /**
     * Determine whether the user can create small groups.
     */
    public function create(User $user): bool
    {
        // Users with leadership privileges can create small groups
        if ($this->hasLeadershipPrivileges($user)) {
            return true;
        }

        // Life Groups ministers (ministry_leader of category 'life_groups') can create as well
        $branchId = $user->getActiveBranchId();
        if ($branchId && $user->isMinistryLeader($branchId)) {
            $ministry = \App\Models\Ministry::where('branch_id', $branchId)
                ->where('leader_id', optional($user->member)->id)
                ->where('category', 'life_groups')
                ->first();

            return (bool) $ministry;
        }

        return false;
    }

    /**
     * Determine whether the user can update the small group.
     */
    public function update(User $user, mixed $smallGroup): bool
    {
        // Super Admins can update any small group
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Branch Pastors can update small groups in their branch
        if ($this->isBranchPastor($user)) {
            return $this->belongsToSameBranch($user, $smallGroup);
        }

        // Ministry and Department Leaders can update small groups in their branch
        if ($this->hasLeadershipPrivileges($user)) {
            return $this->belongsToSameBranch($user, $smallGroup);
        }

        // Life Groups ministers (category) also allowed in-branch
        $branchId = $user->getActiveBranchId();
        if ($branchId && $user->isMinistryLeader($branchId)) {
            $ministry = \App\Models\Ministry::where('branch_id', $branchId)
                ->where('leader_id', optional($user->member)->id)
                ->where('category', 'life_groups')
                ->first();
            if ($ministry) {
                return $this->belongsToSameBranch($user, $smallGroup);
            }
        }

        // Small group leaders can update their own group
        if (isset($smallGroup->leader_id)) {
            $userMember = $user->member;

            return $userMember && $userMember->id === $smallGroup->leader_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the small group.
     */
    public function delete(User $user, mixed $smallGroup): bool
    {
        // Only users with leadership privileges can delete small groups
        return $this->hasLeadershipPrivileges($user) && $this->belongsToSameBranch($user, $smallGroup);
    }

    /**
     * Determine whether the user can join the small group.
     */
    public function join(User $user, SmallGroup $smallGroup): bool
    {
        // Users can join small groups in their branch
        return $this->belongsToSameBranch($user, $smallGroup);
    }

    /**
     * Determine whether the user can manage small group members.
     */
    public function manageMembers(User $user, SmallGroup $smallGroup): bool
    {
        // Super Admins can manage members for any small group
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Branch Pastors can manage members for small groups in their branch
        if ($this->isBranchPastor($user)) {
            return $this->belongsToSameBranch($user, $smallGroup);
        }

        // Small group leaders can manage members for their own groups
        if (isset($smallGroup->leader_id)) {
            $userMember = $user->member;

            return $userMember && $userMember->id === $smallGroup->leader_id;
        }

        // Life Groups ministers can manage members for any groups in branch
        $branchId = $user->getActiveBranchId();
        if ($branchId && $user->isMinistryLeader($branchId)) {
            $ministry = \App\Models\Ministry::where('branch_id', $branchId)
                ->where('leader_id', optional($user->member)->id)
                ->where('category', 'life_groups')
                ->first();
            if ($ministry) {
                return $this->belongsToSameBranch($user, $smallGroup);
            }
        }

        return false;
    }
}

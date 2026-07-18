<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class ReadingPlanPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isBranchPastor();
    }

    public function view(User $user, mixed $model): bool
    {
        $plan = $model;

        if ($user->isSuperAdmin() || $plan->branch_id === null) {
            return $user->isSuperAdmin() || $user->isBranchPastor();
        }

        return $user->getActiveBranchId() === $plan->branch_id;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isBranchPastor();
    }

    /**
     * Network-wide plans belong to super admins: the Bible in a Year plan is
     * shared, so one branch must not rewrite it for everyone.
     */
    public function update(User $user, mixed $model): bool
    {
        $plan = $model;

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->isBranchPastor()) {
            return false;
        }

        $branchId = $user->getActiveBranchId();

        return $branchId !== null && $branchId === $plan->branch_id;
    }

    public function delete(User $user, mixed $model): bool
    {
        return $this->update($user, $model);
    }
}

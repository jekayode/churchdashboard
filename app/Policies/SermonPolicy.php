<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Sermon;
use App\Models\User;

final class SermonPolicy extends BasePolicy
{
    /**
     * Anyone signed in may browse the sermon library.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, mixed $model): bool
    {
        $sermon = $model;

        if ($user->isSuperAdmin()) {
            return true;
        }

        // Network-wide sermons (no branch) are visible to everyone.
        if ($sermon->branch_id === null) {
            return true;
        }

        return $user->getActiveBranchId() === $sermon->branch_id;
    }

    /**
     * Pastors and admins publish sermons; members do not.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isBranchPastor();
    }

    public function update(User $user, mixed $model): bool
    {
        $sermon = $model;

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->isBranchPastor()) {
            return false;
        }

        $branchId = $user->getActiveBranchId();

        // A branch pastor may edit their own branch's sermons. Network-wide
        // sermons stay with super admins so one branch cannot rewrite them.
        return $branchId !== null && $branchId === $sermon->branch_id;
    }

    public function delete(User $user, mixed $model): bool
    {
        return $this->update($user, $model);
    }
}

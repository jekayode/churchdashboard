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
     * Branch pastors maintain their own branch's plans and may also edit
     * network-wide ones, so the shared Bible in a Year plan can be looked after
     * by whichever pastor is doing the writing rather than only a super admin.
     *
     * Shared plans are therefore editable by any branch pastor, which is why
     * `questions_updated_by` records who last changed a day.
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

        if ($plan->branch_id === null) {
            return true;
        }

        return $user->getActiveBranchId() === $plan->branch_id;
    }

    public function delete(User $user, mixed $model): bool
    {
        return $this->update($user, $model);
    }
}

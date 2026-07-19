<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Series;
use App\Models\User;

final class SeriesPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, mixed $model): bool
    {
        $series = $model;

        if ($user->isSuperAdmin() || $series->branch_id === null) {
            return true;
        }

        return $user->getActiveBranchId() === $series->branch_id;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isBranchPastor();
    }

    public function update(User $user, mixed $model): bool
    {
        $series = $model;

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->isBranchPastor()) {
            return false;
        }

        $branchId = $user->getActiveBranchId();

        return $branchId !== null && $branchId === $series->branch_id;
    }

    public function delete(User $user, mixed $model): bool
    {
        return $this->update($user, $model);
    }
}

<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

final class BusinessPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(?User $user, mixed $model): bool
    {
        if (! $model instanceof Business) {
            return false;
        }

        if ($model->isPubliclyVisible()) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $model->isOwnedBy($user) || $user->isDirectoryAdmin();
    }

    public function create(User $user): bool
    {
        return $user->exists;
    }

    public function update(User $user, mixed $model): bool
    {
        if (! $model instanceof Business) {
            return false;
        }

        return $model->isOwnedBy($user) || $user->isDirectoryAdmin();
    }

    public function delete(User $user, mixed $model): bool
    {
        if (! $model instanceof Business) {
            return false;
        }

        return $model->isOwnedBy($user) || $user->isDirectoryAdmin();
    }

    public function moderate(User $user, mixed $model): bool
    {
        return $user->isDirectoryAdmin();
    }

    public function feature(User $user, mixed $model): bool
    {
        return $user->isDirectoryAdmin();
    }

    public function approve(User $user, mixed $model): bool
    {
        return $user->isDirectoryAdmin();
    }
}

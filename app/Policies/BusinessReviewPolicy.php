<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BusinessReview;
use App\Models\User;

final class BusinessReviewPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->exists;
    }

    public function update(User $user, mixed $model): bool
    {
        if (! $model instanceof BusinessReview) {
            return false;
        }

        return $model->user_id === $user->id || $user->isDirectoryAdmin();
    }

    public function delete(User $user, mixed $model): bool
    {
        if (! $model instanceof BusinessReview) {
            return false;
        }

        return $model->user_id === $user->id || $user->isDirectoryAdmin();
    }

    public function moderate(User $user, mixed $model): bool
    {
        return $user->isDirectoryAdmin();
    }
}

<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DirectoryCategory;
use App\Models\User;

final class DirectoryCategoryPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, mixed $model): bool
    {
        return $model instanceof DirectoryCategory;
    }

    public function create(User $user): bool
    {
        return $user->isDirectoryAdmin();
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->isDirectoryAdmin();
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->isDirectoryAdmin();
    }
}

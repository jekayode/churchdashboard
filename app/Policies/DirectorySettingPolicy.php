<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class DirectorySettingPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->isDirectoryAdmin();
    }
}

<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

final class QuizPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isBranchPastor();
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->isSuperAdmin() || $this->belongsToUsersBranch($user, $model);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isBranchPastor();
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->isSuperAdmin() || ($user->isBranchPastor() && $this->belongsToUsersBranch($user, $model));
    }

    /**
     * Running the quiz is the same right as editing it. Anyone trusted to write
     * the questions is trusted to start them.
     */
    public function host(User $user, Quiz $quiz): bool
    {
        return $this->update($user, $quiz);
    }

    public function delete(User $user, mixed $model): bool
    {
        return $this->update($user, $model);
    }

    private function belongsToUsersBranch(User $user, Quiz $quiz): bool
    {
        return $user->getActiveBranchId() === $quiz->branch_id;
    }
}

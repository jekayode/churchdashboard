<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Business;
use App\Models\BusinessMessage;
use App\Models\User;

final class BusinessMessagePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->exists;
    }

    public function create(User $user, mixed $model = null): bool
    {
        if (! $model instanceof Business) {
            return false;
        }

        $business = $model;

        if (! $business->isPubliclyVisible()) {
            return false;
        }

        return ! $business->isOwnedBy($user);
    }

    public function reply(User $user, Business $business, ?string $threadId = null): bool
    {
        if ($business->isOwnedBy($user) || $user->isDirectoryAdmin()) {
            return true;
        }

        if ($threadId === null) {
            return false;
        }

        return BusinessMessage::query()
            ->where('thread_id', $threadId)
            ->where('business_id', $business->id)
            ->where('customer_user_id', $user->id)
            ->exists();
    }

    public function viewThread(User $user, string $threadId): bool
    {
        $message = BusinessMessage::query()->where('thread_id', $threadId)->first();

        if (! $message) {
            return false;
        }

        if ($message->customer_user_id === $user->id) {
            return true;
        }

        return $message->business->isOwnedBy($user) || $user->isDirectoryAdmin();
    }
}

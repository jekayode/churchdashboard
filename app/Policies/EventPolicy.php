<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

final class EventPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any events.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() ||
               $user->isBranchPastor() ||
               $user->isMinistryLeader() ||
               $user->isDepartmentLeader() ||
               $user->isChurchMember();
    }

    /**
     * Determine whether the user can view the event.
     */
    public function view(User $user, mixed $model): bool
    {
        $event = $model;

        // Super admin can view all events
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Branch pastor can view events in their branch
        if ($user->isBranchPastor()) {
            $userBranch = $user->getPrimaryBranch();

            return $userBranch && $userBranch->id === $event->branch_id;
        }

        // Ministry and department leaders can view events in their branch
        if ($user->isMinistryLeader() || $user->isDepartmentLeader()) {
            $userBranch = $user->getPrimaryBranch();

            return $userBranch && $userBranch->id === $event->branch_id;
        }

        // Church members can view active events in their branch
        if ($user->isChurchMember()) {
            $userBranch = $user->getPrimaryBranch();

            return $userBranch &&
                   $userBranch->id === $event->branch_id &&
                   $event->status === 'active';
        }

        return false;
    }

    /**
     * Determine whether the user can create events.
     */
    public function create(User $user): bool
    {
        // Super admin and branch pastor can create
        if ($user->isSuperAdmin() || $user->isBranchPastor()) {
            return true;
        }

        // Operations ministers can create events in their branch
        // Identify if the user leads a ministry with category 'operations' in their active branch
        $branchId = $user->getActiveBranchId();
        if ($branchId && $user->isMinistryLeader($branchId)) {
            $ministry = \App\Models\Ministry::where('branch_id', $branchId)
                ->where('leader_id', optional($user->member)->id)
                ->where('category', 'operations')
                ->first();

            return (bool) $ministry;
        }

        return false;
    }

    /**
     * Determine whether the user can update the event.
     */
    public function update(User $user, mixed $model): bool
    {
        $event = $model;

        // Super admin can update all events
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Branch pastor can update events in their branch
        if ($user->isBranchPastor()) {
            $userBranch = $user->getPrimaryBranch();

            return $userBranch && $userBranch->id === $event->branch_id;
        }

        // Operations ministers can update events in their branch
        $branchId = $user->getActiveBranchId();
        if ($branchId && $user->isMinistryLeader($branchId)) {
            $ministry = \App\Models\Ministry::where('branch_id', $branchId)
                ->where('leader_id', optional($user->member)->id)
                ->where('category', 'operations')
                ->first();

            if ($ministry) {
                return $branchId === $event->branch_id;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can delete the event.
     */
    public function delete(User $user, mixed $model): bool
    {
        $event = $model;

        // Super admin can delete all events
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Branch pastor can delete events in their branch
        if ($user->isBranchPastor()) {
            $userBranch = $user->getPrimaryBranch();

            return $userBranch && $userBranch->id === $event->branch_id;
        }

        // Operations ministers can delete events in their branch
        $branchId = $user->getActiveBranchId();
        if ($branchId && $user->isMinistryLeader($branchId)) {
            $ministry = \App\Models\Ministry::where('branch_id', $branchId)
                ->where('leader_id', optional($user->member)->id)
                ->where('category', 'operations')
                ->first();

            if ($ministry) {
                return $branchId === $event->branch_id;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can restore the event.
     */
    public function restore(User $user, mixed $model): bool
    {
        return $this->update($user, $model);
    }

    /**
     * Determine whether the user can permanently delete the event.
     */
    public function forceDelete(User $user, mixed $model): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can register for the event.
     */
    public function register(User $user, mixed $model): bool
    {
        $event = $model;

        // Event must be active
        if ($event->status !== 'active') {
            return false;
        }

        // Event must be upcoming
        if (! $event->isUpcoming()) {
            return false;
        }

        // Super admin can register for any event
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Users can register for events in their branch or public events
        $userBranch = $user->getPrimaryBranch();

        return $userBranch && $userBranch->id === $event->branch_id;
    }

    /**
     * Determine whether the user can check in registrations for the event.
     */
    public function checkIn(User $user, mixed $model): bool
    {
        $event = $model;

        // Super admin can check in for all events
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Branch pastor can check in for events in their branch
        if ($user->isBranchPastor()) {
            $userBranch = $user->getPrimaryBranch();

            return $userBranch && $userBranch->id === $event->branch_id;
        }

        // Ministry and department leaders can check in for events in their branch
        if ($user->isMinistryLeader() || $user->isDepartmentLeader()) {
            $userBranch = $user->getPrimaryBranch();

            return $userBranch && $userBranch->id === $event->branch_id;
        }

        return false;
    }

    /**
     * Determine whether the user can view event registrations.
     */
    public function viewRegistrations(User $user, mixed $model): bool
    {
        return $this->checkIn($user, $model);
    }

    /**
     * Determine whether the user can view event reports.
     */
    public function viewReports(User $user, mixed $model): bool
    {
        return $this->checkIn($user, $model);
    }

    /**
     * Determine whether the user can create event reports.
     */
    public function createReport(User $user, mixed $model): bool
    {
        return $this->checkIn($user, $model);
    }
}

<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Event;
use Illuminate\Support\Str;

final class EventPublicSlug
{
    public static function uniqueInBranch(int $branchId, string $desiredBase, ?int $ignoreEventId = null): string
    {
        $base = Str::slug($desiredBase);
        if ($base === '') {
            $base = 'event';
        }

        $candidate = $base;
        $n = 2;
        while (self::slugTaken($branchId, $candidate, $ignoreEventId)) {
            $candidate = $base.'-'.$n;
            $n++;
        }

        return $candidate;
    }

    public static function slugTaken(int $branchId, string $slug, ?int $ignoreEventId = null): bool
    {
        $query = Event::query()
            ->where('branch_id', $branchId)
            ->where('public_slug', $slug);

        if ($ignoreEventId !== null) {
            $query->where('id', '!=', $ignoreEventId);
        }

        return $query->exists();
    }

    public static function forRecurringInstance(Event $parent, \Carbon\CarbonInterface $instanceStart): string
    {
        $parentBase = $parent->public_slug !== null && $parent->public_slug !== ''
            ? $parent->public_slug
            : Str::slug($parent->name);

        if ($parentBase === '') {
            $parentBase = 'event';
        }

        $datePart = $instanceStart->format('Y-m-d');
        $desired = $parentBase.'-'.$datePart;

        return self::uniqueInBranch((int) $parent->branch_id, $desired);
    }
}

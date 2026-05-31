<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ReviewStatus;
use App\Models\Business;
use App\Models\BusinessReview;

final class BusinessReviewRatingService
{
    public function recalculate(Business $business): void
    {
        $stats = BusinessReview::query()
            ->where('business_id', $business->id)
            ->where('status', ReviewStatus::Approved)
            ->selectRaw('COUNT(*) as count, AVG(rating) as average')
            ->first();

        $business->update([
            'reviews_count' => (int) ($stats->count ?? 0),
            'average_rating' => round((float) ($stats->average ?? 0), 2),
        ]);
    }
}

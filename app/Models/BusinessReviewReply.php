<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BusinessReviewReply extends Model
{
    protected $fillable = [
        'business_review_id',
        'business_id',
        'owner_user_id',
        'body',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(BusinessReview::class, 'business_review_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
}

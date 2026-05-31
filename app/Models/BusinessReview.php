<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class BusinessReview extends Model
{
    /** @use HasFactory<\Database\Factories\BusinessReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'business_id',
        'user_id',
        'rating',
        'title',
        'body',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'status' => ReviewStatus::class,
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reply(): HasOne
    {
        return $this->hasOne(BusinessReviewReply::class, 'business_review_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', ReviewStatus::Approved);
    }
}

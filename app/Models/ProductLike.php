<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductLike extends Model
{
    protected $fillable = [
        'business_product_id',
        'user_id',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(BusinessProduct::class, 'business_product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

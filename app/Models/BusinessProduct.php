<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

final class BusinessProduct extends Model
{
    /** @use HasFactory<\Database\Factories\BusinessProductFactory> */
    use HasFactory;

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'price_text',
        'image_path',
        'sort_order',
        'is_active',
        'likes_count',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ProductLike::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->image_path);
    }
}

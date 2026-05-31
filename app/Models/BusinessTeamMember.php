<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

final class BusinessTeamMember extends Model
{
    /** @use HasFactory<\Database\Factories\BusinessTeamMemberFactory> */
    use HasFactory;

    protected $fillable = [
        'business_id',
        'name',
        'role',
        'photo_path',
        'bio',
        'sort_order',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->photo_path);
    }
}

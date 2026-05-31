<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

final class DirectoryAnnouncement extends Model
{
    protected $fillable = [
        'title',
        'body',
        'link',
        'image_path',
        'is_active',
        'is_dismissible',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_dismissible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

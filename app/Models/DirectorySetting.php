<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

final class DirectorySetting extends Model
{
    protected $fillable = [
        'logo_path',
        'primary_color',
        'secondary_color',
        'tagline',
        'announcement_title',
        'announcement_body',
        'announcement_link',
        'announcement_active',
        'announcement_dismissible',
        'reviews_require_approval',
        'business_approval_required',
    ];

    protected function casts(): array
    {
        return [
            'announcement_active' => 'boolean',
            'announcement_dismissible' => 'boolean',
            'reviews_require_approval' => 'boolean',
            'business_approval_required' => 'boolean',
        ];
    }

    public static function instance(): self
    {
        return self::query()->firstOrCreate([], [
            'primary_color' => '#F1592A',
            'secondary_color' => '#1e293b',
            'reviews_require_approval' => true,
            'business_approval_required' => true,
        ]);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

final class BuilderResource extends Model
{
    protected $fillable = [
        'title',
        'original_name',
        'file_path',
        'mime_type',
        'size_bytes',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('builders.pack.download', $this);
    }

    public function deleteFile(): void
    {
        if ($this->file_path) {
            Storage::disk('public')->delete($this->file_path);
        }
    }
}

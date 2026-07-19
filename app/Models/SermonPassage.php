<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SermonPassage extends Model
{
    /** @use HasFactory<\Database\Factories\SermonPassageFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'sermon_id',
        'reference',
        'book',
        'chapter',
        'verses',
        'position',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'chapter' => 'integer',
        'position' => 'integer',
    ];

    public function sermon(): BelongsTo
    {
        return $this->belongsTo(Sermon::class);
    }
}

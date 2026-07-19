<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A member's private note.
 *
 * Notes are always personal: nothing in the app exposes one member's notes to
 * another, including leadership.
 */
final class Note extends Model
{
    /** @use HasFactory<\Database\Factories\NoteFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Note kinds the API accepts, mapped to their model.
     */
    public const TYPES = [
        'sermon' => Sermon::class,
        'reading' => ReadingDay::class,
    ];

    /**
     * @var list<string>
     */
    protected $fillable = ['member_id', 'notable_type', 'notable_id', 'title', 'body'];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function notable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForMember(Builder $query, int $memberId): Builder
    {
        return $query->where('member_id', $memberId);
    }

    /**
     * Short kind name used by the API and the app's filter tabs.
     */
    public function kind(): string
    {
        return match ($this->notable_type) {
            Sermon::class => 'sermon',
            ReadingDay::class => 'reading',
            default => 'personal',
        };
    }

    /**
     * What the note is about, so "My Notes" can show context rather than a
     * wall of undifferentiated text.
     *
     * @return array{type: string, id: ?int, label: ?string}
     */
    public function context(): array
    {
        $notable = $this->notable;

        return [
            'type' => $this->kind(),
            'id' => $notable?->getKey(),
            'label' => match (true) {
                $notable instanceof Sermon => $notable->title,
                $notable instanceof ReadingDay => $notable->label ?? 'Day '.$notable->day_number,
                default => null,
            },
        ];
    }
}

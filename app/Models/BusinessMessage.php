<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

final class BusinessMessage extends Model
{
    protected $fillable = [
        'thread_id',
        'business_id',
        'customer_user_id',
        'sender_user_id',
        'subject',
        'body',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_user_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function isFromOwner(): bool
    {
        return $this->sender_user_id === $this->business->owner_user_id;
    }

    public static function newThreadId(): string
    {
        return (string) Str::uuid();
    }
}

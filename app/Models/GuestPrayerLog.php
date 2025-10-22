<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class GuestPrayerLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_prayer_request_id',
        'prayed_by',
        'prayed_at',
        'notes',
    ];

    protected $casts = [
        'prayed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function prayerRequest(): BelongsTo
    {
        return $this->belongsTo(GuestPrayerRequest::class);
    }

    public function prayedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prayed_by');
    }

    // Scopes
    public function scopeByPrayerRequest($query, int $prayerRequestId)
    {
        return $query->where('guest_prayer_request_id', $prayerRequestId);
    }

    public function scopeByPrayer($query, int $userId)
    {
        return $query->where('prayed_by', $userId);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('prayed_at', '>=', now()->subDays($days));
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class GuestPrayerRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_id',
        'prayer_request',
        'assigned_to',
        'status',
        'prayer_count',
        'last_prayed_at',
        'notes',
    ];

    protected $casts = [
        'last_prayed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function prayerLogs(): HasMany
    {
        return $this->hasMany(GuestPrayerLog::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['praying', 'ongoing']);
    }

    public function scopeByBranch($query, int $branchId)
    {
        return $query->whereHas('member', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        });
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // Helper methods
    public function logPrayer(User $user, ?string $notes = null): GuestPrayerLog
    {
        $log = $this->prayerLogs()->create([
            'prayed_by' => $user->id,
            'prayed_at' => now(),
            'notes' => $notes,
        ]);

        $this->increment('prayer_count');
        $this->update(['last_prayed_at' => now()]);

        return $log;
    }

    public function markAnswered(): void
    {
        $this->update(['status' => 'answered']);
    }

    public function markPraying(): void
    {
        $this->update(['status' => 'praying']);
    }

    public function markOngoing(): void
    {
        $this->update(['status' => 'ongoing']);
    }
}

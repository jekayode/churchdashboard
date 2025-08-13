<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

final class EventRegistration extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'user_id',
        'member_id',
        'name',
        'email',
        'phone',
        'custom_fields',
        'registration_date',
        'checked_in',
        'checked_in_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'custom_fields' => 'array',
        'registration_date' => 'datetime',
        'checked_in' => 'boolean',
        'checked_in_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the event this registration belongs to.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user who registered (if applicable).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the member who registered (if applicable).
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Scope to get checked-in registrations.
     */
    public function scopeCheckedIn($query)
    {
        return $query->where('checked_in', true);
    }

    /**
     * Scope to get registrations by event.
     */
    public function scopeByEvent($query, int $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    /**
     * Check in the registration.
     */
    public function checkIn(): void
    {
        $this->update([
            'checked_in' => true,
            'checked_in_at' => now(),
        ]);
    }

    /**
     * Generate QR code for this registration.
     */
    public function generateQrCode(int $size = 200): string
    {
        $checkInUrl = url("/check-in/{$this->id}");
        
        return QrCode::size($size)
            ->margin(2)
            ->generate($checkInUrl);
    }

    /**
     * Get QR code data URL for this registration.
     */
    public function getQrCodeDataUrl(int $size = 200): string
    {
        $checkInUrl = url("/check-in/{$this->id}");
        
        return QrCode::size($size)
            ->margin(2)
            ->format('png')
            ->encoding('UTF-8')
            ->generate($checkInUrl);
    }

    /**
     * Generate a secure token for check-in verification.
     */
    public function generateCheckInToken(): string
    {
        return hash('sha256', $this->id . $this->event_id . $this->email . config('app.key'));
    }

    /**
     * Verify check-in token.
     */
    public function verifyCheckInToken(string $token): bool
    {
        return hash_equals($this->generateCheckInToken(), $token);
    }

    /**
     * Get the check-in URL with secure token.
     */
    public function getSecureCheckInUrl(): string
    {
        $token = $this->generateCheckInToken();
        return url("/check-in/{$this->id}?token={$token}");
    }

    /**
     * Generate secure QR code with token.
     */
    public function generateSecureQrCode(int $size = 200): string
    {
        $checkInUrl = $this->getSecureCheckInUrl();
        
        return QrCode::size($size)
            ->margin(2)
            ->generate($checkInUrl);
    }
}

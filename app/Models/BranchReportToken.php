<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

final class BranchReportToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'token',
        'name',
        'email',
        'is_active',
        'expires_at',
        'last_used_at',
        'usage_count',
        'allowed_events',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'allowed_events' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the branch this token belongs to.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Generate a unique token.
     */
    public static function generateToken(): string
    {
        do {
            $token = Str::random(64);
        } while (self::where('token', $token)->exists());

        return $token;
    }

    /**
     * Create a new token for a branch.
     */
    public static function createForBranch(
        int $branchId,
        string $name,
        ?string $email = null,
        ?array $allowedEvents = null,
        ?\DateTime $expiresAt = null
    ): self {
        return self::create([
            'branch_id' => $branchId,
            'token' => self::generateToken(),
            'name' => $name,
            'email' => $email,
            'allowed_events' => $allowedEvents,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Check if the token is valid and not expired.
     */
    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Record token usage.
     */
    public function recordUsage(): void
    {
        $this->update([
            'last_used_at' => now(),
            'usage_count' => $this->usage_count + 1,
        ]);
    }

    /**
     * Get the public submission URL for this token.
     */
    public function getSubmissionUrl(): string
    {
        return url("/public/reports/submit/{$this->token}");
    }

    /**
     * Scope to get only active tokens.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope to get tokens for a specific branch.
     */
    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}

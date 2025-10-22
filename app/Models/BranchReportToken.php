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
        'event_id',
        'token',
        'name',
        'email',
        'team_name',
        'team_emails',
        'team_roles',
        'is_team_token',
        'is_active',
        'expires_at',
        'last_used_at',
        'usage_count',
        'allowed_events',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_team_token' => 'boolean',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'allowed_events' => 'array',
        'team_emails' => 'array',
        'team_roles' => 'array',
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
     * Get the event this token is for (if event-specific).
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
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
        ?\DateTime $expiresAt = null,
        ?int $eventId = null
    ): self {
        return self::create([
            'branch_id' => $branchId,
            'event_id' => $eventId,
            'token' => self::generateToken(),
            'name' => $name,
            'email' => $email,
            'allowed_events' => $allowedEvents,
            'expires_at' => $expiresAt,
            'is_team_token' => false,
        ]);
    }

    /**
     * Create a new team token for a branch.
     */
    public static function createTeamTokenForBranch(
        int $branchId,
        string $teamName,
        array $teamEmails,
        array $teamRoles,
        ?array $allowedEvents = null,
        ?\DateTime $expiresAt = null,
        ?int $eventId = null
    ): self {
        return self::create([
            'branch_id' => $branchId,
            'event_id' => $eventId,
            'token' => self::generateToken(),
            'name' => $teamName,
            'team_name' => $teamName,
            'team_emails' => $teamEmails,
            'team_roles' => $teamRoles,
            'allowed_events' => $allowedEvents,
            'expires_at' => $expiresAt,
            'is_team_token' => true,
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

    /**
     * Check if this is a team token.
     */
    public function isTeamToken(): bool
    {
        return $this->is_team_token;
    }

    /**
     * Get team members as array of email => role pairs.
     */
    public function getTeamMembers(): array
    {
        if (! $this->isTeamToken()) {
            return [];
        }

        $members = [];
        $emails = $this->team_emails ?? [];
        $roles = $this->team_roles ?? [];

        for ($i = 0; $i < count($emails); $i++) {
            $members[$emails[$i]] = $roles[$i] ?? 'Team Member';
        }

        return $members;
    }

    /**
     * Check if an email is authorized for this token.
     */
    public function isEmailAuthorized(string $email): bool
    {
        if (! $this->isTeamToken()) {
            return $this->email === $email;
        }

        return in_array($email, $this->team_emails ?? []);
    }

    /**
     * Get the role for a specific email.
     */
    public function getRoleForEmail(string $email): ?string
    {
        if (! $this->isTeamToken()) {
            return $this->email === $email ? 'Token Owner' : null;
        }

        $emails = $this->team_emails ?? [];
        $roles = $this->team_roles ?? [];

        $index = array_search($email, $emails);

        return $index !== false ? ($roles[$index] ?? 'Team Member') : null;
    }

    /**
     * Check if this token is event-specific.
     */
    public function isEventSpecific(): bool
    {
        return $this->event_id !== null;
    }

    /**
     * Scope to get tokens for a specific event.
     */
    public function scopeForEvent($query, int $eventId)
    {
        return $query->where('event_id', $eventId);
    }
}

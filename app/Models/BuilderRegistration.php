<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BuilderRegistrationStatus;
use App\Enums\BusinessChallenge;
use App\Enums\BusinessStage;
use App\Enums\CacStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BuilderRegistration extends Model
{
    /** @use HasFactory<\Database\Factories\BuilderRegistrationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'full_name',
        'phone',
        'email',
        'business_name',
        'business_description',
        'business_stage',
        'industry',
        'industry_other',
        'biggest_challenge',
        'success_vision',
        'cac_status',
        'status',
        'contacted_at',
        'contacted_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => BuilderRegistrationStatus::class,
            'business_stage' => BusinessStage::class,
            'biggest_challenge' => BusinessChallenge::class,
            'cac_status' => CacStatus::class,
            'contacted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function contactedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contacted_by_user_id');
    }

    public function markContacted(User $user): void
    {
        $this->update([
            'status' => BuilderRegistrationStatus::Contacted,
            'contacted_at' => now(),
            'contacted_by_user_id' => $user->id,
        ]);
    }
}

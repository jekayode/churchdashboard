<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BuilderRegistrationStatus;
use App\Models\BuilderRegistration;
use App\Models\Role;
use App\Models\User;
use App\Notifications\BuilderAccountActivationNotification;
use App\Notifications\BuilderPackReadyNotification;
use App\Notifications\BuilderRegistrationReceivedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

final class BuilderRegistrationService
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{registration: BuilderRegistration, is_new_user: bool}
     */
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $email = strtolower(trim((string) $data['email']));
            $user = User::query()->where('email', $email)->first();
            $isNewUser = $user === null;

            if ($isNewUser) {
                $user = User::query()->create([
                    'name' => $data['full_name'],
                    'email' => $email,
                    'phone' => $data['phone'],
                    'password' => Hash::make(Str::random(32)),
                    'email_verified_at' => null,
                ]);

                $publicRole = Role::query()->where('name', 'public_user')->first();
                if ($publicRole) {
                    $user->roles()->attach($publicRole->id, [
                        'branch_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $registration = BuilderRegistration::query()->updateOrCreate(
                ['email' => $email],
                [
                    'user_id' => $user->id,
                    'full_name' => $data['full_name'],
                    'phone' => $data['phone'],
                    'business_name' => $data['business_name'],
                    'business_description' => $data['business_description'],
                    'business_stage' => $data['business_stage'],
                    'industry' => $data['industry'],
                    'industry_other' => $data['industry_other'] ?? null,
                    'biggest_challenge' => $data['biggest_challenge'],
                    'success_vision' => $data['success_vision'],
                    'cac_status' => $data['cac_status'],
                    'status' => BuilderRegistrationStatus::New,
                    'contacted_at' => null,
                    'contacted_by_user_id' => null,
                ]
            );

            if ($isNewUser) {
                $user->notify(new BuilderAccountActivationNotification);
            } else {
                $user->notify(new BuilderPackReadyNotification);
            }

            $this->notifyManagers($registration);

            return [
                'registration' => $registration,
                'is_new_user' => $isNewUser,
            ];
        });
    }

    private function notifyManagers(BuilderRegistration $registration): void
    {
        $managers = User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('name', [
                'super_admin',
                'branch_pastor',
                'directory_admin',
                'business_care_leader',
            ]))
            ->get()
            ->filter(fn (User $user) => $user->canManageBuilders())
            ->unique('id');

        if ($managers->isNotEmpty()) {
            Notification::send($managers, new BuilderRegistrationReceivedNotification($registration));
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class GuestRegistrationService
{
    public function __construct(
        private readonly EnvironmentAwareEmailService $emailService
    ) {}

    /**
     * Register a new guest user and member.
     */
    public function registerGuest(array $data): User
    {
        try {
            return DB::transaction(function () use ($data) {
                // Generate a random password
                $password = Str::random(12);

                // Log password for testing purposes (only in local environment)
                if (app()->environment('local')) {
                    \Log::info('Guest Registration - User Credentials', [
                        'email' => $data['email'],
                        'password' => $password,
                        'name' => trim($data['first_name'].' '.$data['surname']),
                    ]);
                }

                // Create user account
                $user = User::create([
                    'name' => trim($data['first_name'].' '.$data['surname']),
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'password' => Hash::make($password),
                    'email_verified_at' => now(), // Auto-verify for guests
                ]);

                // Assign public_user role
                $user->assignRole('public_user', (int) $data['branch_id']);

                // Create member record with fallback for missing columns
                $memberData = [
                    'user_id' => $user->id,
                    'branch_id' => (int) $data['branch_id'],
                    'name' => trim($data['first_name'].' '.$data['surname']),
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'gender' => $data['gender'] ?? null,
                    'preferred_call_time' => $data['preferred_call_time'] ?? null,
                    'home_address' => $data['home_address'] ?? null,
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'age_group' => $data['age_group'] ?? null,
                    'marital_status' => $data['marital_status'] ?? null,
                    'prayer_request' => $data['prayer_request'] ?? null,
                    'discovery_source' => $data['discovery_source'] ?? null,
                    'staying_intention' => $data['staying_intention'] ?? null,
                    'closest_location' => $data['closest_location'] ?? null,
                    'additional_info' => $data['additional_info'] ?? null,
                    'consent_given_at' => $data['consent_given_at'],
                    'consent_ip' => $data['consent_ip'],
                    'registration_source' => 'guest-form',
                    'member_status' => 'visitor',
                    'date_joined' => now(),
                ];

                // Add first_name and surname only if columns exist
                try {
                    // Test if first_name column exists
                    DB::select('SELECT first_name FROM members LIMIT 0');
                    $memberData['first_name'] = $data['first_name'];
                    $memberData['surname'] = $data['surname'];
                } catch (\Exception $e) {
                    // Column doesn't exist, skip it
                    \Log::warning('first_name/surname columns not available, using name field only', [
                        'error' => $e->getMessage(),
                    ]);
                }

                $member = Member::create($memberData);

                // Calculate initial profile completion
                $member->updateProfileCompletion();

                // Send welcome email with login credentials
                $branch = $user->member->branch;
                $this->emailService->sendWelcomeEmail(
                    $user->email,
                    $user->name,
                    $password,
                    $branch->name
                );

                return $user;
            });
        } catch (\Exception $e) {
            \Log::error('Guest registration failed: '.$e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Enroll user in email campaigns.
     */
    public function enrollInCampaigns(User $user): void
    {
        // TODO: Implement campaign enrollment
        // This will be implemented in Phase 4 (Communication System)
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\GuestRegistrationRequest;
use App\Http\Requests\ProfileCompletionRequest;
use App\Models\GuestRegistrationAttempt;
use App\Services\GuestRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class PublicAuthController extends Controller
{
    public function __construct(
        private readonly GuestRegistrationService $guestRegistrationService
    ) {}

    /**
     * Show the guest registration form.
     */
    public function showGuestForm(): View
    {
        $branches = \App\Models\Branch::where('status', 'active')->get();

        return view('public.guest-register', compact('branches'));
    }

    /**
     * Process guest registration.
     */
    public function storeGuest(GuestRegistrationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $attempt = $this->logGuestAttempt($validated, 'started');

        try {
            $user = $this->guestRegistrationService->registerGuest($validated);

            $attempt->update([
                'status' => 'success',
                'error_type' => null,
                'error_message' => null,
                'completed_at' => now(),
            ]);

            Auth::login($user);

            return redirect()
                ->route('member.profile-completion')
                ->with('success', 'Welcome! Please complete your profile to get the most out of your experience.');

        } catch (\Illuminate\Database\QueryException $e) {
            $attempt->update([
                'status' => 'database_error',
                'error_type' => 'query',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            if ($e->getCode() == 23000) {
                if (str_contains($e->getMessage(), 'users_email_unique')) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', 'This email address is already registered. Please use a different email or try logging in.');
                }
            }

            \Log::error('Guest registration database error: '.$e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Registration failed due to a database error. Please try again.');

        } catch (\Exception $e) {
            $attempt->update([
                'status' => 'error',
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            \Log::error('Guest registration error: '.$e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Registration failed. Please try again.');
        }
    }

    /**
     * Log a guest registration attempt for visibility by Guest Management.
     */
    private function logGuestAttempt(array $data, string $status): GuestRegistrationAttempt
    {
        return GuestRegistrationAttempt::create([
            'email' => $data['email'] ?? null,
            'first_name' => $data['first_name'] ?? null,
            'surname' => $data['surname'] ?? null,
            'phone' => $data['phone'] ?? null,
            'branch_id' => isset($data['branch_id']) ? (int) $data['branch_id'] : null,
            'status' => $status,
            'payload' => $data,
        ]);
    }

    /**
     * Show profile completion wizard.
     */
    public function showProfileCompletion(): View|RedirectResponse
    {
        $member = Auth::user()->member;

        if (! $member) {
            return redirect()->route('dashboard');
        }

        return view('member.profile-completion', compact('member'));
    }

    /**
     * Process profile completion.
     */
    public function updateProfileCompletion(ProfileCompletionRequest $request): RedirectResponse
    {
        try {
            $member = Auth::user()->member;

            if (! $member) {
                return redirect()->route('dashboard')
                    ->with('error', 'Member profile not found.');
            }

            $member->update($request->only([
                'gender',
                'preferred_call_time',
                'home_address',
                'date_of_birth',
                'age_group',
                'marital_status',
                'prayer_request',
                'discovery_source',
                'staying_intention',
                'closest_location',
                'additional_info',
            ]));

            $member->updateProfileCompletion();

            return redirect()
                ->route('dashboard')
                ->with('success', 'Profile updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Profile completion error: '.$e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update profile. Please try again.');
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\GuestRegistrationRequest;
use App\Http\Requests\ProfileCompletionRequest;
use App\Services\GuestRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class PublicAuthController extends Controller
{
    public function __construct(
        private readonly GuestRegistrationService $guestRegistrationService
    ) {
    }

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
        try {
            $user = $this->guestRegistrationService->registerGuest($request->validated());
            
            // Auto-login the user
            Auth::login($user);
            
            return redirect()
                ->route('member.profile-completion')
                ->with('success', 'Welcome! Please complete your profile to get the most out of your experience.');
                
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                if (str_contains($e->getMessage(), 'users_email_unique')) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', 'This email address is already registered. Please use a different email or try logging in.');
                }
            }
            
            \Log::error('Guest registration database error: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Registration failed due to a database error. Please try again.');
                
        } catch (\Exception $e) {
            \Log::error('Guest registration error: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Registration failed. Please try again.');
        }
    }

    /**
     * Show profile completion wizard.
     */
    public function showProfileCompletion(): View|RedirectResponse
    {
        $member = Auth::user()->member;
        
        if (!$member) {
            return redirect()->route('dashboard');
        }
        
        return view('member.profile-completion', compact('member'));
    }

    /**
     * Process profile completion.
     */
    public function updateProfileCompletion(ProfileCompletionRequest $request): RedirectResponse
    {

        $member = Auth::user()->member;
        
        if (!$member) {
            return redirect()->route('dashboard');
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
    }
}

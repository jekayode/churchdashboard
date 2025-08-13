<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

final class TwoFactorController extends Controller
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Show the two-factor authentication setup page.
     */
    public function show(Request $request): View
    {
        $user = $request->user();
        
        if (!$user->two_factor_enabled) {
            // Generate a new secret if not already set
            if (!$user->two_factor_secret) {
                $user->two_factor_secret = Crypt::encryptString($this->google2fa->generateSecretKey());
                $user->save();
            }

            $qrCodeUrl = $this->google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email,
                Crypt::decryptString($user->two_factor_secret)
            );
        } else {
            $qrCodeUrl = null;
        }

        return view('auth.two-factor', [
            'user' => $user,
            'qrCodeUrl' => $qrCodeUrl,
            'recoveryCodes' => $user->two_factor_enabled ? $this->getRecoveryCodes($user) : null,
        ]);
    }

    /**
     * Enable two-factor authentication.
     */
    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();
        
        if ($user->two_factor_enabled) {
            return back()->withErrors(['code' => 'Two-factor authentication is already enabled.']);
        }

        $secret = Crypt::decryptString($user->two_factor_secret);
        
        if (!$this->google2fa->verifyKey($secret, $request->code)) {
            return back()->withErrors(['code' => 'The provided two-factor authentication code is invalid.']);
        }

        // Generate recovery codes
        $recoveryCodes = $this->generateRecoveryCodes();
        
        $user->update([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => Crypt::encryptString($recoveryCodes->toJson()),
        ]);

        return back()->with('status', 'Two-factor authentication has been enabled successfully!');
    }

    /**
     * Disable two-factor authentication.
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = $request->user();
        
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return back()->with('status', 'Two-factor authentication has been disabled.');
    }

    /**
     * Show recovery codes.
     */
    public function recoveryCodes(Request $request): View
    {
        $user = $request->user();
        
        if (!$user->two_factor_enabled) {
            abort(404);
        }

        return view('auth.two-factor-recovery-codes', [
            'recoveryCodes' => $this->getRecoveryCodes($user),
        ]);
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = $request->user();
        
        if (!$user->two_factor_enabled) {
            abort(404);
        }

        $recoveryCodes = $this->generateRecoveryCodes();
        
        $user->update([
            'two_factor_recovery_codes' => Crypt::encryptString($recoveryCodes->toJson()),
        ]);

        return back()->with('status', 'Recovery codes have been regenerated.');
    }

    /**
     * Verify two-factor authentication code (for API).
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();
        
        if (!$user->two_factor_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'Two-factor authentication is not enabled.',
            ], 400);
        }

        $secret = Crypt::decryptString($user->two_factor_secret);
        $code = $request->code;

        // Check if it's a recovery code
        if (strlen($code) > 6) {
            $recoveryCodes = $this->getRecoveryCodes($user);
            
            if ($recoveryCodes->contains($code)) {
                // Remove used recovery code
                $updatedCodes = $recoveryCodes->reject(fn($recoveryCode) => $recoveryCode === $code);
                $user->update([
                    'two_factor_recovery_codes' => Crypt::encryptString($updatedCodes->toJson()),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Recovery code verified successfully.',
                    'recovery_code_used' => true,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid recovery code.',
            ], 400);
        }

        // Verify TOTP code
        if ($this->google2fa->verifyKey($secret, $code)) {
            return response()->json([
                'success' => true,
                'message' => 'Two-factor authentication code verified successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid two-factor authentication code.',
        ], 400);
    }

    /**
     * Generate recovery codes.
     */
    private function generateRecoveryCodes(): Collection
    {
        return collect(range(1, 8))->map(function () {
            return strtoupper(substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(32))), 0, 10));
        });
    }

    /**
     * Get decrypted recovery codes.
     */
    private function getRecoveryCodes($user): Collection
    {
        if (!$user->two_factor_recovery_codes) {
            return collect();
        }

        return collect(json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true));
    }
} 
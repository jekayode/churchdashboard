<?php

declare(strict_types=1);

namespace App\Http\Controllers\Builders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Builders\SetBuilderPasswordRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

final class BuilderActivationController extends Controller
{
    public function show(Request $request, User $user): View|RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This activation link is invalid or has expired.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('builders.account');
        }

        return view('builders.activate', compact('user'));
    }

    public function store(SetBuilderPasswordRequest $request, User $user): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This activation link is invalid or has expired.');
        }

        $user->forceFill([
            'password' => Hash::make($request->validated('password')),
            'email_verified_at' => now(),
        ])->save();

        Auth::login($user);

        return redirect()
            ->route('builders.account')
            ->with('status', 'Your account is activated. Download your Business Starter Pack below.');
    }
}

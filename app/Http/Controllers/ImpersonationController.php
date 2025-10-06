<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class ImpersonationController extends Controller
{
    public function start(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();

        // Only super_admin or branch_pastor can impersonate
        if (! $actor->isSuperAdmin() && ! $actor->isBranchPastor()) {
            abort(403);
        }

        // Save the original user id once
        if (! session()->has('impersonator_id')) {
            session(['impersonator_id' => $actor->id]);
        }

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'You are now impersonating '.$user->name);
    }

    public function stop(Request $request): RedirectResponse
    {
        $impersonatorId = (int) session('impersonator_id');
        if (! $impersonatorId) {
            return redirect()->route('dashboard');
        }

        $impersonator = User::find($impersonatorId);
        if (! $impersonator) {
            session()->forget('impersonator_id');

            return redirect()->route('dashboard');
        }

        Auth::login($impersonator);
        session()->forget('impersonator_id');

        return redirect()->route('dashboard')->with('success', 'Impersonation ended.');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

final class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $branches = Branch::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('auth.register', compact('branches', 'roles'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:20'],
            'branch_id' => ['required', 'exists:branches,id'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['required', 'accepted'],
        ]);

        // Prevent super admin registration through public form
        $role = Role::findOrFail($request->role_id);
        if ($role->name === 'super_admin') {
            return back()->withErrors([
                'role_id' => 'This role is not available for public registration.',
            ])->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // Assign the selected role to the user for the selected branch
        $user->roles()->attach($role->id, [
            'branch_id' => $request->branch_id,
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Redirect based on role
        return $this->redirectBasedOnRole($user);
    }

    /**
     * Redirect user to appropriate dashboard based on their role.
     */
    private function redirectBasedOnRole(User $user): RedirectResponse
    {
        $primaryRole = $user->getPrimaryRole();

        return match($primaryRole?->name) {
            'super_admin' => redirect()->route('admin.dashboard'),
            'branch_pastor' => redirect()->route('pastor.dashboard'),
            'ministry_leader' => redirect()->route('ministry.dashboard'),
            'department_leader' => redirect()->route('department.dashboard'),
            'church_member' => redirect()->route('member.dashboard'),
            'public_user' => redirect()->route('public.dashboard'),
            default => redirect()->route('dashboard'),
        };
    }
}

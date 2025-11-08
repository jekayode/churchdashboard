<?php

declare(strict_types=1);

use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\MinisterDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Admin: regenerate recurring instances for a specific event
Route::middleware(['auth'])->group(function () {
    Route::post('/admin/events/{event}/generate-instances', [\App\Http\Controllers\EventController::class, 'generateInstancesForEvent'])
        ->name('admin.events.generate-instances');
    Route::get('/admin/events/{event}', [\App\Http\Controllers\EventController::class, 'showAdmin'])
        ->name('admin.events.show');
    Route::get('/admin/events/{event}/edit', [\App\Http\Controllers\EventController::class, 'showAdmin'])
        ->name('admin.events.edit');
    Route::put('/admin/events/{event}', [\App\Http\Controllers\EventController::class, 'updateAdmin'])
        ->name('admin.events.update');
});

// Impersonation routes (super_admin or branch_pastor)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/impersonate/{user}', [ImpersonationController::class, 'start'])->name('impersonate.start');
    Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])->name('impersonate.stop');
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = Auth::user();
    $primaryRole = $user->getPrimaryRole();

    // Route to role-specific dashboard views
    $dashboardView = match ($primaryRole?->name) {
        'super_admin' => 'dashboards.super-admin',
        'branch_pastor' => 'dashboards.branch-pastor',
        'ministry_leader' => 'dashboards.ministry-leader',
        'department_leader' => 'dashboards.department-leader',
        'church_member' => 'dashboards.church-member',
        'public_user' => 'dashboards.public-user',
        default => 'dashboard',
    };

    return view($dashboardView);
})->middleware(['auth', 'verified'])->name('dashboard');

// Minister dashboard (isolated)
Route::middleware(['auth', 'verified', 'role:ministry_leader'])->group(function () {
    Route::get('/minister/dashboard', [MinisterDashboardController::class, 'index'])->name('minister.dashboard');
    // Communication shortcuts for ministers (reuse pastor/admin views for now)
    Route::get('/minister/communication/settings', function () {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        return view('admin.communication.settings', compact('isSuperAdmin'));
    })->name('minister.communication.settings');
});

// Sidebar Layout Sample Route (for testing)
Route::get('/sidebar-sample', function () {
    return view('dashboards.sidebar-sample');
})->middleware(['auth', 'verified'])->name('sidebar-sample');

Route::get('/test-api', function () {
    return view('test-api');
})->middleware(['auth', 'verified'])->name('test-api');

// Super Admin Routes
Route::middleware(['auth', 'verified', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/branches', [App\Http\Controllers\BranchController::class, 'indexView'])->name('branches');

    Route::get('/members', function () {
        return view('admin.members.index');
    })->name('members');

    Route::get('/departments', function () {
        return view('admin.departments.index');
    })->name('departments');

    Route::get('/ministries', function () {
        return view('admin.ministries.index');
    })->name('ministries');

    Route::get('/events', function () {
        return view('admin.events.index');
    })->name('events');

    Route::get('/small-groups', function () {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        return view('admin.small-groups.index', compact('isSuperAdmin'));
    })->name('small-groups');

    Route::get('/small-groups/reports', function () {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        return view('admin.small-groups.reports.index', compact('isSuperAdmin'));
    })->name('small-groups.reports');

    Route::get('/projections', function () {
        return view('admin.projections.index');
    })->name('projections');

    Route::get('/finances', function () {
        return view('admin.finances.index');
    })->name('finances');

    Route::get('/users', function () {
        return view('admin.users.index');
    })->name('users');

    Route::get('/reports', [ReportingController::class, 'index'])->name('reports');
    Route::get('/reports/dashboard', [ReportingController::class, 'superAdminDashboard'])->name('reports.dashboard');
    Route::get('/api/reports/dashboard', [ReportingController::class, 'getDashboardData'])->name('api.reports.dashboard');
    Route::get('/performance', function () {
        return view('admin.reports.network');
    })->name('performance');

    Route::get('/import-export', function () {
        return view('admin.import-export.index');
    })->name('import-export');

    // Communication Routes
    Route::prefix('communication')->name('communication.')->group(function () {
        Route::get('/settings', function () {
            $isSuperAdmin = true;

            return view('admin.communication.settings', compact('isSuperAdmin'));
        })->name('settings');

        Route::get('/templates', function () {
            $isSuperAdmin = true;

            return view('admin.communication.templates', compact('isSuperAdmin'));
        })->name('templates');

        Route::get('/campaigns', function () {
            $isSuperAdmin = true;

            return view('admin.communication.campaigns', compact('isSuperAdmin'));
        })->name('campaigns');

        Route::get('/logs', function () {
            $isSuperAdmin = true;

            return view('admin.communication.logs', compact('isSuperAdmin'));
        })->name('logs');

        Route::get('/quick-send', function () {
            $isSuperAdmin = true;

            return view('admin.communication.quick-send', compact('isSuperAdmin'));
        })->name('quick-send');

        Route::get('/mass-send', function () {
            $isSuperAdmin = true;

            return view('admin.communication.mass-send', compact('isSuperAdmin'));
        })->name('mass-send');
    });
});

// Branch Pastor Routes
Route::middleware(['auth', 'verified', 'role:branch_pastor,super_admin'])->prefix('pastor')->name('pastor.')->group(function () {
    Route::get('/ministries', function () {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        return view('pastor.ministries.index', compact('isSuperAdmin'));
    })->name('ministries');

    Route::get('/members', function () {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        return view('pastor.members.index', compact('isSuperAdmin'));
    })->name('members');

    Route::get('/events', function () {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        return view('pastor.events.index', compact('isSuperAdmin'));
    })->name('events');

    Route::get('/events/{event}/registrations', function ($eventId) {
        return view('pastor.events.registrations', compact('eventId'));
    })->name('events.registrations');

    Route::get('/finances', function () {
        return view('pastor.finances.index');
    })->name('finances');

    Route::get('/reports', function () {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        // Get event types from EventReport model (same as admin)
        $eventTypes = \App\Models\EventReport::EVENT_TYPES;

        return view('pastor.reports.index', compact('isSuperAdmin', 'eventTypes'));
    })->name('reports');
    Route::get('/performance', function () {
        return view('pastor.reports.performance');
    })->name('performance');

    Route::get('/projections', function () {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        return view('pastor.projections.index', compact('isSuperAdmin'));
    })->name('projections');

    Route::get('/departments', function () {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        return view('pastor.departments.index', compact('isSuperAdmin'));
    })->name('departments');

    Route::get('/ministry-events', function () {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        return view('pastor.ministry-events.index', compact('isSuperAdmin'));
    })->name('ministry-events');

    Route::get('/import-export', function () {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        return view('pastor.import-export.index', compact('isSuperAdmin'));
    })->name('import-export');

    Route::get('/small-groups', function () {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        return view('pastor.small-groups.index', compact('isSuperAdmin'));
    })->name('small-groups');

    Route::get('/small-groups/{smallGroup}/members', [App\Http\Controllers\SmallGroupController::class, 'showMembers'])->name('small-groups.members');

    Route::get('/small-groups/reports', function () {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        return view('pastor.small-groups.reports.index', compact('isSuperAdmin'));
    })->name('small-groups.reports');

    Route::get('/groups/reports', function () {
        return view('member.groups.reports');
    })->name('groups.reports');

    // Communication Routes
    Route::prefix('communication')->name('communication.')->group(function () {
        Route::get('/settings', function () {
            $user = Auth::user();
            $isSuperAdmin = $user->isSuperAdmin();

            return view('admin.communication.settings', compact('isSuperAdmin'));
        })->name('settings');

        Route::get('/templates', function () {
            $user = Auth::user();
            $isSuperAdmin = $user->isSuperAdmin();

            return view('admin.communication.templates', compact('isSuperAdmin'));
        })->name('templates');

        Route::get('/campaigns', function () {
            $user = Auth::user();
            $isSuperAdmin = $user->isSuperAdmin();

            return view('admin.communication.campaigns', compact('isSuperAdmin'));
        })->name('campaigns');

        Route::get('/logs', function () {
            $user = Auth::user();
            $isSuperAdmin = $user->isSuperAdmin();

            return view('admin.communication.logs', compact('isSuperAdmin'));
        })->name('logs');

        Route::get('/quick-send', function () {
            $user = Auth::user();
            $isSuperAdmin = $user->isSuperAdmin();

            return view('admin.communication.quick-send', compact('isSuperAdmin'));
        })->name('quick-send');

        Route::get('/mass-send', function () {
            $user = Auth::user();
            $isSuperAdmin = $user->isSuperAdmin();

            return view('admin.communication.mass-send', compact('isSuperAdmin'));
        })->name('mass-send');
    });
});

// Ministry Leader Routes
Route::middleware(['auth', 'verified', 'role:ministry_leader,super_admin,branch_pastor'])->prefix('ministry')->name('ministry.')->group(function () {
    Route::get('/departments', function () {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        return view('ministry.departments.index', compact('isSuperAdmin'));
    })->name('departments');

    Route::get('/events', function () {
        return view('ministry.events.index');
    })->name('events');
});

// Department Leader Routes
Route::middleware(['auth', 'verified', 'role:department_leader,super_admin,branch_pastor,ministry_leader'])->prefix('department')->name('department.')->group(function () {
    Route::get('/team', function () {
        return view('department.team.index');
    })->name('team');
});

// Church Member Routes
Route::middleware(['auth', 'verified', 'role:church_member,super_admin,branch_pastor,ministry_leader,department_leader'])->prefix('member')->name('member.')->group(function () {
    Route::get('/events', function () {
        return view('member.events.index');
    })->name('events');

    Route::get('/groups', function () {
        $user = Auth::user();

        // Redirect admins and pastors to the management interface
        if ($user->isSuperAdmin() || $user->isBranchPastor()) {
            return redirect()->route('pastor.small-groups');
        }

        return view('member.groups.index');
    })->name('groups');

    Route::get('/groups/reports', function () {
        return view('member.groups.reports');
    })->name('groups.reports');

    Route::get('/departments', function () {
        return view('member.departments.index');
    })->name('departments');

    // Member Profile (split into dedicated pages)
    Route::get('/profile', function () {
        return view('member.profile.details');
    })->name('profile');

    Route::get('/profile/edit', function () {
        return view('member.profile.edit');
    })->name('profile.edit');

    Route::get('/profile/security', function () {
        return view('member.profile.security');
    })->name('profile.security');

    Route::put('/profile', [App\Http\Controllers\MemberController::class, 'updateProfile'])->name('profile.update');

    // Spouse search (member session auth)
    Route::get('/spouse-search', function (\Illuminate\Http\Request $request) {
        $q = trim((string) $request->get('q', ''));
        $excludeId = (int) $request->get('exclude_id', 0);
        $branchId = (int) $request->get('branch_id', 0);

        $query = \App\Models\Member::query()
            ->select(['id', 'name'])
            ->when($q !== '', function ($qBuilder) use ($q) {
                $qBuilder->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->when($excludeId > 0, fn ($qb) => $qb->where('id', '!=', $excludeId))
            ->when($branchId > 0, fn ($qb) => $qb->where('branch_id', $branchId))
            ->orderBy('name')
            ->limit(25);

        return $query->get();
    })->name('spouse.search');

});

// Profile completion routes (accessible to authenticated users)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile-completion', [App\Http\Controllers\PublicAuthController::class, 'showProfileCompletion'])->name('member.profile-completion');
    Route::post('/profile-completion', [App\Http\Controllers\PublicAuthController::class, 'updateProfileCompletion'])->name('member.profile-completion.update');
});

// Authenticated member search endpoint (used for spouse selection) - uses web auth session
Route::middleware(['auth'])->get('/api/members/search', function (\Illuminate\Http\Request $request) {
    $q = trim((string) $request->get('q', ''));
    $excludeId = (int) $request->get('exclude_id', 0);
    $branchId = (int) $request->get('branch_id', 0);

    $query = \App\Models\Member::query()
        ->select(['id', 'name', 'first_name', 'surname', 'branch_id'])
        ->when($q !== '', function ($qBuilder) use ($q) {
            $qBuilder->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('first_name', 'like', "%{$q}%")
                    ->orWhere('surname', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        })
        ->when($excludeId > 0, fn ($qb) => $qb->where('id', '!=', $excludeId))
        ->when($branchId > 0, fn ($qb) => $qb->where('branch_id', $branchId))
        ->orderBy('name')
        ->limit(25);

    return $query->get()->map(function ($m) {
        return [
            'id' => $m->id,
            'name' => $m->name,
        ];
    });
})->name('api.members.search');

// Public Routes (accessible to all)
Route::prefix('public')->name('public.')->group(function () {
    Route::get('/events', function () {
        return view('public.events.index');
    })->name('events');

    Route::get('/lifegroups', function () {
        return view('public.lifegroups.index');
    })->name('lifegroups');

    Route::get('/registration-success', function () {
        // This route should only be accessed via redirect with session data
        $eventName = session('registration_success.event_name');
        $userEmail = session('registration_success.user_email');
        $generatedPassword = session('registration_success.generated_password');

        if (! $eventName || ! $userEmail) {
            return redirect()->route('public.events')->with('error', 'Invalid access to success page.');
        }

        // Clear the session data after displaying
        session()->forget('registration_success');

        return view('public.registration-success', compact('eventName', 'userEmail', 'generatedPassword'));
    })->name('registration-success');

    Route::get('/about', function () {
        return view('public.about.index');
    })->name('about');

    // Guest registration routes
    Route::get('/register/guest', [App\Http\Controllers\PublicAuthController::class, 'showGuestForm'])->name('guest-register');
    Route::post('/register/guest', [App\Http\Controllers\PublicAuthController::class, 'storeGuest'])->name('guest-register.store');
});

// Utility Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/switch-branch/{branch}', function ($branchId) {
        // Logic to switch user's active branch would go here
        // For now, just redirect back to dashboard
        return redirect()->route('dashboard')->with('success', 'Branch switched successfully');
    })->name('switch.branch');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Two-Factor Authentication routes
    Route::prefix('two-factor')->name('two-factor.')->group(function () {
        Route::get('/', [TwoFactorController::class, 'show'])->name('show');
        Route::post('/enable', [TwoFactorController::class, 'enable'])->name('enable');
        Route::post('/disable', [TwoFactorController::class, 'disable'])->name('disable');
        Route::get('/recovery-codes', [TwoFactorController::class, 'recoveryCodes'])->name('recovery-codes');
        Route::post('/recovery-codes/regenerate', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('recovery-codes.regenerate');
    });
});

// Public event routes (no authentication required)
Route::prefix('public-api')->group(function () {
    Route::prefix('events')->group(function () {
        Route::get('/', [App\Http\Controllers\EventController::class, 'publicIndex']); // Public can view events
        Route::get('/{event}', [App\Http\Controllers\EventController::class, 'publicShow']); // Public can view event details
        Route::post('/{event}/register', [App\Http\Controllers\EventController::class, 'publicRegister']); // Public registration
    });
});

// Public check-in routes
Route::get('/scanner', [App\Http\Controllers\EventController::class, 'showScanner'])->name('public.scanner');
Route::get('/check-in/{registration}', [App\Http\Controllers\EventController::class, 'publicCheckIn'])->name('public.check-in');

// Public report submission routes
Route::prefix('public/reports')->group(function () {
    Route::get('/submit/{token}', [\App\Http\Controllers\PublicReportController::class, 'showSubmissionForm'])
        ->name('public.reports.submit');
    Route::post('/submit/{token}', [\App\Http\Controllers\PublicReportController::class, 'submitReport'])
        ->name('public.reports.submit.store');
    Route::get('/events/{token}', [\App\Http\Controllers\PublicReportController::class, 'getEvents'])
        ->name('public.reports.events');
});

require __DIR__.'/auth.php';

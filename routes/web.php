<?php

declare(strict_types=1);

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = Auth::user();
    $primaryRole = $user->getPrimaryRole();
    
    // Route to role-specific dashboard views
    $dashboardView = match($primaryRole?->name) {
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

// Super Admin Routes
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/branches', [App\Http\Controllers\BranchController::class, 'indexView'])->name('branches');
    
    Route::get('/projections', function () {
        return view('admin.projections.index');
    })->name('projections');
    
    Route::get('/users', function () {
        return view('admin.users.index');
    })->name('users');
    
    Route::get('/reports', function () {
        return view('admin.reports.index');
    })->name('reports');
    
    Route::get('/import-export', function () {
        return view('admin.import-export.index');
    })->name('import-export');
});


// Branch Pastor Routes
Route::middleware(['auth', 'verified'])->prefix('pastor')->name('pastor.')->group(function () {
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
    
    Route::get('/import-export', function () {
        return view('admin.import-export.index');
    })->name('import-export');
});

// Ministry Leader Routes
Route::middleware(['auth', 'verified'])->prefix('ministry')->name('ministry.')->group(function () {
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
Route::middleware(['auth', 'verified'])->prefix('department')->name('department.')->group(function () {
    Route::get('/team', function () {
        return view('department.team.index');
    })->name('team');
});

// Church Member Routes
Route::middleware(['auth', 'verified'])->prefix('member')->name('member.')->group(function () {
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
    
    Route::get('/profile', function () {
        return view('member.profile.show');
    })->name('profile');
});

// Public Routes (accessible to all)
Route::prefix('public')->name('public.')->group(function () {
    Route::get('/events', function () {
        return view('public.events.index');
    })->name('events');
    
    Route::get('/registration-success', function () {
        // This route should only be accessed via redirect with session data
        $eventName = session('registration_success.event_name');
        $userEmail = session('registration_success.user_email');
        $generatedPassword = session('registration_success.generated_password');
        
        if (!$eventName || !$userEmail) {
            return redirect()->route('public.events')->with('error', 'Invalid access to success page.');
        }
        
        // Clear the session data after displaying
        session()->forget('registration_success');
        
        return view('public.registration-success', compact('eventName', 'userEmail', 'generatedPassword'));
    })->name('registration-success');
    
    Route::get('/about', function () {
        return view('public.about.index');
    })->name('about');
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

require __DIR__.'/auth.php';

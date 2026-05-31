<?php

declare(strict_types=1);

use App\Http\Controllers\Builders\Admin\BuilderRegistrationAdminController;
use App\Http\Controllers\Builders\Admin\BuilderResourceController;
use App\Http\Controllers\Builders\Admin\BuilderSettingsController;
use App\Http\Controllers\Builders\BuilderAccountController;
use App\Http\Controllers\Builders\BuilderActivationController;
use App\Http\Controllers\Builders\BuilderRegistrationController;
use Illuminate\Support\Facades\Route;

Route::prefix('builders')->name('builders.')->group(function () {
    Route::get('/', [BuilderRegistrationController::class, 'create'])->name('create');
    Route::post('/', [BuilderRegistrationController::class, 'store'])->name('store')->middleware('throttle:10,1');
    Route::get('/thank-you', [BuilderRegistrationController::class, 'thankYou'])->name('thank-you');

    Route::get('/activate/{user}', [BuilderActivationController::class, 'show'])->name('activate');
    Route::post('/activate/{user}', [BuilderActivationController::class, 'store'])->name('activate.store');

    Route::middleware('auth')->group(function () {
        Route::get('/account', [BuilderAccountController::class, 'index'])->name('account');
        Route::get('/pack/{resource}/download', [BuilderAccountController::class, 'download'])->name('pack.download');
    });
});

Route::middleware(['auth', 'verified', 'manage.builders'])
    ->prefix('admin/builders')
    ->name('admin.builders.')
    ->group(function () {
        Route::get('/', fn () => view('admin.builders.index'))->name('index');
        Route::get('/registrations', fn () => view('admin.builders.registrations'))->name('registrations');
        Route::get('/registrations/{registration}', function (\App\Models\BuilderRegistration $registration) {
            return view('admin.builders.registration-show', compact('registration'));
        })->name('registrations.show');
        Route::get('/settings', fn () => view('admin.builders.settings'))->name('settings');
    });

Route::middleware(['auth', 'verified', 'manage.builders'])
    ->prefix('api/admin/builders')
    ->name('api.admin.builders.')
    ->group(function () {
        Route::get('/stats', [BuilderRegistrationAdminController::class, 'stats'])->name('stats');
        Route::get('/settings', [BuilderSettingsController::class, 'show'])->name('settings.show');
        Route::put('/settings', [BuilderSettingsController::class, 'update'])->name('settings.update');

        Route::post('/resources', [BuilderResourceController::class, 'store'])->name('resources.store');
        Route::delete('/resources/{resource}', [BuilderResourceController::class, 'destroy'])->name('resources.destroy');
        Route::post('/resources/reorder', [BuilderResourceController::class, 'reorder'])->name('resources.reorder');

        Route::get('/registrations', [BuilderRegistrationAdminController::class, 'index'])->name('registrations.index');
        Route::get('/registrations/{registration}', [BuilderRegistrationAdminController::class, 'show'])->name('registrations.show');
        Route::post('/registrations/{registration}/contacted', [BuilderRegistrationAdminController::class, 'markContacted'])->name('registrations.contacted');
    });

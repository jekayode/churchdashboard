<?php

declare(strict_types=1);

use App\Http\Controllers\Directory\Admin\DirectoryAnnouncementController;
use App\Http\Controllers\Directory\Admin\DirectoryBusinessController;
use App\Http\Controllers\Directory\Admin\DirectoryCategoryController;
use App\Http\Controllers\Directory\Admin\DirectoryChangelogController;
use App\Http\Controllers\Directory\Admin\DirectorySettingsController;
use App\Http\Controllers\Directory\BusinessController;
use App\Http\Controllers\Directory\BusinessImageController;
use App\Http\Controllers\Directory\BusinessLikeController;
use App\Http\Controllers\Directory\BusinessMessageController;
use App\Http\Controllers\Directory\BusinessReviewController;
use App\Http\Controllers\Directory\BusinessSubResourceController;
use App\Http\Controllers\Directory\ProductLikeController;
use App\Http\Controllers\Directory\PublicBizController;
use App\Http\Controllers\Directory\PublicDirectoryController;
use Illuminate\Support\Facades\Route;

// Public API (no auth)
Route::prefix('api/biz')->name('api.biz.')->group(function () {
    Route::get('/landing', [PublicBizController::class, 'landing'])->name('landing');
    Route::middleware('throttle:60,1')->get('/search', [PublicBizController::class, 'search'])->name('search');
    Route::get('/categories/{slug}', [PublicBizController::class, 'category'])->name('category');
    Route::get('/changelog', [PublicBizController::class, 'changelog'])->name('changelog');
    Route::get('/businesses/{business:slug}', [PublicBizController::class, 'show'])->name('business.show');
    Route::get('/businesses/{business:slug}/reviews', [BusinessReviewController::class, 'index'])->name('business.reviews');
});

Route::get('/biz-sitemap.xml', function () {
    $businesses = \App\Models\Business::query()->publiclyVisible()->get(['slug', 'updated_at']);
    $content = view('directory.sitemap', compact('businesses'))->render();

    return response($content, 200, ['Content-Type' => 'application/xml']);
})->name('biz.sitemap');

// Public web (no auth) — fixed routes before {slug}
Route::prefix('biz')->name('biz.')->group(function () {
    Route::get('/', [PublicDirectoryController::class, 'landing'])->name('landing');
    Route::get('/search', [PublicDirectoryController::class, 'search'])->name('search');
    Route::get('/categories/{slug}', [PublicDirectoryController::class, 'category'])->name('category');
    Route::get('/changelog', [PublicDirectoryController::class, 'changelog'])->name('changelog');

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/owner', [PublicDirectoryController::class, 'ownerDashboard'])->name('owner');
        Route::get('/owner/businesses/create', [PublicDirectoryController::class, 'ownerCreateBusiness'])->name('owner.businesses.create');
        Route::get('/owner/businesses/{business:slug}/edit', [PublicDirectoryController::class, 'ownerEditBusiness'])->name('owner.businesses.edit');
        Route::get('/account', [PublicDirectoryController::class, 'account'])->name('account');
        Route::get('/messages', [PublicDirectoryController::class, 'messages'])->name('messages');
        Route::get('/messages/{threadId}', [PublicDirectoryController::class, 'messageThread'])->name('messages.thread');
        Route::get('/favorites', [PublicDirectoryController::class, 'favorites'])->name('favorites');
        Route::get('/reviews', [PublicDirectoryController::class, 'myReviews'])->name('reviews');
    });

    Route::get('/{business:slug}', [PublicDirectoryController::class, 'show'])->name('show');
});

// Authenticated API
Route::middleware(['auth:sanctum,web'])->group(function () {
    Route::prefix('api/biz')->name('api.biz.')->group(function () {
        Route::get('/my-businesses', [BusinessController::class, 'index'])->name('my-businesses');
        Route::post('/businesses', [BusinessController::class, 'store'])->name('businesses.store');
        Route::get('/businesses/{business}', [BusinessController::class, 'show'])->name('businesses.show');
        Route::put('/businesses/{business}', [BusinessController::class, 'update'])->name('businesses.update');
        Route::delete('/businesses/{business}', [BusinessController::class, 'destroy'])->name('businesses.destroy');
        Route::post('/businesses/{business}/submit', [BusinessController::class, 'submit'])->name('businesses.submit');

        Route::post('/businesses/{business}/logo', [BusinessImageController::class, 'uploadLogo']);
        Route::post('/businesses/{business}/cover', [BusinessImageController::class, 'uploadCover']);
        Route::post('/businesses/{business}/gallery', [BusinessImageController::class, 'uploadGallery']);
        Route::delete('/businesses/{business}/gallery/{mediaId}', [BusinessImageController::class, 'deleteGalleryImage']);

        Route::post('/businesses/{business}/services', [BusinessSubResourceController::class, 'storeService']);
        Route::put('/businesses/{business}/services/{service}', [BusinessSubResourceController::class, 'updateService']);
        Route::delete('/businesses/{business}/services/{service}', [BusinessSubResourceController::class, 'destroyService']);

        Route::post('/businesses/{business}/products', [BusinessSubResourceController::class, 'storeProduct']);
        Route::put('/businesses/{business}/products/{product}', [BusinessSubResourceController::class, 'updateProduct']);
        Route::delete('/businesses/{business}/products/{product}', [BusinessSubResourceController::class, 'destroyProduct']);

        Route::post('/businesses/{business}/posts', [BusinessSubResourceController::class, 'storePost']);
        Route::delete('/businesses/{business}/posts/{post}', [BusinessSubResourceController::class, 'destroyPost']);

        Route::post('/businesses/{business}/team-members', [BusinessSubResourceController::class, 'storeTeamMember']);
        Route::put('/businesses/{business}/team-members/{teamMember}', [BusinessSubResourceController::class, 'updateTeamMember']);
        Route::delete('/businesses/{business}/team-members/{teamMember}', [BusinessSubResourceController::class, 'destroyTeamMember']);

        Route::middleware('throttle:60,1')->group(function () {
            Route::post('/businesses/{business}/like', [BusinessLikeController::class, 'toggle']);
            Route::post('/products/{product}/like', [ProductLikeController::class, 'toggle']);
        });
        Route::get('/favorites', [BusinessLikeController::class, 'favorites']);

        Route::middleware('throttle:20,1')->group(function () {
            Route::post('/businesses/{business}/reviews', [BusinessReviewController::class, 'store']);
            Route::post('/businesses/{business}/reviews/{review}/reply', [BusinessReviewController::class, 'reply']);
        });

        Route::get('/messages/threads', [BusinessMessageController::class, 'threads']);
        Route::get('/messages/threads/{threadId}', [BusinessMessageController::class, 'showThread']);
        Route::middleware('throttle:30,1')->group(function () {
            Route::post('/businesses/{business}/messages', [BusinessMessageController::class, 'store']);
            Route::post('/messages/threads/{threadId}/reply', [BusinessMessageController::class, 'reply']);
        });
    });

    Route::prefix('api/admin/biz')->middleware('role:super_admin,branch_pastor,directory_admin')->name('api.admin.biz.')->group(function () {
        Route::get('/stats', [DirectoryBusinessController::class, 'stats']);
        Route::get('/businesses', [DirectoryBusinessController::class, 'index']);
        Route::post('/businesses/{business}/approve', [DirectoryBusinessController::class, 'approve']);
        Route::post('/businesses/{business}/reject', [DirectoryBusinessController::class, 'reject']);
        Route::post('/businesses/{business}/toggle-active', [DirectoryBusinessController::class, 'toggleActive']);
        Route::post('/businesses/{business}/toggle-featured', [DirectoryBusinessController::class, 'toggleFeatured']);
        Route::post('/owners/{userId}/deactivate', [DirectoryBusinessController::class, 'deactivateOwner']);

        Route::get('/categories', [DirectoryCategoryController::class, 'index']);
        Route::post('/categories', [DirectoryCategoryController::class, 'store']);
        Route::put('/categories/{category}', [DirectoryCategoryController::class, 'update']);
        Route::delete('/categories/{category}', [DirectoryCategoryController::class, 'destroy']);

        Route::get('/settings', [DirectorySettingsController::class, 'show']);
        Route::put('/settings', [DirectorySettingsController::class, 'update']);

        Route::get('/changelog', [DirectoryChangelogController::class, 'index']);
        Route::post('/changelog', [DirectoryChangelogController::class, 'store']);
        Route::put('/changelog/{changelog}', [DirectoryChangelogController::class, 'update']);
        Route::delete('/changelog/{changelog}', [DirectoryChangelogController::class, 'destroy']);

        Route::get('/announcements', [DirectoryAnnouncementController::class, 'index']);
        Route::post('/announcements', [DirectoryAnnouncementController::class, 'store']);
        Route::post('/announcements/{announcement}', [DirectoryAnnouncementController::class, 'update']);
        Route::delete('/announcements/{announcement}', [DirectoryAnnouncementController::class, 'destroy']);

        Route::get('/reviews', [BusinessReviewController::class, 'adminIndex']);
        Route::put('/reviews/{review}/moderate', [BusinessReviewController::class, 'moderate']);
    });
});

// Admin web pages
Route::middleware(['auth', 'verified', 'role:super_admin,branch_pastor,directory_admin'])
    ->prefix('admin/biz')
    ->name('admin.biz.')
    ->group(function () {
        Route::get('/', fn () => view('admin.directory.index'))->name('index');
        Route::get('/businesses', fn () => view('admin.directory.businesses'))->name('businesses');
        Route::get('/categories', fn () => view('admin.directory.categories'))->name('categories');
        Route::get('/reviews', fn () => view('admin.directory.reviews'))->name('reviews');
        Route::get('/settings', fn () => view('admin.directory.settings'))->name('settings');
        Route::get('/changelog', fn () => view('admin.directory.changelog'))->name('changelog');
    });

<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CommunicationLogController;
use App\Http\Controllers\CommunicationPerformanceController;
use App\Http\Controllers\CommunicationSettingController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmailCampaignController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\MassCommunicationController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\MinistryController;
use App\Http\Controllers\ProjectionController;
use App\Http\Controllers\QuickSendController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\SmallGroupController;
use App\Http\Controllers\SmallGroupMeetingReportController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected authentication routes
Route::middleware(['auth:sanctum,web'])->group(function () {
    // User information and authentication management
    Route::prefix('auth')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/tokens', [AuthController::class, 'tokens']);
        Route::delete('/tokens/{token_id}', [AuthController::class, 'revokeToken']);
    });

    // Two-Factor Authentication API routes
    Route::prefix('two-factor')->name('api.two-factor.')->group(function () {
        Route::post('/verify', [TwoFactorController::class, 'verify']);
    });

    // User API route
    Route::get('/user', function () {
        return new \App\Http\Resources\UserResource(auth()->user()->load('roles'));
    });

    // Church management API routes

    // Branch management routes
    Route::prefix('branches')->group(function () {
        Route::get('/', [BranchController::class, 'index']);
        Route::post('/', [BranchController::class, 'store']);
        Route::get('/pastors/available', [BranchController::class, 'getAvailablePastors']);
        Route::get('/{branch}', [BranchController::class, 'show']);
        Route::put('/{branch}', [BranchController::class, 'update']);
        Route::delete('/{branch}', [BranchController::class, 'destroy']);

        // Pastor management for branches
        Route::post('/{branch}/assign-pastor', [BranchController::class, 'assignPastor']);
        Route::delete('/{branch}/remove-pastor', [BranchController::class, 'removePastor']);
    });

    // Ministry management routes
    Route::prefix('ministries')->group(function () {
        Route::get('/', [MinistryController::class, 'index']);
        Route::post('/', [MinistryController::class, 'store']);
        Route::get('/leaders/available', [MinistryController::class, 'getAvailableLeaders']);
        Route::get('/{ministry}', [MinistryController::class, 'show']);
        Route::put('/{ministry}', [MinistryController::class, 'update']);
        Route::delete('/{ministry}', [MinistryController::class, 'destroy']);

        // Leader management
        Route::post('/{ministry}/assign-leader', [MinistryController::class, 'assignLeader']);
        Route::delete('/{ministry}/remove-leader', [MinistryController::class, 'removeLeader']);
    });

    // Department management routes
    Route::prefix('departments')->group(function () {
        Route::get('/', [DepartmentController::class, 'index']);
        Route::post('/', [DepartmentController::class, 'store']);
        Route::get('/leaders/available', [DepartmentController::class, 'getAvailableLeaders']);
        Route::get('/{department}', [DepartmentController::class, 'show']);
        Route::put('/{department}', [DepartmentController::class, 'update']);
        Route::delete('/{department}', [DepartmentController::class, 'destroy']);

        // Leader management
        Route::post('/{department}/assign-leader', [DepartmentController::class, 'assignLeader']);
        Route::delete('/{department}/remove-leader', [DepartmentController::class, 'removeLeader']);

        // Member management
        Route::post('/{department}/assign-members', [DepartmentController::class, 'assignMembers']);
        Route::delete('/{department}/remove-members', [DepartmentController::class, 'removeMembers']);
    });

    // Member management routes
    Route::prefix('members')->group(function () {
        Route::get('/', [MemberController::class, 'index']);
        Route::get('/statistics', [MemberController::class, 'statistics']);
        Route::post('/', [MemberController::class, 'store']);
        Route::get('/{member}', [MemberController::class, 'show']);
        Route::put('/{member}', [MemberController::class, 'update']);
        Route::delete('/{member}', [MemberController::class, 'destroy']);

        // Member assignment routes
        Route::post('/{member}/assign-departments', [MemberController::class, 'assignToDepartments']);
        Route::post('/{member}/assign-small-groups', [MemberController::class, 'assignToSmallGroups']);

        // Member progress tracking
        Route::put('/{member}/growth-level', [MemberController::class, 'updateGrowthLevel']);
        Route::put('/{member}/teci-progress', [MemberController::class, 'updateTeciProgress']);

        // Member status management
        Route::put('/{member}/change-status', [MemberController::class, 'changeStatus']);
        Route::get('/{member}/status-history', [MemberController::class, 'getStatusHistory']);
        Route::get('/status/statistics', [MemberController::class, 'getStatusStatistics']);
        Route::post('/bulk-update-status', [MemberController::class, 'bulkUpdateStatus']);
    });

    // Small Groups Management
    Route::prefix('small-groups')->group(function () {
        Route::get('/', [SmallGroupController::class, 'index']);
        Route::post('/', [SmallGroupController::class, 'store']);
        Route::get('/statistics', [SmallGroupController::class, 'getStatistics']);
        Route::get('/reports/detailed', [SmallGroupController::class, 'getDetailedReports']);
        Route::get('/reports/engagement', [SmallGroupController::class, 'getEngagementReports']);
        Route::get('/reports/branch-comparison', [SmallGroupController::class, 'getBranchComparisonReports']);
        Route::get('/leaders/available', [SmallGroupController::class, 'getAvailableLeaders']);
        Route::get('/{smallGroup}', [SmallGroupController::class, 'show']);
        Route::put('/{smallGroup}', [SmallGroupController::class, 'update']);
        Route::delete('/{smallGroup}', [SmallGroupController::class, 'destroy']);

        // Member management
        Route::post('/{smallGroup}/assign-members', [SmallGroupController::class, 'assignMembers']);
        Route::delete('/{smallGroup}/remove-members', [SmallGroupController::class, 'removeMembers']);
        Route::get('/{smallGroup}/available-members', [SmallGroupController::class, 'getAvailableMembers']);

        // Leader management
        Route::put('/{smallGroup}/change-leader', [SmallGroupController::class, 'changeLeader']);
    });

    // Small Group Meeting Reports
    Route::prefix('small-group-reports')->group(function () {
        Route::get('/', [SmallGroupMeetingReportController::class, 'index']);
        Route::post('/', [SmallGroupMeetingReportController::class, 'store']);
        Route::get('/my-groups', [SmallGroupMeetingReportController::class, 'getMySmallGroups']);
        Route::get('/{report}', [SmallGroupMeetingReportController::class, 'show']);
        Route::put('/{report}', [SmallGroupMeetingReportController::class, 'update']);
        Route::delete('/{report}', [SmallGroupMeetingReportController::class, 'destroy']);

        // Report approval workflow
        Route::post('/{report}/approve', [SmallGroupMeetingReportController::class, 'approve']);
        Route::post('/{report}/reject', [SmallGroupMeetingReportController::class, 'reject']);

        // Analytics and statistics
        Route::get('/statistics', [SmallGroupMeetingReportController::class, 'getAttendanceStatistics']);
        Route::get('/trends', [SmallGroupMeetingReportController::class, 'getTrends']);
        Route::get('/comparison', [SmallGroupMeetingReportController::class, 'getComparison']);
        Route::get('/analytics/statistics', [SmallGroupMeetingReportController::class, 'getAttendanceStatistics']);
        Route::get('/analytics/compare', [SmallGroupMeetingReportController::class, 'compareAttendance']);
    });

    // Projection management routes
    Route::prefix('projections')->group(function () {
        Route::get('/', [ProjectionController::class, 'index']);
        Route::post('/', [ProjectionController::class, 'store']);
        Route::get('/branches/available', [ProjectionController::class, 'getAvailableBranches']);
        Route::get('/statistics', [ProjectionController::class, 'statistics']);
        Route::get('/comparison', [ProjectionController::class, 'comparison']);
        Route::get('/{projection}', [ProjectionController::class, 'show']);
        Route::put('/{projection}', [ProjectionController::class, 'update']);
        Route::delete('/{projection}', [ProjectionController::class, 'destroy']);

        // Status management routes
        Route::post('/{projection}/submit-for-review', [ProjectionController::class, 'submitForReview']);
        Route::post('/{projection}/approve', [ProjectionController::class, 'approve']);
        Route::post('/{projection}/reject', [ProjectionController::class, 'reject']);
        Route::post('/{projection}/set-current-year', [ProjectionController::class, 'setCurrentYear']);
    });

    // Import/Export routes
    Route::prefix('import-export')->group(function () {
        // Dashboard and statistics
        Route::get('/', [ImportExportController::class, 'index']);
        Route::get('stats', [ImportExportController::class, 'getImportExportStats']);
        Route::delete('cleanup-exports', [ImportExportController::class, 'cleanupOldExports']);

        // Member import/export
        Route::post('members/import', [ImportExportController::class, 'importMembers']);
        Route::post('members/export', [ImportExportController::class, 'exportMembers']);
        Route::get('members/import-template', [ImportExportController::class, 'getMemberImportTemplate']);

        // Ministry import/export
        Route::post('ministries/import', [ImportExportController::class, 'importMinistries']);
        Route::get('ministries/import-template', [ImportExportController::class, 'getMinistryImportTemplate']);

        // Department import/export
        Route::post('departments/import', [ImportExportController::class, 'importDepartments']);
        Route::get('departments/import-template', [ImportExportController::class, 'getDepartmentImportTemplate']);

        // Small Group import/export
        Route::post('small-groups/import', [ImportExportController::class, 'importSmallGroups']);
        Route::get('small-groups/import-template', [ImportExportController::class, 'getSmallGroupImportTemplate']);

        // Event Reports import/export
        Route::post('event-reports/import', [ImportExportController::class, 'importEventReports']);
        Route::get('event-reports/import-template', [ImportExportController::class, 'getEventReportsImportTemplate']);

        // Entity exports
        Route::post('branches/export', [ImportExportController::class, 'exportBranches']);
        Route::post('ministries/export', [ImportExportController::class, 'exportMinistries']);
        Route::post('departments/export', [ImportExportController::class, 'exportDepartments']);
        Route::post('small-groups/export', [ImportExportController::class, 'exportSmallGroups']);
        Route::post('events/export', [ImportExportController::class, 'exportEvents']);
        Route::post('projections/export', [ImportExportController::class, 'exportProjections']);

        // File validation
        Route::post('validate-file', [ImportExportController::class, 'validateImportFile']);

        // Entity imports
        Route::post('validate-import-file', [ImportExportController::class, 'validateImportFile']);
    });

    // Reporting and Analytics routes
    Route::prefix('reports')->group(function () {
        Route::get('/dashboard', [ReportingController::class, 'getDashboardStatistics']);
        Route::get('/trends', [ReportingController::class, 'getTrendData']);
        Route::get('/event-types', [ReportingController::class, 'getEventTypes']);
        Route::get('/event-reports', [ReportingController::class, 'getEventReports']);
        Route::post('/event-reports', [ReportingController::class, 'storeEventReport']);
        Route::get('/event-reports/{report}', [ReportingController::class, 'showEventReport']);
        Route::put('/event-reports/{report}', [ReportingController::class, 'updateEventReport']);
        Route::delete('/event-reports/{report}', [ReportingController::class, 'destroyEventReport']);
        Route::get('/comparative', [ReportingController::class, 'getComparativeStatistics']);
        Route::get('/monthly-insights', [ReportingController::class, 'getMonthlyInsights']);
        Route::get('/branch-comparison', [ReportingController::class, 'getBranchComparison']);
        Route::get('/global-ministry-monthly', [ReportingController::class, 'getGlobalMinistryMonthlyReport']);
        Route::get('/global-ministry-monthly/all-branches', [ReportingController::class, 'getAllBranchesGlobalMinistryReport']);
        Route::post('/export', [ReportingController::class, 'exportReports']);
    });

    // Communication API routes
    Route::prefix('communication')->group(function () {
        // Communication Settings
        Route::prefix('settings')->group(function () {
            Route::get('/', [CommunicationSettingController::class, 'index']);
            Route::post('/', [CommunicationSettingController::class, 'store']);
            Route::post('/test', [CommunicationSettingController::class, 'test']);
            Route::get('/provider-template', [CommunicationSettingController::class, 'getProviderTemplate']);
        });

        // Message Templates
        Route::prefix('templates')->group(function () {
            Route::get('/', [MessageTemplateController::class, 'index']);
            Route::post('/', [MessageTemplateController::class, 'store']);
            Route::get('/variables', [MessageTemplateController::class, 'getAvailableVariables']);
            Route::get('/{template}', [MessageTemplateController::class, 'show']);
            Route::put('/{template}', [MessageTemplateController::class, 'update']);
            Route::delete('/{template}', [MessageTemplateController::class, 'destroy']);
            Route::post('/{template}/preview', [MessageTemplateController::class, 'preview']);
            Route::post('/{template}/clone', [MessageTemplateController::class, 'clone']);
        });

        // Message Templates (alternative endpoint for frontend compatibility)
        Route::get('/message-templates', [MessageTemplateController::class, 'index']);
        Route::get('/message-templates/{id}', [MessageTemplateController::class, 'show']);

        // Email Campaigns
        Route::prefix('campaigns')->group(function () {
            Route::get('/', [EmailCampaignController::class, 'index']);
            Route::post('/', [EmailCampaignController::class, 'store']);
            Route::get('/{campaign}', [EmailCampaignController::class, 'show']);
            Route::put('/{campaign}', [EmailCampaignController::class, 'update']);
            Route::delete('/{campaign}', [EmailCampaignController::class, 'destroy']);
            Route::post('/{campaign}/trigger-user', [EmailCampaignController::class, 'triggerForUser']);
            Route::post('/{campaign}/stop-user', [EmailCampaignController::class, 'stopForUser']);
            Route::post('/{campaign}/preview-step', [EmailCampaignController::class, 'previewStep']);
            Route::post('/{campaign}/clone', [EmailCampaignController::class, 'clone']);
            Route::get('/{campaign}/enrollments', [EmailCampaignController::class, 'enrollments']);
        });

        // Communication Logs
        Route::prefix('logs')->group(function () {
            Route::get('/', [CommunicationLogController::class, 'index']);
            Route::get('/statistics', [CommunicationLogController::class, 'statistics']);
            Route::get('/trends', [CommunicationLogController::class, 'trends']);
            Route::get('/export', [CommunicationLogController::class, 'export']);
            Route::get('/{log}', [CommunicationLogController::class, 'show']);
        });

        // Quick Send
        Route::prefix('quick-send')->group(function () {
            Route::post('/send', [QuickSendController::class, 'send']);
            Route::post('/recipients', [QuickSendController::class, 'getRecipients']);
            Route::post('/preview', [QuickSendController::class, 'preview']);
        });

        // Mass Communication
        Route::prefix('mass-send')->group(function () {
            Route::get('/filters', [MassCommunicationController::class, 'getFilters']);
            Route::post('/recipients', [MassCommunicationController::class, 'getRecipients']);
            Route::post('/send', [MassCommunicationController::class, 'send']);
            Route::post('/preview', [MassCommunicationController::class, 'preview']);
        });
    });

    // Event Management routes
    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'index']);
        Route::post('/', [EventController::class, 'store']);
        Route::get('/statistics', [EventController::class, 'getStatistics']);
        Route::get('/service-types', [EventController::class, 'getServiceTypes']);
        Route::get('/branches', [EventController::class, 'getBranches']);
        Route::get('/my-registrations', [EventController::class, 'getMyRegistrations']);

        // Recurring event management
        Route::post('/generate-recurring-instances', [EventController::class, 'generateRecurringInstances']);

        Route::get('/{event}', [EventController::class, 'show']);
        Route::get('/{event}/details', [EventController::class, 'getEventDetails']);
        Route::put('/{event}', [EventController::class, 'update']);
        Route::delete('/{event}', [EventController::class, 'destroy']);

        // Event registration
        Route::post('/{event}/register', [EventController::class, 'register']);
        Route::get('/{event}/registrations', [EventController::class, 'getRegistrations']);
        Route::delete('/{event}/registrations/{registration}', [EventController::class, 'unregister']);
        Route::post('/{event}/registrations/{registration}/check-in', [EventController::class, 'checkIn']);

        // QR Code generation for tickets
        Route::get('/{event}/registrations/{registration}/qr-code', [EventController::class, 'generateQrCode']);
        Route::get('/{event}/registrations/{registration}/qr-code/download', [EventController::class, 'downloadQrCode']);
    });
});

// Communication performance monitoring
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('communication-performance')->group(function () {
        Route::get('/metrics', [CommunicationPerformanceController::class, 'metrics']);
        Route::get('/daily-volume', [CommunicationPerformanceController::class, 'dailyVolume']);
        Route::get('/queue-metrics', [CommunicationPerformanceController::class, 'queueMetrics']);
        Route::get('/recent-failures', [CommunicationPerformanceController::class, 'recentFailures']);
        Route::get('/summary', [CommunicationPerformanceController::class, 'summary']);
    });
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0'),
    ]);
});

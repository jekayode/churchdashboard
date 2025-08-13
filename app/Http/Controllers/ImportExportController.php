<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ImportExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final class ImportExportController extends Controller
{
    private ImportExportService $importExportService;

    public function __construct(ImportExportService $importExportService)
    {
        $this->importExportService = $importExportService;
    }

    /**
     * Display import/export dashboard.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', \App\Models\Member::class);

        $user = Auth::user();
        $branchId = $this->getBranchIdForUser($user);

        $availableExports = $this->importExportService->getAvailableExports();

        return response()->json([
            'message' => 'Import/Export dashboard',
            'data' => [
                'available_exports' => $availableExports,
                'user_branch_id' => $branchId,
                'permissions' => [
                    'can_import_members' => Gate::allows('create', \App\Models\Member::class),
                    'can_export_members' => Gate::allows('viewAny', \App\Models\Member::class),
                    'can_export_ministries' => Gate::allows('viewAny', \App\Models\Ministry::class),
                    'can_export_departments' => Gate::allows('viewAny', \App\Models\Department::class),
                    'can_export_small_groups' => Gate::allows('viewAny', \App\Models\SmallGroup::class),
                    'can_export_events' => Gate::allows('viewAny', \App\Models\Event::class),
                    'can_export_projections' => Gate::allows('viewAny', \App\Models\Projection::class),
                ],
            ],
        ]);
    }

    /**
     * Import members from uploaded file.
     */
    public function importMembers(Request $request): JsonResponse
    {
        try {
            // Authorization check
            $user = Auth::user();
            if (!$user->isSuperAdmin() && !$user->isBranchPastor()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to import members'
                ], 403);
            }

            // Validate request
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
                'branch_id' => 'nullable|integer|exists:branches,id'
            ]);

            // Determine branch ID
            $branchId = $this->getBranchId($request);

            // Perform import
            $result = $this->importExportService->importMembers(
                $request->file('file'),
                $branchId
            );

            $statusCode = $result['success'] ? 200 : 422;

            return response()->json($result, $statusCode);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Import members API error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during import',
                'errors' => ['system' => 'Import operation failed']
            ], 500);
        }
    }

    /**
     * Export members to Excel file.
     */
    public function exportMembers(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            // Authorization check
            $user = Auth::user();
            if (!$user->isSuperAdmin() && !$user->isBranchPastor()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to export members'
                ], 403);
            }

            // Validate request
            $request->validate([
                'branch_id' => 'nullable|integer|exists:branches,id',
                'member_status' => 'nullable|string|in:visitor,member,volunteer,leader,minister',
                'growth_level' => 'nullable|string|in:100_level,200_level,300_level,400_level',
                'teci_status' => 'nullable|string|in:not_started,in_progress,completed',
                'gender' => 'nullable|string|in:male,female',
                'marital_status' => 'nullable|string|in:single,married,divorced,separated,widowed,in_a_relationship,engaged',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from'
            ]);

            // Determine branch ID and filters
            $branchId = $this->getBranchId($request);
            $filters = $request->only([
                'member_status', 
                'growth_level', 
                'teci_status', 
                'gender', 
                'marital_status',
                'date_from',
                'date_to'
            ]);

            // Remove empty filters
            $filters = array_filter($filters, function ($value) {
                return !is_null($value) && $value !== '';
            });

            // Perform export
            $result = $this->importExportService->exportMembers($branchId, $filters);

            if (!$result['success']) {
                return response()->json($result, 500);
            }

            // Return file download
            $fullPath = storage_path('app/public/' . $result['file_path']);
            
            if (!file_exists($fullPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Export file not found'
                ], 404);
            }

            return response()->download($fullPath)->deleteFileAfterSend();

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Export members API error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during export',
                'errors' => ['system' => 'Export operation failed']
            ], 500);
        }
    }

    /**
     * Import ministries from uploaded file.
     */
    public function importMinistries(Request $request): JsonResponse
    {
        try {
            // Authorization check
            $user = Auth::user();
            if (!$user->isSuperAdmin() && !$user->isBranchPastor()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to import ministries'
                ], 403);
            }

            // Validate request
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
                'branch_id' => 'nullable|integer|exists:branches,id'
            ]);

            // Determine branch ID
            $branchId = $this->getBranchId($request);

            // Perform import
            $result = $this->importExportService->importMinistries(
                $request->file('file'),
                $branchId
            );

            $statusCode = $result['success'] ? 200 : 422;

            return response()->json($result, $statusCode);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Import ministries API error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during import',
                'errors' => ['system' => 'Import operation failed']
            ], 500);
        }
    }

    /**
     * Import departments from uploaded file.
     */
    public function importDepartments(Request $request): JsonResponse
    {
        try {
            // Authorization check
            $user = Auth::user();
            if (!$user->isSuperAdmin() && !$user->isBranchPastor()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to import departments'
                ], 403);
            }

            // Validate request
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
                'branch_id' => 'nullable|integer|exists:branches,id'
            ]);

            // Determine branch ID
            $branchId = $this->getBranchId($request);

            // Perform import
            $result = $this->importExportService->importDepartments(
                $request->file('file'),
                $branchId
            );

            $statusCode = $result['success'] ? 200 : 422;

            return response()->json($result, $statusCode);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Import departments API error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during import',
                'errors' => ['system' => 'Import operation failed']
            ], 500);
        }
    }

    /**
     * Import event reports from uploaded file.
     */
    public function importEventReports(Request $request): JsonResponse
    {
        try {
            // Authorization check
            $user = Auth::user();
            if (!$user->isSuperAdmin() && !$user->isBranchPastor()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to import event reports'
                ], 403);
            }

            // Validate request
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
                'branch_id' => 'nullable|integer|exists:branches,id'
            ]);

            // Determine branch ID
            $branchId = $this->getBranchId($request);

            // Perform import
            $result = $this->importExportService->importEventReports(
                $request->file('file'),
                $branchId
            );

            $statusCode = $result['success'] ? 200 : 422;

            return response()->json($result, $statusCode);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Import event reports API error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during import',
                'errors' => ['system' => 'Import operation failed']
            ], 500);
        }
    }

    /**
     * Import small groups from uploaded file.
     */
    public function importSmallGroups(Request $request): JsonResponse
    {
        try {
            // Authorization check
            $user = Auth::user();
            if (!$user->isSuperAdmin() && !$user->isBranchPastor()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to import small groups'
                ], 403);
            }

            // Validate request
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
                'branch_id' => 'nullable|integer|exists:branches,id'
            ]);

            // Determine branch ID
            $branchId = $this->getBranchId($request);

            // Perform import
            $result = $this->importExportService->importSmallGroups(
                $request->file('file'),
                $branchId
            );

            $statusCode = $result['success'] ? 200 : 422;

            return response()->json($result, $statusCode);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Import small groups API error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during import',
                'errors' => ['system' => 'Import operation failed']
            ], 500);
        }
    }

    /**
     * Export ministries to Excel file.
     */
    public function exportMinistries(Request $request): BinaryFileResponse
    {
        Gate::authorize('viewAny', \App\Models\Ministry::class);

        $request->validate([
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);

        $branchId = $this->getBranchId($request);

        $filePath = $this->importExportService->exportMinistries($branchId);

        return response()->download($filePath)->deleteFileAfterSend();
    }

    /**
     * Export departments to Excel file.
     */
    public function exportDepartments(Request $request): BinaryFileResponse
    {
        Gate::authorize('viewAny', \App\Models\Department::class);

        $request->validate([
            'branch_id' => 'nullable|integer|exists:branches,id',
            'ministry_id' => 'nullable|integer|exists:ministries,id',
        ]);

        $branchId = $this->getBranchId($request);

        $filePath = $this->importExportService->exportDepartments($branchId);

        return response()->download($filePath)->deleteFileAfterSend();
    }

    /**
     * Export small groups to Excel file.
     */
    public function exportSmallGroups(Request $request): BinaryFileResponse
    {
        Gate::authorize('viewAny', \App\Models\SmallGroup::class);

        $request->validate([
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);

        $branchId = $this->getBranchId($request);

        $filePath = $this->importExportService->exportSmallGroups($branchId);

        return response()->download($filePath)->deleteFileAfterSend();
    }

    /**
     * Export events to Excel file.
     */
    public function exportEvents(Request $request): BinaryFileResponse
    {
        Gate::authorize('viewAny', \App\Models\Event::class);

        $request->validate([
            'branch_id' => 'nullable|integer|exists:branches,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'event_type' => 'nullable|string',
        ]);

        $branchId = $request->input('branch_id');
        $filters = $request->only(['start_date', 'end_date', 'event_type']);

        if (!$branchId) {
            $user = Auth::user();
            $branchId = $this->getBranchIdForUser($user);
        }

        $filePath = $this->importExportService->exportEvents($branchId, $filters);

        return response()->download($filePath)->deleteFileAfterSend();
    }

    /**
     * Export projections to Excel file.
     */
    public function exportProjections(Request $request): BinaryFileResponse
    {
        Gate::authorize('viewAny', \App\Models\Projection::class);

        $request->validate([
            'branch_id' => 'nullable|integer|exists:branches,id',
            'year' => 'nullable|integer|min:2020|max:2050',
        ]);

        $branchId = $request->input('branch_id');
        $year = $request->input('year');

        if (!$branchId) {
            $user = Auth::user();
            $branchId = $this->getBranchIdForUser($user);
        }

        $filePath = $this->importExportService->exportProjections($branchId, $year);

        return response()->download($filePath)->deleteFileAfterSend();
    }

    /**
     * Export branches to Excel file (Super Admin only).
     */
    public function exportBranches(Request $request): BinaryFileResponse
    {
        Gate::authorize('viewAny', \App\Models\Branch::class);

        $filePath = $this->importExportService->exportBranches();

        return response()->download($filePath)->deleteFileAfterSend();
    }

    /**
     * Get import template for members.
     */
    public function getMemberImportTemplate(): BinaryFileResponse|JsonResponse
    {
        try {
            // Authorization check
            $user = Auth::user();
            if (!$user->isSuperAdmin() && !$user->isBranchPastor()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to access import template'
                ], 403);
            }

            $result = $this->importExportService->getMemberImportTemplate();

            if (!$result['success']) {
                return response()->json($result, 500);
            }

            // Get the file path from the service result
            $filePath = storage_path('app/public/' . $result['file_path']);
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template file not found'
                ], 404);
            }

            return response()->download($filePath, $result['filename'])->deleteFileAfterSend();

        } catch (\Exception $e) {
            Log::error('Get import template API error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating template'
            ], 500);
        }
    }

    /**
     * Get import template for ministries.
     */
    public function getMinistryImportTemplate(): BinaryFileResponse|JsonResponse
    {
        try {
            // Authorization check
            $user = Auth::user();
            if (!$user->isSuperAdmin() && !$user->isBranchPastor()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to access import template'
                ], 403);
            }

            $result = $this->importExportService->getMinistryImportTemplate();

            if (!$result['success']) {
                return response()->json($result, 500);
            }

            // Get the file path from the service result
            $filePath = storage_path('app/public/' . $result['file_path']);
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template file not found'
                ], 404);
            }

            return response()->download($filePath, $result['filename'])->deleteFileAfterSend();

        } catch (\Exception $e) {
            Log::error('Get ministry import template API error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating template'
            ], 500);
        }
    }

    /**
     * Get import template for departments.
     */
    public function getDepartmentImportTemplate(): BinaryFileResponse|JsonResponse
    {
        try {
            // Authorization check
            $user = Auth::user();
            if (!$user->isSuperAdmin() && !$user->isBranchPastor()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to access import template'
                ], 403);
            }

            $result = $this->importExportService->getDepartmentImportTemplate();

            if (!$result['success']) {
                return response()->json($result, 500);
            }

            // Get the file path from the service result
            $filePath = storage_path('app/public/' . $result['file_path']);
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template file not found'
                ], 404);
            }

            return response()->download($filePath, $result['filename'])->deleteFileAfterSend();

        } catch (\Exception $e) {
            Log::error('Get department import template API error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating template'
            ], 500);
        }
    }

    /**
     * Get import template for event reports.
     */
    public function getEventReportsImportTemplate(): BinaryFileResponse|JsonResponse
    {
        try {
            // Authorization check
            $user = Auth::user();
            if (!$user->isSuperAdmin() && !$user->isBranchPastor()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to access import template'
                ], 403);
            }

            $result = $this->importExportService->getEventReportsImportTemplate();

            if (!$result['success']) {
                return response()->json($result, 500);
            }

            // Get the file path from the service result
            $filePath = storage_path('app/public/' . $result['file_path']);
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template file not found'
                ], 404);
            }

            return response()->download($filePath, $result['filename'])->deleteFileAfterSend();

        } catch (\Exception $e) {
            Log::error('Get event reports import template API error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating template'
            ], 500);
        }
    }

    /**
     * Get import template for small groups.
     */
    public function getSmallGroupImportTemplate(): BinaryFileResponse|JsonResponse
    {
        try {
            // Authorization check
            $user = Auth::user();
            if (!$user->isSuperAdmin() && !$user->isBranchPastor()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to access import template'
                ], 403);
            }

            $result = $this->importExportService->getSmallGroupImportTemplate();

            if (!$result['success']) {
                return response()->json($result, 500);
            }

            // Get the file path from the service result
            $filePath = storage_path('app/public/' . $result['file_path']);
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template file not found'
                ], 404);
            }

            return response()->download($filePath, $result['filename'])->deleteFileAfterSend();

        } catch (\Exception $e) {
            Log::error('Get small group import template API error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating template'
            ], 500);
        }
    }

    /**
     * Validate import file without processing.
     */
    public function validateImportFile(Request $request): JsonResponse
    {
        try {
            // Authorization check
            $user = Auth::user();
            if (!$user->isSuperAdmin() && !$user->isBranchPastor()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to validate import files'
                ], 403);
            }

            // Validate request
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240' // 10MB max
            ]);

            $result = $this->importExportService->validateImportFile($request->file('file'));

            return response()->json($result, 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Validate import file API error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'valid' => false,
                'errors' => ['system' => 'File validation failed']
            ], 500);
        }
    }

    /**
     * Get import/export history and statistics.
     */
    public function getImportExportStats(): JsonResponse
    {
        try {
            // Authorization check
            $user = auth()->user();
            if (!$user->isSuperAdmin() && !$user->isBranchPastor()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view import/export statistics'
                ], 403);
            }

            $isSuperAdmin = $user->isSuperAdmin();

            // Get member statistics
            $memberQuery = \App\Models\Member::query();
            
            if (!$isSuperAdmin && $user->isBranchPastor()) {
                $branchId = $user->getActiveBranchId();
                $memberQuery->where('branch_id', $branchId);
            }

            $totalMembers = $memberQuery->count();
            $recentMembers = $memberQuery->where('created_at', '>=', now()->subDays(30))->count();

            $membersByStatus = $memberQuery
                ->selectRaw('member_status, COUNT(*) as count')
                ->groupBy('member_status')
                ->pluck('count', 'member_status')
                ->toArray();

            $membersByGrowthLevel = $memberQuery
                ->selectRaw('growth_level, COUNT(*) as count')
                ->groupBy('growth_level')
                ->pluck('count', 'growth_level')
                ->toArray();

            // Get recent export files (if any)
            $recentExports = collect(\Illuminate\Support\Facades\Storage::disk('public')->files('exports'))
                ->filter(fn($file) => \Illuminate\Support\Facades\Storage::disk('public')->lastModified($file) >= now()->subDays(7)->timestamp)
                ->map(function ($file) {
                    return [
                        'filename' => basename($file),
                        'size' => \Illuminate\Support\Facades\Storage::disk('public')->size($file),
                        'created_at' => date('Y-m-d H:i:s', \Illuminate\Support\Facades\Storage::disk('public')->lastModified($file)),
                        'download_url' => \Illuminate\Support\Facades\Storage::disk('public')->url($file)
                    ];
                })
                ->sortByDesc('created_at')
                ->take(5)
                ->values()
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => [
                    'members' => [
                        'total' => $totalMembers,
                        'recent' => $recentMembers,
                        'by_status' => $membersByStatus,
                        'by_growth_level' => $membersByGrowthLevel
                    ],
                    'exports' => [
                        'recent_files' => $recentExports,
                        'total_recent' => count($recentExports)
                    ],
                    'user_context' => [
                        'is_super_admin' => $isSuperAdmin,
                        'branch_id' => !$isSuperAdmin && $user->isBranchPastor() ? $user->getActiveBranchId() : null,
                        'can_import' => true,
                        'can_export' => true
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get import/export stats API error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching statistics',
                'errors' => ['system' => 'Statistics retrieval failed']
            ], 500);
        }
    }

    /**
     * Clean up old export files.
     */
    public function cleanupOldExports(Request $request): JsonResponse
    {
        try {
            // Only super admins can cleanup exports
            $user = auth()->user();
            if (!$user->isSuperAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to cleanup export files'
                ], 403);
            }

            $request->validate([
                'days_old' => 'nullable|integer|min:1|max:365'
            ]);

            $daysOld = $request->input('days_old', 7);

            $result = $this->importExportService->cleanupOldExports($daysOld);

            $statusCode = $result['success'] ? 200 : 500;

            return response()->json($result, $statusCode);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Cleanup exports API error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during cleanup',
                'errors' => ['system' => 'Cleanup operation failed']
            ], 500);
        }
    }

    /**
     * Determine the branch ID based on user role and request.
     */
    private function getBranchId(Request $request): int
    {
        $user = auth()->user();
        
        // Super admins can specify branch_id or work with all branches
        if ($user->isSuperAdmin()) {
            return $request->input('branch_id') ?? 0; // 0 means all branches for super admin
        }

        // Branch pastors can only work with their own branch
        if ($user->isBranchPastor()) {
            $branchId = $user->getActiveBranchId();
            if (!$branchId) {
                throw new \Exception('Branch pastor must be assigned to a branch');
            }
            return $branchId;
        }

        throw new \Exception('User role not authorized for this operation');
    }

    /**
     * Get appropriate branch ID for user based on their role.
     */
    private function getBranchIdForUser($user): ?int
    {
        // Super admins can access all branches
        if ($user->isSuperAdmin()) {
            return null;
        }

        // Other users are restricted to their branch
        return $user->member?->branch_id;
    }
} 
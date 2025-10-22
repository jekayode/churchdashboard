<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProjectionRequest;
use App\Models\Branch;
use App\Models\Projection;
use App\Services\ProjectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class ProjectionController extends Controller
{
    public function __construct(private ProjectionService $projectionService) {}

    /**
     * Display a listing of projections with filtering and search capabilities.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Start with base query
        $query = Projection::with(['branch', 'creator']);

        // Apply branch-based access control
        if (! Gate::allows('viewAny', Projection::class)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view projections.',
            ], Response::HTTP_FORBIDDEN);
        }

        // Super admins can see all projections, branch pastors only their branch
        if (! $user->hasRole('super_admin')) {
            $userBranches = $user->pastoredBranches()->pluck('id');
            $query->whereIn('branch_id', $userBranches);
        }

        // Apply filters
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('is_global')) {
            $query->where('is_global', $request->boolean('is_global'));
        }

        if ($request->filled('year')) {
            $query->where('year', $request->integer('year'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('current_year')) {
            $query->currentYearDesignated();
        }

        if ($request->filled('is_current_year')) {
            $query->where('is_current_year', $request->boolean('is_current_year'));
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'year');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $perPage = min($request->integer('per_page', 15), 100);
        $projections = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $projections,
            'message' => 'Projections retrieved successfully.',
        ]);
    }

    /**
     * Bulk update Q1..Q4 values for a projection using provided weights or explicit values.
     */
    public function updateQuarters(Request $request, Projection $projection): JsonResponse
    {
        $this->authorize('update', $projection);

        $data = $request->validate([
            'weights' => 'array|size:4',
            'weights.*' => 'integer|min:0',
            'quarters.attendance' => 'array|size:4',
            'quarters.converts' => 'array|size:4',
            'quarters.leaders' => 'array|size:4',
            'quarters.volunteers' => 'array|size:4',
        ]);

        if (isset($data['weights'])) {
            $projection = $this->projectionService->fillProjectionQuarters($projection, $data['weights']);
        }

        if (isset($data['quarters'])) {
            $q = $data['quarters'];
            $projection->quarterly_attendance = $q['attendance'] ?? $projection->quarterly_attendance;
            $projection->quarterly_converts = $q['converts'] ?? $projection->quarterly_converts;
            $projection->quarterly_leaders = $q['leaders'] ?? $projection->quarterly_leaders;
            $projection->quarterly_volunteers = $q['volunteers'] ?? $projection->quarterly_volunteers;
        }

        $projection->save();

        return response()->json([
            'success' => true,
            'message' => 'Projection quarters updated',
            'data' => $projection->only([
                'quarterly_attendance', 'quarterly_converts', 'quarterly_leaders', 'quarterly_volunteers',
            ]),
        ]);
    }

    /**
     * Store a newly created projection.
     */
    public function store(ProjectionRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        // Check authorization
        if (! Gate::allows('create', Projection::class)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create projections.',
            ], Response::HTTP_FORBIDDEN);
        }

        // Verify branch access for non-super admins
        $user = $request->user();
        if (! $user->hasRole('super_admin') && isset($validatedData['branch_id'])) {
            $userBranches = $user->pastoredBranches()->pluck('id');
            if (! $userBranches->contains($validatedData['branch_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to create projections for this branch.',
                ], Response::HTTP_FORBIDDEN);
            }
        }

        try {
            // Add creator information
            $validatedData['created_by'] = $user->id;

            // Handle quarterly data if provided
            if (isset($validatedData['quarters'])) {
                $quarters = $validatedData['quarters'];
                $validatedData['quarterly_attendance'] = $quarters['attendance'] ?? [];
                $validatedData['quarterly_converts'] = $quarters['converts'] ?? [];
                $validatedData['quarterly_leaders'] = $quarters['leaders'] ?? [];
                $validatedData['quarterly_volunteers'] = $quarters['volunteers'] ?? [];
                unset($validatedData['quarters']); // Remove from validated data
            }

            // Create the projection
            $projection = Projection::create($validatedData);

            // Load relationships for response
            $projection->load(['branch', 'creator']);

            // Log the action
            Log::info('Projection created', [
                'projection_id' => $projection->id,
                'branch_id' => $projection->branch_id,
                'year' => $projection->year,
                'created_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $projection,
                'message' => 'Projection created successfully.',
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            Log::error('Failed to create projection', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'branch_id' => $validatedData['branch_id'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create projection. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified projection.
     */
    public function show(Projection $projection): JsonResponse
    {
        if (! Gate::allows('view', $projection)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this projection.',
            ], Response::HTTP_FORBIDDEN);
        }

        $projection->load(['branch', 'creator']);

        return response()->json([
            'success' => true,
            'data' => $projection,
            'message' => 'Projection retrieved successfully.',
        ]);
    }

    /**
     * Update the specified projection.
     */
    public function update(ProjectionRequest $request, Projection $projection): JsonResponse
    {
        if (! Gate::allows('update', $projection)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this projection.',
            ], Response::HTTP_FORBIDDEN);
        }

        $validatedData = $request->validated();
        $user = $request->user();

        // Verify branch access for non-super admins when changing branch
        if (! $user->hasRole('super_admin') && isset($validatedData['branch_id'])) {
            $userBranches = $user->pastoredBranches()->pluck('id');
            if (! $userBranches->contains($validatedData['branch_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to assign projections to this branch.',
                ], Response::HTTP_FORBIDDEN);
            }
        }

        try {
            // Handle quarterly data if provided
            if (isset($validatedData['quarters'])) {
                $quarters = $validatedData['quarters'];
                $validatedData['quarterly_attendance'] = $quarters['attendance'] ?? $projection->quarterly_attendance;
                $validatedData['quarterly_converts'] = $quarters['converts'] ?? $projection->quarterly_converts;
                $validatedData['quarterly_leaders'] = $quarters['leaders'] ?? $projection->quarterly_leaders;
                $validatedData['quarterly_volunteers'] = $quarters['volunteers'] ?? $projection->quarterly_volunteers;
                unset($validatedData['quarters']); // Remove from validated data
            }

            $projection->update($validatedData);
            $projection->load(['branch', 'creator']);

            Log::info('Projection updated', [
                'projection_id' => $projection->id,
                'updated_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $projection,
                'message' => 'Projection updated successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update projection', [
                'error' => $e->getMessage(),
                'projection_id' => $projection->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update projection. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified projection.
     */
    public function destroy(Projection $projection): JsonResponse
    {
        if (! Gate::allows('delete', $projection)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this projection.',
            ], Response::HTTP_FORBIDDEN);
        }

        $user = request()->user();

        try {
            $projectionId = $projection->id;
            $projection->delete();

            Log::info('Projection deleted', [
                'projection_id' => $projectionId,
                'deleted_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Projection deleted successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete projection', [
                'error' => $e->getMessage(),
                'projection_id' => $projection->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete projection. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Reopen an approved projection for editing.
     */
    public function reopen(Request $request, Projection $projection): JsonResponse
    {
        $user = $request->user();

        // Check if user can view the projection
        if (! Gate::allows('view', $projection)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this projection.',
            ], Response::HTTP_FORBIDDEN);
        }

        // Only allow reopening approved projections
        if (! $projection->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Only approved projections can be reopened for editing.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Branch pastors can only reopen projections for their branches
        if ($user->hasRole('branch_pastor') && ! $user->hasRole('super_admin')) {
            $userBranches = $user->pastoredBranches()->pluck('id');
            if (! $userBranches->contains($projection->branch_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to reopen projections for this branch.',
                ], Response::HTTP_FORBIDDEN);
            }
        }

        try {
            // Change status back to draft
            $projection->update([
                'status' => 'draft',
                'approved_by' => null,
                'approved_at' => null,
                'approval_notes' => null,
            ]);

            Log::info('Projection reopened for editing', [
                'projection_id' => $projection->id,
                'reopened_by' => $user->id,
                'branch_id' => $projection->branch_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Projection reopened for editing successfully.',
                'data' => $projection->fresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to reopen projection', [
                'error' => $e->getMessage(),
                'projection_id' => $projection->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reopen projection. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get projection statistics and variance analysis.
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! Gate::allows('viewAny', Projection::class)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view projection statistics.',
            ], Response::HTTP_FORBIDDEN);
        }

        // Build query with branch access control
        $query = Projection::with('branch');

        if (! $user->hasRole('super_admin')) {
            $userBranches = $user->pastoredBranches()->pluck('id');
            $query->whereIn('branch_id', $userBranches);
        }

        // Apply filters
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('year')) {
            $query->where('year', $request->integer('year'));
        }
        // Note: For statistics, we want ALL projections, not just current year

        $projections = $query->get();

        // Calculate statistics
        $statistics = [
            'total' => $projections->count(),
            'approved' => $projections->where('status', 'approved')->count(),
            'pending' => $projections->whereIn('status', ['draft', 'in_review'])->count(),
            'branches_covered' => $projections->pluck('branch_id')->unique()->count(),
            'current_year_projections' => $projections->where('is_current_year', true)->count(),
            'total_attendance_target' => $projections->sum('attendance_target'),
            'total_converts_target' => $projections->sum('converts_target'),
            'total_leaders_target' => $projections->sum('leaders_target'),
            'total_volunteers_target' => $projections->sum('volunteers_target'),
            'avg_attendance_target' => $projections->avg('attendance_target'),
            'avg_converts_target' => $projections->avg('converts_target'),
            'avg_leaders_target' => $projections->avg('leaders_target'),
            'avg_volunteers_target' => $projections->avg('volunteers_target'),
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics,
            'message' => 'Projection statistics retrieved successfully.',
        ]);
    }

    /**
     * Get branches available for projection creation.
     */
    public function getAvailableBranches(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! Gate::allows('viewAny', Branch::class)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view branches.',
            ], Response::HTTP_FORBIDDEN);
        }

        $query = Branch::active()->select('id', 'name', 'venue', 'pastor_id');

        // Apply branch access control
        if (! $user->hasRole('super_admin')) {
            $userBranches = $user->pastoredBranches()->pluck('id');
            $query->whereIn('id', $userBranches);
        }

        $branches = $query->with('pastor:id,name,email')->get();

        return response()->json([
            'success' => true,
            'data' => $branches,
            'message' => 'Available branches retrieved successfully.',
        ]);
    }

    /**
     * Get comparison data between projection targets and actuals.
     * This is a placeholder for future implementation when actual data tracking is available.
     */
    public function comparison(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! Gate::allows('viewAny', Projection::class)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view projection comparisons.',
            ], Response::HTTP_FORBIDDEN);
        }

        // TODO: Implement actual vs projection comparison
        // This will require additional models for tracking actual performance data

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Comparison feature will be implemented when actual performance tracking is available.',
                'planned_features' => [
                    'attendance_variance',
                    'converts_variance',
                    'leaders_variance',
                    'volunteers_variance',
                    'quarterly_progress',
                    'monthly_progress',
                ],
            ],
            'message' => 'Projection comparison data (placeholder).',
        ]);
    }

    /**
     * Submit projection for review.
     */
    public function submitForReview(Projection $projection): JsonResponse
    {
        if (! Gate::allows('update', $projection)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to submit this projection for review.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (! $projection->isDraft()) {
            return response()->json([
                'success' => false,
                'message' => 'Only draft projections can be submitted for review.',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $success = $projection->submitForReview();

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to submit projection for review.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $projection->load(['branch', 'creator']);

            Log::info('Projection submitted for review', [
                'projection_id' => $projection->id,
                'submitted_by' => request()->user()->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $projection,
                'message' => 'Projection submitted for review successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to submit projection for review', [
                'error' => $e->getMessage(),
                'projection_id' => $projection->id,
                'user_id' => request()->user()->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit projection for review. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Approve a projection.
     */
    public function approve(Request $request, Projection $projection): JsonResponse
    {
        $user = $request->user();

        // Only super admins can approve projections
        if (! $user->hasRole('super_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only super administrators can approve projections.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (! $projection->isInReview()) {
            return response()->json([
                'success' => false,
                'message' => 'Only projections in review can be approved.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $success = $projection->approve($user, $request->input('approval_notes'));

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to approve projection.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $projection->load(['branch', 'creator', 'approver']);

            Log::info('Projection approved', [
                'projection_id' => $projection->id,
                'approved_by' => $user->id,
                'approval_notes' => $request->input('approval_notes'),
            ]);

            return response()->json([
                'success' => true,
                'data' => $projection,
                'message' => 'Projection approved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to approve projection', [
                'error' => $e->getMessage(),
                'projection_id' => $projection->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve projection. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Reject a projection.
     */
    public function reject(Request $request, Projection $projection): JsonResponse
    {
        $user = $request->user();

        // Only super admins can reject projections
        if (! $user->hasRole('super_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only super administrators can reject projections.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (! $projection->isInReview()) {
            return response()->json([
                'success' => false,
                'message' => 'Only projections in review can be rejected.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        try {
            $success = $projection->reject($user, $request->input('rejection_reason'));

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to reject projection.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $projection->load(['branch', 'creator', 'rejector']);

            Log::info('Projection rejected', [
                'projection_id' => $projection->id,
                'rejected_by' => $user->id,
                'rejection_reason' => $request->input('rejection_reason'),
            ]);

            return response()->json([
                'success' => true,
                'data' => $projection,
                'message' => 'Projection rejected successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to reject projection', [
                'error' => $e->getMessage(),
                'projection_id' => $projection->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject projection. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Set projection as current year.
     */
    public function setCurrentYear(Projection $projection): JsonResponse
    {
        $user = request()->user();

        // Only super admins can set current year projections
        if (! $user->hasRole('super_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only super administrators can set current year projections.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (! $projection->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Only approved projections can be set as current year.',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $success = $projection->setAsCurrentYear();

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to set projection as current year.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $projection->load(['branch', 'creator', 'approver']);

            Log::info('Projection set as current year', [
                'projection_id' => $projection->id,
                'branch_id' => $projection->branch_id,
                'is_global' => $projection->is_global,
                'set_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $projection,
                'message' => 'Projection set as current year successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to set projection as current year', [
                'error' => $e->getMessage(),
                'projection_id' => $projection->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to set projection as current year. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create or update global projection (super admin only).
     */
    public function storeGlobal(ProjectionRequest $request): JsonResponse
    {
        $user = $request->user();

        // Only super admins can create global projections
        if (! $user->hasRole('super_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only super administrators can create global projections.',
            ], Response::HTTP_FORBIDDEN);
        }

        $validatedData = $request->validated();

        // Force global flag and remove branch_id
        $validatedData['is_global'] = true;
        $validatedData['branch_id'] = null;
        $validatedData['created_by'] = $user->id;

        try {
            // Check if global projection already exists for this year
            $existingProjection = Projection::where('year', $validatedData['year'])
                ->where('is_global', true)
                ->first();

            if ($existingProjection) {
                // Update existing global projection
                $existingProjection->update($validatedData);
                $projection = $existingProjection;

                Log::info('Global projection updated', [
                    'projection_id' => $projection->id,
                    'year' => $projection->year,
                    'updated_by' => $user->id,
                ]);

                $message = 'Global projection updated successfully.';
            } else {
                // Create new global projection
                $projection = Projection::create($validatedData);

                Log::info('Global projection created', [
                    'projection_id' => $projection->id,
                    'year' => $projection->year,
                    'created_by' => $user->id,
                ]);

                $message = 'Global projection created successfully.';
            }

            // Load relationships for response
            $projection->load(['creator']);

            $statusCode = $existingProjection ? Response::HTTP_OK : Response::HTTP_CREATED;

            return response()->json([
                'success' => true,
                'data' => $projection,
                'message' => $message,
            ], $statusCode);

        } catch (\Exception $e) {
            Log::error('Failed to create/update global projection', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'year' => $validatedData['year'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create/update global projection. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

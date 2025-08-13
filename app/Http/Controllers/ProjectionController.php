<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProjectionRequest;
use App\Models\Branch;
use App\Models\Projection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class ProjectionController extends Controller
{
    /**
     * Display a listing of projections with filtering and search capabilities.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Start with base query
        $query = Projection::with(['branch', 'creator']);
        
        // Apply branch-based access control
        if (!Gate::allows('viewAny', Projection::class)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view projections.',
            ], Response::HTTP_FORBIDDEN);
        }
        
        // Super admins can see all projections, branch pastors only their branch
        if (!$user->hasRole('super_admin')) {
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
        
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        
        if ($request->filled('current_year')) {
            $query->currentYear();
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
     * Store a newly created projection.
     */
    public function store(ProjectionRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        
        // Check authorization
        if (!Gate::allows('create', Projection::class)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create projections.',
            ], Response::HTTP_FORBIDDEN);
        }
        
        // Verify branch access for non-super admins
        $user = $request->user();
        if (!$user->hasRole('super_admin')) {
            $userBranches = $user->pastoredBranches()->pluck('id');
            if (!$userBranches->contains($validatedData['branch_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to create projections for this branch.',
                ], Response::HTTP_FORBIDDEN);
            }
        }
        
        try {
            // Add creator information
            $validatedData['created_by'] = $user->id;
            
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
        if (!Gate::allows('view', $projection)) {
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
        if (!Gate::allows('update', $projection)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this projection.',
            ], Response::HTTP_FORBIDDEN);
        }
        
        $validatedData = $request->validated();
        $user = $request->user();
        
        // Verify branch access for non-super admins when changing branch
        if (!$user->hasRole('super_admin') && isset($validatedData['branch_id'])) {
            $userBranches = $user->pastoredBranches()->pluck('id');
            if (!$userBranches->contains($validatedData['branch_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to assign projections to this branch.',
                ], Response::HTTP_FORBIDDEN);
            }
        }
        
        try {
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
        if (!Gate::allows('delete', $projection)) {
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
     * Get projection statistics and variance analysis.
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!Gate::allows('viewAny', Projection::class)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view projection statistics.',
            ], Response::HTTP_FORBIDDEN);
        }
        
        // Build query with branch access control
        $query = Projection::with('branch');
        
        if (!$user->hasRole('super_admin')) {
            $userBranches = $user->pastoredBranches()->pluck('id');
            $query->whereIn('branch_id', $userBranches);
        }
        
        // Apply filters
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }
        
        if ($request->filled('year')) {
            $query->where('year', $request->integer('year'));
        } else {
            $query->currentYear();
        }
        
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
        
        if (!Gate::allows('viewAny', Branch::class)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view branches.',
            ], Response::HTTP_FORBIDDEN);
        }
        
        $query = Branch::active()->select('id', 'name', 'venue', 'pastor_id');
        
        // Apply branch access control
        if (!$user->hasRole('super_admin')) {
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
        
        if (!Gate::allows('viewAny', Projection::class)) {
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
        if (!Gate::allows('update', $projection)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to submit this projection for review.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (!$projection->isDraft()) {
            return response()->json([
                'success' => false,
                'message' => 'Only draft projections can be submitted for review.',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $success = $projection->submitForReview();
            
            if (!$success) {
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
        if (!$user->hasRole('super_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only super administrators can approve projections.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (!$projection->isInReview()) {
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
            
            if (!$success) {
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
        if (!$user->hasRole('super_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only super administrators can reject projections.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (!$projection->isInReview()) {
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
            
            if (!$success) {
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
        if (!$user->hasRole('super_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only super administrators can set current year projections.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (!$projection->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Only approved projections can be set as current year.',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $success = $projection->setAsCurrentYear();
            
            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to set projection as current year.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $projection->load(['branch', 'creator', 'approver']);

            Log::info('Projection set as current year', [
                'projection_id' => $projection->id,
                'branch_id' => $projection->branch_id,
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
} 
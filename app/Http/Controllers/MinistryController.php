<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MinistryRequest;
use App\Models\Member;
use App\Models\Ministry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

final class MinistryController extends Controller
{
    /**
     * Display a listing of ministries.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Ministry::class);

        try {
            $query = Ministry::with(['branch:id,name', 'leader:id,name,email'])
                ->withCount(['departments']);

            // Apply branch-based filtering for non-super admins
            $user = auth()->user();
            if (! $user->isSuperAdmin()) {
                $userBranch = $user->getPrimaryBranch();
                if ($userBranch) {
                    $query->where('branch_id', $userBranch->id);
                }
            }

            // Apply filters
            if ($request->filled('branch_id')) {
                // Only allow super admins to filter by different branches
                if ($user->isSuperAdmin()) {
                    $query->where('branch_id', $request->branch_id);
                }
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'name');
            $sortDirection = $request->get('sort_direction', 'asc');

            if (in_array($sortBy, ['name', 'status', 'created_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            }

            $ministries = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $ministries,
                'message' => 'Ministries retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving ministries: '.$e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve ministries.',
            ], 500);
        }
    }

    /**
     * Create a new ministry.
     */
    public function store(MinistryRequest $request): JsonResponse
    {
        Gate::authorize('create', Ministry::class);

        try {
            $validated = $request->validated();

            $ministry = Ministry::create($validated);

            Log::info('Ministry created successfully', [
                'ministry_id' => $ministry->id,
                'ministry_name' => $ministry->name,
                'branch_id' => $ministry->branch_id,
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $ministry->load(['branch:id,name', 'leader:id,name,email']),
                'message' => 'Ministry created successfully.',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating ministry: '.$e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create ministry.',
            ], 500);
        }
    }

    /**
     * Display the specified ministry.
     */
    public function show(Ministry $ministry): JsonResponse
    {
        Gate::authorize('view', $ministry);

        try {
            $ministry->load([
                'branch:id,name,venue,phone,email',
                'leader:id,name,email,phone',
                'departments' => function ($query) {
                    $query->with(['leader:id,name,email'])
                        ->withCount(['members']);
                },
            ]);

            return response()->json([
                'success' => true,
                'data' => $ministry,
                'message' => 'Ministry retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving ministry: '.$e->getMessage(), [
                'ministry_id' => $ministry->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve ministry.',
            ], 500);
        }
    }

    /**
     * Update the specified ministry.
     */
    public function update(MinistryRequest $request, Ministry $ministry): JsonResponse
    {
        Gate::authorize('update', $ministry);

        try {
            $validated = $request->validated();

            $ministry->update($validated);

            $ministry->load(['branch:id,name', 'leader:id,name,email']);

            Log::info('Ministry updated successfully', [
                'ministry_id' => $ministry->id,
                'ministry_name' => $ministry->name,
                'updated_by' => auth()->id(),
                'changes' => $validated,
            ]);

            return response()->json([
                'success' => true,
                'data' => $ministry,
                'message' => 'Ministry updated successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating ministry: '.$e->getMessage(), [
                'ministry_id' => $ministry->id,
                'user_id' => auth()->id(),
                'request_data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update ministry.',
            ], 500);
        }
    }

    /**
     * Remove the specified ministry.
     */
    public function destroy(Ministry $ministry): JsonResponse
    {
        Gate::authorize('delete', $ministry);

        try {
            // Check if ministry has any dependent records
            $hasDepartments = $ministry->departments()->count() > 0;

            if ($hasDepartments) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete ministry with existing departments.',
                    'details' => [
                        'departments_count' => $ministry->departments()->count(),
                    ],
                ], 422);
            }

            $ministryName = $ministry->name;
            $ministry->delete();

            Log::info('Ministry deleted successfully', [
                'ministry_name' => $ministryName,
                'deleted_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ministry deleted successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting ministry: '.$e->getMessage(), [
                'ministry_id' => $ministry->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete ministry.',
            ], 500);
        }
    }

    /**
     * Assign a leader to a ministry.
     */
    public function assignLeader(Request $request, Ministry $ministry): JsonResponse
    {
        Gate::authorize('assignLeader', $ministry);

        $request->validate([
            'leader_id' => 'required|exists:members,id',
        ]);

        try {
            $leader = Member::findOrFail($request->leader_id);

            // Check if member is a visitor
            if ($leader->member_status === 'visitor') {
                return response()->json([
                    'success' => false,
                    'message' => 'Visitors cannot be assigned as ministry leaders.',
                ], 422);
            }

            $ministry->update(['leader_id' => $leader->id]);

            // Update member status to 'minister'
            $leader->update(['member_status' => 'minister']);

            // Ensure role assignment exists for this branch
            if ($leader->user) {
                $leader->user->assignRole('ministry_leader', $ministry->branch_id);
            }

            $ministry->load(['branch:id,name', 'leader:id,name,email']);

            Log::info('Leader assigned to ministry', [
                'ministry_id' => $ministry->id,
                'leader_id' => $leader->id,
                'member_status_updated' => 'minister',
                'assigned_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $ministry,
                'message' => 'Leader assigned successfully. Member status updated to minister.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error assigning leader to ministry: '.$e->getMessage(), [
                'ministry_id' => $ministry->id,
                'leader_id' => $request->leader_id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign leader.',
            ], 500);
        }
    }

    /**
     * Remove leader from a ministry.
     */
    public function removeLeader(Ministry $ministry): JsonResponse
    {
        Gate::authorize('assignLeader', $ministry);

        try {
            if (! $ministry->leader_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ministry does not have an assigned leader.',
                ], 422);
            }

            $previousLeader = $ministry->leader;
            $ministry->update(['leader_id' => null]);

            // Update previous leader's status based on remaining assignments
            if ($previousLeader) {
                $previousLeader->updateStatusBasedOnAssignments();

                // If the previous leader no longer leads any ministry in this branch, remove branch-scoped role
                if ($previousLeader->user) {
                    $stillLeadsInBranch = \App\Models\Ministry::where('branch_id', $ministry->branch_id)
                        ->where('leader_id', $previousLeader->id)
                        ->exists();

                    if (! $stillLeadsInBranch) {
                        $previousLeader->user->removeRole('ministry_leader', $ministry->branch_id);
                    }
                }
            }

            $ministry->load(['branch:id,name', 'leader:id,name,email']);

            Log::info('Leader removed from ministry', [
                'ministry_id' => $ministry->id,
                'previous_leader_id' => $previousLeader?->id,
                'removed_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $ministry,
                'message' => 'Leader removed successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error removing leader from ministry: '.$e->getMessage(), [
                'ministry_id' => $ministry->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove leader.',
            ], 500);
        }
    }

    /**
     * Get available members for leadership assignment.
     */
    public function getAvailableLeaders(Request $request): JsonResponse
    {
        Gate::authorize('create', Ministry::class);

        try {
            $query = Member::select(['id', 'name', 'email', 'phone'])
                ->where('member_status', '!=', 'visitor')
                ->whereNull('deleted_at');

            $user = auth()->user();

            // For non-super admins, restrict to their branch
            if (! $user->isSuperAdmin()) {
                $userBranch = $user->getPrimaryBranch();
                if ($userBranch) {
                    $query->where('branch_id', $userBranch->id);
                }
            } else {
                // For super admins, filter by branch if provided
                if ($request->filled('branch_id')) {
                    $query->where('branch_id', $request->branch_id);
                }
            }

            // Add search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $leaders = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $leaders,
                'message' => 'Available leaders retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving available leaders: '.$e->getMessage(), [
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve available leaders.',
            ], 500);
        }
    }
}

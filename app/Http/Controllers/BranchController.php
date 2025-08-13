<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BranchRequest;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

final class BranchController extends Controller
{
    /**
     * Display the branches management view.
     */
    public function indexView()
    {
        Gate::authorize('viewAny', Branch::class);
        
        $branches = Branch::with(['pastor:id,name,email'])
            ->withCount(['members', 'ministries', 'events'])
            ->orderBy('name')
            ->get();
            
        return view('admin.branches.index', compact('branches'));
    }

    /**
     * Display a listing of branches.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Branch::class);

        try {
            $query = Branch::with(['pastor:id,name,email'])
                ->withCount(['members', 'ministries', 'smallGroups', 'events']);

            // Apply branch-based filtering for non-super admins
            $user = auth()->user();
            if (!$user->isSuperAdmin()) {
                $userBranch = $user->getPrimaryBranch();
                if ($userBranch) {
                    $query->where('id', $userBranch->id);
                }
            }

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('venue', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhereHas('pastor', function ($pastorQuery) use ($search) {
                          $pastorQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Apply sorting
            $sortField = $request->get('sort_by', 'name');
            $sortDirection = $request->get('sort_direction', 'asc');
            
            if (in_array($sortField, ['name', 'venue', 'status', 'created_at'])) {
                $query->orderBy($sortField, $sortDirection);
            }

            // Paginate results
            $perPage = min($request->get('per_page', 15), 100);
            $branches = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $branches,
                'message' => 'Branches retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving branches: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve branches.',
            ], 500);
        }
    }

    /**
     * Store a newly created branch.
     */
    public function store(BranchRequest $request): JsonResponse
    {
        Gate::authorize('create', Branch::class);

        try {
            $validatedData = $request->validated();

            // Validate pastor assignment if provided
            if (isset($validatedData['pastor_id'])) {
                $pastor = User::find($validatedData['pastor_id']);
                if (!$pastor || !$pastor->hasRole('branch_pastor')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected user is not a valid pastor.',
                    ], 422);
                }
            }

            $branch = Branch::create($validatedData);

            // Assign pastor role to branch if pastor_id is provided
            if (isset($validatedData['pastor_id'])) {
                $pastor = User::find($validatedData['pastor_id']);
                $pastor->assignRole('branch_pastor', $branch->id);
            }

            $branch->load(['pastor:id,name,email']);

            Log::info('Branch created successfully', [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $branch,
                'message' => 'Branch created successfully.',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error creating branch: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create branch.',
            ], 500);
        }
    }

    /**
     * Display the specified branch.
     */
    public function show(Branch $branch): JsonResponse
    {
        Gate::authorize('view', $branch);

        try {
            $branch->load([
                'pastor:id,name,email',
                'members:id,first_name,last_name,email,phone,membership_status',
                'ministries:id,name,description,ministry_leader_id',
                'smallGroups:id,name,leader_id,meeting_day,meeting_time',
                'events:id,title,start_date,end_date,location',
            ]);

            $branch->loadCount(['members', 'ministries', 'smallGroups', 'events']);

            return response()->json([
                'success' => true,
                'data' => $branch,
                'message' => 'Branch retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving branch: ' . $e->getMessage(), [
                'branch_id' => $branch->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve branch.',
            ], 500);
        }
    }

    /**
     * Update the specified branch.
     */
    public function update(BranchRequest $request, Branch $branch): JsonResponse
    {
        Gate::authorize('update', $branch);

        try {
            $validatedData = $request->validated();
            $oldPastorId = $branch->pastor_id;

            // Validate pastor assignment if provided
            if (isset($validatedData['pastor_id']) && $validatedData['pastor_id'] !== $oldPastorId) {
                $pastor = User::find($validatedData['pastor_id']);
                if (!$pastor || !$pastor->hasRole('branch_pastor')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected user is not a valid pastor.',
                    ], 422);
                }
            }

            $branch->update($validatedData);

            // Handle pastor role assignments
            if (isset($validatedData['pastor_id'])) {
                // If pastor_id is null or empty, remove current pastor
                if (empty($validatedData['pastor_id'])) {
                    if ($oldPastorId) {
                        $oldPastor = User::find($oldPastorId);
                        if ($oldPastor) {
                            $oldPastor->removeRole('branch_pastor', $branch->id);
                        }
                    }
                } else {
                    // Remove old pastor role if exists and is different
                    if ($oldPastorId && $oldPastorId !== $validatedData['pastor_id']) {
                        $oldPastor = User::find($oldPastorId);
                        if ($oldPastor) {
                            $oldPastor->removeRole('branch_pastor', $branch->id);
                        }
                    }

                    // Assign new pastor role only if it's different and doesn't already exist
                    if ($validatedData['pastor_id'] !== $oldPastorId) {
                        $newPastor = User::find($validatedData['pastor_id']);
                        if ($newPastor && !$newPastor->hasRole('branch_pastor', $branch->id)) {
                            $newPastor->assignRole('branch_pastor', $branch->id);
                        }
                    }
                }
            }

            $branch->load(['pastor:id,name,email']);

            Log::info('Branch updated successfully', [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'updated_by' => auth()->id(),
                'changes' => $validatedData,
            ]);

            return response()->json([
                'success' => true,
                'data' => $branch,
                'message' => 'Branch updated successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating branch: ' . $e->getMessage(), [
                'branch_id' => $branch->id,
                'user_id' => auth()->id(),
                'request_data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update branch.',
            ], 500);
        }
    }

    /**
     * Remove the specified branch.
     */
    public function destroy(Branch $branch): JsonResponse
    {
        Gate::authorize('delete', $branch);

        try {
            // Check if branch has any dependent records
            $hasMembers = $branch->members()->count() > 0;
            $hasMinistries = $branch->ministries()->count() > 0;
            $hasSmallGroups = $branch->smallGroups()->count() > 0;
            $hasEvents = $branch->events()->count() > 0;

            if ($hasMembers || $hasMinistries || $hasSmallGroups || $hasEvents) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete branch with existing members, ministries, small groups, or events.',
                    'details' => [
                        'members_count' => $branch->members()->count(),
                        'ministries_count' => $branch->ministries()->count(),
                        'small_groups_count' => $branch->smallGroups()->count(),
                        'events_count' => $branch->events()->count(),
                    ],
                ], 422);
            }

            // Remove pastor role assignment
            if ($branch->pastor_id) {
                $pastor = User::find($branch->pastor_id);
                if ($pastor) {
                    $pastor->removeRole('branch_pastor', $branch->id);
                }
            }

            $branchName = $branch->name;
            $branch->delete();

            Log::info('Branch deleted successfully', [
                'branch_name' => $branchName,
                'deleted_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Branch deleted successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting branch: ' . $e->getMessage(), [
                'branch_id' => $branch->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete branch.',
            ], 500);
        }
    }

    /**
     * Assign a pastor to a branch.
     */
    public function assignPastor(Request $request, Branch $branch): JsonResponse
    {
        Gate::authorize('managePastors', $branch);

        $request->validate([
            'pastor_id' => 'required|exists:users,id',
        ]);

        try {
            $pastor = User::findOrFail($request->pastor_id);

            // Validate that the user has the branch_pastor role
            if (!$pastor->hasRole('branch_pastor')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected user is not a valid pastor.',
                ], 422);
            }

            // Remove current pastor if exists
            if ($branch->pastor_id) {
                $currentPastor = User::find($branch->pastor_id);
                if ($currentPastor) {
                    $currentPastor->removeRole('branch_pastor', $branch->id);
                }
            }

            // Assign new pastor
            $branch->update(['pastor_id' => $pastor->id]);
            $pastor->assignRole('branch_pastor', $branch->id);

            $branch->load(['pastor:id,name,email']);

            Log::info('Pastor assigned to branch', [
                'branch_id' => $branch->id,
                'pastor_id' => $pastor->id,
                'assigned_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $branch,
                'message' => 'Pastor assigned successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error assigning pastor to branch: ' . $e->getMessage(), [
                'branch_id' => $branch->id,
                'pastor_id' => $request->pastor_id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign pastor.',
            ], 500);
        }
    }

    /**
     * Remove pastor from a branch.
     */
    public function removePastor(Branch $branch): JsonResponse
    {
        Gate::authorize('managePastors', $branch);

        try {
            if (!$branch->pastor_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Branch does not have an assigned pastor.',
                ], 422);
            }

            $pastor = User::find($branch->pastor_id);
            if ($pastor) {
                $pastor->removeRole('branch_pastor', $branch->id);
            }

            $branch->update(['pastor_id' => null]);
            $branch->load(['pastor:id,name,email']);

            Log::info('Pastor removed from branch', [
                'branch_id' => $branch->id,
                'removed_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $branch,
                'message' => 'Pastor removed successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error removing pastor from branch: ' . $e->getMessage(), [
                'branch_id' => $branch->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove pastor.',
            ], 500);
        }
    }

    /**
     * Get available pastors for assignment.
     */
    public function getAvailablePastors(): JsonResponse
    {
        Gate::authorize('create', Branch::class);

        try {
            $pastors = User::whereHas('roles', function ($query) {
                $query->where('name', 'branch_pastor');
            })
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $pastors,
                'message' => 'Available pastors retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving available pastors: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve available pastors.',
            ], 500);
        }
    }
} 
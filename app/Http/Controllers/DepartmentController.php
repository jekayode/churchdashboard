<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

final class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Department::class);

        try {
            $query = Department::with([
                'ministry:id,name,branch_id',
                'ministry.branch:id,name',
                'leader:id,name,email',
            ])->withCount(['members']);

            // Apply branch-based filtering for non-super admins
            $user = auth()->user();
            if (! $user->isSuperAdmin()) {
                $userBranch = $user->getPrimaryBranch();
                if ($userBranch) {
                    $query->whereHas('ministry', function ($q) use ($userBranch) {
                        $q->where('branch_id', $userBranch->id);
                    });
                }
            }

            // If ministry leader, restrict to ministries they lead
            if ($user->isMinistryLeader() && $user->member) {
                $leaderMemberId = $user->member->id;
                $query->whereHas('ministry', function ($q) use ($leaderMemberId) {
                    $q->where('leader_id', $leaderMemberId);
                });
            }

            // Apply filters
            if ($request->filled('ministry_id')) {
                $query->where('ministry_id', $request->ministry_id);
            }

            if ($request->filled('branch_id')) {
                // Only allow super admins to filter by different branches
                if ($user->isSuperAdmin()) {
                    $query->whereHas('ministry', function ($q) use ($request) {
                        $q->where('branch_id', $request->branch_id);
                    });
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

            $departments = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $departments,
                'message' => 'Departments retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving departments: '.$e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve departments.',
            ], 500);
        }
    }

    /**
     * Create a new department.
     */
    public function store(DepartmentRequest $request): JsonResponse
    {
        Gate::authorize('create', Department::class);

        try {
            $validated = $request->validated();

            $department = Department::create($validated);

            Log::info('Department created successfully', [
                'department_id' => $department->id,
                'department_name' => $department->name,
                'ministry_id' => $department->ministry_id,
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $department->load([
                    'ministry:id,name,branch_id',
                    'ministry.branch:id,name',
                    'leader:id,name,email',
                ]),
                'message' => 'Department created successfully.',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating department: '.$e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create department.',
            ], 500);
        }
    }

    /**
     * Display the specified department.
     */
    public function show(Department $department): JsonResponse
    {
        Gate::authorize('view', $department);

        try {
            $department->load([
                'ministry:id,name,branch_id,description',
                'ministry.branch:id,name,venue,phone,email',
                'leader:id,name,email,phone',
                'members:id,name,email,phone,member_status',
            ]);

            return response()->json([
                'success' => true,
                'data' => $department,
                'message' => 'Department retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving department: '.$e->getMessage(), [
                'department_id' => $department->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve department.',
            ], 500);
        }
    }

    /**
     * Update the specified department.
     */
    public function update(DepartmentRequest $request, Department $department): JsonResponse
    {
        Gate::authorize('update', $department);

        try {
            $validated = $request->validated();

            $department->update($validated);

            $department->load([
                'ministry:id,name,branch_id',
                'ministry.branch:id,name',
                'leader:id,name,email',
            ]);

            Log::info('Department updated successfully', [
                'department_id' => $department->id,
                'department_name' => $department->name,
                'updated_by' => auth()->id(),
                'changes' => $validated,
            ]);

            return response()->json([
                'success' => true,
                'data' => $department,
                'message' => 'Department updated successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating department: '.$e->getMessage(), [
                'department_id' => $department->id,
                'user_id' => auth()->id(),
                'request_data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update department.',
            ], 500);
        }
    }

    /**
     * Remove the specified department.
     */
    public function destroy(Department $department): JsonResponse
    {
        Gate::authorize('delete', $department);

        try {
            // Check if department has any dependent records
            $hasMembers = $department->members()->count() > 0;

            if ($hasMembers) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete department with assigned members.',
                    'details' => [
                        'members_count' => $department->members()->count(),
                    ],
                ], 422);
            }

            $departmentName = $department->name;
            $department->delete();

            Log::info('Department deleted successfully', [
                'department_name' => $departmentName,
                'deleted_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Department deleted successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting department: '.$e->getMessage(), [
                'department_id' => $department->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete department.',
            ], 500);
        }
    }

    /**
     * Assign a leader to a department.
     */
    public function assignLeader(Request $request, Department $department): JsonResponse
    {
        Gate::authorize('assignLeader', $department);

        $request->validate([
            'leader_id' => 'required|exists:members,id',
        ]);

        try {
            $leader = Member::findOrFail($request->leader_id);

            // Check if member is a visitor
            if ($leader->member_status === 'visitor') {
                return response()->json([
                    'success' => false,
                    'message' => 'Visitors cannot be assigned as department leaders.',
                ], 422);
            }

            $department->update(['leader_id' => $leader->id]);

            // Update member status to 'leader'
            $leader->update(['member_status' => 'leader']);

            // Ensure role assignment exists for this branch
            if ($leader->user && $department->ministry) {
                $leader->user->assignRole('department_leader', $department->ministry->branch_id);
            }

            $department->load([
                'ministry:id,name,branch_id',
                'ministry.branch:id,name',
                'leader:id,name,email',
            ]);

            Log::info('Leader assigned to department', [
                'department_id' => $department->id,
                'leader_id' => $leader->id,
                'member_status_updated' => 'leader',
                'assigned_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $department,
                'message' => 'Leader assigned successfully. Member status updated to leader.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error assigning leader to department: '.$e->getMessage(), [
                'department_id' => $department->id,
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
     * Remove leader from a department.
     */
    public function removeLeader(Department $department): JsonResponse
    {
        Gate::authorize('assignLeader', $department);

        try {
            if (! $department->leader_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Department does not have an assigned leader.',
                ], 422);
            }

            $previousLeader = $department->leader;
            $department->update(['leader_id' => null]);

            // Update previous leader's status based on remaining assignments
            if ($previousLeader) {
                $previousLeader->updateStatusBasedOnAssignments();

                // If the previous leader no longer leads any department in this branch, remove role
                if ($previousLeader->user && $department->ministry) {
                    $branchId = $department->ministry->branch_id;
                    $stillLeadsInBranch = \App\Models\Department::whereHas('ministry', function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId);
                    })->where('leader_id', $previousLeader->id)->exists();

                    if (! $stillLeadsInBranch) {
                        $previousLeader->user->removeRole('department_leader', $branchId);
                    }
                }
            }

            $department->load([
                'ministry:id,name,branch_id',
                'ministry.branch:id,name',
                'leader:id,name,email',
            ]);

            Log::info('Leader removed from department', [
                'department_id' => $department->id,
                'previous_leader_id' => $previousLeader?->id,
                'removed_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $department,
                'message' => 'Leader removed successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error removing leader from department: '.$e->getMessage(), [
                'department_id' => $department->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove leader.',
            ], 500);
        }
    }

    /**
     * Assign members to a department.
     */
    public function assignMembers(Request $request, Department $department): JsonResponse
    {
        Gate::authorize('manageMembers', $department);

        $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'exists:members,id',
        ]);

        try {
            $memberIds = $request->member_ids;

            // Attach members with timestamp
            $department->members()->syncWithoutDetaching(
                array_fill_keys($memberIds, ['assigned_at' => now()])
            );

            // Update member status to 'volunteer' for assigned members (if not already leader)
            Member::whereIn('id', $memberIds)
                ->where('member_status', '!=', 'leader')
                ->update(['member_status' => 'volunteer']);

            $department->load([
                'ministry:id,name,branch_id',
                'ministry.branch:id,name',
                'members:id,name,email',
            ]);

            Log::info('Members assigned to department', [
                'department_id' => $department->id,
                'member_ids' => $memberIds,
                'member_status_updated' => 'volunteer',
                'assigned_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $department,
                'message' => 'Members assigned successfully. Member status updated to volunteer.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error assigning members to department: '.$e->getMessage(), [
                'department_id' => $department->id,
                'member_ids' => $request->member_ids ?? [],
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign members.',
            ], 500);
        }
    }

    /**
     * Remove members from a department.
     */
    public function removeMembers(Request $request, Department $department): JsonResponse
    {
        Gate::authorize('manageMembers', $department);

        $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'exists:members,id',
        ]);

        try {
            $memberIds = $request->member_ids;

            $department->members()->detach($memberIds);

            $department->load([
                'ministry:id,name,branch_id',
                'ministry.branch:id,name',
                'members:id,name,email',
            ]);

            Log::info('Members removed from department', [
                'department_id' => $department->id,
                'member_ids' => $memberIds,
                'removed_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $department,
                'message' => 'Members removed successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error removing members from department: '.$e->getMessage(), [
                'department_id' => $department->id,
                'member_ids' => $request->member_ids ?? [],
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove members.',
            ], 500);
        }
    }

    /**
     * Get available members for leadership assignment.
     */
    public function getAvailableLeaders(Request $request): JsonResponse
    {
        Gate::authorize('create', Department::class);

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

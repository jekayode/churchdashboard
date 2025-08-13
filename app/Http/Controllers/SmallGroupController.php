<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SmallGroupRequest;
use App\Models\SmallGroup;
use App\Models\Member;
use App\Models\Branch;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class SmallGroupController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of small groups.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SmallGroup::class);

        try {
            $query = SmallGroup::with([
                'branch:id,name',
                'leader:id,name,phone,email',
                'members:id,name,phone,email'
            ]);

            // Apply branch-based filtering for non-super admins
            $user = auth()->user();
            if (!$user->isSuperAdmin()) {
                $userBranch = $user->getPrimaryBranch();
                if ($userBranch) {
                    $query->where('branch_id', $userBranch->id);
                }
            }

            // Apply filters
            $this->applyFilters($query, $request);

            // Apply search
            $this->applySearch($query, $request);

            // Apply sorting
            $this->applySorting($query, $request);

            // Paginate results
            $perPage = min($request->get('per_page', 15), 100);
            $smallGroups = $query->paginate($perPage);

            // Add computed fields
            $smallGroups->getCollection()->transform(function ($group) {
                $group->members_count = $group->members->count();
                $group->has_leader = !is_null($group->leader_id);
                $group->is_active = $group->status === 'active';
                return $group;
            });

            return response()->json([
                'success' => true,
                'data' => $smallGroups,
                'message' => 'Small groups retrieved successfully.',
                'filters' => $this->getAvailableFilters($user),
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving small groups: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve small groups.',
            ], 500);
        }
    }

    /**
     * Store a newly created small group.
     */
    public function store(SmallGroupRequest $request): JsonResponse
    {
        $this->authorize('create', SmallGroup::class);

        try {
            $data = $request->validated();
            
            // For non-super admins, ensure they can only create groups in their own branch
            $user = auth()->user();
            if (!$user->isSuperAdmin()) {
                $userBranch = $user->getPrimaryBranch();
                if ($userBranch) {
                    $data['branch_id'] = $userBranch->id;
                }
            }

            $smallGroup = SmallGroup::create($data);
            
            // Load relationships for response
            $smallGroup->load([
                'branch:id,name',
                'leader:id,name,phone,email',
                'members:id,name,phone,email'
            ]);

            Log::info('Small group created successfully', [
                'small_group_id' => $smallGroup->id,
                'small_group_name' => $smallGroup->name,
                'branch_id' => $smallGroup->branch_id,
                'leader_id' => $smallGroup->leader_id,
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $smallGroup,
                'message' => 'Small group created successfully.',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating small group: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create small group.',
            ], 500);
        }
    }

    /**
     * Display the specified small group.
     */
    public function show(SmallGroup $smallGroup): JsonResponse
    {
        $this->authorize('view', $smallGroup);

        try {
            $smallGroup->load([
                'branch:id,name,venue,phone,email',
                'leader:id,name,phone,email,member_status',
                'members' => function ($query) {
                    $query->withPivot('joined_at')
                          ->orderBy('name');
                }
            ]);

            // Add computed fields
            $smallGroup->members_count = $smallGroup->members->count();
            $smallGroup->has_leader = !is_null($smallGroup->leader_id);
            $smallGroup->is_active = $smallGroup->status === 'active';

            return response()->json([
                'success' => true,
                'data' => $smallGroup,
                'message' => 'Small group retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving small group: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'small_group_id' => $smallGroup->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve small group.',
            ], 500);
        }
    }

    /**
     * Update the specified small group.
     */
    public function update(SmallGroupRequest $request, SmallGroup $smallGroup): JsonResponse
    {
        $this->authorize('update', $smallGroup);

        try {
            $data = $request->validated();
            
            // For non-super admins, prevent changing branch
            $user = auth()->user();
            if (!$user->isSuperAdmin()) {
                unset($data['branch_id']);
            }

            $smallGroup->update($data);
            
            // Load relationships for response
            $smallGroup->load([
                'branch:id,name',
                'leader:id,name,phone,email',
                'members:id,name,phone,email'
            ]);

            Log::info('Small group updated successfully', [
                'small_group_id' => $smallGroup->id,
                'small_group_name' => $smallGroup->name,
                'updated_by' => auth()->id(),
                'changes' => $smallGroup->getChanges(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $smallGroup,
                'message' => 'Small group updated successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating small group: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'small_group_id' => $smallGroup->id,
                'request_data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update small group.',
            ], 500);
        }
    }

    /**
     * Remove the specified small group.
     */
    public function destroy(SmallGroup $smallGroup): JsonResponse
    {
        $this->authorize('delete', $smallGroup);

        try {
            $smallGroupName = $smallGroup->name;
            $smallGroupId = $smallGroup->id;

            // Remove all member associations first
            $smallGroup->members()->detach();

            // Delete the small group
            $smallGroup->delete();

            Log::info('Small group deleted successfully', [
                'small_group_id' => $smallGroupId,
                'small_group_name' => $smallGroupName,
                'deleted_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Small group deleted successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting small group: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'small_group_id' => $smallGroup->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete small group.',
            ], 500);
        }
    }

    /**
     * Assign members to a small group.
     */
    public function assignMembers(Request $request, SmallGroup $smallGroup): JsonResponse
    {
        $this->authorize('manageMembers', $smallGroup);

        // Validate the request
        $validated = $request->validate([
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'required|integer|exists:members,id',
        ]);

        try {
            // Get members and validate they belong to the same branch
            $members = Member::whereIn('id', $validated['member_ids'])
                ->where('branch_id', $smallGroup->branch_id)
                ->get();

            if ($members->count() !== count($validated['member_ids'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some members do not belong to the same branch as the small group.',
                ], 422);
            }

            // Attach members with joined_at timestamp
            $syncData = [];
            foreach ($validated['member_ids'] as $memberId) {
                $syncData[$memberId] = ['joined_at' => now()];
            }

            $smallGroup->members()->syncWithoutDetaching($syncData);

            Log::info('Members assigned to small group', [
                'small_group_id' => $smallGroup->id,
                'member_ids' => $validated['member_ids'],
                'assigned_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Members assigned to small group successfully.',
                'assigned_count' => count($validated['member_ids']),
            ]);

        } catch (\Exception $e) {
            Log::error('Error assigning members to small group: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'small_group_id' => $smallGroup->id,
                'member_ids' => $validated['member_ids'] ?? [],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign members to small group.',
            ], 500);
        }
    }

    /**
     * Remove members from a small group.
     */
    public function removeMembers(Request $request, SmallGroup $smallGroup): JsonResponse
    {
        $this->authorize('manageMembers', $smallGroup);

        // Validate the request
        $validated = $request->validate([
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'required|integer|exists:members,id',
        ]);

        try {
            $smallGroup->members()->detach($validated['member_ids']);

            Log::info('Members removed from small group', [
                'small_group_id' => $smallGroup->id,
                'member_ids' => $validated['member_ids'],
                'removed_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Members removed from small group successfully.',
                'removed_count' => count($validated['member_ids']),
            ]);

        } catch (\Exception $e) {
            Log::error('Error removing members from small group: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'small_group_id' => $smallGroup->id,
                'member_ids' => $validated['member_ids'] ?? [],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove members from small group.',
            ], 500);
        }
    }

    /**
     * Change the leader of a small group.
     */
    public function changeLeader(Request $request, SmallGroup $smallGroup): JsonResponse
    {
        $this->authorize('update', $smallGroup);

        // Validate the request
        $validated = $request->validate([
            'leader_id' => 'nullable|integer|exists:members,id',
        ]);

        try {
            // If setting a leader, validate they belong to the same branch
            if ($validated['leader_id']) {
                $leader = Member::find($validated['leader_id']);
                if ($leader->branch_id !== $smallGroup->branch_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The leader must belong to the same branch as the small group.',
                    ], 422);
                }
            }

            $oldLeaderId = $smallGroup->leader_id;
            $smallGroup->update(['leader_id' => $validated['leader_id']]);

            Log::info('Small group leader changed', [
                'small_group_id' => $smallGroup->id,
                'old_leader_id' => $oldLeaderId,
                'new_leader_id' => $validated['leader_id'],
                'changed_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => $validated['leader_id'] 
                    ? 'Small group leader updated successfully.' 
                    : 'Small group leader removed successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error changing small group leader: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'small_group_id' => $smallGroup->id,
                'leader_id' => $validated['leader_id'] ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to change small group leader.',
            ], 500);
        }
    }

    /**
     * Get available members for assignment to a small group.
     */
    public function getAvailableMembers(SmallGroup $smallGroup): JsonResponse
    {
        $this->authorize('view', $smallGroup);

        try {
            $availableMembers = Member::where('branch_id', $smallGroup->branch_id)
                ->whereNotIn('id', $smallGroup->members->pluck('id'))
                ->select('id', 'name', 'phone', 'email', 'member_status')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $availableMembers,
                'message' => 'Available members retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving available members: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'small_group_id' => $smallGroup->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve available members.',
            ], 500);
        }
    }

    /**
     * Show members management view for a small group.
     */
    public function showMembers(SmallGroup $smallGroup)
    {
        $this->authorize('view', $smallGroup);

        // Load the small group with all necessary relationships
        $smallGroup->load([
            'branch:id,name,venue',
            'leader:id,name,phone,email,member_status',
            'members' => function ($query) {
                $query->withPivot('created_at')
                      ->orderBy('name');
            }
        ]);

        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();

        return view('pastor.small-groups.members', compact('smallGroup', 'isSuperAdmin'));
    }

    /**
     * Get small group statistics.
     */
    public function getStatistics(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SmallGroup::class);

        try {
            $query = SmallGroup::query();

            // Apply branch filtering for non-super admins
            $user = auth()->user();
            $userBranch = null;
            if (!$user->isSuperAdmin()) {
                $userBranch = $user->getPrimaryBranch();
                if ($userBranch) {
                    $query->where('branch_id', $userBranch->id);
                }
            }

            // Apply branch filter if requested
            if ($request->has('branch_id') && $user->isSuperAdmin()) {
                $query->where('branch_id', $request->branch_id);
            }

            $statistics = [
                'total_groups' => $query->count(),
                'active_groups' => (clone $query)->where('status', 'active')->count(),
                'inactive_groups' => (clone $query)->where('status', 'inactive')->count(),
                'groups_with_leaders' => (clone $query)->whereNotNull('leader_id')->count(),
                'groups_without_leader' => (clone $query)->whereNull('leader_id')->count(),
                'total_members' => DB::table('member_small_groups')
                    ->join('small_groups', 'small_groups.id', '=', 'member_small_groups.small_group_id')
                    ->when(!$user->isSuperAdmin() && isset($userBranch), function ($q) use ($userBranch) {
                        return $q->where('small_groups.branch_id', $userBranch->id);
                    })
                    ->when($request->has('branch_id') && $user->isSuperAdmin(), function ($q) use ($request) {
                        return $q->where('small_groups.branch_id', $request->branch_id);
                    })
                    ->count(),
                'average_group_size' => round(
                    DB::table('member_small_groups')
                        ->join('small_groups', 'small_groups.id', '=', 'member_small_groups.small_group_id')
                        ->when(!$user->isSuperAdmin() && isset($userBranch), function ($q) use ($userBranch) {
                            return $q->where('small_groups.branch_id', $userBranch->id);
                        })
                        ->when($request->has('branch_id') && $user->isSuperAdmin(), function ($q) use ($request) {
                            return $q->where('small_groups.branch_id', $request->branch_id);
                        })
                        ->count() / max($query->count(), 1), 1
                ),
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics,
                'message' => 'Small group statistics retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving small group statistics: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve small group statistics.',
            ], 500);
        }
    }

    /**
     * Apply filters to the query.
     */
    private function applyFilters($query, Request $request): void
    {
        if ($request->has('branch_id') && !empty($request->branch_id) && auth()->user()->isSuperAdmin()) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('has_leader') && $request->has_leader !== '' && $request->has_leader !== null) {
            if ($request->boolean('has_leader')) {
                $query->whereNotNull('leader_id');
            } else {
                $query->whereNull('leader_id');
            }
        }

        if ($request->has('meeting_day') && !empty($request->meeting_day)) {
            $query->where('meeting_day', $request->meeting_day);
        }
    }

    /**
     * Apply search to the query.
     */
    private function applySearch($query, Request $request): void
    {
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('leader', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
    }

    /**
     * Apply sorting to the query.
     */
    private function applySorting($query, Request $request): void
    {
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');

        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }

        switch ($sortBy) {
            case 'name':
            case 'status':
            case 'meeting_day':
            case 'location':
                $query->orderBy($sortBy, $sortDirection);
                break;
            case 'branch':
                $query->join('branches', 'branches.id', '=', 'small_groups.branch_id')
                      ->orderBy('branches.name', $sortDirection)
                      ->select('small_groups.*');
                break;
            case 'leader':
                $query->leftJoin('members', 'members.id', '=', 'small_groups.leader_id')
                      ->orderBy('members.name', $sortDirection)
                      ->select('small_groups.*');
                break;
            case 'created_at':
                $query->orderBy('small_groups.created_at', $sortDirection);
                break;
            default:
                $query->orderBy('name', 'asc');
        }
    }

    /**
     * Get available filters for the current user.
     */
    private function getAvailableFilters($user): array
    {
        $filters = [
            'status' => ['active', 'inactive'],
            'has_leader' => [true, false],
            'meeting_day' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
        ];

        // Add branch filter for super admins
        if ($user->isSuperAdmin()) {
            $filters['branches'] = Branch::select('id', 'name')->orderBy('name')->get();
        }

        return $filters;
    }

    /**
     * Get available members for small group leaders.
     */
    public function getAvailableLeaders(): JsonResponse
    {
        $this->authorize('viewAny', SmallGroup::class);

        try {
            $user = auth()->user();
            $query = Member::query()
                ->select(['id', 'name', 'member_status', 'branch_id'])
                ->whereIn('member_status', ['member', 'volunteer', 'leader', 'minister']);

            // Apply branch-based filtering for non-super admins
            if (!$user->isSuperAdmin()) {
                $userBranch = $user->getPrimaryBranch();
                if ($userBranch) {
                    $query->where('branch_id', $userBranch->id);
                }
            }

            $leaders = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $leaders,
                'message' => 'Available leaders retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving available leaders: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve available leaders.',
            ], 500);
        }
    }

    /**
     * Get detailed small group reports with various metrics.
     */
    public function getDetailedReports(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SmallGroup::class);

        try {
            $user = auth()->user();
            $query = SmallGroup::query();

            // Apply branch filtering for non-super admins
            if (!$user->isSuperAdmin()) {
                $userBranch = $user->getPrimaryBranch();
                if ($userBranch) {
                    $query->where('branch_id', $userBranch->id);
                }
            }

            // Apply branch filter if requested
            if ($request->has('branch_id') && $user->isSuperAdmin()) {
                $query->where('branch_id', $request->branch_id);
            }

            // Get groups with member counts and leader information
            $groupsWithDetails = $query->with(['branch:id,name', 'leader:id,name'])
                ->withCount('members')
                ->get()
                ->map(function ($group) {
                    return [
                        'id' => $group->id,
                        'name' => $group->name,
                        'branch' => $group->branch->name ?? 'N/A',
                        'leader' => $group->leader->name ?? 'No Leader',
                        'member_count' => $group->members_count,
                        'capacity' => $group->capacity,
                        'capacity_utilization' => $group->capacity > 0 ? round(($group->members_count / $group->capacity) * 100, 1) : 0,
                        'status' => $group->status,
                        'meeting_day' => $group->meeting_day,
                        'location' => $group->location,
                        'created_at' => $group->created_at->format('Y-m-d'),
                    ];
                });

            // Calculate meeting day distribution
            $meetingDayDistribution = $query->get()
                ->groupBy('meeting_day')
                ->map(function ($groups, $day) {
                    return [
                        'day' => $day,
                        'group_count' => $groups->count(),
                        'total_members' => $groups->sum(function ($group) {
                            return $group->members()->count();
                        }),
                    ];
                })
                ->values();

            // Calculate capacity utilization metrics
            $capacityMetrics = $this->calculateCapacityMetrics($query);

            // Get growth trends (last 6 months)
            $growthTrends = $this->getGrowthTrends($query);

            $report = [
                'summary' => [
                    'total_groups' => $groupsWithDetails->count(),
                    'active_groups' => $groupsWithDetails->where('status', 'active')->count(),
                    'groups_with_leaders' => $groupsWithDetails->where('leader', '!=', 'No Leader')->count(),
                    'total_members' => $groupsWithDetails->sum('member_count'),
                    'average_group_size' => $groupsWithDetails->avg('member_count'),
                    'average_capacity_utilization' => $groupsWithDetails->avg('capacity_utilization'),
                ],
                'groups_detail' => $groupsWithDetails,
                'meeting_day_distribution' => $meetingDayDistribution,
                'capacity_metrics' => $capacityMetrics,
                'growth_trends' => $growthTrends,
                'generated_at' => now()->toISOString(),
            ];

            return response()->json([
                'success' => true,
                'data' => $report,
                'message' => 'Detailed small group reports retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving detailed small group reports: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve detailed small group reports.',
            ], 500);
        }
    }

    /**
     * Get small group attendance and engagement reports.
     */
    public function getEngagementReports(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SmallGroup::class);

        try {
            $user = auth()->user();
            $query = SmallGroup::query();

            // Apply branch filtering for non-super admins
            if (!$user->isSuperAdmin()) {
                $userBranch = $user->getPrimaryBranch();
                if ($userBranch) {
                    $query->where('branch_id', $userBranch->id);
                }
            }

            // Apply branch filter if requested
            if ($request->has('branch_id') && $user->isSuperAdmin()) {
                $query->where('branch_id', $request->branch_id);
            }

            // Get member engagement metrics
            $memberEngagement = $this->getMemberEngagementMetrics($query);

            // Get leader effectiveness metrics
            $leaderEffectiveness = $this->getLeaderEffectivenessMetrics($query);

            // Get retention metrics
            $retentionMetrics = $this->getRetentionMetrics($query);

            $report = [
                'member_engagement' => $memberEngagement,
                'leader_effectiveness' => $leaderEffectiveness,
                'retention_metrics' => $retentionMetrics,
                'generated_at' => now()->toISOString(),
            ];

            return response()->json([
                'success' => true,
                'data' => $report,
                'message' => 'Small group engagement reports retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving small group engagement reports: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve small group engagement reports.',
            ], 500);
        }
    }

    /**
     * Get branch comparison reports for small groups.
     */
    public function getBranchComparisonReports(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SmallGroup::class);

        // Only super admins can access branch comparison reports
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access branch comparison reports.',
            ], 403);
        }

        try {
            $branchComparisons = Branch::with(['smallGroups' => function ($query) {
                $query->withCount('members');
            }])
            ->get()
            ->map(function ($branch) {
                $groups = $branch->smallGroups;
                $totalMembers = $groups->sum('members_count');
                $activeGroups = $groups->where('status', 'active')->count();
                $groupsWithLeaders = $groups->whereNotNull('leader_id')->count();

                return [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'total_groups' => $groups->count(),
                    'active_groups' => $activeGroups,
                    'groups_with_leaders' => $groupsWithLeaders,
                    'total_members' => $totalMembers,
                    'average_group_size' => $groups->count() > 0 ? round($totalMembers / $groups->count(), 1) : 0,
                    'leadership_coverage' => $groups->count() > 0 ? round(($groupsWithLeaders / $groups->count()) * 100, 1) : 0,
                    'activity_rate' => $groups->count() > 0 ? round(($activeGroups / $groups->count()) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('total_groups');

            // Calculate overall metrics
            $overallMetrics = [
                'total_branches' => $branchComparisons->count(),
                'total_groups_across_branches' => $branchComparisons->sum('total_groups'),
                'total_members_across_branches' => $branchComparisons->sum('total_members'),
                'average_groups_per_branch' => $branchComparisons->avg('total_groups'),
                'average_members_per_branch' => $branchComparisons->avg('total_members'),
                'highest_performing_branch' => $branchComparisons->first(),
                'branches_needing_attention' => $branchComparisons->where('leadership_coverage', '<', 50)->values(),
            ];

            $report = [
                'branch_comparisons' => $branchComparisons->values(),
                'overall_metrics' => $overallMetrics,
                'generated_at' => now()->toISOString(),
            ];

            return response()->json([
                'success' => true,
                'data' => $report,
                'message' => 'Branch comparison reports retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving branch comparison reports: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve branch comparison reports.',
            ], 500);
        }
    }

    /**
     * Calculate capacity utilization metrics.
     */
    private function calculateCapacityMetrics($query): array
    {
        $groups = $query->with('members')->get();
        
        $capacityData = $groups->map(function ($group) {
            $memberCount = $group->members->count();
            $capacity = $group->capacity ?? 0;
            
            return [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'member_count' => $memberCount,
                'capacity' => $capacity,
                'utilization' => $capacity > 0 ? ($memberCount / $capacity) * 100 : 0,
            ];
        });

        return [
            'groups_at_capacity' => $capacityData->where('utilization', '>=', 100)->count(),
            'groups_near_capacity' => $capacityData->where('utilization', '>=', 80)->where('utilization', '<', 100)->count(),
            'groups_under_capacity' => $capacityData->where('utilization', '<', 50)->count(),
            'average_utilization' => $capacityData->avg('utilization'),
            'capacity_details' => $capacityData->values(),
        ];
    }

    /**
     * Get growth trends for the last 6 months.
     */
    private function getGrowthTrends($query): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();
            
            $groupsCreated = (clone $query)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();
            
            $months[] = [
                'month' => $date->format('Y-m'),
                'month_name' => $date->format('F Y'),
                'groups_created' => $groupsCreated,
            ];
        }

        return $months;
    }

    /**
     * Get member engagement metrics.
     */
    private function getMemberEngagementMetrics($query): array
    {
        $groups = $query->with(['members'])->get();
        
        $engagementData = $groups->map(function ($group) {
            $memberCount = $group->members->count();
            
            return [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'member_count' => $memberCount,
                'engagement_score' => $this->calculateEngagementScore($group),
            ];
        });

        return [
            'highly_engaged_groups' => $engagementData->where('engagement_score', '>=', 80)->count(),
            'moderately_engaged_groups' => $engagementData->where('engagement_score', '>=', 50)->where('engagement_score', '<', 80)->count(),
            'low_engagement_groups' => $engagementData->where('engagement_score', '<', 50)->count(),
            'average_engagement_score' => $engagementData->avg('engagement_score'),
            'engagement_details' => $engagementData->values(),
        ];
    }

    /**
     * Get leader effectiveness metrics.
     */
    private function getLeaderEffectivenessMetrics($query): array
    {
        $groupsWithLeaders = $query->whereNotNull('leader_id')
            ->with(['leader', 'members'])
            ->get();
        
        $leaderData = $groupsWithLeaders->map(function ($group) {
            return [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'leader_name' => $group->leader->name ?? 'Unknown',
                'member_count' => $group->members->count(),
                'effectiveness_score' => $this->calculateLeaderEffectivenessScore($group),
            ];
        });

        return [
            'highly_effective_leaders' => $leaderData->where('effectiveness_score', '>=', 80)->count(),
            'moderately_effective_leaders' => $leaderData->where('effectiveness_score', '>=', 50)->where('effectiveness_score', '<', 80)->count(),
            'leaders_needing_support' => $leaderData->where('effectiveness_score', '<', 50)->count(),
            'average_effectiveness_score' => $leaderData->avg('effectiveness_score'),
            'leader_details' => $leaderData->values(),
        ];
    }

    /**
     * Get retention metrics.
     */
    private function getRetentionMetrics($query): array
    {
        // This is a simplified version - in a real application, you'd track member join/leave dates
        $groups = $query->with('members')->get();
        
        return [
            'stable_groups' => $groups->where('status', 'active')->count(),
            'groups_at_risk' => $groups->filter(function ($group) {
                return $group->members->count() < 3; // Groups with very few members might be at risk
            })->count(),
            'average_group_longevity' => $groups->avg(function ($group) {
                return $group->created_at->diffInMonths(now());
            }),
        ];
    }

    /**
     * Calculate engagement score for a group (simplified algorithm).
     */
    private function calculateEngagementScore($group): float
    {
        $score = 0;
        
        // Base score from member count (max 40 points)
        $memberCount = $group->members->count();
        $score += min(40, $memberCount * 8);
        
        // Leader presence (20 points)
        if ($group->leader_id) {
            $score += 20;
        }
        
        // Active status (20 points)
        if ($group->status === 'active') {
            $score += 20;
        }
        
        // Regular meeting schedule (10 points)
        if ($group->meeting_day && $group->meeting_time) {
            $score += 10;
        }
        
        // Location specified (10 points)
        if ($group->location) {
            $score += 10;
        }
        
        return min(100, $score);
    }

    /**
     * Calculate leader effectiveness score (simplified algorithm).
     */
    private function calculateLeaderEffectivenessScore($group): float
    {
        $score = 0;
        $memberCount = $group->members->count();
        
        // Group size management (40 points)
        if ($memberCount >= 5 && $memberCount <= 12) {
            $score += 40;
        } elseif ($memberCount >= 3 && $memberCount < 15) {
            $score += 25;
        } elseif ($memberCount > 0) {
            $score += 10;
        }
        
        // Group activity (30 points)
        if ($group->status === 'active') {
            $score += 30;
        }
        
        // Meeting consistency (20 points)
        if ($group->meeting_day && $group->meeting_time) {
            $score += 20;
        }
        
        // Group longevity (10 points)
        $monthsActive = $group->created_at->diffInMonths(now());
        if ($monthsActive >= 6) {
            $score += 10;
        } elseif ($monthsActive >= 3) {
            $score += 5;
        }
        
        return min(100, $score);
    }
}

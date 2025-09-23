<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AdminMemberRequest;
use App\Models\Member;
use App\Models\Department;
use App\Models\SmallGroup;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

final class MemberController extends Controller
{
    use AuthorizesRequests;
    /**
     * Get member statistics for dashboard.
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $branchId = null;

            // For non-super admins, limit to their branch
            if (!$user->isSuperAdmin()) {
                $userBranch = $user->getPrimaryBranch();
                if ($userBranch) {
                    $branchId = $userBranch->id;
                }
            } else {
                // Super admin can filter by branch if provided
                $branchId = $request->get('branch_id');
            }

            // Base query with branch filter
            $baseQuery = Member::query();
            if ($branchId) {
                $baseQuery->where('branch_id', $branchId);
            }

            // Get basic counts - create fresh queries for each
            $totalMembers = (clone $baseQuery)->count();
            $visitorCount = (clone $baseQuery)->where('member_status', 'visitor')->count();
            $leaderCount = (clone $baseQuery)->where('member_status', 'leader')->count();
            $volunteerCount = (clone $baseQuery)->where('member_status', 'volunteer')->count();

            // TECI Status breakdown
            $teciStats = (clone $baseQuery)
                ->selectRaw('teci_status, COUNT(*) as count')
                ->groupBy('teci_status')
                ->pluck('count', 'teci_status')
                ->toArray();

            // Leadership Training breakdown
            $trainingStats = (clone $baseQuery)
                ->whereNotNull('leadership_trainings')
                ->where('leadership_trainings', '!=', '[]')
                ->where('leadership_trainings', '!=', '')
                ->get()
                ->flatMap(function ($member) {
                    $trainings = is_string($member->leadership_trainings) 
                        ? json_decode($member->leadership_trainings, true) 
                        : $member->leadership_trainings;
                    return is_array($trainings) && !empty($trainings) ? $trainings : [];
                })
                ->countBy()
                ->toArray();

            // Debug logging
            \Log::info('Member Statistics Debug', [
                'total_members' => $totalMembers,
                'visitor_count' => $visitorCount,
                'leader_count' => $leaderCount,
                'volunteer_count' => $volunteerCount,
                'teci_stats' => $teciStats,
                'training_stats' => $trainingStats,
                'branch_id' => $branchId
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'total_members' => $totalMembers,
                    'visitor_count' => $visitorCount,
                    'leader_count' => $leaderCount,
                    'volunteer_count' => $volunteerCount,
                    'teci_stats' => [
                        'not_started' => $teciStats['not_started'] ?? 0,
                        '100_level' => $teciStats['100_level'] ?? 0,
                        '200_level' => $teciStats['200_level'] ?? 0,
                        '300_level' => $teciStats['300_level'] ?? 0,
                        '400_level' => $teciStats['400_level'] ?? 0,
                        '500_level' => $teciStats['500_level'] ?? 0,
                        'graduated' => $teciStats['graduated'] ?? 0,
                        'paused' => $teciStats['paused'] ?? 0,
                    ],
                    'training_stats' => [
                        'ELP' => $trainingStats['ELP'] ?? 0,
                        'MLCC' => $trainingStats['MLCC'] ?? 0,
                        'MLCP Basic' => $trainingStats['MLCP Basic'] ?? 0,
                        'MLCP Advanced' => $trainingStats['MLCP Advanced'] ?? 0,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching member statistics', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch member statistics'
            ], 500);
        }
    }

    /**
     * Display a listing of members with advanced search and filtering.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Member::class);

        try {
            $query = Member::with([
                'branch:id,name',
                'user:id,name,email',
                'departments:id,name,ministry_id',
                'departments.ministry:id,name',
                'smallGroups:id,name',
                'ledMinistries:id,name',
                'ledDepartments:id,name',
                'ledSmallGroups:id,name'
            ]);

            // Apply branch-based filtering for non-super admins
            $user = auth()->user();
            if (!$user->isSuperAdmin()) {
                $userBranch = $user->getPrimaryBranch();
                if ($userBranch) {
                    $query->where('branch_id', $userBranch->id);
                }
                
                Log::info('Member API Debug - Branch filtering', [
                    'user_id' => $user->id,
                    'is_super_admin' => $user->isSuperAdmin(),
                    'user_branch_id' => $userBranch ? $userBranch->id : null,
                    'user_branch_name' => $userBranch ? $userBranch->name : null,
                    'request_params' => $request->all(),
                ]);
            }

            // Apply filters
            $this->applyFilters($query, $request, $user);

            // Apply search
            $this->applySearch($query, $request);

            // Apply sorting
            $this->applySorting($query, $request);

            // Paginate results
            // Allow larger pagination for assignment scenarios (when exclude_department is used)
            $maxPerPage = $request->has('exclude_department') ? 1000 : 100;
            $perPage = min($request->get('per_page', 15), $maxPerPage);
            $members = $query->paginate($perPage);

            // Add debug logging for member results
            Log::info('Member API Debug - Query results', [
                'total_members' => $members->total(),
                'current_page_count' => $members->count(),
                'per_page' => $members->perPage(),
                'max_per_page_allowed' => $maxPerPage,
                'is_assignment_request' => $request->has('exclude_department'),
                'exclude_department' => $request->get('exclude_department'),
                'member_names' => $members->pluck('name')->toArray(),
                'member_branch_ids' => $members->pluck('branch_id')->toArray(),
            ]);

            // Add computed fields
            $members->getCollection()->transform(function ($member) {
                $member->age = $member->age;
                $member->is_leader = $member->isLeader();
                $member->is_volunteer = $member->isVolunteer();
                $member->leadership_roles = $this->getLeadershipRoles($member);
                return $member;
            });

            return response()->json([
                'success' => true,
                'data' => $members,
                'message' => 'Members retrieved successfully.',
                'filters' => $this->getAvailableFilters($user),
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving members: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve members.',
            ], 500);
        }
    }

    /**
     * Store a newly created member.
     */
    public function store(AdminMemberRequest $request): JsonResponse
    {
        Gate::authorize('create', Member::class);

        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data = $this->normalizeMemberPayload($data);
            
            // For non-super admins, ensure they can only assign members to their own branch
            $user = auth()->user();
            if (!$user->isSuperAdmin()) {
                $userBranch = $user->getPrimaryBranch();
                if ($userBranch) {
                    $data['branch_id'] = $userBranch->id;
                }
            }

            // Create user account automatically if email is provided and no user_id is set
            if (!empty($data['email']) && empty($data['user_id'])) {
                $userData = $this->createUserAccount($data);
                if ($userData) {
                    $data['user_id'] = $userData['user_id'];
                }
            }

            $member = Member::create($data);
            
            // Load relationships for response
            $member->load([
                'branch:id,name',
                'user:id,name,email',
                'departments:id,name',
                'smallGroups:id,name'
            ]);

            DB::commit();

            Log::info('Member created successfully', [
                'member_id' => $member->id,
                'member_name' => $member->name,
                'branch_id' => $member->branch_id,
                'user_created' => !empty($userData),
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $member,
                'message' => 'Member created successfully.' . (!empty($userData) ? ' User account created with temporary password.' : ''),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error creating member: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create member.',
            ], 500);
        }
    }

    /**
     * Display the specified member.
     */
    public function show(Member $member): JsonResponse
    {
        Gate::authorize('view', $member);

        try {
            $member->load([
                'branch:id,name,venue,phone,email',
                'user:id,name,email',
                'departments' => function ($query) {
                    $query->with(['ministry:id,name'])
                          ->withPivot('assigned_at');
                },
                'smallGroups' => function ($query) {
                    $query->withPivot('joined_at');
                },
                'ledMinistries:id,name,description,status',
                'ledDepartments' => function ($query) {
                    $query->with(['ministry:id,name']);
                },
                'ledSmallGroups:id,name,description,meeting_day,meeting_time',
                'eventRegistrations' => function ($query) {
                    $query->with(['event:id,name,start_date,location'])
                          ->latest()
                          ->limit(10);
                }
            ]);

            // Add computed fields
            $member->age = $member->age;
            $member->is_leader = $member->isLeader();
            $member->is_volunteer = $member->isVolunteer();
            $member->leadership_roles = $this->getLeadershipRoles($member);

            return response()->json([
                'success' => true,
                'data' => $member,
                'message' => 'Member retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving member: ' . $e->getMessage(), [
                'member_id' => $member->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve member.',
            ], 500);
        }
    }

    /**
     * Update the specified member.
     */
    public function update(AdminMemberRequest $request, Member $member): JsonResponse
    {
        Gate::authorize('update', $member);

        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data = $this->normalizeMemberPayload($data);
            $oldStatus = $member->member_status;
            
            // For non-super admins, ensure they can only assign members to their own branch
            $user = auth()->user();
            if (!$user->isSuperAdmin()) {
                $userBranch = $user->getPrimaryBranch();
                if ($userBranch) {
                    $data['branch_id'] = $userBranch->id;
                }
            }

            $member->update($data);

            // Update status based on assignments if status changed
            if ($oldStatus !== $member->member_status) {
                $member->updateStatusBasedOnAssignments();
            }

            // Load relationships for response
            $member->load([
                'branch:id,name',
                'user:id,name,email',
                'departments:id,name',
                'smallGroups:id,name'
            ]);

            DB::commit();

            Log::info('Member updated successfully', [
                'member_id' => $member->id,
                'member_name' => $member->name,
                'branch_id' => $member->branch_id,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $member,
                'message' => 'Member updated successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error updating member: ' . $e->getMessage(), [
                'member_id' => $member->id,
                'user_id' => auth()->id(),
                'request_data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update member.',
            ], 500);
        }
    }

    /**
     * Remove the specified member.
     */
    public function destroy(Member $member): JsonResponse
    {
        Gate::authorize('delete', $member);

        try {
            // Check if member has leadership roles
            $hasLeadershipRoles = $member->ledMinistries()->exists() ||
                                 $member->ledDepartments()->exists() ||
                                 $member->ledSmallGroups()->exists();

            if ($hasLeadershipRoles) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete member with active leadership roles.',
                    'details' => [
                        'led_ministries_count' => $member->ledMinistries()->count(),
                        'led_departments_count' => $member->ledDepartments()->count(),
                        'led_small_groups_count' => $member->ledSmallGroups()->count(),
                    ],
                ], 422);
            }

            $memberName = $member->name;
            $member->delete();

            Log::info('Member deleted successfully', [
                'member_name' => $memberName,
                'deleted_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Member deleted successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting member: ' . $e->getMessage(), [
                'member_id' => $member->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete member.',
            ], 500);
        }
    }

    /**
     * Assign member to departments.
     */
    public function assignToDepartments(Request $request, Member $member): JsonResponse
    {
        Gate::authorize('update', $member);

        $request->validate([
            'department_ids' => 'required|array',
            'department_ids.*' => 'exists:departments,id',
        ]);

        try {
            $departments = Department::whereIn('id', $request->department_ids)->get();
            
            // Sync departments with current timestamp
            $syncData = [];
            foreach ($request->department_ids as $departmentId) {
                $syncData[$departmentId] = ['assigned_at' => now()];
            }
            
            $member->departments()->sync($syncData);
            $member->updateStatusBasedOnAssignments();
            
            $member->load(['departments:id,name', 'departments.ministry:id,name']);

            Log::info('Member assigned to departments', [
                'member_id' => $member->id,
                'department_ids' => $request->department_ids,
                'assigned_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $member,
                'message' => 'Member assigned to departments successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error assigning member to departments: ' . $e->getMessage(), [
                'member_id' => $member->id,
                'department_ids' => $request->department_ids,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign member to departments.',
            ], 500);
        }
    }

    /**
     * Assign member to small groups.
     */
    public function assignToSmallGroups(Request $request, Member $member): JsonResponse
    {
        Gate::authorize('update', $member);

        $request->validate([
            'small_group_ids' => 'required|array',
            'small_group_ids.*' => 'exists:small_groups,id',
        ]);

        try {
            $smallGroups = SmallGroup::whereIn('id', $request->small_group_ids)->get();
            
            // Sync small groups with current timestamp
            $syncData = [];
            foreach ($request->small_group_ids as $smallGroupId) {
                $syncData[$smallGroupId] = ['joined_at' => now()];
            }
            
            $member->smallGroups()->sync($syncData);
            $member->load(['smallGroups:id,name']);

            Log::info('Member assigned to small groups', [
                'member_id' => $member->id,
                'small_group_ids' => $request->small_group_ids,
                'assigned_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $member,
                'message' => 'Member assigned to small groups successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error assigning member to small groups: ' . $e->getMessage(), [
                'member_id' => $member->id,
                'small_group_ids' => $request->small_group_ids,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign member to small groups.',
            ], 500);
        }
    }

    /**
     * Update member growth level.
     */
    public function updateGrowthLevel(Request $request, Member $member): JsonResponse
    {
        Gate::authorize('updateGrowthLevel', $member);

        $request->validate([
            'growth_level' => 'required|in:core,pastor,growing,new_believer',
        ]);

        try {
            $member->update(['growth_level' => $request->growth_level]);

            Log::info('Member growth level updated', [
                'member_id' => $member->id,
                'growth_level' => $request->growth_level,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $member,
                'message' => 'Growth level updated successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating member growth level: ' . $e->getMessage(), [
                'member_id' => $member->id,
                'growth_level' => $request->growth_level,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update growth level.',
            ], 500);
        }
    }

    /**
     * Update member TECI progress.
     */
    public function updateTeciProgress(Request $request, Member $member): JsonResponse
    {
        Gate::authorize('updateTeciProgress', $member);

        $request->validate([
            'teci_status' => 'required|in:not_started,100_level,200_level,300_level,400_level,500_level,graduated,paused',
        ]);

        try {
            $member->update(['teci_status' => $request->teci_status]);

            Log::info('Member TECI progress updated', [
                'member_id' => $member->id,
                'teci_status' => $request->teci_status,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $member,
                'message' => 'TECI progress updated successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating member TECI progress: ' . $e->getMessage(), [
                'member_id' => $member->id,
                'teci_status' => $request->teci_status,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update TECI progress.',
            ], 500);
        }
    }

    /**
     * Apply filters to the query.
     */
    private function applyFilters($query, Request $request, $user): void
    {
        // Branch filter (only for super admins)
        if ($request->filled('branch_id') && $user->isSuperAdmin()) {
            $query->where('branch_id', $request->branch_id);
        }

        // Member status filter
        if ($request->filled('member_status')) {
            $query->where('member_status', $request->member_status);
        }

        // Gender filter
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Marital status filter
        if ($request->filled('marital_status')) {
            $query->where('marital_status', $request->marital_status);
        }

        // Growth level filter
        if ($request->filled('growth_level')) {
            $query->where('growth_level', $request->growth_level);
        }

        // TECI status filter
        if ($request->filled('teci_status')) {
            $query->where('teci_status', $request->teci_status);
        }

        // Age range filter
        if ($request->filled('min_age') || $request->filled('max_age')) {
            $minAge = $request->get('min_age');
            $maxAge = $request->get('max_age');
            
            if ($minAge) {
                $maxDate = now()->subYears($minAge)->format('Y-m-d');
                $query->where('date_of_birth', '<=', $maxDate);
            }
            
            if ($maxAge) {
                $minDate = now()->subYears($maxAge + 1)->format('Y-m-d');
                $query->where('date_of_birth', '>', $minDate);
            }
        }

        // Ministry involvement filter
        if ($request->filled('ministry_id')) {
            $query->whereHas('departments.ministry', function ($q) use ($request) {
                $q->where('id', $request->ministry_id);
            });
        }

        // Department involvement filter
        if ($request->filled('department_id')) {
            $query->whereHas('departments', function ($q) use ($request) {
                $q->where('departments.id', $request->department_id);
            });
        }

        // Small group involvement filter
        if ($request->filled('small_group_id')) {
            $query->whereHas('smallGroups', function ($q) use ($request) {
                $q->where('small_groups.id', $request->small_group_id);
            });
        }

        // Exclude department filter (for member assignment)
        if ($request->filled('exclude_department')) {
            $query->whereDoesntHave('departments', function ($q) use ($request) {
                $q->where('departments.id', $request->exclude_department);
            });
        }

        // Leadership roles filter
        if ($request->filled('has_leadership_role')) {
            if ($request->boolean('has_leadership_role')) {
                $query->where(function ($q) {
                    $q->whereHas('ledMinistries')
                      ->orWhereHas('ledDepartments')
                      ->orWhereHas('ledSmallGroups');
                });
            } else {
                $query->whereDoesntHave('ledMinistries')
                     ->whereDoesntHave('ledDepartments')
                     ->whereDoesntHave('ledSmallGroups');
            }
        }

        // Date joined range filter
        if ($request->filled('joined_from') || $request->filled('joined_to')) {
            if ($request->filled('joined_from')) {
                $query->where('date_joined', '>=', $request->joined_from);
            }
            if ($request->filled('joined_to')) {
                $query->where('date_joined', '<=', $request->joined_to);
            }
        }
    }

    /**
     * Apply search to the query.
     */
    private function applySearch($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('occupation', 'like', "%{$search}%")
                  ->orWhere('nearest_bus_stop', 'like', "%{$search}%");
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

        $allowedSortFields = [
            'name', 'email', 'phone', 'date_of_birth', 'date_joined',
            'member_status', 'growth_level', 'teci_status', 'created_at'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('name', 'asc');
        }
    }

    /**
     * Get leadership roles for a member.
     */
    private function getLeadershipRoles(Member $member): array
    {
        $roles = [];

        foreach ($member->ledMinistries as $ministry) {
            $roles[] = [
                'type' => 'ministry',
                'id' => $ministry->id,
                'name' => $ministry->name,
                'title' => 'Ministry Leader'
            ];
        }

        foreach ($member->ledDepartments as $department) {
            $roles[] = [
                'type' => 'department',
                'id' => $department->id,
                'name' => $department->name,
                'title' => 'Department Leader',
                'ministry' => $department->ministry->name ?? null
            ];
        }

        foreach ($member->ledSmallGroups as $smallGroup) {
            $roles[] = [
                'type' => 'small_group',
                'id' => $smallGroup->id,
                'name' => $smallGroup->name,
                'title' => 'Small Group Leader'
            ];
        }

        return $roles;
    }

    /**
     * Get available filter options.
     */
    private function getAvailableFilters($user): array
    {
        $filters = [
            'member_status' => ['visitor', 'member', 'volunteer', 'leader', 'minister'],
            'gender' => ['male', 'female'],
            'marital_status' => ['single', 'married', 'divorced', 'separated', 'widowed', 'in_a_relationship', 'engaged'],
            'growth_level' => ['core', 'pastor', 'growing', 'new_believer'],
            'teci_status' => [
                'not_started', '100_level', '200_level', '300_level',
                '400_level', '500_level', 'graduated', 'paused'
            ],
        ];

        // Add branch filter for super admins
        if ($user->isSuperAdmin()) {
            $filters['branches'] = \App\Models\Branch::select('id', 'name')->get();
        }

        return $filters;
    }

    /**
     * Create a user account for a member.
     */
    private function createUserAccount(array $memberData): ?array
    {
        try {
            // Check if user with this email already exists
            $existingUser = \App\Models\User::where('email', $memberData['email'])->first();
            if ($existingUser) {
                Log::warning('User account already exists for email', [
                    'email' => $memberData['email'],
                    'existing_user_id' => $existingUser->id,
                ]);
                return ['user_id' => $existingUser->id];
            }

            // Generate a temporary password
            $tempPassword = 'Church' . rand(1000, 9999);

            // Create the user account
            $user = \App\Models\User::create([
                'name' => trim(($memberData['first_name'] ?? '') . ' ' . ($memberData['surname'] ?? '')),
                'email' => $memberData['email'],
                'password' => bcrypt($tempPassword),
                'email_verified_at' => now(), // Auto-verify for church members
            ]);

            // Assign member role
            $memberRole = \App\Models\Role::where('name', 'member')->first();
            if ($memberRole) {
                $user->roles()->attach($memberRole->id);
            } else {
                Log::warning('Member role not found when creating user account', [
                    'email' => $memberData['email'],
                ]);
            }

            Log::info('User account created for member', [
                'user_id' => $user->id,
                'email' => $user->email,
                'temp_password' => $tempPassword,
            ]);

            // TODO: Send welcome email with login credentials
            // This could be implemented later with a notification/email job

            return [
                'user_id' => $user->id,
                'temp_password' => $tempPassword,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to create user account for member', [
                'email' => $memberData['email'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Change member status with history tracking.
     */
    public function changeStatus(Request $request, Member $member): JsonResponse
    {
        // Authorize the action
        $this->authorize('update', $member);

        // Validate the request
        $validated = $request->validate([
            'status' => 'required|in:visitor,member,volunteer,leader,minister',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $changed = $member->changeStatus(
                $validated['status'],
                $validated['reason'] ?? null,
                $validated['notes'] ?? null,
                auth()->id()
            );

            if (!$changed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member is already in the requested status.',
                ], 400);
            }

            Log::info('Member status changed', [
                'member_id' => $member->id,
                'member_name' => $member->name,
                'new_status' => $validated['status'],
                'reason' => $validated['reason'] ?? null,
                'changed_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Member status updated successfully.',
                'data' => [
                    'member' => $member->fresh(['branch', 'statusHistory.changedBy']),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to change member status', [
                'member_id' => $member->id,
                'requested_status' => $validated['status'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update member status.',
            ], 500);
        }
    }

    /**
     * Get member status history.
     */
    public function getStatusHistory(Member $member): JsonResponse
    {
        // Authorize the action
        $this->authorize('view', $member);

        $history = $member->statusHistory()
            ->with('changedBy:id,name')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Get member status statistics.
     */
    public function getStatusStatistics(Request $request): JsonResponse
    {
        // Authorize the action (only super admins and branch pastors can view statistics)
        $this->authorize('viewAny', Member::class);

        $user = auth()->user();
        
        // Build base query with branch filtering
        $query = Member::query();
        
        // Apply branch filtering based on user role
        if (!$user->isSuperAdmin()) {
            $userBranches = $user->pastoredBranches()->pluck('id');
            $query->whereIn('branch_id', $userBranches);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Get status counts
        $statusCounts = $query->selectRaw('member_status, COUNT(*) as count')
            ->groupBy('member_status')
            ->pluck('count', 'member_status')
            ->toArray();

        // Get recent status changes (last 30 days)
        $recentChangesQuery = \App\Models\MemberStatusHistory::query()
            ->with(['member.branch', 'changedBy:id,name'])
            ->where('changed_at', '>=', now()->subDays(30));

        // Apply branch filtering to status history
        if (!$user->isSuperAdmin()) {
            $userBranches = $user->pastoredBranches()->pluck('id');
            $recentChangesQuery->whereHas('member', function ($q) use ($userBranches) {
                $q->whereIn('branch_id', $userBranches);
            });
        } elseif ($request->filled('branch_id')) {
            $recentChangesQuery->whereHas('member', function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        }

        $recentChanges = $recentChangesQuery->orderBy('changed_at', 'desc')
            ->limit(20)
            ->get();

        // Get status distribution by branch (for super admins)
        $branchDistribution = [];
        if ($user->isSuperAdmin() && !$request->filled('branch_id')) {
            $branchDistribution = Member::join('branches', 'members.branch_id', '=', 'branches.id')
                ->selectRaw('branches.name as branch_name, member_status, COUNT(*) as count')
                ->groupBy('branches.name', 'member_status')
                ->get()
                ->groupBy('branch_name')
                ->map(function ($items) {
                    return $items->pluck('count', 'member_status')->toArray();
                })
                ->toArray();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'status_counts' => $statusCounts,
                'recent_changes' => $recentChanges,
                'branch_distribution' => $branchDistribution,
                'total_members' => array_sum($statusCounts),
            ],
        ]);
    }

    /**
     * Bulk update member statuses.
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        // Validate the request
        $validated = $request->validate([
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'required|integer',
            'status' => 'required|in:visitor,member,volunteer,leader,minister',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $updatedCount = 0;
        $errors = [];

        try {
            foreach ($validated['member_ids'] as $memberId) {
                $member = Member::find($memberId);
                
                if (!$member) {
                    $errors[] = "Member with ID {$memberId} not found.";
                    continue;
                }

                // Check authorization for each member
                if (!$user->can('update', $member)) {
                    $errors[] = "Not authorized to update member: {$member->name}";
                    continue;
                }

                $changed = $member->changeStatus(
                    $validated['status'],
                    $validated['reason'] ?? null,
                    $validated['notes'] ?? null,
                    auth()->id()
                );

                if ($changed) {
                    $updatedCount++;
                }
            }

            Log::info('Bulk member status update completed', [
                'updated_count' => $updatedCount,
                'total_requested' => count($validated['member_ids']),
                'new_status' => $validated['status'],
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$updatedCount} member(s).",
                'data' => [
                    'updated_count' => $updatedCount,
                    'total_requested' => count($validated['member_ids']),
                    'errors' => $errors,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to bulk update member statuses', [
                'member_ids' => $validated['member_ids'],
                'requested_status' => $validated['status'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update member statuses.',
                'data' => [
                    'updated_count' => $updatedCount,
                    'errors' => $errors,
                ],
            ], 500);
        }
    }

    private function normalizeMemberPayload(array $data): array
    {
        $dateFields = [
            'date_of_birth', 'anniversary', 'date_joined', 'date_attended_membership_class'
        ];
        foreach ($dateFields as $field) {
            if (array_key_exists($field, $data) && ($data[$field] === '' || $data[$field] === null)) {
                $data[$field] = null;
            }
        }

        // Map growth_level from UI to DB where needed
        $growthMap = [
            'new_believer' => 'new_believer',
            'growing' => 'growing',
            'core' => 'core',
            'pastor' => 'pastor',
            'mature' => 'pastor', // fallback mapping if legacy UI sends 'mature'
        ];
        if (isset($data['growth_level'])) {
            $data['growth_level'] = $growthMap[$data['growth_level']] ?? $data['growth_level'];
        }

        // Ensure leadership_trainings is array
        if (isset($data['leadership_trainings']) && is_string($data['leadership_trainings'])) {
            $decoded = json_decode($data['leadership_trainings'], true);
            $data['leadership_trainings'] = is_array($decoded) ? $decoded : [];
        }

        // Keep name in sync when first/surname provided
        if (!empty($data['first_name']) || !empty($data['surname'])) {
            $first = trim($data['first_name'] ?? '');
            $last = trim($data['surname'] ?? '');
            $data['name'] = trim($first.' '.$last) ?: ($data['name'] ?? '');
        }

        return $data;
    }
} 
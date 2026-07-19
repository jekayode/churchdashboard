<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Controller;
use App\Http\Resources\MemberSmallGroupResource;
use App\Models\SmallGroup;
use App\Models\SmallGroupJoinRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SmallGroupController extends Controller
{
    use ResolvesCurrentMember;

    /**
     * The groups the authenticated member belongs to.
     */
    public function index(Request $request): JsonResponse
    {
        $member = $this->currentMember($request);

        $groups = $member->smallGroups()
            ->with(['leader', 'members'])
            ->withCount('members')
            ->get();

        return response()->json([
            'success' => true,
            'data' => MemberSmallGroupResource::collection($groups),
        ]);
    }

    /**
     * Active groups in the member's branch they could join.
     */
    public function available(Request $request): JsonResponse
    {
        $member = $this->currentMember($request);

        $currentGroupIds = $member->smallGroups()->pluck('small_groups.id')->all();

        $requestStatuses = SmallGroupJoinRequest::query()
            ->where('member_id', $member->id)
            ->pluck('status', 'small_group_id');

        $groups = SmallGroup::query()
            ->where('branch_id', $member->branch_id)
            ->where('status', 'active')
            ->whereNotIn('id', $currentGroupIds)
            ->with('leader')
            ->withCount('members')
            ->orderBy('name')
            ->get()
            ->each(function (SmallGroup $group) use ($requestStatuses): void {
                $group->join_request_status = $requestStatuses[$group->id] ?? null;
            });

        return response()->json([
            'success' => true,
            'data' => MemberSmallGroupResource::collection($groups),
        ]);
    }

    /**
     * Request to join a small group. A leader must approve before the member
     * is actually added to the group.
     */
    public function requestToJoin(Request $request, SmallGroup $smallGroup): JsonResponse
    {
        $member = $this->currentMember($request);

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($smallGroup->branch_id !== $member->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'This group is not available for your branch.',
            ], 403);
        }

        if ($smallGroup->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This group is not accepting new members.',
            ], 422);
        }

        $alreadyMember = $member->smallGroups()
            ->where('small_groups.id', $smallGroup->id)
            ->exists();

        if ($alreadyMember) {
            return response()->json([
                'success' => false,
                'message' => 'You are already a member of this group.',
            ], 409);
        }

        $pending = SmallGroupJoinRequest::query()
            ->where('small_group_id', $smallGroup->id)
            ->where('member_id', $member->id)
            ->pending()
            ->exists();

        if ($pending) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending request for this group.',
            ], 409);
        }

        $joinRequest = SmallGroupJoinRequest::create([
            'small_group_id' => $smallGroup->id,
            'member_id' => $member->id,
            'status' => SmallGroupJoinRequest::STATUS_PENDING,
            'message' => $validated['message'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your request to join has been sent to the group leader.',
            'data' => [
                'join_request_id' => $joinRequest->id,
                'small_group_id' => $smallGroup->id,
                'status' => $joinRequest->status,
            ],
        ], 201);
    }

    /**
     * The authenticated member's own join requests.
     */
    public function joinRequests(Request $request): JsonResponse
    {
        $member = $this->currentMember($request);

        $requests = SmallGroupJoinRequest::query()
            ->where('member_id', $member->id)
            ->with('smallGroup:id,name')
            ->latest()
            ->get()
            ->map(fn (SmallGroupJoinRequest $joinRequest): array => [
                'id' => $joinRequest->id,
                'status' => $joinRequest->status,
                'message' => $joinRequest->message,
                'response_note' => $joinRequest->response_note,
                'created_at' => $joinRequest->created_at?->toIso8601String(),
                'reviewed_at' => $joinRequest->reviewed_at?->toIso8601String(),
                'small_group' => [
                    'id' => $joinRequest->smallGroup?->id,
                    'name' => $joinRequest->smallGroup?->name,
                ],
            ]);

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }
}

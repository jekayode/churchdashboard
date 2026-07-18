<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmallGroup;
use App\Models\SmallGroupJoinRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Leadership-side review of member requests to join a small group.
 */
final class SmallGroupJoinRequestController extends Controller
{
    /**
     * Pending join requests for groups the user leads (or, for admins, their branch).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $requests = SmallGroupJoinRequest::query()
            ->pending()
            ->whereIn('small_group_id', $this->reviewableGroupIds($user))
            ->with(['member:id,name,email,phone', 'smallGroup:id,name'])
            ->latest()
            ->get()
            ->map(fn (SmallGroupJoinRequest $joinRequest): array => [
                'id' => $joinRequest->id,
                'status' => $joinRequest->status,
                'message' => $joinRequest->message,
                'created_at' => $joinRequest->created_at?->toIso8601String(),
                'member' => [
                    'id' => $joinRequest->member?->id,
                    'name' => $joinRequest->member?->name,
                    'email' => $joinRequest->member?->email,
                    'phone' => $joinRequest->member?->phone,
                ],
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

    /**
     * Approve a request and add the member to the group.
     */
    public function approve(Request $request, SmallGroupJoinRequest $joinRequest): JsonResponse
    {
        if (! $this->canReview($request->user(), $joinRequest)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorised to review this request.',
            ], 403);
        }

        if (! $joinRequest->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'This request has already been reviewed.',
            ], 422);
        }

        $validated = $request->validate([
            'response_note' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($joinRequest, $request, $validated): void {
            $joinRequest->smallGroup->members()->syncWithoutDetaching([
                $joinRequest->member_id => ['joined_at' => now()],
            ]);

            $joinRequest->update([
                'status' => SmallGroupJoinRequest::STATUS_APPROVED,
                'response_note' => $validated['response_note'] ?? null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Request approved and member added to the group.',
        ]);
    }

    /**
     * Decline a request.
     */
    public function decline(Request $request, SmallGroupJoinRequest $joinRequest): JsonResponse
    {
        if (! $this->canReview($request->user(), $joinRequest)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorised to review this request.',
            ], 403);
        }

        if (! $joinRequest->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'This request has already been reviewed.',
            ], 422);
        }

        $validated = $request->validate([
            'response_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $joinRequest->update([
            'status' => SmallGroupJoinRequest::STATUS_DECLINED,
            'response_note' => $validated['response_note'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request declined.',
        ]);
    }

    private function canReview(User $user, SmallGroupJoinRequest $joinRequest): bool
    {
        return in_array(
            $joinRequest->small_group_id,
            $this->reviewableGroupIds($user),
            true,
        );
    }

    /**
     * Groups whose join requests this user may review: groups they lead, plus
     * every group in the branch for pastors and super admins.
     *
     * @return list<int>
     */
    private function reviewableGroupIds(User $user): array
    {
        $query = SmallGroup::query();

        if ($user->isSuperAdmin()) {
            return $query->pluck('id')->all();
        }

        $branchId = $user->getActiveBranchId();

        if ($user->isBranchPastor() && $branchId !== null) {
            return $query->where('branch_id', $branchId)->pluck('id')->all();
        }

        $memberId = $user->member?->id;

        if ($memberId === null) {
            return [];
        }

        return $query->where('leader_id', $memberId)->pluck('id')->all();
    }
}

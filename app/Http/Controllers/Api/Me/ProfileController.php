<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Controller;
use App\Http\Requests\Me\UpdateProfileRequest;
use App\Http\Resources\MemberProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProfileController extends Controller
{
    use ResolvesCurrentMember;

    /**
     * The authenticated member's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $member = $this->currentMember($request)->load('branch');

        return response()->json([
            'success' => true,
            'data' => new MemberProfileResource($member),
        ]);
    }

    /**
     * Update the authenticated member's own profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $member = $this->currentMember($request);

        $member->fill($request->validated());
        $member->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => new MemberProfileResource($member->fresh()->load('branch')),
        ]);
    }
}

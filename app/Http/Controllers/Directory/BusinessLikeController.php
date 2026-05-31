<?php

declare(strict_types=1);

namespace App\Http\Controllers\Directory;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class BusinessLikeController extends Controller
{
    public function toggle(Request $request, Business $business): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless($business->isPubliclyVisible(), 404);

        [$liked, $likesCount] = DB::transaction(function () use ($business, $user) {
            $locked = Business::query()->whereKey($business->id)->lockForUpdate()->first();

            $existing = BusinessLike::query()
                ->where('business_id', $locked->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                $existing->delete();
                $locked->decrement('likes_count');

                return [false, $locked->refresh()->likes_count];
            }

            BusinessLike::query()->create([
                'business_id' => $locked->id,
                'user_id' => $user->id,
            ]);
            $locked->increment('likes_count');

            return [true, $locked->refresh()->likes_count];
        });

        return response()->json([
            'success' => true,
            'data' => ['liked' => $liked, 'likes_count' => $likesCount],
        ]);
    }

    public function favorites(Request $request): JsonResponse
    {
        $user = $request->user();

        $businesses = Business::query()
            ->publiclyVisible()
            ->whereHas('likes', fn ($q) => $q->where('user_id', $user->id))
            ->with('categories:id,name,slug')
            ->paginate(12);

        return response()->json(['success' => true, 'data' => $businesses]);
    }
}

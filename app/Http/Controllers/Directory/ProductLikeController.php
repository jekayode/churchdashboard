<?php

declare(strict_types=1);

namespace App\Http\Controllers\Directory;

use App\Http\Controllers\Controller;
use App\Models\BusinessProduct;
use App\Models\ProductLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ProductLikeController extends Controller
{
    public function toggle(Request $request, BusinessProduct $product): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        [$liked, $likesCount] = DB::transaction(function () use ($product, $user) {
            $locked = BusinessProduct::query()->whereKey($product->id)->lockForUpdate()->first();

            $existing = ProductLike::query()
                ->where('business_product_id', $locked->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                $existing->delete();
                $locked->decrement('likes_count');

                return [false, $locked->refresh()->likes_count];
            }

            ProductLike::query()->create([
                'business_product_id' => $locked->id,
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
}

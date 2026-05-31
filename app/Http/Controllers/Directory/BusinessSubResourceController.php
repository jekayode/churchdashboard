<?php

declare(strict_types=1);

namespace App\Http\Controllers\Directory;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessPost;
use App\Models\BusinessProduct;
use App\Models\BusinessService;
use App\Models\BusinessTeamMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

final class BusinessSubResourceController extends Controller
{
    public function storeService(Request $request, Business $business): JsonResponse
    {
        Gate::authorize('update', $business);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_text' => ['nullable', 'string', 'max:100'],
            'price_text' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store("businesses/{$business->id}/services", 'public');
        }
        unset($data['image']);

        $service = $business->services()->create($data);

        return response()->json(['success' => true, 'data' => $service], 201);
    }

    public function updateService(Request $request, Business $business, BusinessService $service): JsonResponse
    {
        Gate::authorize('update', $business);
        abort_unless($service->business_id === $business->id, 404);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_text' => ['nullable', 'string', 'max:100'],
            'price_text' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            if ($service->image_path) {
                Storage::disk('public')->delete($service->image_path);
            }
            $data['image_path'] = $request->file('image')->store("businesses/{$business->id}/services", 'public');
        }
        unset($data['image']);
        $service->update($data);

        return response()->json(['success' => true, 'data' => $service->fresh()]);
    }

    public function destroyService(Business $business, BusinessService $service): JsonResponse
    {
        Gate::authorize('update', $business);
        abort_unless($service->business_id === $business->id, 404);
        $service->delete();

        return response()->json(['success' => true, 'message' => 'Service deleted.']);
    }

    public function storeProduct(Request $request, Business $business): JsonResponse
    {
        Gate::authorize('update', $business);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price_text' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store("businesses/{$business->id}/products", 'public');
        }
        unset($data['image']);

        $product = $business->products()->create($data);

        return response()->json(['success' => true, 'data' => $product], 201);
    }

    public function updateProduct(Request $request, Business $business, BusinessProduct $product): JsonResponse
    {
        Gate::authorize('update', $business);
        abort_unless($product->business_id === $business->id, 404);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price_text' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store("businesses/{$business->id}/products", 'public');
        }
        unset($data['image']);
        $product->update($data);

        return response()->json(['success' => true, 'data' => $product->fresh()]);
    }

    public function destroyProduct(Business $business, BusinessProduct $product): JsonResponse
    {
        Gate::authorize('update', $business);
        abort_unless($product->business_id === $business->id, 404);
        $product->delete();

        return response()->json(['success' => true, 'message' => 'Product deleted.']);
    }

    public function storePost(Request $request, Business $business): JsonResponse
    {
        Gate::authorize('update', $business);
        $data = $request->validate([
            'body' => ['required', 'string'],
            'published_at' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store("businesses/{$business->id}/posts", 'public');
        }
        unset($data['image']);

        if (empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post = $business->posts()->create($data);

        return response()->json(['success' => true, 'data' => $post], 201);
    }

    public function destroyPost(Business $business, BusinessPost $post): JsonResponse
    {
        Gate::authorize('update', $business);
        abort_unless($post->business_id === $business->id, 404);
        $post->delete();

        return response()->json(['success' => true, 'message' => 'Post deleted.']);
    }

    public function storeTeamMember(Request $request, Business $business): JsonResponse
    {
        Gate::authorize('update', $business);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store("businesses/{$business->id}/team", 'public');
        }
        unset($data['photo']);

        $member = $business->teamMembers()->create($data);

        return response()->json(['success' => true, 'data' => $member], 201);
    }

    public function updateTeamMember(Request $request, Business $business, BusinessTeamMember $teamMember): JsonResponse
    {
        Gate::authorize('update', $business);
        abort_unless($teamMember->business_id === $business->id, 404);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            if ($teamMember->photo_path) {
                Storage::disk('public')->delete($teamMember->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store("businesses/{$business->id}/team", 'public');
        }
        unset($data['photo']);
        $teamMember->update($data);

        return response()->json(['success' => true, 'data' => $teamMember->fresh()]);
    }

    public function destroyTeamMember(Business $business, BusinessTeamMember $teamMember): JsonResponse
    {
        Gate::authorize('update', $business);
        abort_unless($teamMember->business_id === $business->id, 404);
        $teamMember->delete();

        return response()->json(['success' => true, 'message' => 'Team member removed.']);
    }
}

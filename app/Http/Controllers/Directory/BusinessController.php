<?php

declare(strict_types=1);

namespace App\Http\Controllers\Directory;

use App\Enums\BusinessStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Directory\StoreBusinessRequest;
use App\Http\Requests\Directory\UpdateBusinessRequest;
use App\Models\Business;
use App\Models\DirectorySetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class BusinessController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Business::query()->with(['categories:id,name,slug']);

        if ($user->isDirectoryAdmin() && $request->boolean('admin')) {
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
        } else {
            $query->where('owner_user_id', $user->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%"));
        }

        $businesses = $query->latest()->paginate($request->integer('per_page', 15));

        return response()->json(['success' => true, 'data' => $businesses]);
    }

    public function store(StoreBusinessRequest $request): JsonResponse
    {
        $data = $request->validated();
        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);

        $data['owner_user_id'] = $request->user()->id;
        $data['slug'] = $data['slug'] ?? Business::generateSlug($data['name']);
        $data['status'] = BusinessStatus::Draft;

        $business = Business::query()->create($data);
        $business->categories()->sync($categoryIds);

        return response()->json([
            'success' => true,
            'data' => $business->load('categories'),
            'message' => 'Business draft created. Submit when ready.',
        ], 201);
    }

    public function submit(Request $request, Business $business): JsonResponse
    {
        Gate::authorize('update', $business);

        $settings = DirectorySetting::instance();

        $data = [
            'rejection_reason' => null,
        ];

        if ($settings->business_approval_required) {
            $data['status'] = BusinessStatus::PendingReview;
            $data['approved_at'] = null;
            $data['approved_by_user_id'] = null;
        } else {
            $data['status'] = BusinessStatus::Active;
            $data['approved_at'] = now();
            $data['approved_by_user_id'] = null;
        }

        $business->update($data);

        return response()->json([
            'success' => true,
            'data' => $business->fresh(),
            'message' => $settings->business_approval_required
                ? 'Business submitted for admin approval.'
                : 'Business is now active.',
        ]);
    }

    public function show(Business $business): JsonResponse
    {
        Gate::authorize('view', $business);

        $business->load([
            'categories',
            'teamMembers',
            'services' => fn ($q) => $q->where('is_active', true),
            'products' => fn ($q) => $q->where('is_active', true),
            'posts' => fn ($q) => $q->published(),
            'owner:id,name,email',
        ]);

        return response()->json(['success' => true, 'data' => $business]);
    }

    public function update(UpdateBusinessRequest $request, Business $business): JsonResponse
    {
        $data = $request->validated();
        $categoryIds = $data['category_ids'] ?? null;
        unset($data['category_ids']);

        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Business::generateSlug($data['name'], $business->id);
        }

        if (! $request->user()->isDirectoryAdmin()) {
            unset($data['status'], $data['is_featured'], $data['featured_until']);
        }

        $business->update($data);

        if ($categoryIds !== null) {
            $business->categories()->sync($categoryIds);
        }

        return response()->json([
            'success' => true,
            'data' => $business->fresh()->load('categories'),
            'message' => 'Business updated.',
        ]);
    }

    public function destroy(Business $business): JsonResponse
    {
        Gate::authorize('delete', $business);
        $business->delete();

        return response()->json(['success' => true, 'message' => 'Business deleted.']);
    }
}

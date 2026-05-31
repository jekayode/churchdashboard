<?php

declare(strict_types=1);

namespace App\Http\Controllers\Directory\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Directory\StoreCategoryRequest;
use App\Http\Requests\Directory\UpdateCategoryRequest;
use App\Models\DirectoryCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class DirectoryCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', DirectoryCategory::class);

        $categories = DirectoryCategory::query()
            ->withCount('businesses')
            ->when($request->boolean('active_only'), fn ($q) => $q->where('is_active', true))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'data' => $categories]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? DirectoryCategory::generateSlug($data['name']);

        $category = DirectoryCategory::query()->create($data);

        return response()->json(['success' => true, 'data' => $category, 'message' => 'Category created.'], 201);
    }

    public function update(UpdateCategoryRequest $request, DirectoryCategory $category): JsonResponse
    {
        Gate::authorize('update', $category);

        $category->update($request->validated());

        return response()->json(['success' => true, 'data' => $category->fresh(), 'message' => 'Category updated.']);
    }

    public function destroy(DirectoryCategory $category): JsonResponse
    {
        Gate::authorize('delete', $category);

        $category->delete();

        return response()->json(['success' => true, 'message' => 'Category deleted.']);
    }
}

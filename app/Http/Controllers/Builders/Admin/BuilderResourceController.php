<?php

declare(strict_types=1);

namespace App\Http\Controllers\Builders\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Builders\StoreBuilderResourceRequest;
use App\Models\BuilderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BuilderResourceController extends Controller
{
    public function store(StoreBuilderResourceRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $path = $file->store('builders/pack', 'public');

        $resource = BuilderResource::query()->create([
            'title' => $request->validated('title'),
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize() ?: 0,
            'sort_order' => (int) BuilderResource::query()->max('sort_order') + 1,
        ]);

        return response()->json([
            'success' => true,
            'data' => $resource,
            'message' => 'Pack file uploaded.',
        ], 201);
    }

    public function destroy(BuilderResource $resource): JsonResponse
    {
        $resource->deleteFile();
        $resource->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pack file removed.',
        ]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:builder_resources,id'],
        ]);

        foreach ($request->input('order', []) as $index => $id) {
            BuilderResource::query()->whereKey($id)->update(['sort_order' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order updated.',
        ]);
    }
}

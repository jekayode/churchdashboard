<?php

declare(strict_types=1);

namespace App\Http\Controllers\Directory;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class BusinessImageController extends Controller
{
    public function uploadLogo(Request $request, Business $business): JsonResponse
    {
        Gate::authorize('update', $business);

        $request->validate(['logo' => ['required', 'image', 'max:2048']]);
        $business->addMediaFromRequest('logo')->toMediaCollection('logo');

        return response()->json([
            'success' => true,
            'data' => ['url' => $business->getFirstMediaUrl('logo')],
            'message' => 'Logo uploaded.',
        ]);
    }

    public function uploadCover(Request $request, Business $business): JsonResponse
    {
        Gate::authorize('update', $business);

        $request->validate(['cover' => ['required', 'image', 'max:4096']]);
        $business->addMediaFromRequest('cover')->toMediaCollection('cover');

        return response()->json([
            'success' => true,
            'data' => ['url' => $business->getFirstMediaUrl('cover')],
            'message' => 'Cover uploaded.',
        ]);
    }

    public function uploadGallery(Request $request, Business $business): JsonResponse
    {
        Gate::authorize('update', $business);

        $request->validate(['images' => ['required', 'array'], 'images.*' => ['image', 'max:4096']]);

        $existingCount = $business->getMedia('gallery')->count();
        $incomingCount = count($request->file('images', []));
        abort_unless($existingCount + $incomingCount <= 10, 422, 'Max 10 gallery images.');

        foreach ($request->file('images', []) as $image) {
            $business->addMedia($image)->toMediaCollection('gallery');
        }

        return response()->json([
            'success' => true,
            'data' => $business->getMedia('gallery')->map(fn ($m) => $m->getUrl()),
            'message' => 'Gallery images uploaded.',
        ]);
    }

    public function deleteGalleryImage(Business $business, int $mediaId): JsonResponse
    {
        Gate::authorize('update', $business);

        $media = $business->media()
            ->where('id', $mediaId)
            ->where('collection_name', 'gallery')
            ->firstOrFail();
        $media->delete();

        return response()->json(['success' => true, 'message' => 'Image removed.']);
    }
}

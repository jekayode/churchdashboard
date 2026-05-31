<?php

declare(strict_types=1);

namespace App\Http\Controllers\Directory\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Directory\StoreAnnouncementRequest;
use App\Http\Requests\Directory\UpdateAnnouncementRequest;
use App\Models\DirectoryAnnouncement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

final class DirectoryAnnouncementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isDirectoryAdmin(), 403);

        $announcements = DirectoryAnnouncement::query()
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (DirectoryAnnouncement $a) => array_merge($a->toArray(), ['image_url' => $a->image_url]));

        return response()->json(['success' => true, 'data' => $announcements]);
    }

    public function store(StoreAnnouncementRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('directory/announcements', 'public');
        }
        unset($data['image']);

        $announcement = DirectoryAnnouncement::query()->create($data);

        return response()->json([
            'success' => true,
            'data' => array_merge($announcement->toArray(), ['image_url' => $announcement->image_url]),
            'message' => 'Announcement created.',
        ], 201);
    }

    public function update(UpdateAnnouncementRequest $request, DirectoryAnnouncement $announcement): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($announcement->image_path) {
                Storage::disk('public')->delete($announcement->image_path);
            }
            $data['image_path'] = $request->file('image')->store('directory/announcements', 'public');
        } elseif (! empty($data['remove_image']) && $announcement->image_path) {
            Storage::disk('public')->delete($announcement->image_path);
            $data['image_path'] = null;
        }
        unset($data['image'], $data['remove_image']);

        $announcement->update($data);

        return response()->json([
            'success' => true,
            'data' => array_merge($announcement->fresh()->toArray(), ['image_url' => $announcement->fresh()->image_url]),
            'message' => 'Announcement updated.',
        ]);
    }

    public function destroy(Request $request, DirectoryAnnouncement $announcement): JsonResponse
    {
        abort_unless($request->user()?->isDirectoryAdmin(), 403);

        if ($announcement->image_path) {
            Storage::disk('public')->delete($announcement->image_path);
        }
        $announcement->delete();

        return response()->json(['success' => true, 'message' => 'Announcement deleted.']);
    }
}

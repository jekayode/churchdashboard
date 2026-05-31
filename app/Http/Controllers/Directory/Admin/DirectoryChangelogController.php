<?php

declare(strict_types=1);

namespace App\Http\Controllers\Directory\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Directory\StoreChangelogEntryRequest;
use App\Http\Requests\Directory\UpdateChangelogEntryRequest;
use App\Models\DirectoryChangelogEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DirectoryChangelogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isDirectoryAdmin(), 403);

        $entries = DirectoryChangelogEntry::query()
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'data' => $entries]);
    }

    public function store(StoreChangelogEntryRequest $request): JsonResponse
    {
        $entry = DirectoryChangelogEntry::query()->create($request->validated());

        return response()->json(['success' => true, 'data' => $entry, 'message' => 'Changelog entry created.'], 201);
    }

    public function update(UpdateChangelogEntryRequest $request, DirectoryChangelogEntry $changelog): JsonResponse
    {
        $changelog->update($request->validated());

        return response()->json(['success' => true, 'data' => $changelog->fresh(), 'message' => 'Changelog entry updated.']);
    }

    public function destroy(DirectoryChangelogEntry $changelog): JsonResponse
    {
        abort_unless(auth()->user()?->isDirectoryAdmin(), 403);

        $changelog->delete();

        return response()->json(['success' => true, 'message' => 'Changelog entry deleted.']);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Controller;
use App\Http\Resources\SeriesResource;
use App\Http\Resources\SermonResource;
use App\Models\Series;
use App\Models\Sermon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SermonController extends Controller
{
    use ResolvesCurrentMember;

    /**
     * Browse published sermons, filterable by series, speaker and search.
     */
    public function index(Request $request): JsonResponse
    {
        $member = $this->currentMember($request);

        $validated = $request->validate([
            'series_id' => ['nullable', 'integer', 'exists:series,id'],
            'speaker' => ['nullable', 'string', 'max:255'],
            'search' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'in:newest,oldest,title'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $savedIds = $member->savedSermons()->pluck('sermons.id')->all();

        $query = Sermon::query()
            ->published()
            ->forBranch($member->branch_id)
            ->with(['series', 'media'])
            ->when($validated['series_id'] ?? null, fn ($q, $seriesId) => $q->where('series_id', $seriesId))
            ->when($validated['speaker'] ?? null, fn ($q, $speaker) => $q->where('speaker', 'like', '%'.$speaker.'%'))
            ->when($validated['search'] ?? null, function ($q, $search): void {
                $q->where(function ($inner) use ($search): void {
                    $inner->where('title', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('speaker', 'like', '%'.$search.'%');
                });
            });

        $query = match ($validated['sort'] ?? 'newest') {
            'oldest' => $query->orderBy('preached_on'),
            'title' => $query->orderBy('title'),
            default => $query->orderByDesc('preached_on'),
        };

        $sermons = $query->paginate((int) ($validated['per_page'] ?? 20));

        $sermons->getCollection()->transform(function (Sermon $sermon) use ($savedIds): Sermon {
            $sermon->is_saved = in_array($sermon->id, $savedIds, true);

            return $sermon;
        });

        return response()->json([
            'success' => true,
            'data' => SermonResource::collection($sermons->items()),
            'meta' => [
                'current_page' => $sermons->currentPage(),
                'per_page' => $sermons->perPage(),
                'total' => $sermons->total(),
                'last_page' => $sermons->lastPage(),
            ],
        ]);
    }

    /**
     * A single sermon with its passages and slides.
     */
    public function show(Request $request, Sermon $sermon): JsonResponse
    {
        $member = $this->currentMember($request);

        if (! $this->isVisibleTo($sermon, $member->branch_id)) {
            return response()->json([
                'success' => false,
                'message' => 'This sermon is not available.',
            ], 404);
        }

        $sermon->load(['series', 'passages', 'media']);
        $sermon->is_saved = $member->savedSermons()->where('sermons.id', $sermon->id)->exists();

        return response()->json([
            'success' => true,
            'data' => new SermonResource($sermon),
        ]);
    }

    /**
     * Series available to the member, for the Watch tab's series rail.
     */
    public function series(Request $request): JsonResponse
    {
        $member = $this->currentMember($request);

        $series = Series::query()
            ->published()
            ->where(function ($query) use ($member): void {
                $query->whereNull('branch_id')->orWhere('branch_id', $member->branch_id);
            })
            ->withCount(['sermons' => fn ($query) => $query->where('is_published', true)])
            ->orderByDesc('starts_on')
            ->get();

        return response()->json([
            'success' => true,
            'data' => SeriesResource::collection($series),
        ]);
    }

    /**
     * Sermons the member has saved.
     */
    public function saved(Request $request): JsonResponse
    {
        $member = $this->currentMember($request);

        $sermons = $member->savedSermons()
            ->with(['series', 'media'])
            ->orderByDesc('preached_on')
            ->get()
            ->each(fn (Sermon $sermon) => $sermon->is_saved = true);

        return response()->json([
            'success' => true,
            'data' => SermonResource::collection($sermons),
        ]);
    }

    /**
     * Save a sermon for later.
     */
    public function save(Request $request, Sermon $sermon): JsonResponse
    {
        $member = $this->currentMember($request);

        if (! $this->isVisibleTo($sermon, $member->branch_id)) {
            return response()->json([
                'success' => false,
                'message' => 'This sermon is not available.',
            ], 404);
        }

        $member->savedSermons()->syncWithoutDetaching([
            $sermon->id => ['saved_at' => now()],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sermon saved.',
        ]);
    }

    /**
     * Remove a saved sermon.
     */
    public function unsave(Request $request, Sermon $sermon): JsonResponse
    {
        $member = $this->currentMember($request);

        $member->savedSermons()->detach($sermon->id);

        return response()->json([
            'success' => true,
            'message' => 'Sermon removed from saved.',
        ]);
    }

    private function isVisibleTo(Sermon $sermon, ?int $branchId): bool
    {
        if (! $sermon->is_published) {
            return false;
        }

        return $sermon->branch_id === null || $sermon->branch_id === $branchId;
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pastor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pastor\SermonRequest;
use App\Models\Member;
use App\Models\Series;
use App\Models\Sermon;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class SermonController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Sermon::class);

        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();
        $branchId = $user->getActiveBranchId();

        $sermons = Sermon::query()
            ->with('series')
            ->when(! $isSuperAdmin, fn ($query) => $query->where(function ($inner) use ($branchId): void {
                $inner->whereNull('branch_id');

                if ($branchId !== null) {
                    $inner->orWhere('branch_id', $branchId);
                }
            }))
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('title', 'like', '%'.$search.'%')
                        ->orWhere('speaker', 'like', '%'.$search.'%');
                });
            })
            ->when($request->integer('series_id'), fn ($query, $id) => $query->where('series_id', $id))
            ->orderByDesc('preached_on')
            ->paginate(15)
            ->withQueryString();

        return view('pastor.sermons.index', [
            'sermons' => $sermons,
            'seriesList' => $this->availableSeries(),
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Sermon::class);

        return view('pastor.sermons.form', [
            'sermon' => null,
            'seriesList' => $this->availableSeries(),
            'speakers' => $this->availableSpeakers(),
        ]);
    }

    public function edit(Sermon $sermon): View
    {
        $this->authorize('update', $sermon);

        $sermon->load(['passages', 'series', 'media']);

        return view('pastor.sermons.form', [
            'sermon' => $sermon,
            'seriesList' => $this->availableSeries(),
            'speakers' => $this->availableSpeakers(),
        ]);
    }

    public function store(SermonRequest $request): RedirectResponse
    {
        $this->authorize('create', Sermon::class);

        $sermon = DB::transaction(function () use ($request): Sermon {
            $sermon = Sermon::create($this->sermonAttributes($request));

            $this->syncPassages($sermon, $request);
            $this->syncMedia($sermon, $request);

            return $sermon;
        });

        return redirect()
            ->route('pastor.sermons.edit', $sermon)
            ->with('success', 'Sermon created successfully.');
    }

    public function update(SermonRequest $request, Sermon $sermon): RedirectResponse
    {
        $this->authorize('update', $sermon);

        DB::transaction(function () use ($request, $sermon): void {
            $sermon->update($this->sermonAttributes($request, $sermon));

            $this->syncPassages($sermon, $request);
            $this->syncMedia($sermon, $request);
        });

        return redirect()
            ->route('pastor.sermons.edit', $sermon)
            ->with('success', 'Sermon updated successfully.');
    }

    public function destroy(Sermon $sermon): RedirectResponse
    {
        $this->authorize('delete', $sermon);

        $sermon->delete();

        return redirect()
            ->route('pastor.sermons')
            ->with('success', 'Sermon deleted.');
    }

    /**
     * Remove a single uploaded file without touching the rest of the sermon.
     */
    public function destroyMedia(Sermon $sermon, int $mediaId): RedirectResponse
    {
        $this->authorize('update', $sermon);

        $media = $sermon->media()->find($mediaId);

        if ($media !== null) {
            $media->delete();
        }

        return back()->with('success', 'File removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function sermonAttributes(SermonRequest $request, ?Sermon $sermon = null): array
    {
        $validated = $request->validated();
        $user = Auth::user();

        $attributes = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'speaker' => $validated['speaker'],
            'speaker_member_id' => $validated['speaker_member_id'] ?? null,
            'series_id' => $validated['series_id'] ?? null,
            'preached_on' => $validated['preached_on'],
            'duration_seconds' => $validated['duration_seconds'] ?? null,
            'tone' => $validated['tone'] ?? 'orange',
            'is_live' => $validated['is_live'] ?? false,
            'live_url' => $validated['live_url'] ?? null,
            'video_url' => $validated['video_url'] ?? null,
            'is_published' => $validated['is_published'] ?? false,
        ];

        // Branch is set once, from the creating pastor. Super admins create
        // network-wide sermons; existing sermons keep the branch they have.
        if ($sermon === null) {
            $attributes['branch_id'] = $user->isSuperAdmin() ? null : $user->getActiveBranchId();
        }

        return $attributes;
    }

    private function syncPassages(Sermon $sermon, SermonRequest $request): void
    {
        if (! $request->has('passages')) {
            return;
        }

        $sermon->passages()->delete();

        foreach (array_values($request->validated()['passages'] ?? []) as $position => $passage) {
            if (blank($passage['reference'] ?? null)) {
                continue;
            }

            $sermon->passages()->create([
                'reference' => $passage['reference'],
                'book' => $passage['book'] ?? null,
                'chapter' => $passage['chapter'] ?? null,
                'verses' => $passage['verses'] ?? null,
                'position' => $position,
            ]);
        }
    }

    private function syncMedia(Sermon $sermon, SermonRequest $request): void
    {
        if ($request->hasFile('recording')) {
            $sermon->addMedia($request->file('recording'))->toMediaCollection('recording');
        }

        if ($request->hasFile('cover')) {
            $sermon->addMedia($request->file('cover'))->toMediaCollection('cover');
        }

        foreach ($request->file('slides', []) as $slide) {
            $sermon->addMedia($slide)->toMediaCollection('slides');
        }
    }

    /**
     * Series the current user may attach a sermon to.
     */
    private function availableSeries()
    {
        $user = Auth::user();
        $branchId = $user->getActiveBranchId();

        return Series::query()
            ->when(! $user->isSuperAdmin(), fn ($query) => $query->where(function ($inner) use ($branchId): void {
                $inner->whereNull('branch_id');

                if ($branchId !== null) {
                    $inner->orWhere('branch_id', $branchId);
                }
            }))
            ->orderBy('name')
            ->get();
    }

    /**
     * Members who can be linked as the speaker (optional — guests are free text).
     */
    private function availableSpeakers()
    {
        $user = Auth::user();
        $branchId = $user->getActiveBranchId();

        return Member::query()
            ->when(! $user->isSuperAdmin() && $branchId !== null, fn ($query) => $query->where('branch_id', $branchId))
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'name']);
    }
}

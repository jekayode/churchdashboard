<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pastor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pastor\SeriesRequest;
use App\Models\Series;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

final class SeriesController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $this->authorize('viewAny', Series::class);

        $user = Auth::user();
        $branchId = $user->getActiveBranchId();

        $series = Series::query()
            ->when(! $user->isSuperAdmin(), fn ($query) => $query->where(function ($inner) use ($branchId): void {
                $inner->whereNull('branch_id');

                if ($branchId !== null) {
                    $inner->orWhere('branch_id', $branchId);
                }
            }))
            ->withCount('sermons')
            ->orderByDesc('starts_on')
            ->orderBy('name')
            ->paginate(15);

        return view('pastor.series.index', ['seriesList' => $series]);
    }

    public function create(): View
    {
        $this->authorize('create', Series::class);

        return view('pastor.series.form', ['series' => null]);
    }

    public function edit(Series $series): View
    {
        $this->authorize('update', $series);

        return view('pastor.series.form', ['series' => $series]);
    }

    public function store(SeriesRequest $request): RedirectResponse
    {
        $this->authorize('create', Series::class);

        $user = Auth::user();
        $validated = $request->validated();

        $series = Series::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'tone' => $validated['tone'] ?? 'orange',
            'starts_on' => $validated['starts_on'] ?? null,
            'ends_on' => $validated['ends_on'] ?? null,
            'is_published' => $validated['is_published'] ?? true,
            'branch_id' => $user->isSuperAdmin() ? null : $user->getActiveBranchId(),
        ]);

        if ($request->hasFile('cover')) {
            $series->addMedia($request->file('cover'))->toMediaCollection('cover');
        }

        return redirect()
            ->route('pastor.series')
            ->with('success', 'Series created successfully.');
    }

    public function update(SeriesRequest $request, Series $series): RedirectResponse
    {
        $this->authorize('update', $series);

        $validated = $request->validated();

        $series->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'tone' => $validated['tone'] ?? 'orange',
            'starts_on' => $validated['starts_on'] ?? null,
            'ends_on' => $validated['ends_on'] ?? null,
            'is_published' => $validated['is_published'] ?? false,
        ]);

        if ($request->hasFile('cover')) {
            $series->addMedia($request->file('cover'))->toMediaCollection('cover');
        }

        return redirect()
            ->route('pastor.series')
            ->with('success', 'Series updated successfully.');
    }

    public function destroy(Series $series): RedirectResponse
    {
        $this->authorize('delete', $series);

        // Sermons outlive their series; detach rather than cascade-delete them.
        $series->sermons()->update(['series_id' => null]);
        $series->delete();

        return redirect()
            ->route('pastor.series')
            ->with('success', 'Series deleted. Its sermons were kept and are no longer in a series.');
    }
}

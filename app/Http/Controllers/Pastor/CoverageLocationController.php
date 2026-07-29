<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pastor;

use App\Http\Controllers\Controller;
use App\Models\CoverageLocation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Lets a branch pastor manage the "closest location" options their members
 * choose between — the areas their LifeGroups cover.
 *
 * Scoped to the pastor's own branch throughout: a branch only ever sees and
 * edits its own coverage, and every write is pinned to that branch id rather
 * than trusting one from the form.
 */
final class CoverageLocationController extends Controller
{
    public function index(): View
    {
        $branchId = $this->branchId();

        return view('pastor.coverage-locations.index', [
            'locations' => CoverageLocation::forBranch($branchId)->ordered()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $branchId = $this->branchId();

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:120',
                // Unique within the branch, so the same area cannot be added twice.
                \Illuminate\Validation\Rule::unique('coverage_locations')->where('branch_id', $branchId),
            ],
        ]);

        CoverageLocation::create([
            'branch_id' => $branchId,
            'name' => $validated['name'],
            'sort_order' => (int) CoverageLocation::forBranch($branchId)->max('sort_order') + 1,
            'is_active' => true,
        ]);

        return back()->with('success', 'Location added.');
    }

    public function update(Request $request, CoverageLocation $coverageLocation): RedirectResponse
    {
        $this->ensureOwnBranch($coverageLocation);
        $branchId = $this->branchId();

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:120',
                \Illuminate\Validation\Rule::unique('coverage_locations')
                    ->where('branch_id', $branchId)
                    ->ignore($coverageLocation->id),
            ],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['boolean'],
        ]);

        $coverageLocation->update([
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Location updated.');
    }

    /**
     * Retire rather than delete: a member may already have chosen this, and a
     * hard delete would orphan their stored value. Deactivating keeps it
     * meaningful while removing it from the dropdowns.
     */
    public function destroy(CoverageLocation $coverageLocation): RedirectResponse
    {
        $this->ensureOwnBranch($coverageLocation);

        $coverageLocation->update(['is_active' => false]);

        return back()->with('success', 'Location retired. Members who chose it keep it; it is no longer offered to new people.');
    }

    private function branchId(): int
    {
        $branchId = Auth::user()->getActiveBranchId();

        abort_if($branchId === null, 403, 'Your account is not attached to a branch.');

        return $branchId;
    }

    private function ensureOwnBranch(CoverageLocation $location): void
    {
        abort_unless($location->branch_id === $this->branchId() || Auth::user()->isSuperAdmin(), 404);
    }
}

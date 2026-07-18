<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pastor;

use App\Http\Controllers\Controller;
use App\Models\ReadingDay;
use App\Models\ReadingPlan;
use App\Support\BibleReference;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class ReadingPlanController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $this->authorize('viewAny', ReadingPlan::class);

        $user = Auth::user();
        $branchId = $user->getActiveBranchId();

        $plans = ReadingPlan::query()
            ->when(! $user->isSuperAdmin(), fn ($query) => $query->where(function ($inner) use ($branchId): void {
                $inner->whereNull('branch_id');

                if ($branchId !== null) {
                    $inner->orWhere('branch_id', $branchId);
                }
            }))
            ->withCount([
                'days',
                'days as rewritten_days_count' => fn ($query) => $query->whereNotNull('questions_updated_at'),
            ])
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('pastor.reading-plans.index', ['plans' => $plans]);
    }

    /**
     * The day list for a plan — where the rewrite work happens.
     */
    public function days(Request $request, ReadingPlan $plan): View
    {
        $this->authorize('view', $plan);

        $days = $plan->days()
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('label', 'like', '%'.$search.'%')
                        ->orWhere('title', 'like', '%'.$search.'%')
                        ->orWhere('old_testament', 'like', '%'.$search.'%')
                        ->orWhere('study_question_1', 'like', '%'.$search.'%');
                });
            })
            ->when($request->string('month')->toString(), fn ($query, string $month) => $query->where('month_day', 'like', str_pad($month, 2, '0', STR_PAD_LEFT).'%'))
            ->when($request->string('status')->toString() === 'todo', fn ($query) => $query->whereNull('questions_updated_at'))
            ->when($request->string('status')->toString() === 'done', fn ($query) => $query->whereNotNull('questions_updated_at'))
            ->paginate(25)
            ->withQueryString();

        return view('pastor.reading-plans.days', [
            'plan' => $plan,
            'days' => $days,
            'rewrittenCount' => $plan->days()->whereNotNull('questions_updated_at')->count(),
            'totalCount' => $plan->days()->count(),
        ]);
    }

    public function editDay(ReadingPlan $plan, ReadingDay $day): View
    {
        $this->authorize('view', $plan);
        abort_unless($day->reading_plan_id === $plan->id, 404);

        return view('pastor.reading-plans.day-form', [
            'plan' => $plan,
            'day' => $day->load('questionsAuthor'),
            'previous' => $plan->days()->where('day_number', '<', $day->day_number)->orderByDesc('day_number')->first(),
            'next' => $plan->days()->where('day_number', '>', $day->day_number)->orderBy('day_number')->first(),
        ]);
    }

    public function updateDay(Request $request, ReadingPlan $plan, ReadingDay $day): RedirectResponse
    {
        $this->authorize('update', $plan);
        abort_unless($day->reading_plan_id === $plan->id, 404);

        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'old_testament' => ['nullable', 'string', 'max:255'],
            'new_testament' => ['nullable', 'string', 'max:255'],
            'psalm' => ['nullable', 'string', 'max:255'],
            'proverbs' => ['nullable', 'string', 'max:255'],
            'focus_verse' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:20000'],
            'reflection_prompt' => ['nullable', 'string', 'max:2000'],
            'study_question_1' => ['nullable', 'string', 'max:5000'],
            'study_question_2' => ['nullable', 'string', 'max:5000'],
        ]);

        // Only stamp the rewrite marker when the questions actually change, so
        // editing a reference does not falsely mark a day as rewritten.
        $questionsChanged = ($validated['study_question_1'] ?? null) !== $day->study_question_1
            || ($validated['study_question_2'] ?? null) !== $day->study_question_2;

        $day->fill($validated);

        if ($questionsChanged) {
            $day->questions_updated_at = now();
            $day->questions_updated_by = Auth::id();
        }

        $day->save();

        // Warn, don't block: a pastor may deliberately type something the
        // parser cannot resolve, but they should know the app won't show text.
        $unresolved = collect(['old_testament', 'new_testament', 'psalm', 'proverbs'])
            ->map(fn (string $field): ?string => $validated[$field] ?? null)
            ->filter()
            ->reject(fn (string $reference): bool => BibleReference::toPassageId($reference) !== null)
            ->values();

        $next = $request->boolean('save_and_next')
            ? $plan->days()->where('day_number', '>', $day->day_number)->orderBy('day_number')->first()
            : null;

        $target = $next ?? $day;
        $message = $next !== null
            ? 'Saved. Now editing '.($next->label ?? 'day '.$next->day_number).'.'
            : 'Reading day saved.';

        $redirect = redirect()->route('pastor.reading-plans.days.edit', [$plan, $target])->with('success', $message);

        if ($unresolved->isNotEmpty()) {
            $redirect->with('warning', 'Saved, but these references could not be matched to a Bible passage, so no text will show in the app: '.$unresolved->implode(', '));
        }

        return $redirect;
    }

    public function edit(ReadingPlan $plan): View
    {
        $this->authorize('update', $plan);

        return view('pastor.reading-plans.form', ['plan' => $plan]);
    }

    public function update(Request $request, ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('update', $plan);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'attribution' => ['nullable', 'string', 'max:255'],
            'tone' => ['nullable', 'in:orange,purple,amber,lemon'],
            'is_published' => ['sometimes', 'boolean'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        $plan->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'attribution' => $validated['attribution'] ?? null,
            'tone' => $validated['tone'] ?? 'orange',
            'is_published' => $request->boolean('is_published'),
            'is_default' => $request->boolean('is_default'),
        ]);

        if ($plan->is_default) {
            ReadingPlan::where('id', '!=', $plan->id)->update(['is_default' => false]);
        }

        return redirect()
            ->route('pastor.reading-plans')
            ->with('success', 'Plan updated.');
    }
}

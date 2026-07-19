<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Controller;
use App\Models\MemberPlanEnrolment;
use App\Models\MemberReadingProgress;
use App\Models\ReadingDay;
use App\Models\ReadingPlan;
use App\Services\BibleService;
use App\Services\ReadingStreakService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReadingController extends Controller
{
    use ResolvesCurrentMember;

    public function __construct(
        private readonly ReadingStreakService $streaks,
        private readonly BibleService $bible,
    ) {}

    /**
     * Plans the member can follow.
     */
    public function plans(Request $request): JsonResponse
    {
        $member = $this->currentMember($request);

        $plans = ReadingPlan::query()
            ->published()
            ->where(fn ($query) => $query->whereNull('branch_id')->orWhere('branch_id', $member->branch_id))
            ->withCount('days')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $enrolled = MemberPlanEnrolment::query()
            ->where('member_id', $member->id)
            ->pluck('is_active', 'reading_plan_id');

        return response()->json([
            'success' => true,
            'data' => $plans->map(fn (ReadingPlan $plan): array => [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'description' => $plan->description,
                'type' => $plan->type,
                'tone' => $plan->tone,
                'is_annual' => $plan->is_annual,
                'is_default' => $plan->is_default,
                'length_days' => $plan->days_count,
                'attribution' => $plan->attribution,
                'is_enrolled' => $enrolled->has($plan->id),
                'is_active' => (bool) $enrolled->get($plan->id, false),
            ])->values(),
        ]);
    }

    /**
     * Follow a plan (and make it the member's active one).
     */
    public function enrol(Request $request, ReadingPlan $plan): JsonResponse
    {
        $member = $this->currentMember($request);

        if (! $this->planVisibleTo($plan, $member->branch_id)) {
            return response()->json(['success' => false, 'message' => 'This plan is not available.'], 404);
        }

        MemberPlanEnrolment::query()
            ->where('member_id', $member->id)
            ->update(['is_active' => false]);

        $enrolment = MemberPlanEnrolment::updateOrCreate(
            ['member_id' => $member->id, 'reading_plan_id' => $plan->id],
            ['started_on' => now()->toDateString(), 'is_active' => true],
        );

        return response()->json([
            'success' => true,
            'message' => 'You are now following this plan.',
            'data' => ['plan_id' => $plan->id, 'started_on' => $enrolment->started_on->toDateString()],
        ]);
    }

    /**
     * Today's reading for the member's active plan.
     */
    public function today(Request $request): JsonResponse
    {
        $member = $this->currentMember($request);
        $date = $this->resolveDate($request);

        $enrolment = $this->activeEnrolment($member);
        $plan = $enrolment?->plan ?? $this->defaultPlan($member);

        if ($plan === null) {
            return response()->json([
                'success' => true,
                'data' => null,
                'meta' => ['message' => 'No reading plan is available yet.'],
            ]);
        }

        $day = $plan->dayForDate($date, $enrolment?->started_on);

        return response()->json([
            'success' => true,
            'data' => $day === null ? null : $this->dayPayload($day, $member, $plan),
            'meta' => [
                'plan' => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'attribution' => $plan->attribution,
                    // So the app can show "Day 12 of 21".
                    'length_days' => $plan->length_days,
                ],
                'date' => $date->toDateString(),
                'streak' => $this->streaks->summary($member, $date),
            ],
        ]);
    }

    /**
     * The plan's day list, with done / today flags — the app's "The Plan" tab.
     */
    public function plan(Request $request): JsonResponse
    {
        $member = $this->currentMember($request);
        $date = $this->resolveDate($request);

        $enrolment = $this->activeEnrolment($member);
        $plan = $enrolment?->plan ?? $this->defaultPlan($member);

        if ($plan === null) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $todayDay = $plan->dayForDate($date, $enrolment?->started_on);

        $completedIds = MemberReadingProgress::query()
            ->where('member_id', $member->id)
            ->where('reading_plan_id', $plan->id)
            ->pluck('reading_day_id')
            ->all();

        // Window the list around today so a 365-day plan stays a small response.
        $around = $todayDay?->day_number ?? 1;
        $days = $plan->days()
            ->whereBetween('day_number', [max(1, $around - 7), $around + 21])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $days->map(fn (ReadingDay $day): array => [
                'id' => $day->id,
                'day_number' => $day->day_number,
                'label' => $day->label,
                'title' => $day->title,
                'references' => $day->references(),
                'is_done' => in_array($day->id, $completedIds, true),
                'is_today' => $todayDay !== null && $day->id === $todayDay->id,
            ])->values(),
            'meta' => [
                'plan' => ['id' => $plan->id, 'name' => $plan->name, 'length_days' => $plan->length_days],
                'completed_count' => count($completedIds),
            ],
        ]);
    }

    /**
     * A single day, optionally with scripture text.
     */
    public function show(Request $request, ReadingDay $day): JsonResponse
    {
        $member = $this->currentMember($request);
        $plan = $day->plan;

        if (! $this->planVisibleTo($plan, $member->branch_id)) {
            return response()->json(['success' => false, 'message' => 'This reading is not available.'], 404);
        }

        $validated = $request->validate([
            'with_text' => ['nullable', 'boolean'],
            'translation' => ['nullable', 'string', 'in:'.implode(',', array_keys($this->bible->translations()))],
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->dayPayload(
                $day,
                $member,
                $plan,
                withText: (bool) ($validated['with_text'] ?? false),
                translation: $validated['translation'] ?? null,
            ),
        ]);
    }

    /**
     * Mark a day as read.
     */
    public function complete(Request $request, ReadingDay $day): JsonResponse
    {
        $member = $this->currentMember($request);
        $plan = $day->plan;

        if (! $this->planVisibleTo($plan, $member->branch_id)) {
            return response()->json(['success' => false, 'message' => 'This reading is not available.'], 404);
        }

        $date = $this->resolveDate($request);

        MemberReadingProgress::updateOrCreate(
            ['member_id' => $member->id, 'reading_day_id' => $day->id],
            [
                'reading_plan_id' => $plan->id,
                'completed_on' => $date->toDateString(),
                'completed_at' => now(),
            ],
        );

        return response()->json([
            'success' => true,
            'message' => 'Reading marked as complete.',
            'data' => ['streak' => $this->streaks->summary($member, $date)],
        ]);
    }

    /**
     * Undo a completion.
     */
    public function uncomplete(Request $request, ReadingDay $day): JsonResponse
    {
        $member = $this->currentMember($request);

        MemberReadingProgress::query()
            ->where('member_id', $member->id)
            ->where('reading_day_id', $day->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reading marked as not read.',
            'data' => ['streak' => $this->streaks->summary($member, $this->resolveDate($request))],
        ]);
    }

    /**
     * Streak summary on its own, for the home screen and profile stats.
     */
    public function streak(Request $request): JsonResponse
    {
        $member = $this->currentMember($request);

        return response()->json([
            'success' => true,
            'data' => $this->streaks->summary($member, $this->resolveDate($request)),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function dayPayload(
        ReadingDay $day,
        \App\Models\Member $member,
        ReadingPlan $plan,
        bool $withText = false,
        ?string $translation = null,
    ): array {
        $references = $day->references();

        if ($withText) {
            $references = array_map(function (array $reference) use ($translation): array {
                $text = $this->bible->passage($reference['reference'], $translation);

                return $reference + [
                    'text' => $text['content'] ?? null,
                    'copyright' => $text['copyright'] ?? null,
                    'translation' => $text['translation'] ?? null,
                ];
            }, $references);
        }

        return [
            'id' => $day->id,
            'day_number' => $day->day_number,
            'label' => $day->label,
            'title' => $day->title,
            'focus_verse' => $day->focus_verse,
            'body' => $day->body,
            'reflection_prompt' => $day->reflection_prompt,
            'references' => $references,
            'study_questions' => $day->studyQuestions(),
            'source_url' => $day->source_url,
            'is_done' => MemberReadingProgress::query()
                ->where('member_id', $member->id)
                ->where('reading_day_id', $day->id)
                ->exists(),
            'plan_id' => $plan->id,
        ];
    }

    private function activeEnrolment(\App\Models\Member $member): ?MemberPlanEnrolment
    {
        return MemberPlanEnrolment::query()
            ->with('plan')
            ->where('member_id', $member->id)
            ->where('is_active', true)
            ->first();
    }

    private function defaultPlan(\App\Models\Member $member): ?ReadingPlan
    {
        return ReadingPlan::query()
            ->published()
            ->where(fn ($query) => $query->whereNull('branch_id')->orWhere('branch_id', $member->branch_id))
            ->orderByDesc('is_default')
            ->first();
    }

    private function planVisibleTo(?ReadingPlan $plan, ?int $branchId): bool
    {
        if ($plan === null || ! $plan->is_published) {
            return false;
        }

        return $plan->branch_id === null || $plan->branch_id === $branchId;
    }

    /**
     * The member's local date, sent by the app so day boundaries are theirs and
     * not the server's. Constrained to a day either side to stop streak gaming.
     */
    private function resolveDate(Request $request): CarbonImmutable
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        $serverToday = CarbonImmutable::now()->startOfDay();

        if (blank($validated['date'] ?? null)) {
            return $serverToday;
        }

        $requested = CarbonImmutable::parse($validated['date'])->startOfDay();

        if (abs($requested->diffInDays($serverToday)) > 1) {
            return $serverToday;
        }

        return $requested;
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Controller;
use App\Models\QuizParticipant;
use App\Services\Quiz\QuizService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The half of guest play that needs an account: your past quizzes, and pulling
 * the scores you earned as a guest onto your new profile.
 */
final class QuizController extends Controller
{
    use ResolvesCurrentMember;

    public function __construct(private readonly QuizService $quizzes) {}

    /**
     * Called by the app right after signing in, with whatever device token is
     * on the phone. Without this step, "sign in to keep your score" would be an
     * empty promise, since a guest score has nothing linking it to the person.
     */
    public function claim(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_token' => ['required', 'string', 'max:64'],
        ]);

        $claimed = $this->quizzes->claimGuestScores(
            $this->currentMember($request),
            $validated['device_token'],
        );

        return response()->json(['claimed' => $claimed]);
    }

    public function history(Request $request): JsonResponse
    {
        $member = $this->currentMember($request);

        $played = QuizParticipant::with('quiz')
            ->where('member_id', $member->id)
            ->whereHas('quiz', fn ($query) => $query->where('status', 'finished'))
            ->whereNull('removed_at')
            ->get()
            ->sortByDesc(fn (QuizParticipant $p) => $p->quiz->finished_at)
            ->values()
            ->map(fn (QuizParticipant $p): array => [
                'quiz_id' => $p->quiz_id,
                'title' => $p->quiz->title,
                'played_on' => $p->quiz->finished_at?->toDateString(),
                'score' => $p->score,
                'correct_count' => $p->correct_count,
                'question_count' => $p->quiz->questions()->count(),
                'rank' => $this->quizzes->placementFor($p),
                'players' => $p->quiz->participants()->whereNull('removed_at')->count(),
            ]);

        return response()->json(['data' => $played]);
    }
}

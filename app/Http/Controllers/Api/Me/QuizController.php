<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
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

    /**
     * Whether a quiz is open at this member's branch right now.
     *
     * This is what lets a signed-in member tap Join on the home screen instead
     * of reading five characters off a wall and typing them. A guest still has
     * to type the code — which is the point: signing in stops being an abstract
     * benefit and becomes a visible convenience, in the moment, in front of
     * everyone.
     *
     * Nothing shows until the pastor has opened the quiz for joining. A draft
     * may have half-written questions in it, and showing one greyed out only
     * invites "why can't I join?" from whoever spots it first.
     */
    public function active(Request $request): JsonResponse
    {
        $member = $this->currentMember($request);

        $quiz = Quiz::query()
            ->where('branch_id', $member->branch_id)
            ->whereIn('status', ['lobby', 'running'])
            ->orderByDesc('started_at')
            ->orderByDesc('updated_at')
            ->first();

        // A run that has passed its last question is still marked running until
        // somebody looks, and the home screen is often who looks first.
        if ($quiz !== null) {
            $this->quizzes->refreshStatus($quiz);

            if ($quiz->status === 'finished') {
                $quiz = null;
            }
        }

        if ($quiz === null) {
            return response()->json(['data' => null]);
        }

        return response()->json(['data' => [
            'code' => $quiz->code,
            'title' => $quiz->title,
            'status' => $quiz->status,
            'participant_count' => $quiz->participants()->whereNull('removed_at')->count(),
            // Lets the banner say "Rejoin" rather than "Join" for someone whose
            // phone locked halfway through.
            'joined' => $quiz->participants()
                ->where('member_id', $member->id)
                ->whereNull('removed_at')
                ->exists(),
        ]]);
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

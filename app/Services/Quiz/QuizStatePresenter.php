<?php

declare(strict_types=1);

namespace App\Services\Quiz;

use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizParticipant;
use Carbon\CarbonInterface;

/**
 * Builds the payload every surface polls for.
 *
 * The rule that matters here is that a question's correct option is withheld
 * until answers have closed. The projector page is deliberately open to anyone
 * with the code — it has to be, since it is opened on a machine nobody signs in
 * on — so if the answer were in that payload, winning would be a matter of
 * opening the screen URL on your phone instead of playing.
 */
final class QuizStatePresenter
{
    public function __construct(private readonly QuizService $quizzes) {}

    /**
     * @return array<string, mixed>
     */
    public function forScreen(Quiz $quiz, ?CarbonInterface $at = null): array
    {
        $at ??= now();
        $this->quizzes->refreshStatus($quiz, $at);

        $quiz->loadMissing('questions.options');
        $state = $quiz->timeline()->stateAt($at);

        return [
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'code' => $quiz->code,
                'status' => $quiz->status,
            ],
            'state' => $state->toArray(),
            'question' => $this->question($state),
            'answer_counts' => $this->answerCounts($state),
            'leaderboard' => $this->quizzes->leaderboard($quiz, 10),
            'participant_count' => $quiz->participants()->whereNull('removed_at')->count(),
        ];
    }

    /**
     * The same view, plus where this player stands and what they have already
     * answered — so a phone that reconnects mid-question knows not to offer the
     * buttons again.
     *
     * @return array<string, mixed>
     */
    public function forPlayer(Quiz $quiz, QuizParticipant $participant, ?CarbonInterface $at = null): array
    {
        $payload = $this->forScreen($quiz, $at);
        $state = $quiz->timeline()->stateAt($at ?? now());

        $answer = $state->question === null
            ? null
            : $participant->answers()->where('quiz_question_id', $state->question->id)->first();

        $payload['me'] = [
            'participant_id' => $participant->id,
            'name' => $participant->display_name,
            'score' => $participant->score,
            'correct_count' => $participant->correct_count,
            'rank' => $this->quizzes->placementFor($participant),
            'is_guest' => $participant->isGuest(),
            'answered_option_id' => $answer?->quiz_option_id,
            // Withheld until the reveal, for the same reason as the option flags.
            'answer_was_correct' => $state->phase === QuizPhase::Question ? null : $answer?->is_correct,
            'points_from_answer' => $state->phase === QuizPhase::Question ? null : $answer?->points_awarded,
        ];

        return $payload;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function question(QuizState $state): ?array
    {
        if ($state->question === null) {
            return null;
        }

        $revealed = $state->phase !== QuizPhase::Question;

        return [
            'id' => $state->question->id,
            'number' => $state->questionNumber(),
            'text' => $state->question->text,
            'options' => $state->question->options
                ->map(fn (QuizOption $option): array => array_filter([
                    'id' => $option->id,
                    'position' => $option->position,
                    'text' => $option->text,
                    // Present only once answering has closed.
                    'is_correct' => $revealed ? $option->is_correct : null,
                ], fn ($value): bool => $value !== null))
                ->values()
                ->all(),
        ];
    }

    /**
     * How the room split across the options, for the reveal. Withheld while the
     * question is open, because a live tally is a strong hint.
     *
     * @return array<int, int>|null
     */
    private function answerCounts(QuizState $state): ?array
    {
        if ($state->question === null || $state->phase === QuizPhase::Question) {
            return null;
        }

        return $state->question->answers()
            ->selectRaw('quiz_option_id, COUNT(*) as total')
            ->whereNotNull('quiz_option_id')
            ->groupBy('quiz_option_id')
            ->pluck('total', 'quiz_option_id')
            ->map(fn ($total): int => (int) $total)
            ->all();
    }
}

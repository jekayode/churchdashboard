<?php

declare(strict_types=1);

namespace App\Services\Quiz;

use App\Models\Member;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizOption;
use App\Models\QuizParticipant;
use Carbon\CarbonInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class QuizService
{
    // Host controls ------------------------------------------------------

    /** Puts the code on the screen and lets people in, without starting play. */
    public function openLobby(Quiz $quiz): Quiz
    {
        $quiz->update([
            'status' => 'lobby',
            'code' => $quiz->code ?? Quiz::generateCode(),
            'started_at' => null,
            'paused_at' => null,
            'paused_ms' => 0,
            'finished_at' => null,
        ]);

        return $quiz;
    }

    /**
     * The pastor's one live action. Everything after this is arithmetic from
     * started_at, so there is nothing further to press.
     */
    public function start(Quiz $quiz): Quiz
    {
        $quiz->update([
            'status' => 'running',
            'code' => $quiz->code ?? Quiz::generateCode(),
            'started_at' => now(),
            'paused_at' => null,
            'paused_ms' => 0,
        ]);

        return $quiz;
    }

    public function pause(Quiz $quiz): Quiz
    {
        if ($quiz->status === 'running' && $quiz->paused_at === null) {
            $quiz->update(['paused_at' => now()]);
        }

        return $quiz;
    }

    /** Banks the paused stretch into paused_ms; started_at never moves. */
    public function resume(Quiz $quiz): Quiz
    {
        if ($quiz->paused_at !== null) {
            $quiz->update([
                'paused_ms' => $quiz->paused_ms + (int) $quiz->paused_at->diffInMilliseconds(now(), absolute: true),
                'paused_at' => null,
            ]);
        }

        return $quiz;
    }

    public function finish(Quiz $quiz): Quiz
    {
        $quiz->update(['status' => 'finished', 'finished_at' => now()]);

        return $quiz;
    }

    /**
     * Nothing drives the clock forward on its own, so the run past the last
     * reveal is only noticed when somebody looks. Every read of the state calls
     * this, which is what closes the quiz without a scheduled job.
     */
    public function refreshStatus(Quiz $quiz, ?CarbonInterface $at = null): Quiz
    {
        if ($quiz->status !== 'running') {
            return $quiz;
        }

        if ($quiz->timeline()->stateAt($at ?? now())->phase === QuizPhase::Finished) {
            return $this->finish($quiz);
        }

        return $quiz;
    }

    // Players ------------------------------------------------------------

    /**
     * Members and guests come through the same door. A repeat join is a rejoin,
     * not a second player — someone whose phone locked mid-quiz must land back
     * on their own score rather than starting again on zero.
     */
    public function join(Quiz $quiz, ?Member $member, ?string $guestToken, ?string $displayName): QuizParticipant
    {
        if (! $quiz->isJoinable()) {
            throw QuizException::notJoinable();
        }

        if ($member !== null) {
            $existing = $quiz->participants()->where('member_id', $member->id)->first();

            if ($existing !== null) {
                if ($existing->removed_at !== null) {
                    throw QuizException::removed();
                }

                return $existing;
            }

            return $quiz->participants()->create([
                'member_id' => $member->id,
                'guest_token' => Str::random(48),
                'display_name' => $this->nameForMember($member, $displayName),
                'joined_at' => now(),
            ]);
        }

        if (! $quiz->allow_guests) {
            throw QuizException::guestsNotAllowed();
        }

        if ($guestToken !== null) {
            $existing = $quiz->participants()
                ->whereNull('member_id')
                ->where('guest_token', $guestToken)
                ->first();

            if ($existing !== null) {
                if ($existing->removed_at !== null) {
                    throw QuizException::removed();
                }

                return $existing;
            }
        }

        $name = DisplayName::clean((string) $displayName);

        if (! DisplayName::isAcceptable($name)) {
            throw QuizException::nameRejected();
        }

        return $quiz->participants()->create([
            'guest_token' => $guestToken ?: Str::random(48),
            'display_name' => $name,
            'joined_at' => now(),
        ]);
    }

    /**
     * A member's own name is used where there is one, so the projector shows the
     * congregation to itself rather than a wall of nicknames.
     */
    private function nameForMember(Member $member, ?string $fallback): string
    {
        $name = DisplayName::clean(trim(($member->first_name ?? '').' '.($member->surname ?? '')));

        if ($name === '' && $fallback !== null && DisplayName::isAcceptable(DisplayName::clean($fallback))) {
            $name = DisplayName::clean($fallback);
        }

        return Str::limit($name !== '' ? $name : 'Member', config('quiz.display_name.max'), '');
    }

    /**
     * The response time is taken from the server's own view of where the quiz
     * is, never from the phone. A client-reported time would make winning a
     * matter of editing one number before sending it.
     */
    public function submitAnswer(QuizParticipant $participant, int $optionId, ?CarbonInterface $at = null): QuizAnswer
    {
        $at ??= now();
        $quiz = $participant->quiz;

        if ($participant->removed_at !== null) {
            throw QuizException::removed();
        }

        $state = $quiz->timeline()->stateAt($at);

        if (! $state->isAnswerable()) {
            throw QuizException::notAnswerable();
        }

        $option = QuizOption::where('quiz_question_id', $state->question->id)
            ->where('id', $optionId)
            ->first();

        if ($option === null) {
            throw QuizException::wrongQuestion();
        }

        $responseMs = max(0, $quiz->timeline()->elapsedMs($at) - $state->questionStartOffsetMs);
        $points = $option->is_correct
            ? QuizScoring::award(
                $state->question->effectivePoints(),
                $responseMs,
                $state->question->effectiveTimeLimit() * 1000,
            )
            : 0;

        try {
            $answer = DB::transaction(fn (): QuizAnswer => QuizAnswer::create([
                'quiz_question_id' => $state->question->id,
                'quiz_participant_id' => $participant->id,
                'quiz_option_id' => $option->id,
                'response_ms' => $responseMs,
                'is_correct' => $option->is_correct,
                'points_awarded' => $points,
            ]));
        } catch (UniqueConstraintViolationException) {
            // The unique index is the real guard: two taps in the same instant,
            // a retried request, or a rejoin would all otherwise score twice.
            throw QuizException::alreadyAnswered();
        }

        $participant->recalculateScore();

        return $answer;
    }

    /** Kept rather than deleted, so their answers stay in the audit trail. */
    public function removeParticipant(QuizParticipant $participant): void
    {
        $participant->update(['removed_at' => now()]);
    }

    /**
     * The other half of guest play. Someone plays with just a name, sees their
     * score, signs up — and this is what makes "sign in to keep your score"
     * true rather than an empty promise.
     *
     * @return int quizzes claimed
     */
    public function claimGuestScores(Member $member, string $guestToken): int
    {
        if (trim($guestToken) === '') {
            return 0;
        }

        $claimable = QuizParticipant::whereNull('member_id')
            ->where('guest_token', $guestToken)
            ->get();

        $claimed = 0;

        foreach ($claimable as $participant) {
            // They may have played the same quiz signed in on another device;
            // the one-per-quiz constraint would reject the claim, so leave it.
            $alreadyPlayed = QuizParticipant::where('quiz_id', $participant->quiz_id)
                ->where('member_id', $member->id)
                ->exists();

            if ($alreadyPlayed) {
                continue;
            }

            $participant->update(['member_id' => $member->id]);
            $claimed++;
        }

        return $claimed;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function leaderboard(Quiz $quiz, int $limit = 10): array
    {
        return $quiz->leaderboardQuery()
            ->limit($limit)
            ->get()
            ->values()
            ->map(fn (QuizParticipant $p, int $i): array => [
                'rank' => $i + 1,
                'participant_id' => $p->id,
                'name' => $p->display_name,
                'score' => $p->score,
                'correct_count' => $p->correct_count,
                'is_guest' => $p->isGuest(),
            ])
            ->all();
    }

    /** Where a named participant stands, which may be well outside the top ten. */
    public function placementFor(QuizParticipant $participant): int
    {
        return $participant->quiz->leaderboardQuery()
            ->where(function ($query) use ($participant): void {
                $query->where('score', '>', $participant->score)
                    ->orWhere(function ($q) use ($participant): void {
                        $q->where('score', $participant->score)
                            ->where('total_response_ms', '<', $participant->total_response_ms);
                    })
                    ->orWhere(function ($q) use ($participant): void {
                        $q->where('score', $participant->score)
                            ->where('total_response_ms', $participant->total_response_ms)
                            ->where('id', '<', $participant->id);
                    });
            })
            ->count() + 1;
    }
}

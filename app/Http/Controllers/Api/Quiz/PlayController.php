<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Quiz;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizParticipant;
use App\Services\Quiz\QuizService;
use App\Services\Quiz\QuizStatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * The player surface, reached from the app.
 *
 * These routes are not behind auth, because a guest playing with nothing but a
 * name is the point — it is what removes the barrier to joining in, and the
 * score history afterwards is the reason to sign up. A participant is instead
 * identified by the device token handed back when they joined, which is also
 * what lets them claim their scores later.
 */
final class PlayController extends Controller
{
    public function __construct(
        private readonly QuizService $quizzes,
        private readonly QuizStatePresenter $presenter,
    ) {}

    public function join(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:8'],
            'name' => ['nullable', 'string', 'max:40'],
            'device_token' => ['nullable', 'string', 'max:64'],
        ]);

        $quiz = Quiz::where('code', strtoupper($validated['code']))->first();

        if ($quiz === null) {
            throw new HttpException(404, 'No quiz found with that code.');
        }

        // Resolved by hand rather than by middleware: the route has to stay open
        // for guests, but a signed-in member should still be recognised.
        $member = auth('sanctum')->user()?->member;

        $participant = $this->quizzes->join(
            $quiz,
            $member,
            $validated['device_token'] ?? null,
            $validated['name'] ?? null,
        );

        return response()->json([
            'participant_id' => $participant->id,
            'device_token' => $participant->guest_token,
            'display_name' => $participant->display_name,
            'is_guest' => $participant->isGuest(),
        ] + $this->presenter->forPlayer($quiz, $participant));
    }

    public function state(Request $request, string $code): JsonResponse
    {
        $quiz = $this->quizByCode($code);
        $participant = $this->participantFrom($request, $quiz);

        return response()->json(
            $participant === null
                ? $this->presenter->forScreen($quiz)
                : $this->presenter->forPlayer($quiz, $participant),
        );
    }

    public function answer(Request $request, string $code): JsonResponse
    {
        $validated = $request->validate([
            'device_token' => ['required', 'string', 'max:64'],
            'option_id' => ['required', 'integer'],
        ]);

        $quiz = $this->quizByCode($code);
        $participant = $this->participantFrom($request, $quiz);

        if ($participant === null) {
            throw new HttpException(403, 'Join the quiz before answering.');
        }

        $this->quizzes->submitAnswer($participant, $validated['option_id']);

        return response()->json($this->presenter->forPlayer($quiz, $participant->fresh()));
    }

    private function quizByCode(string $code): Quiz
    {
        $quiz = Quiz::with('questions.options')->where('code', strtoupper($code))->first();

        if ($quiz === null) {
            throw new HttpException(404, 'No quiz found with that code.');
        }

        return $quiz;
    }

    private function participantFrom(Request $request, Quiz $quiz): ?QuizParticipant
    {
        $token = $request->string('device_token')->toString();

        if ($token === '') {
            return null;
        }

        return $quiz->participants()->where('guest_token', $token)->first();
    }
}

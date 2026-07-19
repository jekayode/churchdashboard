<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pastor;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizParticipant;
use App\Services\Quiz\QuizService;
use App\Services\Quiz\QuizStatePresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * The pastor's console, held on a phone while standing up in front of everyone.
 *
 * It is a web page rather than a screen in the app on purpose: a page can be
 * corrected between services, whereas an app change needs a rebuild and every
 * phone in the room to reload.
 *
 * There is deliberately very little here. Questions advance on their own, so the
 * live job is to start the quiz and watch the leaderboard; pause and remove
 * exist for when the room needs holding or a name needs taking down, not as
 * part of the normal run.
 */
final class QuizHostController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly QuizService $quizzes,
        private readonly QuizStatePresenter $presenter,
    ) {}

    public function show(Quiz $quiz): View
    {
        $this->authorize('host', $quiz);

        return view('pastor.quizzes.host', ['quiz' => $quiz]);
    }

    public function state(Quiz $quiz): JsonResponse
    {
        $this->authorize('host', $quiz);

        return response()->json($this->presenter->forScreen($quiz));
    }

    public function open(Quiz $quiz): RedirectResponse
    {
        $this->authorize('host', $quiz);

        if ($quiz->questions()->count() === 0) {
            return back()->with('error', 'Add some questions before opening the quiz.');
        }

        $this->quizzes->openLobby($quiz);

        return back()->with('success', 'The code is live. People can join now.');
    }

    public function start(Quiz $quiz): RedirectResponse
    {
        $this->authorize('host', $quiz);

        if ($quiz->questions()->count() === 0) {
            return back()->with('error', 'Add some questions before starting the quiz.');
        }

        $this->quizzes->start($quiz);

        return back();
    }

    public function pause(Quiz $quiz): RedirectResponse
    {
        $this->authorize('host', $quiz);
        $this->quizzes->pause($quiz);

        return back();
    }

    public function resume(Quiz $quiz): RedirectResponse
    {
        $this->authorize('host', $quiz);
        $this->quizzes->resume($quiz);

        return back();
    }

    public function finish(Quiz $quiz): RedirectResponse
    {
        $this->authorize('host', $quiz);
        $this->quizzes->finish($quiz);

        return back()->with('success', 'Quiz ended.');
    }

    public function removeParticipant(Quiz $quiz, QuizParticipant $participant): RedirectResponse
    {
        $this->authorize('host', $quiz);

        abort_unless($participant->quiz_id === $quiz->id, 404);

        $this->quizzes->removeParticipant($participant);

        return back()->with('success', $participant->display_name.' was removed.');
    }
}

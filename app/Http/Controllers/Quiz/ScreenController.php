<?php

declare(strict_types=1);

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Services\Quiz\QuizStatePresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * The projector.
 *
 * Open to anyone with the code, because it is opened on whatever machine drives
 * the screen and nobody is going to sign in on that during a service. Nothing
 * secret is exposed: the presenter withholds the correct option and the running
 * tally until answering has closed, so this URL is worth no more to a player
 * than looking up at the wall.
 */
final class ScreenController extends Controller
{
    public function __construct(private readonly QuizStatePresenter $presenter) {}

    public function show(string $code): View
    {
        return view('quiz.screen', ['quiz' => $this->quizByCode($code)]);
    }

    public function state(string $code): JsonResponse
    {
        return response()->json($this->presenter->forScreen($this->quizByCode($code)));
    }

    private function quizByCode(string $code): Quiz
    {
        $quiz = Quiz::with('questions.options')->where('code', strtoupper($code))->first();

        if ($quiz === null) {
            throw new HttpException(404, 'No quiz found with that code.');
        }

        return $quiz;
    }
}

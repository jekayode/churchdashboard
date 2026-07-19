<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pastor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pastor\QuizQuestionsRequest;
use App\Http\Requests\Pastor\QuizRequest;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Services\Quiz\QuizService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Writing a quiz is a desk job, so it lives here on the dashboard. Running one
 * is a different surface entirely — see QuizHostController.
 */
final class QuizController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly QuizService $quizzes) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Quiz::class);

        $user = Auth::user();

        $quizzes = Quiz::query()
            ->withCount(['questions', 'participants'])
            ->when(! $user->isSuperAdmin(), fn ($query) => $query->where('branch_id', $user->getActiveBranchId()))
            ->orderByRaw("FIELD(status, 'running', 'lobby', 'draft', 'finished')")
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('pastor.quizzes.index', ['quizzes' => $quizzes]);
    }

    public function create(): View
    {
        $this->authorize('create', Quiz::class);

        return view('pastor.quizzes.form', ['quiz' => null]);
    }

    public function store(QuizRequest $request): RedirectResponse
    {
        $this->authorize('create', Quiz::class);

        $quiz = Quiz::create($request->validated() + [
            'branch_id' => Auth::user()->getActiveBranchId(),
            'created_by' => Auth::id(),
            'status' => 'draft',
        ]);

        return redirect()
            ->route('pastor.quizzes.questions', $quiz)
            ->with('success', 'Quiz created. Now add the questions.');
    }

    public function edit(Quiz $quiz): View
    {
        $this->authorize('update', $quiz);

        return view('pastor.quizzes.form', ['quiz' => $quiz]);
    }

    public function update(QuizRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->authorize('update', $quiz);

        $quiz->update($request->validated());

        return redirect()
            ->route('pastor.quizzes')
            ->with('success', 'Quiz updated.');
    }

    public function questions(Quiz $quiz): View
    {
        $this->authorize('update', $quiz);

        return view('pastor.quizzes.questions', [
            'quiz' => $quiz,
            'questions' => $quiz->questions()->with('options')->get(),
        ]);
    }

    /**
     * The whole set is submitted and rewritten together. Questions are few and
     * only ever edited before the quiz runs, so replacing them wholesale avoids
     * an entire class of ordering bugs for no real cost.
     */
    public function updateQuestions(QuizQuestionsRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->authorize('update', $quiz);

        if ($quiz->status !== 'draft') {
            return back()->with('error', 'This quiz has already been opened, so its questions are locked.');
        }

        DB::transaction(function () use ($request, $quiz): void {
            $quiz->questions()->delete();

            foreach (array_values($request->validated()['questions']) as $position => $input) {
                $question = QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'position' => $position + 1,
                    'text' => $input['text'],
                    'time_limit_seconds' => $input['time_limit_seconds'] ?? null,
                    'points' => $input['points'] ?? null,
                ]);

                foreach (array_values($input['options']) as $index => $option) {
                    $question->options()->create([
                        'position' => $index + 1,
                        'text' => $option['text'],
                        'is_correct' => $index === (int) $input['correct'],
                    ]);
                }
            }
        });

        return redirect()
            ->route('pastor.quizzes')
            ->with('success', 'Questions saved.');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $this->authorize('delete', $quiz);

        $quiz->delete();

        return redirect()->route('pastor.quizzes')->with('success', 'Quiz deleted.');
    }
}

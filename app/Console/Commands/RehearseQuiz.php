<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Services\Quiz\QuizException;
use App\Services\Quiz\QuizService;
use Illuminate\Console\Command;

/**
 * A dry run of the whole thing, at the size of a real service.
 *
 * A live quiz fails in front of the congregation rather than in a log, so it is
 * worth having watched it work once with a full room of players before Sunday.
 * This builds a clearly labelled rehearsal quiz, fills it with simulated
 * players, and can play the whole thing through.
 */
final class RehearseQuiz extends Command
{
    protected $signature = 'quiz:rehearse
        {--players=100 : How many simulated players to put in the room}
        {--branch= : Branch id to attach the quiz to}
        {--play : Play the whole quiz through rather than stopping in the lobby}
        {--cleanup : Delete previous rehearsal quizzes and their players}';

    protected $description = 'Create a rehearsal quiz with simulated players, to try the whole run before a service';

    /** Marks everything this command makes, so cleanup can find it again. */
    private const TITLE_PREFIX = '[Rehearsal]';

    public function handle(QuizService $quizzes): int
    {
        if ($this->option('cleanup')) {
            return $this->cleanup();
        }

        $branchId = $this->option('branch') ?? Branch::query()->min('id');

        if ($branchId === null) {
            $this->error('No branches exist to attach a quiz to.');

            return self::FAILURE;
        }

        $players = max(1, (int) $this->option('players'));

        $quiz = Quiz::create([
            'branch_id' => $branchId,
            'title' => self::TITLE_PREFIX.' '.now()->format('j M, H:i'),
            'description' => 'Practice run — safe to delete.',
            'status' => 'draft',
            'seconds_per_question' => 15,
            'reveal_seconds' => 5,
            'base_points' => 1000,
            'allow_guests' => true,
        ]);

        foreach ($this->sampleQuestions() as $position => $sample) {
            $question = QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'position' => $position + 1,
                'text' => $sample['text'],
            ]);

            foreach ($sample['options'] as $index => $text) {
                QuizOption::create([
                    'quiz_question_id' => $question->id,
                    'position' => $index + 1,
                    'text' => $text,
                    'is_correct' => $index === $sample['correct'],
                ]);
            }
        }

        $quizzes->start($quiz->fresh());
        $quiz->refresh()->load('questions.options');

        $this->info("Rehearsal quiz ready — code {$quiz->code}");

        $participants = collect(range(1, $players))->map(
            fn (int $i) => $quizzes->join($quiz, null, null, 'Player '.$i)
        );
        $this->info("{$players} simulated players joined.");

        if (! $this->option('play')) {
            $this->newLine();
            $this->line('The quiz is running. Open the projector screen at:');
            $this->line('  '.url("/quiz/{$quiz->code}/screen"));
            $this->line('and join from the app with code '.$quiz->code);

            return self::SUCCESS;
        }

        $this->playThrough($quizzes, $quiz, $participants);

        return self::SUCCESS;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\QuizParticipant>  $participants
     */
    private function playThrough(QuizService $quizzes, Quiz $quiz, $participants): void
    {
        $this->newLine();
        $bar = $this->output->createProgressBar($quiz->questions->count());

        $offset = 0;

        foreach ($quiz->questions as $question) {
            $limitMs = $question->effectiveTimeLimit() * 1000;

            foreach ($participants as $i => $participant) {
                // Spread across the window the way a real room does, with most
                // people in the first few seconds and a tail of stragglers.
                $responseMs = (int) min($limitMs - 250, 800 + ($i * 37) % ($limitMs - 1000));
                $options = $question->options;
                $chosen = $i % 4 === 0
                    ? $options->firstWhere('is_correct', false)
                    : $options->firstWhere('is_correct', true);

                try {
                    $quizzes->submitAnswer($participant, $chosen->id, $quiz->started_at->copy()
                        ->addMilliseconds($offset + $responseMs));
                } catch (QuizException) {
                    // A player who has already answered, exactly as on the night.
                }
            }

            $offset += $limitMs + ($quiz->reveal_seconds * 1000);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $quizzes->finish($quiz);

        $this->info('Final standings:');
        $this->table(
            ['#', 'Player', 'Score', 'Correct'],
            collect($quizzes->leaderboard($quiz, 10))
                ->map(fn (array $row): array => [$row['rank'], $row['name'], $row['score'], $row['correct_count']])
                ->all(),
        );

        $this->line('Projector screen: '.url("/quiz/{$quiz->code}/screen"));
    }

    private function cleanup(): int
    {
        // Scoped to this command's own quizzes by title, and nothing else. The
        // players and answers go with them through the foreign keys.
        $quizzes = Quiz::where('title', 'like', self::TITLE_PREFIX.'%')->get();

        if ($quizzes->isEmpty()) {
            $this->info('No rehearsal quizzes to remove.');

            return self::SUCCESS;
        }

        if (! $this->confirm("Delete {$quizzes->count()} rehearsal quiz(zes) and their players?", true)) {
            return self::SUCCESS;
        }

        $quizzes->each->forceDelete();
        $this->info("Removed {$quizzes->count()} rehearsal quiz(zes).");

        return self::SUCCESS;
    }

    /**
     * @return list<array{text: string, options: list<string>, correct: int}>
     */
    private function sampleQuestions(): array
    {
        return [
            ['text' => 'Who led Israel across the Jordan?', 'options' => ['Moses', 'Joshua', 'Caleb', 'Aaron'], 'correct' => 1],
            ['text' => 'How many books are in the New Testament?', 'options' => ['27', '39', '66', '24'], 'correct' => 0],
            ['text' => 'Where was Jesus born?', 'options' => ['Nazareth', 'Jerusalem', 'Bethlehem', 'Capernaum'], 'correct' => 2],
            ['text' => 'Who wrote most of the New Testament letters?', 'options' => ['Peter', 'John', 'Luke', 'Paul'], 'correct' => 3],
            ['text' => 'What is the first book of the Bible?', 'options' => ['Genesis', 'Exodus', 'Job', 'Psalms'], 'correct' => 0],
        ];
    }
}

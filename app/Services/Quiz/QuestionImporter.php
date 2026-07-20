<?php

declare(strict_types=1);

namespace App\Services\Quiz;

/**
 * Turns pasted text or a CSV into quiz questions.
 *
 * Typing forty questions into a form one at a time is miserable, and the
 * questions usually already exist somewhere — a WhatsApp message, a Word file,
 * last year's sheet. So this is written to accept how people actually write
 * quizzes rather than a format they have to learn: numbered or not, lettered or
 * not, blank lines or not, the answer starred or named on its own line.
 *
 * It never guesses. Anything it cannot read with certainty comes back as an
 * error naming the question, because a quietly mis-parsed answer key is only
 * discovered in front of the congregation.
 */
final class QuestionImporter
{
    public const MAX_QUESTIONS = 50;

    private const MIN_OPTIONS = 2;

    private const MAX_OPTIONS = 4;

    /**
     * @return array{questions: list<array{text: string, options: list<array{text: string}>, correct: int}>, errors: list<string>}
     */
    public static function parseText(string $input): array
    {
        $blocks = self::splitIntoBlocks($input);

        $questions = [];
        $errors = [];

        foreach ($blocks as $index => $lines) {
            $parsed = self::parseBlock($lines, $index + 1);

            if (isset($parsed['error'])) {
                $errors[] = $parsed['error'];

                continue;
            }

            $questions[] = $parsed['question'];
        }

        if ($questions === [] && $errors === []) {
            $errors[] = 'Nothing to import — paste your questions above.';
        }

        if (count($questions) > self::MAX_QUESTIONS) {
            $errors[] = 'That is more than '.self::MAX_QUESTIONS.' questions. Split it across two quizzes.';
        }

        return ['questions' => $questions, 'errors' => $errors];
    }

    /**
     * A sheet exported to CSV. The header row names the columns, and the usual
     * spellings are all accepted because nobody will look this up.
     *
     * @return array{questions: list<array{text: string, options: list<array{text: string}>, correct: int}>, errors: list<string>}
     */
    public static function parseCsv(string $path): array
    {
        $handle = @fopen($path, 'r');

        if ($handle === false) {
            return ['questions' => [], 'errors' => ['That file could not be read.']];
        }

        // escape: '' gives plain RFC 4180 behaviour. PHP's historic default
        // treats a backslash as an escape character, which is not CSV and would
        // mangle any question containing one.
        $header = fgetcsv($handle, escape: '');

        if ($header === false) {
            fclose($handle);

            return ['questions' => [], 'errors' => ['That file is empty.']];
        }

        $columns = self::mapCsvColumns($header);

        if ($columns['question'] === null) {
            fclose($handle);

            return ['questions' => [], 'errors' => [
                'No "question" column found. The first row should name the columns, for example: question, a, b, c, d, answer.',
            ]];
        }

        $questions = [];
        $errors = [];
        $row = 1;

        while (($record = fgetcsv($handle, escape: '')) !== false) {
            $row++;

            if (self::isBlankRecord($record)) {
                continue;
            }

            $text = trim((string) ($record[$columns['question']] ?? ''));

            if ($text === '') {
                continue;
            }

            $options = [];

            foreach ($columns['options'] as $column) {
                $value = trim((string) ($record[$column] ?? ''));

                if ($value !== '') {
                    $options[] = ['text' => $value];
                }
            }

            if (count($options) < self::MIN_OPTIONS) {
                $errors[] = "Row {$row}: needs at least two answers.";

                continue;
            }

            if (count($options) > self::MAX_OPTIONS) {
                $errors[] = "Row {$row}: more than four answers.";

                continue;
            }

            $answer = $columns['answer'] === null ? '' : trim((string) ($record[$columns['answer']] ?? ''));
            $correct = self::resolveAnswer($answer, $options);

            if ($correct === null) {
                $errors[] = "Row {$row}: could not tell which answer is correct"
                    .($answer === '' ? ' — the answer column is empty.' : " from \"{$answer}\".");

                continue;
            }

            $questions[] = ['text' => $text, 'options' => $options, 'correct' => $correct];
        }

        fclose($handle);

        if ($questions === [] && $errors === []) {
            $errors[] = 'No questions found in that file.';
        }

        if (count($questions) > self::MAX_QUESTIONS) {
            $errors[] = 'That is more than '.self::MAX_QUESTIONS.' questions. Split it across two quizzes.';
        }

        return ['questions' => $questions, 'errors' => $errors];
    }

    /**
     * @return list<list<string>>
     */
    private static function splitIntoBlocks(string $input): array
    {
        $normalised = str_replace(["\r\n", "\r"], "\n", trim($input));

        if ($normalised === '') {
            return [];
        }

        // Blank lines between questions is the clearest signal, so it wins.
        $chunks = preg_split('/\n\s*\n/', $normalised) ?: [];
        $blocks = [];

        foreach ($chunks as $chunk) {
            $lines = self::nonEmptyLines($chunk);

            if ($lines !== []) {
                $blocks[] = $lines;
            }
        }

        if (count($blocks) > 1) {
            return $blocks;
        }

        /*
         * Nobody separated anything with blank lines, which is just as common —
         * a numbered list run together. Start a new question at each numbered
         * line instead.
         */
        $lines = self::nonEmptyLines($normalised);
        $byNumber = [];
        $current = [];

        foreach ($lines as $line) {
            if (preg_match('/^\s*\d+\s*[.):]\s+\S/', $line) === 1 && $current !== []) {
                $byNumber[] = $current;
                $current = [];
            }

            $current[] = $line;
        }

        if ($current !== []) {
            $byNumber[] = $current;
        }

        return $byNumber;
    }

    /**
     * @return list<string>
     */
    private static function nonEmptyLines(string $chunk): array
    {
        return array_values(array_filter(
            array_map('trim', explode("\n", $chunk)),
            static fn (string $line): bool => $line !== '',
        ));
    }

    /**
     * @param  list<string>  $lines
     * @return array{question?: array{text: string, options: list<array{text: string}>, correct: int}, error?: string}
     */
    private static function parseBlock(array $lines, int $number): array
    {
        $questionText = self::stripListMarker(array_shift($lines) ?? '');

        if ($questionText === '') {
            return ['error' => "Question {$number}: no wording found."];
        }

        $options = [];
        $starredIndexes = [];
        $namedAnswer = null;

        foreach ($lines as $line) {
            if (preg_match('/^(?:answer|ans|correct)\s*[:\-]\s*(.+)$/i', $line, $matches) === 1) {
                $namedAnswer = trim($matches[1]);

                continue;
            }

            // List marker first, so "- *Joshua" and "a) *Joshua" are seen for
            // what they are. stripListMarker deliberately leaves * alone.
            [$text, $isStarred] = self::stripCorrectMarker(self::stripListMarker($line));

            if ($text === '') {
                continue;
            }

            if ($isStarred) {
                $starredIndexes[] = count($options);
            }

            $options[] = ['text' => $text];
        }

        /*
         * "* Moses" is a Markdown bullet as often as it is a correct marker, and
         * the two are indistinguishable line by line. Taken together they are
         * not: if every answer is starred, they were bullets. If only some are,
         * the star means what it usually means.
         */
        if ($starredIndexes !== [] && count($starredIndexes) === count($options)) {
            $starredIndexes = [];
        }

        if (count($starredIndexes) > 1) {
            return ['error' => "Question {$number} (\"{$questionText}\"): more than one answer is marked correct."];
        }

        $starred = $starredIndexes[0] ?? null;

        if (count($options) < self::MIN_OPTIONS) {
            return ['error' => "Question {$number} (\"{$questionText}\"): needs at least two answers."];
        }

        if (count($options) > self::MAX_OPTIONS) {
            return ['error' => "Question {$number} (\"{$questionText}\"): has more than four answers."];
        }

        $correct = $starred;

        if ($correct === null && $namedAnswer !== null) {
            $correct = self::resolveAnswer($namedAnswer, $options);
        }

        if ($correct === null) {
            return ['error' => "Question {$number} (\"{$questionText}\"): no correct answer marked. "
                .'Put a * against it, or add a line reading "Answer: B".'];
        }

        return ['question' => ['text' => $questionText, 'options' => $options, 'correct' => $correct]];
    }

    /**
     * "B", "b)", "2" or the answer written out in full — all of which people use.
     *
     * @param  list<array{text: string}>  $options
     */
    private static function resolveAnswer(string $answer, array $options): ?int
    {
        $answer = trim($answer);

        if ($answer === '') {
            return null;
        }

        // Written out in full, which is the least ambiguous and worth trying first.
        foreach ($options as $index => $option) {
            if (mb_strtolower($option['text']) === mb_strtolower($answer)) {
                return $index;
            }
        }

        $bare = rtrim(trim($answer), '.):');

        if (preg_match('/^[a-zA-Z]$/', $bare) === 1) {
            $index = ord(mb_strtolower($bare)) - ord('a');

            return isset($options[$index]) ? $index : null;
        }

        if (preg_match('/^\d+$/', $bare) === 1) {
            // People number answers from one; arrays do not.
            $index = (int) $bare - 1;

            return isset($options[$index]) ? $index : null;
        }

        return null;
    }

    /**
     * @return array{0: string, 1: bool}
     */
    private static function stripCorrectMarker(string $line): array
    {
        $trimmed = trim($line);

        if (str_ends_with($trimmed, '*')) {
            return [trim(rtrim($trimmed, '*')), true];
        }

        // Leading, before any list marker: "*b) Joshua".
        if (str_starts_with($trimmed, '*')) {
            return [trim(ltrim($trimmed, '*')), true];
        }

        if (preg_match('/^\s*\[[xX✓]\]\s*(.+)$/', $trimmed, $matches) === 1) {
            return [trim($matches[1]), true];
        }

        return [$trimmed, false];
    }

    /** Removes "1.", "a)", "-", "Q:" and friends, which carry no meaning here. */
    private static function stripListMarker(string $line): string
    {
        $stripped = preg_replace('/^\s*(?:q(?:uestion)?\s*\d*\s*[:.\)]|\d+\s*[.):]|[a-zA-Z]\s*[.)]|[-–—•])\s*/i', '', trim($line));

        return trim((string) $stripped);
    }

    /**
     * @param  list<string>  $header
     * @return array{question: int|null, answer: int|null, options: list<int>}
     */
    private static function mapCsvColumns(array $header): array
    {
        $question = null;
        $answer = null;
        $options = [];

        foreach ($header as $index => $name) {
            $key = mb_strtolower(trim((string) $name));

            if (in_array($key, ['question', 'q', 'text', 'question text'], true)) {
                $question = $index;
            } elseif (in_array($key, ['answer', 'correct', 'correct answer', 'key'], true)) {
                $answer = $index;
            } elseif (preg_match('/^(?:option\s*\d+|answer\s*\d+|[a-d])$/', $key) === 1) {
                $options[] = $index;
            }
        }

        return ['question' => $question, 'answer' => $answer, 'options' => $options];
    }

    /**
     * @param  list<string|null>  $record
     */
    private static function isBlankRecord(array $record): bool
    {
        foreach ($record as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}

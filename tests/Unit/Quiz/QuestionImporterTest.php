<?php

declare(strict_types=1);

namespace Tests\Unit\Quiz;

use App\Services\Quiz\QuestionImporter;
use PHPUnit\Framework\TestCase;

/**
 * Tested against the ways people actually write quizzes, rather than one format
 * they would have to be taught. A quietly mis-parsed answer key is only found
 * out in front of the congregation, so anything ambiguous must come back as an
 * error rather than a guess.
 */
final class QuestionImporterTest extends TestCase
{
    public function test_plain_blocks_separated_by_blank_lines(): void
    {
        $result = QuestionImporter::parseText(<<<'TEXT'
        Who led Israel across the Jordan?
        Moses
        *Joshua
        Caleb

        Where was Jesus born?
        Nazareth
        *Bethlehem
        TEXT);

        $this->assertSame([], $result['errors']);
        $this->assertCount(2, $result['questions']);
        $this->assertSame('Who led Israel across the Jordan?', $result['questions'][0]['text']);
        $this->assertSame(1, $result['questions'][0]['correct']);
        $this->assertSame('Joshua', $result['questions'][0]['options'][1]['text']);
    }

    public function test_a_numbered_list_with_no_blank_lines(): void
    {
        // Extremely common when it has been pasted out of a message.
        $result = QuestionImporter::parseText(<<<'TEXT'
        1. Who led Israel across the Jordan?
        a) Moses
        b) Joshua *
        2. Where was Jesus born?
        a) Nazareth *
        b) Bethlehem
        TEXT);

        $this->assertSame([], $result['errors']);
        $this->assertCount(2, $result['questions']);
        $this->assertSame('Who led Israel across the Jordan?', $result['questions'][0]['text']);
        $this->assertSame(1, $result['questions'][0]['correct']);
        $this->assertSame(0, $result['questions'][1]['correct']);
    }

    public function test_the_answer_can_be_named_on_its_own_line(): void
    {
        $result = QuestionImporter::parseText(<<<'TEXT'
        Who led Israel across the Jordan?
        a) Moses
        b) Joshua
        c) Caleb
        Answer: B
        TEXT);

        $this->assertSame([], $result['errors']);
        $this->assertSame(1, $result['questions'][0]['correct']);
        $this->assertCount(3, $result['questions'][0]['options'], 'The answer line is not an option');
    }

    public function test_the_answer_line_accepts_a_number_or_the_words(): void
    {
        foreach (['Answer: 2', 'Ans: Joshua', 'Correct - joshua', 'ANSWER: b)'] as $line) {
            $result = QuestionImporter::parseText("Who led Israel?\nMoses\nJoshua\nCaleb\n{$line}");

            $this->assertSame([], $result['errors'], "Failed on: {$line}");
            $this->assertSame(1, $result['questions'][0]['correct'], "Failed on: {$line}");
        }
    }

    public function test_list_markers_are_stripped_from_the_wording(): void
    {
        $result = QuestionImporter::parseText(<<<'TEXT'
        Q1. Who led Israel across the Jordan?
        - Moses
        - *Joshua
        TEXT);

        $this->assertSame('Who led Israel across the Jordan?', $result['questions'][0]['text']);
        $this->assertSame('Moses', $result['questions'][0]['options'][0]['text']);
        $this->assertSame('Joshua', $result['questions'][0]['options'][1]['text']);
    }

    public function test_a_ticked_checkbox_marks_the_answer(): void
    {
        $result = QuestionImporter::parseText("Who led Israel?\n[ ] Moses\n[x] Joshua");

        $this->assertSame([], $result['errors']);
        $this->assertSame(1, $result['questions'][0]['correct']);
    }

    // Refusing to guess ---------------------------------------------------

    public function test_a_question_with_no_marked_answer_is_an_error(): void
    {
        $result = QuestionImporter::parseText("Who led Israel?\nMoses\nJoshua");

        $this->assertSame([], $result['questions']);
        $this->assertStringContainsString('no correct answer marked', $result['errors'][0]);
        $this->assertStringContainsString('Who led Israel?', $result['errors'][0], 'The error must say which question');
    }

    public function test_two_marked_answers_is_an_error_rather_than_a_guess(): void
    {
        $result = QuestionImporter::parseText("Who led Israel?\n*Moses\n*Joshua\nCaleb");

        $this->assertSame([], $result['questions']);
        $this->assertStringContainsString('more than one answer', $result['errors'][0]);
    }

    public function test_a_star_bulleted_list_is_read_as_bullets_not_as_answers(): void
    {
        // "* Moses" is a Markdown bullet as often as a correct marker, and the
        // two cannot be told apart line by line. Every line starred means it was
        // a bullet list, so the right complaint is that nothing is marked.
        $result = QuestionImporter::parseText("Who led Israel?\n* Moses\n* Joshua\n* Caleb");

        $this->assertSame([], $result['questions']);
        $this->assertStringContainsString('no correct answer marked', $result['errors'][0]);
    }

    public function test_a_star_bulleted_list_still_works_with_a_named_answer(): void
    {
        $result = QuestionImporter::parseText("Who led Israel?\n* Moses\n* Joshua\nAnswer: Joshua");

        $this->assertSame([], $result['errors']);
        $this->assertSame(1, $result['questions'][0]['correct']);
        $this->assertSame('Moses', $result['questions'][0]['options'][0]['text'], 'The bullet is not part of the answer');
    }

    public function test_a_question_with_one_answer_is_an_error(): void
    {
        $result = QuestionImporter::parseText("Who led Israel?\n*Joshua");

        $this->assertStringContainsString('at least two answers', $result['errors'][0]);
    }

    public function test_more_than_four_answers_is_an_error(): void
    {
        $result = QuestionImporter::parseText("Pick one\n*A\nB\nC\nD\nE");

        $this->assertStringContainsString('more than four answers', $result['errors'][0]);
    }

    public function test_an_answer_line_naming_something_that_is_not_there_is_an_error(): void
    {
        $result = QuestionImporter::parseText("Who led Israel?\nMoses\nJoshua\nAnswer: D");

        $this->assertSame([], $result['questions']);
        $this->assertStringContainsString('no correct answer marked', $result['errors'][0]);
    }

    public function test_good_questions_survive_alongside_a_broken_one(): void
    {
        $result = QuestionImporter::parseText(<<<'TEXT'
        Who led Israel?
        Moses
        *Joshua

        A broken one
        No marker here
        Nor here

        Where was Jesus born?
        *Bethlehem
        Nazareth
        TEXT);

        // Rejecting the whole paste over one bad question would mean hunting for
        // it by hand, so the rest is kept and the failure named.
        $this->assertCount(2, $result['questions']);
        $this->assertCount(1, $result['errors']);
    }

    public function test_empty_input_says_so(): void
    {
        $this->assertStringContainsString('Nothing to import', QuestionImporter::parseText('   ')['errors'][0]);
    }

    public function test_windows_line_endings_are_handled(): void
    {
        $result = QuestionImporter::parseText("Who led Israel?\r\nMoses\r\n*Joshua\r\n");

        $this->assertSame([], $result['errors']);
        $this->assertSame(1, $result['questions'][0]['correct']);
    }

    public function test_too_many_questions_is_refused(): void
    {
        $block = "Question?\n*Yes\nNo";
        $result = QuestionImporter::parseText(implode("\n\n", array_fill(0, 60, $block)));

        $this->assertStringContainsString('more than 50 questions', $result['errors'][0]);
    }

    // CSV ------------------------------------------------------------------

    private function csv(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'quizcsv');
        file_put_contents($path, $contents);

        return $path;
    }

    public function test_a_csv_with_letter_columns(): void
    {
        $path = $this->csv(<<<'CSV'
        question,a,b,c,d,answer
        "Who led Israel across the Jordan?",Moses,Joshua,Caleb,Aaron,B
        "Where was Jesus born?",Nazareth,Bethlehem,,,2
        CSV);

        $result = QuestionImporter::parseCsv($path);

        $this->assertSame([], $result['errors']);
        $this->assertCount(2, $result['questions']);
        $this->assertSame(1, $result['questions'][0]['correct']);
        $this->assertCount(4, $result['questions'][0]['options']);
        // Empty cells are not blank answers.
        $this->assertCount(2, $result['questions'][1]['options']);

        unlink($path);
    }

    public function test_a_csv_using_option_headings(): void
    {
        $path = $this->csv("Question,Option 1,Option 2,Correct\nWho led Israel?,Moses,Joshua,Joshua\n");

        $result = QuestionImporter::parseCsv($path);

        $this->assertSame([], $result['errors']);
        $this->assertSame(1, $result['questions'][0]['correct']);

        unlink($path);
    }

    public function test_a_csv_row_with_an_unreadable_answer_names_the_row(): void
    {
        $path = $this->csv("question,a,b,answer\nWho led Israel?,Moses,Joshua,Zebedee\n");

        $result = QuestionImporter::parseCsv($path);

        $this->assertSame([], $result['questions']);
        $this->assertStringContainsString('Row 2', $result['errors'][0]);

        unlink($path);
    }

    public function test_a_csv_with_no_question_column_explains_the_format(): void
    {
        $path = $this->csv("title,a,b\nSomething,One,Two\n");

        $result = QuestionImporter::parseCsv($path);

        $this->assertStringContainsString('question', $result['errors'][0]);

        unlink($path);
    }

    public function test_blank_csv_rows_are_skipped(): void
    {
        $path = $this->csv("question,a,b,answer\nWho led Israel?,Moses,Joshua,B\n,,,\n\n");

        $result = QuestionImporter::parseCsv($path);

        $this->assertSame([], $result['errors']);
        $this->assertCount(1, $result['questions']);

        unlink($path);
    }
}

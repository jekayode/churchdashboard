<?php

declare(strict_types=1);

namespace App\Services\Quiz;

use RuntimeException;

/**
 * Everything a player can be told "no" for. Each carries the wording the phone
 * shows, because these all surface mid-service where a stack trace is useless.
 */
final class QuizException extends RuntimeException
{
    public function __construct(string $message, public readonly string $reason, public readonly int $status = 422)
    {
        parent::__construct($message);
    }

    public static function notJoinable(): self
    {
        return new self('This quiz is not open to join.', 'not_joinable', 409);
    }

    public static function guestsNotAllowed(): self
    {
        return new self('Sign in to join this quiz.', 'guests_not_allowed', 403);
    }

    public static function nameRejected(): self
    {
        return new self('Please choose a different name.', 'name_rejected');
    }

    public static function removed(): self
    {
        return new self('You have been removed from this quiz.', 'removed', 403);
    }

    public static function notAnswerable(): self
    {
        return new self('That question has closed.', 'not_answerable', 409);
    }

    public static function wrongQuestion(): self
    {
        return new self('The quiz has moved on to another question.', 'wrong_question', 409);
    }

    public static function alreadyAnswered(): self
    {
        return new self('You have already answered this question.', 'already_answered', 409);
    }
}

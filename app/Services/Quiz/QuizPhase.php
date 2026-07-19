<?php

declare(strict_types=1);

namespace App\Services\Quiz;

enum QuizPhase: string
{
    /** Code is up, players joining, nothing answerable yet. */
    case Lobby = 'lobby';
    /** A question is showing and answers are being accepted. */
    case Question = 'question';
    /** Answers closed, correct option and standings on screen. */
    case Reveal = 'reveal';
    /** Final standings; the quiz is history from here. */
    case Finished = 'finished';
}

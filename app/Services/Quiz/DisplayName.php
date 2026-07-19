<?php

declare(strict_types=1);

namespace App\Services\Quiz;

use Illuminate\Support\Str;

/**
 * Guests choose their own name and it appears on the projector, so this is the
 * only moderation between someone's phone and the wall of the auditorium.
 */
final class DisplayName
{
    public static function isAcceptable(string $name): bool
    {
        $trimmed = trim($name);

        if (Str::length($trimmed) < config('quiz.display_name.min')
            || Str::length($trimmed) > config('quiz.display_name.max')) {
            return false;
        }

        $normalised = self::normalise($trimmed);

        if ($normalised === '') {
            return false;
        }

        foreach (config('quiz.display_name.blocked') as $word) {
            if (str_contains($normalised, $word)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Reduces a name to bare lowercase letters so the obvious evasions —
     * spacing it out, punctuating it, swapping digits for letters — collapse
     * back onto the word being hidden.
     */
    private static function normalise(string $name): string
    {
        $lower = Str::lower($name);

        $lower = strtr($lower, [
            '0' => 'o', '1' => 'i', '3' => 'e', '4' => 'a',
            '5' => 's', '7' => 't', '8' => 'b', '@' => 'a', '$' => 's', '!' => 'i',
        ]);

        return (string) preg_replace('/[^a-z]/', '', $lower);
    }

    /** Collapses runs of whitespace so the projector layout stays predictable. */
    public static function clean(string $name): string
    {
        return trim((string) preg_replace('/\s+/', ' ', $name));
    }
}

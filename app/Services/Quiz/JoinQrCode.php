<?php

declare(strict_types=1);

namespace App\Services\Quiz;

use App\Models\Quiz;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * The QR that gets people into a quiz without typing anything.
 *
 * Worth it here because the screen is a cinema screen. A QR is readable from
 * roughly ten times its own width, so one projected two metres across carries
 * to about twenty metres — most of the room, rather than the front few rows,
 * which is what makes this the main way in rather than a decoration.
 */
final class JoinQrCode
{
    /**
     * The short form, on purpose.
     *
     * Every character is more modules in the grid, and more modules on a fixed
     * screen width means smaller squares to resolve from the back. /q/ instead
     * of /quiz/ drops it a whole QR version.
     */
    public static function url(Quiz $quiz): string
    {
        return rtrim(config('app.url'), '/').'/q/'.$quiz->code;
    }

    /** The same thing without the scheme, for printing on the screen to read. */
    public static function readableUrl(Quiz $quiz): string
    {
        return preg_replace('~^https?://~', '', self::url($quiz));
    }

    /**
     * Deliberately dark-on-light with a quiet zone, whatever the page around it
     * looks like. Inverted QR codes — light modules on a dark background — are
     * rejected outright by a good number of phone cameras, and the projector
     * screen behind this is nearly black.
     */
    public static function svg(Quiz $quiz, int $size = 420): string
    {
        return (string) QrCode::format('svg')
            ->size($size)
            ->margin(1)
            ->errorCorrection('M')
            ->style('square')
            ->backgroundColor(255, 255, 255)
            ->color(17, 17, 17)
            ->generate(self::url($quiz));
    }
}

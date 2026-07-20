<?php

declare(strict_types=1);

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Playing along in a browser, with nothing installed.
 *
 * The app cannot be the way in on a Sunday: it is not on the App Store yet, and
 * asking a congregation to install a developer tool to join in would be worse
 * than not running the quiz at all. A web page removes the question entirely —
 * open a link, type a name, play — and works the same on an iPhone as on an
 * Android.
 *
 * That also changes what the app is for, in a healthier way. It stops being the
 * price of joining in and becomes the upgrade: your score kept, your history,
 * your notes and readings. A far easier thing to offer someone who has just
 * enjoyed playing than to demand of them beforehand.
 *
 * Guest play only, deliberately. Asking for a password on a phone in a service
 * is exactly the friction this page exists to remove, and a guest who signs in
 * on the app afterwards keeps the score anyway — the device token that makes
 * that work is issued here too.
 */
final class WebPlayerController extends Controller
{
    public function show(string $code): View
    {
        $quiz = Quiz::where('code', strtoupper($code))->first();

        if ($quiz === null) {
            throw new HttpException(404, 'No quiz found with that code.');
        }

        return view('quiz.play', ['quiz' => $quiz]);
    }
}

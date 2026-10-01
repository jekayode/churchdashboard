<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Join — {{ $quiz->title }}</title>
    {{--
        Put on the screen before the quiz starts, so it must stand entirely on
        its own: no player counts, nothing that assumes the quiz has been opened,
        and nothing that changes while it is up. It can go on ten minutes early.
    --}}
    <style>
        :root { --brand: #E8541E; --ink: #FFFFFF; --muted: rgba(255,255,255,.62); }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #0B0B0F; color: var(--ink); min-height: 100vh; overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 3vh 3vw; text-align: center;
        }
        h1 { font-size: 5vh; font-weight: 800; margin-bottom: .6vh; }
        .kicker { font-size: 2.2vh; letter-spacing: .3vh; text-transform: uppercase; color: var(--muted); margin-bottom: 2vh; }

        /*
         * width:100% matters. Left to size itself the row measured its content
         * but not the gap between, coming out six tenths of a pixel narrower
         * than it needed — so on a cinema screen, with metres of space spare,
         * the QR and the code stacked instead of sitting side by side.
         */
        .split { display: flex; width: 100%; align-items: center; justify-content: center; gap: 6vw; flex-wrap: wrap; }

        /*
         * The QR keeps its own white surround whatever the page behind it looks
         * like. Inverted codes — light modules on dark — are refused outright by
         * a good number of phone cameras, and this page is nearly black.
         */
        .qr { background: #fff; padding: 2vh; border-radius: 1.6vh; line-height: 0; }
        /* As large as the height allows. Scanning range scales directly with
           the printed size — every centimetre here is metres of room. */
        .qr svg { width: 62vh; height: 62vh; display: block; }

        .alt { text-align: left; }
        .alt .or { font-size: 2.4vh; color: var(--muted); margin-bottom: 1.6vh; }
        .alt .url { font-size: 4vh; font-weight: 700; margin-bottom: 3.4vh; word-break: break-all; }
        .alt .code-label { font-size: 2vh; letter-spacing: .3vh; text-transform: uppercase; color: var(--muted); }
        .alt .code { font-size: 13vh; font-weight: 800; letter-spacing: 1.2vh; line-height: 1.05; font-variant-numeric: tabular-nums; }

        /* Independence: the QR sits between two green panels, like the flag. */
        .flag-band { position: fixed; top: 0; left: 0; }
        .theme-nigeria .qr { box-shadow: -6vh 0 0 0 #008751, 6vh 0 0 0 #008751; }
        .theme-nigeria .split { gap: 10vw; }
        .occasion { font-size: 3vh; font-weight: 800; color: #00A862; margin-bottom: 1vh; }

        .foot { margin-top: 3vh; font-size: 2.4vh; color: var(--muted); }

        /* Everything is sized in vh so the layout holds on a cinema screen,
           which is far wider than the 16:9 a browser assumes. */
        @media (max-aspect-ratio: 1/1) {
            .split { flex-direction: column; gap: 4vh; }
            .alt { text-align: center; }
        }
    </style>
</head>
<body class="theme-{{ $quiz->theme->value }}">
    @include('quiz.partials.flag-band')
    @if ($quiz->theme === \App\Enums\QuizTheme::Nigeria)
        <div class="occasion">Happy Independence · Nigeria @ {{ now()->year - 1960 }}</div>
    @endif
    <div class="kicker">{{ $quiz->title }}</div>
    <h1>Join the quiz</h1>

    <div class="split">
        <div class="qr">{!! $qr !!}</div>

        <div class="alt">
            <div class="or">Point your camera at the code, or go to</div>
            <div class="url">{{ $url }}</div>
            <div class="code-label">Quiz code</div>
            <div class="code">{{ $quiz->code }}</div>
        </div>
    </div>

    <div class="foot">No app needed — it opens straight in your browser.</div>
</body>
</html>

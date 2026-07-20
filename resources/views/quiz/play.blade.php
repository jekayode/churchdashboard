<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    {{-- viewport-fit and user-scalable=no: this is a game of tapping quickly,
         and a double tap that zooms instead of answering costs the round. --}}
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#DD5D20">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $quiz->title }}</title>
    <style>
        :root {
            --brand: #DD5D20; --amber: #F79000;
            --bg: #FBF4EA; --card: #FFFFFF; --ink: #241813; --sub: #6E4A34;
            --muted: #9C7A64; --border: #EBDCC6; --chip: #F3E9D8;
            --a: #E8541E; --b: #2563EB; --c: #16A34A; --d: #9333EA;
            --good: #16A34A; --bad: #D5341A;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }
        body {
            background: var(--bg); color: var(--ink); min-height: 100dvh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            padding: env(safe-area-inset-top) 16px calc(env(safe-area-inset-bottom) + 20px);
            display: flex; flex-direction: column;
        }
        .head { display: flex; align-items: center; justify-content: space-between; padding: 16px 0 12px; }
        .head h1 { font-size: 17px; font-weight: 700; }
        .head .code { font-size: 13px; color: var(--muted); letter-spacing: .12em; font-weight: 700; }

        .card { background: var(--card); border-radius: 18px; padding: 18px; box-shadow: 0 2px 14px rgba(36,24,19,.07); }

        .me { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .me .name { font-weight: 700; font-size: 16px; }
        .me .rank { font-size: 13px; color: var(--muted); margin-top: 2px; }
        .me .score { font-size: 26px; font-weight: 800; color: var(--brand); font-variant-numeric: tabular-nums; }

        .counter { font-size: 12px; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); font-weight: 700; }
        .question { font-size: 23px; font-weight: 700; line-height: 1.3; margin: 8px 0 16px; }

        .timerwrap { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
        .timer { flex: 1; height: 8px; background: var(--chip); border-radius: 8px; overflow: hidden; }
        .timer > div { height: 100%; background: var(--brand); border-radius: 8px; transition: width .2s linear; }
        .secs { font-size: 17px; font-weight: 800; min-width: 30px; text-align: right; font-variant-numeric: tabular-nums; }

        .options { display: grid; gap: 12px; }
        /* Deliberately large: thumbs, in a dim room, in a hurry. */
        .opt {
            min-height: 66px; border: none; border-radius: 16px; color: #fff;
            font-size: 17px; font-weight: 700; text-align: left; padding: 16px 18px;
            display: flex; align-items: center; justify-content: space-between; gap: 10px;
            width: 100%; font-family: inherit; cursor: pointer;
            transition: opacity .2s, transform .1s;
        }
        .opt:active { transform: scale(.985); }
        .opt:nth-child(1) { background: var(--a); } .opt:nth-child(2) { background: var(--b); }
        .opt:nth-child(3) { background: var(--c); } .opt:nth-child(4) { background: var(--d); }
        .opt[disabled] { cursor: default; }
        .opt.faded { opacity: .3; }
        .opt.mine { outline: 3px solid var(--ink); outline-offset: 2px; }
        .opt.right { outline: 3px solid #fff; outline-offset: -6px; }

        .big { font-size: 26px; font-weight: 800; text-align: center; }
        .sub { font-size: 15px; color: var(--sub); text-align: center; margin-top: 8px; line-height: 1.5; }
        .centre { text-align: center; padding: 26px 0; }

        .wait { text-align: center; margin-top: 16px; }
        .wait .lead { font-size: 13px; color: var(--muted); font-weight: 600; }
        .wait .pos { font-size: 30px; font-weight: 800; color: var(--brand); margin-top: 2px; }
        .wait .of { font-size: 14px; color: var(--sub); margin-top: 4px; }

        .verdict { text-align: center; margin-top: 16px; font-size: 17px; font-weight: 700; }
        .verdict.good { color: var(--good); } .verdict.bad { color: var(--bad); }

        label { display: block; font-size: 14px; font-weight: 600; color: var(--sub); margin-bottom: 8px; }
        input[type=text] {
            width: 100%; border: 1px solid var(--border); background: #F6EEE1; border-radius: 14px;
            padding: 16px; font-size: 17px; font-family: inherit; color: var(--ink);
        }
        input[type=text]:focus { outline: 2px solid var(--brand); border-color: transparent; }
        .btn {
            width: 100%; border: none; border-radius: 14px; background: var(--brand); color: #fff;
            font-size: 17px; font-weight: 700; padding: 17px; margin-top: 14px;
            font-family: inherit; cursor: pointer;
        }
        .btn[disabled] { background: var(--chip); color: var(--muted); }
        .btn.ghost { background: transparent; color: var(--brand); border: 1px solid var(--border); }
        .err { color: var(--bad); font-size: 14px; font-weight: 600; margin-top: 12px; text-align: center; }

        .board { margin-top: 20px; }
        .board h2 { font-size: 16px; margin-bottom: 10px; }
        .row { display: flex; align-items: center; gap: 12px; background: var(--card); border-radius: 12px; padding: 11px 14px; margin-bottom: 8px; }
        .row.mine { outline: 2px solid var(--brand); }
        .row .r { font-weight: 800; color: var(--brand); width: 22px; }
        .row .n { flex: 1; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .row .s { font-weight: 800; font-variant-numeric: tabular-nums; }

        .cta { margin-top: 22px; text-align: center; }
        .cta p { font-size: 15px; color: var(--sub); line-height: 1.5; }
        .hidden { display: none !important; }
        main { flex: 1; }
    </style>
</head>
<body>
    <div class="head">
        <h1>{{ $quiz->title }}</h1>
        <span class="code">{{ $quiz->code }}</span>
    </div>

    <main id="main">
        <div class="card centre"><p class="sub">Loading…</p></div>
    </main>

<script>
(function () {
    const CODE = @json($quiz->code);
    const CSRF = document.querySelector('meta[name=csrf-token]').content;
    const TOKEN_KEY = 'lifepointe.quiz.device';
    const main = document.getElementById('main');

    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => (
        { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
    ));

    /*
     * The same device token the app uses, kept in localStorage. It outlives this
     * quiz on purpose: it is what lets someone who played here as a guest claim
     * the score if they install the app and sign in later.
     */
    const token = () => localStorage.getItem(TOKEN_KEY);
    const setToken = (t) => localStorage.setItem(TOKEN_KEY, t);

    async function api(path, options = {}) {
        const response = await fetch(path, {
            ...options,
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                // Sanctum treats same-origin requests as stateful, which brings
                // CSRF with it. Guests have no session, but the check still runs.
                'X-CSRF-TOKEN': CSRF,
                ...(options.headers || {}),
            },
        });

        const body = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw Object.assign(new Error(body.message || 'Something went wrong.'), { body, status: response.status });
        }

        return body;
    }

    let state = null, remaining = 0, phaseDuration = 1, pending = null, joining = false, error = null;
    let lastKey = null;

    // ---- rendering -------------------------------------------------------

    function ordinal(n) {
        const s = ['th', 'st', 'nd', 'rd'];
        return n + (s[(n % 100 - 20) % 10] || s[n % 100] || s[0]);
    }

    function renderJoin() {
        main.innerHTML = `
            <div class="card">
                <label for="name">Your name</label>
                <input type="text" id="name" maxlength="24" autocomplete="given-name"
                       placeholder="What should we call you?" enterkeyhint="go">
                <button class="btn" id="join">Join the quiz</button>
                <p class="sub" style="margin-top:14px">This is what everyone sees on the screen.</p>
                ${error ? `<p class="err">${esc(error)}</p>` : ''}
            </div>`;

        const input = document.getElementById('name');
        const button = document.getElementById('join');
        const go = () => join(input.value);

        button.addEventListener('click', go);
        input.addEventListener('keydown', (e) => { if (e.key === 'Enter') go(); });
        if (joining) { button.disabled = true; button.textContent = 'Joining…'; }
    }

    function meBlock() {
        if (!state.me) return '';
        const rank = state.state.phase === 'lobby'
            ? 'Ready'
            : `${ordinal(state.me.rank)} of ${state.participant_count}`;

        return `<div class="card me">
            <div><div class="name">${esc(state.me.name)}</div><div class="rank">${rank}</div></div>
            <div class="score">${state.me.score.toLocaleString()}</div>
        </div>`;
    }

    function renderLobby() {
        main.innerHTML = meBlock() + `
            <div class="card centre">
                <p class="big">You’re in</p>
                <p class="sub">${state.participant_count} ${state.participant_count === 1 ? 'person' : 'people'} waiting.<br>
                   Look up at the screen — it starts in a moment.</p>
            </div>`;
    }

    function renderQuestion() {
        const q = state.question;
        const revealed = state.state.phase === 'reveal';
        const answered = state.me ? state.me.answered_option_id : null;

        const options = q.options.map((o) => {
            const classes = ['opt'];
            if (revealed && !o.is_correct && answered !== o.id) classes.push('faded');
            if (answered === o.id) classes.push('mine');
            if (revealed && o.is_correct) classes.push('right');
            const locked = revealed || answered !== null || state.state.paused;

            return `<button class="${classes.join(' ')}" data-id="${o.id}" ${locked ? 'disabled' : ''}>
                        <span>${esc(o.text)}</span>${pending === o.id ? '<span>…</span>' : ''}
                        ${revealed && o.is_correct ? '<span>✓</span>' : ''}
                    </button>`;
        }).join('');

        // The bar belongs to the answering window only — leaving it up through
        // the reveal reads as "there is still time".
        const timer = revealed ? '' : `
            <div class="timerwrap">
                <div class="timer"><div id="fill" style="width:100%"></div></div>
                <div class="secs" id="secs"></div>
            </div>`;

        let footer = '';

        if (revealed && state.me) {
            const verdict = state.me.answered_option_id === null
                ? '<p class="verdict">No answer that time.</p>'
                : state.me.answer_was_correct
                    ? `<p class="verdict good">Correct — ${(state.me.points_from_answer || 0).toLocaleString()} points</p>`
                    : '<p class="verdict bad">Not that one.</p>';
            footer = verdict;
        } else if (answered !== null && state.me) {
            /* The wait cannot be skipped: everyone has to start the next
               question together or answering quickly stops being worth
               anything. So it gets something to watch instead. */
            footer = `<div class="wait">
                <div class="lead">Answer locked in</div>
                <div class="pos">${ordinal(state.me.rank)}</div>
                <div class="of">${state.answered_count ?? 0} of ${state.participant_count} answered</div>
            </div>`;
        }

        main.innerHTML = meBlock() + `
            <div class="card">
                <div class="counter">Question ${q.number} of ${state.state.question_count}</div>
                <div class="question">${esc(q.text)}</div>
                ${timer}
                <div class="options">${options}</div>
                ${footer}
                ${error ? `<p class="err">${esc(error)}</p>` : ''}
            </div>`;

        main.querySelectorAll('.opt:not([disabled])').forEach((button) => {
            button.addEventListener('click', () => answer(Number(button.dataset.id)));
        });
    }

    function renderFinished() {
        const rows = state.leaderboard.slice(0, 5).map((p) => `
            <div class="row ${state.me && p.participant_id === state.me.participant_id ? 'mine' : ''}">
                <span class="r">${p.rank}</span><span class="n">${esc(p.name)}</span>
                <span class="s">${p.score.toLocaleString()}</span>
            </div>`).join('');

        const mine = state.me ? `
            <div class="card centre">
                <p class="big">That’s the lot</p>
                <p class="sub">You finished ${ordinal(state.me.rank)} of ${state.participant_count},
                   with ${state.me.score.toLocaleString()} points from ${state.me.correct_count} correct.</p>
            </div>` : '';

        /* The pitch lands better here than before anyone had played: they have
           a score in front of them, and the app is what keeps it. */
        main.innerHTML = mine + `
            <div class="board"><h2>Final scores</h2>${rows}</div>
            <div class="cta">
                <p><strong>Want to keep your score?</strong><br>
                   Get the LifePointe app and your history follows you — plus your
                   notes, the daily reading and what’s on this week.</p>
            </div>`;
    }

    function paint() {
        if (!state) return;

        /*
         * The join screen must survive the poll. Re-rendering it every 1.5s
         * wipes whatever the person is halfway through typing — their name
         * vanished letter by letter as they entered it — so it is only redrawn
         * when something about it has actually changed.
         */
        if (!state.me && state.quiz.status !== 'finished') {
            const joinKey = ['join', joining, error].join('|');

            if (joinKey !== lastKey) { renderJoin(); lastKey = joinKey; }

            return;
        }

        const phase = state.state.phase;
        const key = [phase, state.question ? state.question.id : null,
                     state.me ? state.me.answered_option_id : null, pending, error].join('|');

        // Only rebuild when something actually changed, so a tap is not lost to
        // a re-render landing between finger down and finger up.
        if (key !== lastKey) {
            if (phase === 'lobby') renderLobby();
            else if (phase === 'finished') renderFinished();
            else renderQuestion();
            lastKey = key;
        }

        remaining = state.state.remaining_ms;
        phaseDuration = state.state.phase_duration_ms || 1;
    }

    // ---- actions ---------------------------------------------------------

    async function join(name) {
        if (!name || name.trim().length < 2) { error = 'Please put in a name.'; lastKey = null; paint(); return; }

        joining = true; error = null; lastKey = null; renderJoin();

        try {
            const result = await api('/api/quiz/join', {
                method: 'POST',
                body: JSON.stringify({ code: CODE, name: name.trim(), device_token: token() || undefined }),
            });
            setToken(result.device_token);
            state = result;
            lastKey = null;
        } catch (e) {
            error = e.message;
        } finally {
            joining = false;
            lastKey = null;
            paint();
        }
    }

    async function answer(optionId) {
        pending = optionId; error = null; lastKey = null; paint();

        try {
            state = await api(`/api/quiz/${CODE}/answer`, {
                method: 'POST',
                body: JSON.stringify({ device_token: token(), option_id: optionId }),
            });
        } catch (e) {
            // The server decides whether an answer counted, so show its wording
            // rather than guessing, and re-sync straight away.
            error = e.message;
            await poll();
        } finally {
            pending = null; lastKey = null; paint();
        }
    }

    async function poll() {
        try {
            const t = token();
            state = await api(`/api/quiz/${CODE}/state${t ? `?device_token=${encodeURIComponent(t)}` : ''}`);
            paint();
        } catch (e) {
            // Church wifi drops. Keep the last good screen and try again.
        }
    }

    setInterval(poll, 1500);
    setInterval(() => {
        if (!state || state.state.phase !== 'question' || state.state.paused) return;
        remaining = Math.max(0, remaining - 100);
        const fill = document.getElementById('fill');
        if (fill) fill.style.width = `${Math.max(0, (remaining / phaseDuration) * 100)}%`;
        const secs = document.getElementById('secs');
        if (secs) secs.textContent = Math.ceil(remaining / 1000);
    }, 100);

    poll();
})();
</script>
</body>
</html>

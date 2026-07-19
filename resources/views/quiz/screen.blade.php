<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $quiz->title }}</title>
    {{--
        Standalone rather than part of the dashboard layout. This is opened on
        whatever machine drives the projector, read from the back of the room,
        and nobody can fix it once the service has started — so it carries its
        own styling and depends on nothing else loading.
    --}}
    <style>
        :root {
            --brand: #E8541E;
            --ink: #FFFFFF;
            --muted: rgba(255, 255, 255, .62);
            --a: #E8541E; --b: #2563EB; --c: #16A34A; --d: #9333EA;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #0B0B0F; color: var(--ink); min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex; flex-direction: column; overflow: hidden;
        }
        .bar { display: flex; justify-content: space-between; align-items: center; padding: 1.4vw 2.4vw; font-size: 1.5vw; color: var(--muted); }
        .bar strong { color: var(--ink); }
        .stage { flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 0 4vw; text-align: center; }

        /* Everything is sized in vw so it scales to whatever the projector is. */
        .join-label { font-size: 2.2vw; color: var(--muted); letter-spacing: .3vw; text-transform: uppercase; }
        .code { font-size: 15vw; font-weight: 800; letter-spacing: 1.5vw; line-height: 1; margin: 1vw 0 2vw; font-variant-numeric: tabular-nums; }
        .join-hint { font-size: 2vw; color: var(--muted); }
        .count { margin-top: 3vw; font-size: 3vw; font-weight: 700; }

        .qnum { font-size: 1.8vw; color: var(--muted); letter-spacing: .2vw; text-transform: uppercase; }
        .question { font-size: 4.6vw; font-weight: 700; line-height: 1.15; margin: 1.5vw 0 3vw; max-width: 88vw; }

        .options { display: grid; grid-template-columns: 1fr 1fr; gap: 1.4vw; width: 92vw; }
        .opt {
            display: flex; align-items: center; gap: 1.4vw; padding: 2vw 2.4vw; border-radius: 1.2vw;
            font-size: 2.8vw; font-weight: 600; text-align: left; transition: opacity .3s, transform .3s;
        }
        .opt:nth-child(1) { background: var(--a); } .opt:nth-child(2) { background: var(--b); }
        .opt:nth-child(3) { background: var(--c); } .opt:nth-child(4) { background: var(--d); }
        .opt .tally { margin-left: auto; font-size: 2vw; opacity: .85; font-variant-numeric: tabular-nums; }
        /* On the reveal, the wrong answers recede rather than disappear, so the
           room can still see what it was choosing between. */
        .opt.dim { opacity: .28; transform: scale(.97); }
        .opt.right { box-shadow: 0 0 0 .6vw #FFF inset; }

        .timer { width: 92vw; height: 1.2vw; background: rgba(255,255,255,.14); border-radius: 1vw; overflow: hidden; margin-top: 3vw; }
        .timer > div { height: 100%; background: var(--brand); border-radius: 1vw; transition: width .25s linear; }

        .board { width: 70vw; }
        .board h2 { font-size: 3.4vw; margin-bottom: 2vw; }
        .row { display: flex; align-items: center; gap: 1.6vw; padding: 1.1vw 2vw; border-radius: 1vw; background: rgba(255,255,255,.07); margin-bottom: .9vw; font-size: 2.4vw; }
        .row .rank { width: 3.5vw; font-weight: 800; color: var(--brand); }
        .row .name { flex: 1; text-align: left; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .row .score { font-weight: 800; font-variant-numeric: tabular-nums; }
        .row:nth-child(1) { background: rgba(232,84,30,.25); }

        .paused { position: fixed; inset: 0; background: rgba(11,11,15,.92); display: flex; align-items: center; justify-content: center; font-size: 6vw; font-weight: 800; }
        .hidden { display: none !important; }
    </style>
</head>
<body>
    <div class="bar">
        <span>{{ $quiz->title }}</span>
        <span id="bar-right"></span>
    </div>

    <div class="stage" id="stage"></div>
    <div class="paused hidden" id="paused">Paused</div>

<script>
(function () {
    const stateUrl = @json(route('quiz.screen.state', ['code' => $quiz->code]));
    const stage = document.getElementById('stage');
    const barRight = document.getElementById('bar-right');
    const pausedEl = document.getElementById('paused');

    const escape = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => (
        { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
    ));

    /*
     * The countdown is driven locally between polls, because a bar that only
     * moved when the network replied would visibly stutter in front of everyone.
     * Each poll re-anchors it, so it cannot drift away from the server.
     */
    let remaining = 0, phaseDuration = 1, lastPhase = null, lastQuestionId = null;

    function renderLobby(data) {
        stage.innerHTML = `
            <div class="join-label">Join at</div>
            <div class="code">${escape(data.quiz.code ?? '—')}</div>
            <div class="join-hint">Open the LifePointe app and tap Quiz</div>
            <div class="count">${data.participant_count} ${data.participant_count === 1 ? 'player' : 'players'} in</div>`;
    }

    function renderQuestion(data, revealed) {
        const q = data.question;
        const counts = data.answer_counts || {};
        const options = q.options.map((o) => {
            const classes = ['opt'];
            if (revealed) classes.push(o.is_correct ? 'right' : 'dim');
            const tally = revealed && counts[o.id] ? `<span class="tally">${counts[o.id]}</span>` : '';
            return `<div class="${classes.join(' ')}"><span>${escape(o.text)}</span>${tally}</div>`;
        }).join('');

        stage.innerHTML = `
            <div class="qnum">Question ${q.number} of ${data.state.question_count}</div>
            <div class="question">${escape(q.text)}</div>
            <div class="options">${options}</div>
            <div class="timer"><div id="timer-fill" style="width:100%"></div></div>`;
    }

    function renderFinished(data) {
        const rows = data.leaderboard.slice(0, 8).map((p) => `
            <div class="row">
                <span class="rank">${p.rank}</span>
                <span class="name">${escape(p.name)}</span>
                <span class="score">${p.score.toLocaleString()}</span>
            </div>`).join('');

        stage.innerHTML = `<div class="board"><h2>Final scores</h2>${rows || '<p>No players</p>'}</div>`;
    }

    function paint(data) {
        const phase = data.state.phase;
        const questionId = data.question ? data.question.id : null;

        pausedEl.classList.toggle('hidden', !data.state.paused);
        barRight.textContent = phase === 'lobby'
            ? `${data.participant_count} joined`
            : (data.state.question_number ? `${data.participant_count} playing` : '');

        // Only rebuild when the view actually changes, so the timer bar is not
        // thrown away and restarted on every poll.
        if (phase !== lastPhase || questionId !== lastQuestionId) {
            if (phase === 'lobby') renderLobby(data);
            else if (phase === 'question') renderQuestion(data, false);
            else if (phase === 'reveal') renderQuestion(data, true);
            else renderFinished(data);
            lastPhase = phase;
            lastQuestionId = questionId;
        } else if (phase === 'lobby') {
            renderLobby(data);
        }

        remaining = data.state.remaining_ms;
        phaseDuration = data.state.phase_duration_ms || 1;
    }

    async function poll() {
        try {
            const response = await fetch(stateUrl, { headers: { Accept: 'application/json' } });
            if (response.ok) paint(await response.json());
        } catch (e) {
            // A projector cannot show an error usefully. Keep the last good
            // frame on screen and try again on the next tick.
        }
    }

    setInterval(poll, 1500);
    setInterval(() => {
        if (lastPhase !== 'question' || !pausedEl.classList.contains('hidden')) return;
        remaining = Math.max(0, remaining - 100);
        const fill = document.getElementById('timer-fill');
        if (fill) fill.style.width = `${Math.max(0, (remaining / phaseDuration) * 100)}%`;
    }, 100);

    poll();
})();
</script>
</body>
</html>

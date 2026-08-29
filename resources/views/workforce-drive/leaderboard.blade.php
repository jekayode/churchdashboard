<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Workforce Drive — Leaderboard</title>
    {{--
        Standalone LifePointe slide, driven off the projector. Follows the deck
        design system: warm-ink stage, Amaranth/Quicksand, the lemon-dot kicker,
        white cards (the leader in a solid-orange card) and orange rank circles.
        The QR is rendered once into the page; only the counts refresh after that.
    --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&family=Amaranth:wght@400;700&family=Pacifico&display=swap" rel="stylesheet">
    <style>
        :root {
            /* LifePointe colour tokens */
            --orange: #DD5D20; --orange-deep: #C24E16; --amber: #F79000;
            --lemon: #B6DF19; --brown: #7A3B12; --purple: #6B3FA0;
            --ink: #241813; --cream: #FBF4EA; --cream-deep: #EFE3CF; --white: #FFFFFF;
            --font-heading: 'Amaranth', 'Quicksand', system-ui, sans-serif;
            --font-body: 'Quicksand', 'Trebuchet MS', system-ui, sans-serif;
            --font-script: 'Pacifico', cursive;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--ink); color: var(--cream); min-height: 100vh; overflow: hidden;
            font-family: var(--font-body);
        }
        .slide { position: relative; height: 100vh; width: 100vw; padding: 6vh 5vw; display: flex; gap: 3vw; }

        /* Decorative brand rings — the deck marks every slide with these. */
        .ring { position: absolute; border-radius: 999px; pointer-events: none; }
        .ring-a { width: 46vw; height: 46vw; border: 5vw solid var(--orange); opacity: .10; top: -20vw; right: -14vw; }
        .ring-b { width: 16vw; height: 16vw; background: var(--purple); opacity: .16; bottom: -6vw; left: -5vw; }

        .logo { position: absolute; top: 5vh; right: 3vw; height: 6vh; z-index: 3; }

        /* Left — leaderboard */
        .board { position: relative; z-index: 2; flex: 1 1 64%; display: flex; flex-direction: column; min-width: 0; }
        .kicker {
            display: inline-flex; align-items: center; gap: .9vw;
            font-weight: 700; font-size: 1.35vw; letter-spacing: .16em; text-transform: uppercase;
            color: var(--amber);
        }
        .kicker .dot { width: .95vw; height: .95vw; border-radius: 999px; background: var(--lemon); }
        h1 {
            font-family: var(--font-heading); font-weight: 700; color: var(--white);
            font-size: 4.6vw; line-height: 1.02; margin-top: 1vh; text-transform: none;
        }
        .cards { position: relative; flex: 1; margin-top: 3vh; }

        .card {
            position: absolute; left: 0; right: 0;
            display: flex; align-items: center; gap: 1.8vw;
            background: var(--white); color: var(--ink);
            border: 1px solid var(--cream-deep); border-radius: 1.46vw;
            box-shadow: 0 1.5vw 4vw -2.5vw rgba(60, 30, 10, .45);
            padding: 1.4vh 2vw;
            transition: transform .6s cubic-bezier(.22,.61,.36,1), background-color .4s ease, color .4s ease;
            will-change: transform;
        }
        .rank {
            flex: none; width: 4vw; height: 4vw; border-radius: 999px;
            background: var(--orange); color: var(--white);
            font-family: var(--font-heading); font-weight: 700; font-size: 1.9vw;
            display: flex; align-items: center; justify-content: center;
        }
        .card .body { flex: 1; min-width: 0; }
        .card .name {
            font-family: var(--font-heading); font-weight: 700; color: var(--orange);
            font-size: 2.1vw; line-height: 1.1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .card .meta { color: var(--brown); font-size: 1.15vw; margin-top: .3vh; font-weight: 500; }
        .card .meta b { color: var(--ink); font-weight: 700; }
        .card .count { font-family: var(--font-heading); font-weight: 700; color: var(--orange); font-size: 3.4vw; line-height: 1; }

        /* The leader card — solid orange, like the deck's featured card. */
        .card.rank1 { background: var(--orange); color: var(--white); border-color: var(--orange); }
        .card.rank1 .rank { background: var(--white); color: var(--orange); }
        .card.rank1 .name,
        .card.rank1 .count { color: var(--white); }
        .card.rank1 .meta { color: rgba(255,255,255,.9); }
        .card.rank1 .meta b { color: var(--white); }

        .empty { color: var(--cream); opacity: .7; font-size: 1.5vw; margin-top: 4vh; }

        /* Right — scan to join + running total */
        .side {
            position: relative; z-index: 2; flex: 0 0 30%; align-self: center;
            display: flex; flex-direction: column; gap: 2.6vh;
        }
        .total { text-align: center; color: var(--cream); opacity: .9; font-size: 1.4vw; line-height: 1.3; }
        .total strong { display: block; font-family: var(--font-heading); color: var(--amber); font-size: 3.6vw; line-height: 1; }
        .join {
            display: flex; flex-direction: column; border-radius: 1.46vw; overflow: hidden;
            box-shadow: 0 1.5vw 4vw -2.5vw rgba(60, 30, 10, .45);
        }
        .join .head {
            background: var(--orange); color: var(--white); text-align: center;
            font-family: var(--font-heading); font-weight: 700; text-transform: uppercase;
            letter-spacing: .05em; font-size: 1.9vw; padding: 2.4vh 1vw;
            display: flex; align-items: center; justify-content: center; gap: 1vw;
        }
        .join .head .dot { width: 1vw; height: 1vw; border-radius: 999px; background: var(--lemon); }
        .join .panel { background: var(--white); padding: 3vh 2vw; text-align: center; }
        .join .qr { width: 100%; max-width: 20vw; margin: 0 auto; }
        .join .qr svg { width: 100%; height: auto; display: block; }
        .join .caption { font-family: var(--font-script); color: var(--orange); font-size: 1.7vw; margin-top: 1.6vh; line-height: 1; }
        .join .url { color: var(--brown); font-size: 1.05vw; font-weight: 600; margin-top: 1vh; word-break: break-all; }
    </style>
</head>
<body>
    <div class="slide">
        <span class="ring ring-a"></span>
        <span class="ring ring-b"></span>
        <img class="logo" src="{{ asset('img/lifepointe-logo-white.png') }}" alt="LifePointe">

        <div class="board">
            <span class="kicker"><span class="dot"></span>Workforce Drive</span>
            <h1>Leaderboard</h1>
            <div class="cards" id="cards">
                <div class="empty" id="empty">Waiting for the first sign-up…</div>
            </div>
        </div>

        <div class="side">
            <div class="join">
                <div class="head"><span class="dot"></span>Scan to Join</div>
                <div class="panel">
                    <div class="qr">{!! $qr !!}</div>
                    <div class="caption">join a team</div>
                    <div class="url">{{ $readableJoinUrl }}</div>
                </div>
            </div>
            <div class="total"><strong id="total">0</strong> <span id="total-label">people have joined a team</span></div>
        </div>
    </div>

<script>
(function () {
    const stateUrl = @json(route('workforce-drive.leaderboard.state'));
    const cardsEl = document.getElementById('cards');
    const emptyEl = document.getElementById('empty');
    const totalEl = document.getElementById('total');
    const totalLabelEl = document.getElementById('total-label');

    const escape = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => (
        { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
    ));

    // Fixed row height (vh) so cards can be absolutely positioned and slide to
    // their new rank with a transform rather than jumping on each poll.
    const ROW_H = 12.4; // vh, including the gap below each card
    const cardEls = new Map(); // team id -> element

    function cardMarkup(team, rank) {
        const meta = [];
        if (team.sponsor_name) meta.push(`Sponsor: <b>${escape(team.sponsor_name)}</b>`);
        if (team.core_value) meta.push(`Core Value: <b>${escape(team.core_value)}</b>`);
        return `
            <div class="rank">${rank}</div>
            <div class="body">
                <div class="name">${escape(team.name)}</div>
                ${meta.length ? `<div class="meta">${meta.join('&nbsp;&nbsp;·&nbsp;&nbsp;')}</div>` : ''}
            </div>
            <div class="count">${team.count}</div>`;
    }

    function paint(data) {
        const teams = data.teams || [];
        const total = data.total ?? 0;
        totalEl.textContent = total.toLocaleString();
        totalLabelEl.textContent = total === 1 ? 'person has joined a team' : 'people have joined a team';
        emptyEl.style.display = teams.length ? 'none' : 'block';

        const seen = new Set();
        teams.forEach((team, i) => {
            seen.add(team.id);
            let el = cardEls.get(team.id);
            if (!el) {
                el = document.createElement('div');
                el.className = 'card';
                cardEls.set(team.id, el);
                cardsEl.appendChild(el);
            }
            // Only the outright leader (a non-zero top count) wears the orange card.
            el.classList.toggle('rank1', i === 0 && team.count > 0);
            el.innerHTML = cardMarkup(team, i + 1);
            el.style.transform = `translateY(${i * ROW_H}vh)`;
        });

        // Drop teams that vanished (e.g. deactivated mid-drive).
        for (const [id, el] of cardEls) {
            if (!seen.has(id)) { el.remove(); cardEls.delete(id); }
        }
    }

    async function poll() {
        try {
            const response = await fetch(stateUrl, { headers: { Accept: 'application/json' } });
            if (response.ok) paint(await response.json());
        } catch (e) {
            // A projector cannot show an error usefully — keep the last good
            // frame on screen and try again on the next tick.
        }
    }

    setInterval(poll, 2000);
    poll();
})();
</script>
</body>
</html>

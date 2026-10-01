{{--
    The finish: medals for the top three and a confetti burst for the winner.

    Inline rather than a library from a CDN. The screen is built to keep
    running on a weak church connection, and the finale is the one moment
    nobody will forgive it failing to load.
--}}
<script>
window.quizCelebration = (function () {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const theme = @json($quiz->theme->value);

    const palettes = {
        nigeria: ['#008751', '#FFFFFF', '#00A862', '#F5C542', '#FFFFFF'],
        standard: ['#E8541E', '#2563EB', '#16A34A', '#9333EA', '#F5C542', '#FFFFFF'],
    };

    const metals = {
        1: { face: '#F5C542', edge: '#B8860B' },
        2: { face: '#DDE1E6', edge: '#8A939E' },
        3: { face: '#D7894A', edge: '#8F4F22' },
    };

    /* Drawn rather than an emoji: emoji differ on every machine and can come
       out as an empty box on an old projector PC. */
    function medal(rank) {
        const metal = metals[rank];
        if (!metal) return '';

        const ribbon = theme === 'nigeria'
            ? '<rect x="11" y="0" width="6" height="26" fill="#008751"/>'
              + '<rect x="17" y="0" width="6" height="26" fill="#FFFFFF"/>'
              + '<rect x="23" y="0" width="6" height="26" fill="#008751"/>'
            : '<rect x="11" y="0" width="18" height="26" fill="#E8541E"/>'
              + '<rect x="17" y="0" width="6" height="26" fill="#FFFFFF" opacity=".35"/>';

        return `<svg class="medal" viewBox="0 0 40 56" role="img" aria-label="Place ${rank}">
            ${ribbon}
            <circle cx="20" cy="38" r="15" fill="${metal.face}" stroke="${metal.edge}" stroke-width="2.5"/>
            <circle cx="20" cy="38" r="10" fill="none" stroke="${metal.edge}" stroke-width="1" opacity=".55"/>
            <text x="20" y="42.5" text-anchor="middle" font-size="13" font-weight="800"
                  font-family="Noto Sans, sans-serif" fill="${metal.edge}">${rank}</text>
        </svg>`;
    }

    /*
     * A burst from both bottom corners, then a gentle fall for a few seconds,
     * then it stops and removes itself, so it is over before anyone speaks.
     * Capped in count so an old laptop driving the projector does not stutter.
     */
    function confetti(options = {}) {
        if (reducedMotion) return;

        const palette = palettes[theme] || palettes.standard;
        // White pieces vanish against the phone's light background.
        const colors = options.onLight ? palette.filter((c) => c !== '#FFFFFF') : palette;
        const rainFor = options.rainMs ?? 10000;
        const burstSize = options.burst ?? 80;
        const cap = options.cap ?? 220;

        const canvas = document.createElement('canvas');
        canvas.setAttribute('aria-hidden', 'true');
        canvas.style.cssText = 'position:fixed;inset:0;width:100vw;height:100vh;pointer-events:none;z-index:60';
        document.body.appendChild(canvas);

        const ctx = canvas.getContext('2d');
        const ratio = Math.min(window.devicePixelRatio || 1, 2);
        const resize = () => { canvas.width = innerWidth * ratio; canvas.height = innerHeight * ratio; };
        resize();
        addEventListener('resize', resize);

        const pieces = [];
        const random = (min, max) => min + Math.random() * (max - min);

        function add(x, y, vx, vy) {
            pieces.push({
                x, y, vx, vy,
                w: random(6, 12) * ratio, h: random(10, 18) * ratio,
                spin: random(0, Math.PI * 2), spinSpeed: random(-.25, .25),
                flutter: random(0, Math.PI * 2),
                color: colors[Math.floor(Math.random() * colors.length)],
            });
        }

        function burst() {
            const H = canvas.height, W = canvas.width;
            for (let i = 0; i < burstSize; i++) {
                const speed = H * random(.03, .042);
                const angle = random(-1.35, -.95); // up and inwards
                const vx = Math.cos(angle) * speed, vy = Math.sin(angle) * speed;
                if (i % 2) add(0, H, vx, vy); else add(W, H, -vx, vy);
            }
        }

        const started = performance.now();
        burst();
        setTimeout(burst, 700);

        function frame(now) {
            const W = canvas.width, H = canvas.height;
            const gravity = H * .0006, terminal = H * .006;

            if (now - started < rainFor && pieces.length < cap && Math.random() < .55) {
                add(random(0, W), -20 * ratio, random(-1, 1) * ratio, random(1, 3) * ratio);
            }

            ctx.clearRect(0, 0, W, H);

            for (let i = pieces.length - 1; i >= 0; i--) {
                const p = pieces[i];
                p.vx *= .99;
                p.vy = Math.min(p.vy + gravity, terminal);
                p.x += p.vx + Math.sin(p.flutter) * ratio;
                p.y += p.vy;
                p.spin += p.spinSpeed;
                p.flutter += .08;

                if (p.y > H + 40 * ratio) { pieces.splice(i, 1); continue; }

                ctx.save();
                ctx.translate(p.x, p.y);
                ctx.rotate(p.spin);
                ctx.scale(1, Math.cos(p.flutter));
                ctx.fillStyle = p.color;
                ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
                ctx.restore();
            }

            if (pieces.length === 0 && now - started > rainFor) {
                removeEventListener('resize', resize);
                canvas.remove();
                return;
            }

            requestAnimationFrame(frame);
        }

        requestAnimationFrame(frame);
    }

    return { medal, confetti, reducedMotion };
})();
</script>

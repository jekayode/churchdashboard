<x-sidebar-layout title="Run quiz">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $quiz->title }}</h2>
                <p class="text-sm text-gray-500">Host console</p>
            </div>
            <a href="{{ route('pastor.quizzes') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to quizzes</a>
        </div>
    </x-slot>

    {{--
        Held on a phone while standing in front of the church, so this stays
        deliberately thin. Questions advance by themselves; the live job is to
        start it and watch. Pause and remove are here for when the room needs
        holding or a name needs taking down, not for the normal run.
    --}}
    <div class="max-w-2xl mx-auto space-y-4" x-data="quizHost()" x-init="start()">
        @if (session('success'))
            <div class="rounded-lg border border-church-200 bg-church-50 px-4 py-3 text-church-800">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
        @endif

        {{-- Code and the projector link --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
            <template x-if="state.quiz && state.quiz.code">
                <div>
                    <p class="text-xs uppercase tracking-widest text-gray-400">Join code</p>
                    <p class="text-5xl font-extrabold tracking-[0.2em] text-gray-900 my-2" x-text="state.quiz.code"></p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-2 mt-3">
                        <a href="{{ route('quiz.join-slide', ['code' => $quiz->code]) }}" target="_blank"
                           class="w-full sm:w-auto px-4 py-2 rounded-lg bg-church-500 text-white text-sm font-semibold hover:bg-church-600">
                            Join slide (show before the quiz)
                        </a>
                        <a href="{{ route('quiz.screen', ['code' => $quiz->code]) }}" target="_blank"
                           class="w-full sm:w-auto px-4 py-2 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Projector screen
                        </a>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">
                        Both open without a login — send them to whoever runs the screen.
                    </p>

                    {{-- The link people scan or type. Copyable because it is
                         going into a WhatsApp group, not being retyped. --}}
                    <div class="mt-5 pt-5 border-t border-gray-100">
                        <div class="flex justify-center">{!! $qr !!}</div>
                        <p class="mt-3 text-sm font-mono text-gray-600 break-all">{{ $joinUrl }}</p>
                        <button type="button"
                                x-data="{ copied: false }"
                                @click="navigator.clipboard.writeText(@js($joinUrl)).then(() => {
                                    copied = true; setTimeout(() => copied = false, 2000);
                                })"
                                class="mt-2 px-4 py-2 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            <span x-show="!copied">Copy link for WhatsApp</span>
                            <span x-show="copied" x-cloak class="text-church-600">Copied</span>
                        </button>
                    </div>
                </div>
            </template>
            <template x-if="!state.quiz || !state.quiz.code">
                <p class="text-gray-500">Loading…</p>
            </template>
        </div>

        {{-- What the room is seeing --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500" x-text="phaseLabel()"></p>
                    <p class="text-lg font-semibold text-gray-900" x-text="state.question ? state.question.text : '—'"></p>
                </div>
                <div class="text-right shrink-0 ml-4">
                    <p class="text-3xl font-bold text-gray-900" x-text="state.participant_count ?? 0"></p>
                    <p class="text-xs text-gray-400">playing</p>
                </div>
            </div>
        </div>

        {{-- Controls --}}
        <div class="flex flex-wrap gap-2">
            @if ($quiz->status === 'draft')
                <form method="POST" action="{{ route('pastor.quizzes.open', $quiz) }}">
                    @csrf
                    <button class="px-5 py-3 rounded-xl bg-church-500 text-white font-semibold hover:bg-church-600">Open for joining</button>
                </form>
            @endif

            @if (in_array($quiz->status, ['draft', 'lobby'], true))
                <form method="POST" action="{{ route('pastor.quizzes.start', $quiz) }}">
                    @csrf
                    <button class="px-5 py-3 rounded-xl bg-green-600 text-white font-semibold hover:bg-green-700">Start the quiz</button>
                </form>
            @endif

            @if ($quiz->status === 'running')
                <template x-if="!state.state || !state.state.paused">
                    <form method="POST" action="{{ route('pastor.quizzes.pause', $quiz) }}">
                        @csrf
                        <button class="px-5 py-3 rounded-xl border border-gray-300 font-semibold text-gray-700 hover:bg-gray-50">Pause</button>
                    </form>
                </template>
                <template x-if="state.state && state.state.paused">
                    <form method="POST" action="{{ route('pastor.quizzes.resume', $quiz) }}">
                        @csrf
                        <button class="px-5 py-3 rounded-xl bg-green-600 text-white font-semibold hover:bg-green-700">Resume</button>
                    </form>
                </template>
                <form method="POST" action="{{ route('pastor.quizzes.finish', $quiz) }}"
                      onsubmit="return confirm('End the quiz now and show the final scores?')">
                    @csrf
                    <button class="px-5 py-3 rounded-xl border border-red-200 font-semibold text-red-700 hover:bg-red-50">End now</button>
                </form>
            @endif
        </div>

        {{-- Leaderboard --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <h3 class="px-5 py-3 font-semibold text-gray-900 border-b border-gray-100">Leaderboard</h3>
            <template x-for="player in (state.leaderboard || [])" :key="player.participant_id">
                <div class="flex items-center gap-3 px-5 py-3 border-b border-gray-50 last:border-0">
                    <span class="w-6 font-bold text-church-600" x-text="player.rank"></span>
                    <span class="flex-1 font-medium text-gray-900 truncate" x-text="player.name"></span>
                    <span class="text-xs text-gray-400" x-show="player.is_guest">guest</span>
                    <span class="font-bold text-gray-900 tabular-nums" x-text="player.score.toLocaleString()"></span>
                    <form method="POST" :action="`{{ url('pastor/quizzes/'.$quiz->id.'/participants') }}/${player.participant_id}`"
                          onsubmit="return confirm('Remove this player from the quiz?')">
                        @csrf @method('DELETE')
                        <button class="text-gray-300 hover:text-red-600 text-lg leading-none" title="Remove player">&times;</button>
                    </form>
                </div>
            </template>
            <p class="px-5 py-6 text-center text-gray-400 text-sm" x-show="!(state.leaderboard || []).length">
                Nobody has scored yet.
            </p>
        </div>
    </div>

    @push('scripts')
    <script>
        function quizHost() {
            return {
                state: {},
                start() {
                    this.poll();
                    // Two seconds is plenty: nothing here needs to be frame
                    // accurate, and the projector is the surface people watch.
                    setInterval(() => this.poll(), 2000);
                },
                async poll() {
                    try {
                        const response = await fetch(@json(route('pastor.quizzes.host.state', $quiz)), {
                            headers: { Accept: 'application/json' },
                        });
                        if (response.ok) this.state = await response.json();
                    } catch (e) {
                        // Keep the last good reading rather than blanking the
                        // console in someone's hand mid-service.
                    }
                },
                phaseLabel() {
                    const phase = this.state.state?.phase;
                    if (!phase) return 'Loading…';
                    if (this.state.state.paused) return 'Paused';
                    return {
                        lobby: 'Waiting to start',
                        question: `Question ${this.state.state.question_number} of ${this.state.state.question_count}`,
                        reveal: 'Showing the answer',
                        finished: 'Finished',
                    }[phase] ?? phase;
                },
            };
        }
    </script>
    @endpush
</x-sidebar-layout>

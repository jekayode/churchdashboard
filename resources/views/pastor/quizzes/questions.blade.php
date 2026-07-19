<x-sidebar-layout title="Quiz questions">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $quiz->title }}</h2>
                <p class="text-sm text-gray-500">Questions</p>
            </div>
            <a href="{{ route('pastor.quizzes') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to quizzes</a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto" x-data="quizQuestions()">
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                <p class="font-medium">Please check the questions below.</p>
                <ul class="mt-1 text-sm list-disc list-inside">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        @if ($quiz->status !== 'draft')
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 text-sm">
                This quiz has already been opened, so its questions are locked. Scores would stop making sense if the
                questions changed underneath people who had already answered them.
            </div>
        @endif

        <form method="POST" action="{{ route('pastor.quizzes.questions.update', $quiz) }}" class="space-y-4">
            @csrf @method('PUT')

            <template x-for="(question, qi) in questions" :key="question.key">
                <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <span class="text-sm font-semibold text-gray-400 pt-2" x-text="'Question ' + (qi + 1)"></span>
                        <button type="button" @click="removeQuestion(qi)" x-show="questions.length > 1"
                                class="text-sm text-red-600 hover:text-red-800">Remove</button>
                    </div>

                    <textarea :name="`questions[${qi}][text]`" x-model="question.text" rows="2" required maxlength="500"
                              placeholder="Who led the Israelites across the Jordan?"
                              class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500"></textarea>

                    <div class="space-y-2">
                        <p class="text-sm font-medium text-gray-700">Answers — tick the correct one</p>
                        <template x-for="(option, oi) in question.options" :key="oi">
                            <div class="flex items-center gap-3">
                                <input type="radio" :name="`questions[${qi}][correct]`" :value="oi"
                                       :checked="question.correct === oi" @change="question.correct = oi" required
                                       class="text-church-500 focus:ring-church-500">
                                <input :name="`questions[${qi}][options][${oi}][text]`" x-model="option.text" required maxlength="120"
                                       :placeholder="`Answer ${oi + 1}`"
                                       class="flex-1 rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                                <button type="button" @click="removeOption(qi, oi)" x-show="question.options.length > 2"
                                        class="text-gray-400 hover:text-red-600" aria-label="Remove answer">&times;</button>
                            </div>
                        </template>
                        <button type="button" @click="addOption(qi)" x-show="question.options.length < 4"
                                class="text-sm text-church-600 hover:text-church-800 font-medium">+ Add answer</button>
                    </div>

                    <details class="text-sm">
                        <summary class="cursor-pointer text-gray-500 hover:text-gray-700">Override timing or points for this question</summary>
                        <div class="grid sm:grid-cols-2 gap-3 mt-3">
                            <label class="block">
                                <span class="text-gray-600">Seconds</span>
                                <input type="number" min="5" max="120" :name="`questions[${qi}][time_limit_seconds]`"
                                       x-model="question.time_limit_seconds" :placeholder="{{ $quiz->seconds_per_question }}"
                                       class="w-full mt-1 rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                            </label>
                            <label class="block">
                                <span class="text-gray-600">Points</span>
                                <input type="number" min="100" max="5000" step="100" :name="`questions[${qi}][points]`"
                                       x-model="question.points" :placeholder="{{ $quiz->base_points }}"
                                       class="w-full mt-1 rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                            </label>
                        </div>
                    </details>
                </div>
            </template>

            <button type="button" @click="addQuestion()"
                    class="w-full py-3 rounded-xl border-2 border-dashed border-gray-300 text-gray-600 font-medium hover:border-church-400 hover:text-church-600">
                + Add question
            </button>

            <div class="flex items-center gap-3 pt-2">
                <button class="px-4 py-2 rounded-lg bg-church-500 text-white font-semibold hover:bg-church-600"
                        @if ($quiz->status !== 'draft') disabled @endif>
                    Save questions
                </button>
                <span class="text-sm text-gray-500" x-text="`${questions.length} ${questions.length === 1 ? 'question' : 'questions'}`"></span>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function quizQuestions() {
            const saved = @json($existing);

            let nextKey = 0;
            const blank = () => ({
                key: nextKey++,
                text: '',
                time_limit_seconds: '',
                points: '',
                correct: 0,
                options: [{ text: '' }, { text: '' }],
            });

            return {
                questions: saved.length
                    ? saved.map(q => ({ ...q, key: nextKey++, time_limit_seconds: q.time_limit_seconds ?? '', points: q.points ?? '' }))
                    : [blank()],
                addQuestion() { this.questions.push(blank()); },
                removeQuestion(i) { this.questions.splice(i, 1); },
                addOption(qi) { this.questions[qi].options.push({ text: '' }); },
                removeOption(qi, oi) {
                    const question = this.questions[qi];
                    question.options.splice(oi, 1);
                    // The tick points at an index, so removing an option above it
                    // would otherwise silently move the correct answer.
                    if (question.correct === oi) question.correct = 0;
                    else if (question.correct > oi) question.correct--;
                },
            };
        }
    </script>
    @endpush
</x-sidebar-layout>

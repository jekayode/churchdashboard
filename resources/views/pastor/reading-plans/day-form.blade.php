<x-sidebar-layout :title="($day->label ?? 'Day '.$day->day_number).' — '.$plan->name">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $day->label ?? 'Day '.$day->day_number }}
                <span class="ml-2 text-sm font-normal text-gray-500">{{ $plan->name }}</span>
            </h2>
            <a href="{{ route('pastor.reading-plans.days', $plan) }}" class="text-sm font-medium text-church-600 hover:text-church-700">
                {{ __('Back to days') }}
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        @if (session('success'))
            <div class="rounded-lg border border-church-200 bg-church-50 px-4 py-3 text-church-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('warning'))
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900">
                {{ session('warning') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                <p class="font-semibold">Please fix the following:</p>
                <ul class="mt-1 list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Day-to-day navigation, so a long rewrite doesn't mean going back to the list --}}
        <div class="flex items-center justify-between text-sm">
            <div>
                @if ($previous)
                    <a href="{{ route('pastor.reading-plans.days.edit', [$plan, $previous]) }}"
                       class="text-church-600 hover:text-church-700">&larr; {{ $previous->label ?? 'Day '.$previous->day_number }}</a>
                @endif
            </div>
            <div>
                @if ($next)
                    <a href="{{ route('pastor.reading-plans.days.edit', [$plan, $next]) }}"
                       class="text-church-600 hover:text-church-700">{{ $next->label ?? 'Day '.$next->day_number }} &rarr;</a>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('pastor.reading-plans.days.update', [$plan, $day]) }}">
            @csrf
            @method('PUT')

            {{-- Questions first: this is the work being done --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="font-semibold text-gray-900">Study questions</h3>
                        <p class="text-sm text-gray-500">Shown under the reading in the app.</p>
                    </div>
                    @if ($day->hasOwnQuestions())
                        <span class="inline-flex shrink-0 items-center rounded-full bg-church-100 px-2.5 py-0.5 text-xs font-medium text-church-800">
                            Your own · {{ $day->questions_updated_at->diffForHumans() }}
                        </span>
                    @else
                        <span class="inline-flex shrink-0 items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">
                            Imported text
                        </span>
                    @endif
                </div>

                <div>
                    <label for="study_question_1" class="block text-sm font-medium text-gray-700 mb-1">Question 1</label>
                    <textarea id="study_question_1" name="study_question_1" rows="5"
                              class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">{{ old('study_question_1', $day->study_question_1) }}</textarea>
                </div>

                <div>
                    <label for="study_question_2" class="block text-sm font-medium text-gray-700 mb-1">Question 2</label>
                    <textarea id="study_question_2" name="study_question_2" rows="5"
                              class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">{{ old('study_question_2', $day->study_question_2) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Leave blank if the day only needs one.</p>
                </div>
            </div>

            {{-- Readings --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5 mt-6">
                <div>
                    <h3 class="font-semibold text-gray-900">Readings</h3>
                    <p class="text-sm text-gray-500">
                        References such as <code class="rounded bg-gray-100 px-1">1 CHRONICLES 24:1-26:11</code>.
                        Chapter and book spans are understood.
                    </p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="old_testament" class="block text-sm font-medium text-gray-700 mb-1">Old Testament</label>
                        <input id="old_testament" name="old_testament" type="text"
                               value="{{ old('old_testament', $day->old_testament) }}"
                               class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                    </div>
                    <div>
                        <label for="new_testament" class="block text-sm font-medium text-gray-700 mb-1">New Testament</label>
                        <input id="new_testament" name="new_testament" type="text"
                               value="{{ old('new_testament', $day->new_testament) }}"
                               class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                    </div>
                    <div>
                        <label for="psalm" class="block text-sm font-medium text-gray-700 mb-1">Psalm</label>
                        <input id="psalm" name="psalm" type="text"
                               value="{{ old('psalm', $day->psalm) }}"
                               class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                    </div>
                    <div>
                        <label for="proverbs" class="block text-sm font-medium text-gray-700 mb-1">Proverbs</label>
                        <input id="proverbs" name="proverbs" type="text"
                               value="{{ old('proverbs', $day->proverbs) }}"
                               class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                    </div>
                </div>
            </div>

            {{-- Devotional fields, for written plans --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5 mt-6">
                <div>
                    <h3 class="font-semibold text-gray-900">Devotional (optional)</h3>
                    <p class="text-sm text-gray-500">Use these for a written devotional day rather than references alone.</p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="label" class="block text-sm font-medium text-gray-700 mb-1">Day label</label>
                        <input id="label" name="label" type="text" value="{{ old('label', $day->label) }}"
                               class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                    </div>
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input id="title" name="title" type="text" value="{{ old('title', $day->title) }}"
                               placeholder="e.g. Like a Tree by Water"
                               class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                    </div>
                </div>

                <div>
                    <label for="focus_verse" class="block text-sm font-medium text-gray-700 mb-1">Focus verse</label>
                    <input id="focus_verse" name="focus_verse" type="text"
                           value="{{ old('focus_verse', $day->focus_verse) }}"
                           class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                </div>

                <div>
                    <label for="body" class="block text-sm font-medium text-gray-700 mb-1">Devotional body</label>
                    <textarea id="body" name="body" rows="8"
                              class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">{{ old('body', $day->body) }}</textarea>
                </div>

                <div>
                    <label for="reflection_prompt" class="block text-sm font-medium text-gray-700 mb-1">Reflection prompt</label>
                    <textarea id="reflection_prompt" name="reflection_prompt" rows="3"
                              class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">{{ old('reflection_prompt', $day->reflection_prompt) }}</textarea>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 mt-6">
                <a href="{{ route('pastor.reading-plans.days', $plan) }}"
                   class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit"
                        class="px-5 py-2 rounded-lg border border-church-300 text-church-700 font-semibold hover:bg-church-50">
                    Save
                </button>
                @if ($next)
                    <button type="submit" name="save_and_next" value="1"
                            class="px-5 py-2 rounded-lg bg-church-500 text-white font-semibold hover:bg-church-600">
                        Save &amp; next day
                    </button>
                @endif
            </div>
        </form>
    </div>
</x-sidebar-layout>

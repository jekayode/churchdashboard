<x-sidebar-layout title="Quizzes">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('In-Service Quiz') }}</h2>
            @can('create', \App\Models\Quiz::class)
                <a href="{{ route('pastor.quizzes.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-church-500 text-white font-semibold hover:bg-church-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('New Quiz') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        @if (session('success'))
            <div class="rounded-lg border border-church-200 bg-church-50 px-4 py-3 text-church-800">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @forelse ($quizzes as $quiz)
                <div class="flex flex-wrap items-center justify-between gap-4 p-4 border-b border-gray-100 last:border-0">
                    <div class="min-w-[220px]">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-gray-900">{{ $quiz->title }}</span>
                            @php
                                $badge = match ($quiz->status) {
                                    'running' => 'bg-green-100 text-green-800',
                                    'lobby' => 'bg-amber-100 text-amber-800',
                                    'finished' => 'bg-gray-100 text-gray-600',
                                    default => 'bg-blue-100 text-blue-800',
                                };
                            @endphp
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $badge }}">{{ ucfirst($quiz->status) }}</span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $quiz->questions_count }} {{ Str::plural('question', $quiz->questions_count) }}
                            @if ($quiz->participants_count > 0)
                                · {{ $quiz->participants_count }} {{ Str::plural('player', $quiz->participants_count) }}
                            @endif
                            @if ($quiz->code)
                                · code <span class="font-mono font-semibold tracking-widest">{{ $quiz->code }}</span>
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if ($quiz->status === 'draft')
                            <a href="{{ route('pastor.quizzes.questions', $quiz) }}"
                               class="px-3 py-1.5 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">Questions</a>
                            <a href="{{ route('pastor.quizzes.edit', $quiz) }}"
                               class="px-3 py-1.5 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">Settings</a>
                        @endif
                        <a href="{{ route('pastor.quizzes.host', $quiz) }}"
                           class="px-3 py-1.5 rounded-lg bg-church-500 text-white text-sm font-semibold hover:bg-church-600">
                            {{ $quiz->status === 'finished' ? 'Results' : 'Run it' }}
                        </a>
                        @if ($quiz->status === 'draft')
                            <form method="POST" action="{{ route('pastor.quizzes.destroy', $quiz) }}"
                                  onsubmit="return confirm('Delete this quiz and its questions?')">
                                @csrf @method('DELETE')
                                <button class="px-3 py-1.5 rounded-lg border border-red-200 text-sm font-medium text-red-700 hover:bg-red-50">Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-10 text-center text-gray-500">
                    <p class="font-medium text-gray-700">No quizzes yet.</p>
                    <p class="text-sm mt-1">Write the questions here, then run it from your phone on the day.</p>
                </div>
            @endforelse
        </div>

        {{ $quizzes->links() }}
    </div>
</x-sidebar-layout>

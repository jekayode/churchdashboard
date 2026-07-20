<x-sidebar-layout :title="$quiz ? 'Edit quiz' : 'New quiz'">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $quiz ? __('Quiz settings') : __('New quiz') }}
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <form method="POST"
              action="{{ $quiz ? route('pastor.quizzes.update', $quiz) : route('pastor.quizzes.store') }}"
              class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
            @csrf
            @if ($quiz) @method('PUT') @endif

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input id="title" name="title" value="{{ old('title', $quiz?->title) }}" required maxlength="120"
                       class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500"
                       placeholder="LifeGroup Sunday Bible Quiz">
                @error('title') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-gray-400">(optional)</span></label>
                <input id="description" name="description" value="{{ old('description', $quiz?->description) }}" maxlength="255"
                       class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label for="seconds_per_question" class="block text-sm font-medium text-gray-700 mb-1">Seconds per question</label>
                    <input id="seconds_per_question" name="seconds_per_question" type="number" min="5" max="120" required
                           value="{{ old('seconds_per_question', $quiz?->seconds_per_question ?? 20) }}"
                           class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                    @error('seconds_per_question') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="reveal_seconds" class="block text-sm font-medium text-gray-700 mb-1">Answer shown for</label>
                    <input id="reveal_seconds" name="reveal_seconds" type="number" min="2" max="30" required
                           value="{{ old('reveal_seconds', $quiz?->reveal_seconds ?? 6) }}"
                           class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                    @error('reveal_seconds') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="base_points" class="block text-sm font-medium text-gray-700 mb-1">Points per question</label>
                    <input id="base_points" name="base_points" type="number" min="100" max="5000" step="100" required
                           value="{{ old('base_points', $quiz?->base_points ?? 1000) }}"
                           class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                    @error('base_points') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <p class="text-sm text-gray-500 -mt-2">
                A correct answer earns between half and all of the points depending on how quickly it comes.
                Questions move on by themselves, so on the day you only need to press start.
            </p>

            <label class="flex items-start gap-3">
                <input type="checkbox" name="allow_guests" value="1"
                       {{ old('allow_guests', $quiz?->allow_guests ?? true) ? 'checked' : '' }}
                       class="mt-1 rounded border-gray-300 text-church-500 focus:ring-church-500">
                <span class="text-sm">
                    <span class="font-medium text-gray-700">Let guests play with just their name</span>
                    <span class="block text-gray-500">They can see their score, but need an account to keep their history.</span>
                </span>
            </label>

            <div class="flex items-center gap-3 pt-2">
                <button class="px-4 py-2 rounded-lg bg-church-500 text-white font-semibold hover:bg-church-600">
                    {{ $quiz ? 'Save settings' : 'Create and add questions' }}
                </button>
                <a href="{{ route('pastor.quizzes') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-sidebar-layout>

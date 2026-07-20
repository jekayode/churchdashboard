<x-sidebar-layout title="Import questions">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $quiz->title }}</h2>
                <p class="text-sm text-gray-500">Import questions</p>
            </div>
            <a href="{{ route('pastor.quizzes.questions', $quiz) }}" class="text-sm text-gray-600 hover:text-gray-900">
                Back to the editor
            </a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-5">
        @if (session('import_errors'))
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900">
                <p class="font-medium">These could not be read:</p>
                <ul class="mt-1 text-sm list-disc list-inside space-y-1">
                    @foreach (session('import_errors') as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
        @endif

        <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-blue-900 text-sm">
            Importing <strong>replaces</strong> whatever questions this quiz already has. Nothing is
            published straight away — you land back in the editor to check the result first.
        </div>

        {{-- Paste --}}
        <form method="POST" action="{{ route('pastor.quizzes.import.store', $quiz) }}"
              class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            @csrf

            <div>
                <label for="pasted" class="block font-semibold text-gray-900 mb-1">Paste your questions</label>
                <p class="text-sm text-gray-500 mb-3">
                    Put the question on one line and the answers underneath. Mark the correct one with a
                    <code class="px-1 bg-gray-100 rounded">*</code>, or add a line reading
                    <code class="px-1 bg-gray-100 rounded">Answer: B</code>.
                    Numbering and lettering are optional — they are stripped out either way.
                </p>
                <textarea id="pasted" name="pasted" rows="14"
                          class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500 font-mono text-sm"
                          placeholder="1. Who led Israel across the Jordan?&#10;a) Moses&#10;b) Joshua *&#10;c) Caleb&#10;&#10;2. Where was Jesus born?&#10;a) Nazareth&#10;b) Bethlehem&#10;Answer: B">{{ old('pasted') }}</textarea>
                @error('pasted') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <details class="text-sm">
                <summary class="cursor-pointer text-gray-600 hover:text-gray-900 font-medium">
                    What it will and will not accept
                </summary>
                <div class="mt-3 space-y-2 text-gray-600">
                    <p><strong>Fine:</strong> numbered or not, lettered or not, blank lines between questions or not,
                       <code class="px-1 bg-gray-100 rounded">-</code> or <code class="px-1 bg-gray-100 rounded">•</code> bullets,
                       <code class="px-1 bg-gray-100 rounded">[x]</code> against the answer,
                       <code class="px-1 bg-gray-100 rounded">Answer:</code> giving a letter, a number, or the words.</p>
                    <p><strong>Refused:</strong> a question with no answer marked, with two marked, with fewer than
                       two answers or more than four. It will tell you which question, and import the rest.</p>
                    <p>Every answer starting with <code class="px-1 bg-gray-100 rounded">*</code> is read as a bullet
                       list rather than as marking them all correct, so use <code class="px-1 bg-gray-100 rounded">Answer:</code>
                       in that case.</p>
                </div>
            </details>

            <button class="px-4 py-2 rounded-lg bg-church-500 text-white font-semibold hover:bg-church-600">
                Import from paste
            </button>
        </form>

        {{-- CSV --}}
        <form method="POST" action="{{ route('pastor.quizzes.import.store', $quiz) }}"
              enctype="multipart/form-data"
              class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            @csrf

            <div>
                <label for="file" class="block font-semibold text-gray-900 mb-1">Or upload a CSV</label>
                <p class="text-sm text-gray-500 mb-3">
                    First row names the columns. <code class="px-1 bg-gray-100 rounded">question</code>,
                    then <code class="px-1 bg-gray-100 rounded">a, b, c, d</code> (or
                    <code class="px-1 bg-gray-100 rounded">option 1, option 2…</code>), then
                    <code class="px-1 bg-gray-100 rounded">answer</code> holding the letter, the number, or the words.
                </p>
                <input id="file" name="file" type="file" accept=".csv,text/csv"
                       class="w-full text-sm text-gray-700 file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-church-50 file:text-church-700 file:font-semibold hover:file:bg-church-100">
                @error('file') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <button class="px-4 py-2 rounded-lg border border-gray-300 font-semibold text-gray-700 hover:bg-gray-50">
                Import from file
            </button>
        </form>
    </div>
</x-sidebar-layout>

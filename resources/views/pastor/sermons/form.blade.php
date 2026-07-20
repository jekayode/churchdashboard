<x-sidebar-layout :title="$sermon !== null ? __('Edit sermon') : __('Add sermon')">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $sermon !== null ? __('Edit sermon') : __('Add sermon') }}
            </h2>
            <a href="{{ route('pastor.sermons') }}" class="text-sm font-medium text-church-600 hover:text-church-700">
                {{ __('Back to sermons') }}
            </a>
        </div>
    </x-slot>

    {{-- x-data lives on the wrapper so the out-of-band forms below share this scope --}}
    <div class="max-w-4xl mx-auto space-y-6"
         x-data="sermonForm({{ Js::from($sermon?->passages->map(fn ($p) => [
             'reference' => $p->reference,
             'book' => $p->book,
             'chapter' => $p->chapter,
             'verses' => $p->verses,
         ])->values() ?? []) }})">
        @if (session('success'))
            <div class="rounded-lg border border-church-200 bg-church-50 px-4 py-3 text-church-800">
                {{ session('success') }}
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

        <form method="POST"
              action="{{ $sermon !== null ? route('pastor.sermons.update', $sermon) : route('pastor.sermons.store') }}"
              enctype="multipart/form-data">
            @csrf
            @if ($sermon !== null)
                @method('PUT')
            @endif

            {{-- Details --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
                <h3 class="font-semibold text-gray-900">Details</h3>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                    <input id="title" name="title" type="text" required
                           value="{{ old('title', $sermon?->title) }}"
                           class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="speaker" class="block text-sm font-medium text-gray-700 mb-1">Speaker *</label>
                        <input id="speaker" name="speaker" type="text" required list="speaker-options"
                               value="{{ old('speaker', $sermon?->speaker) }}"
                               placeholder="e.g. Pastor Emmanuel Joseph"
                               class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                        <datalist id="speaker-options">
                            @foreach ($speakers as $speaker)
                                <option value="{{ $speaker->name }}"></option>
                            @endforeach
                        </datalist>
                        <p class="mt-1 text-xs text-gray-500">Guest speakers can be typed freely.</p>
                    </div>

                    <div>
                        <label for="preached_on" class="block text-sm font-medium text-gray-700 mb-1">Date preached *</label>
                        <input id="preached_on" name="preached_on" type="date" required
                               value="{{ old('preached_on', $sermon?->preached_on?->format('Y-m-d')) }}"
                               class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="series_id" class="block text-sm font-medium text-gray-700 mb-1">Series</label>
                        <select id="series_id" name="series_id"
                                class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                            <option value="">No series</option>
                            @foreach ($seriesList as $series)
                                <option value="{{ $series->id }}" @selected(old('series_id', $sermon?->series_id) == $series->id)>
                                    {{ $series->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="tone" class="block text-sm font-medium text-gray-700 mb-1">Accent colour</label>
                        <select id="tone" name="tone"
                                class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                            @foreach (['orange' => 'Burnt orange', 'purple' => 'Purple', 'amber' => 'Amber', 'lemon' => 'Lemon'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('tone', $sermon?->tone ?? 'orange') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Used for the sermon card in the app.</p>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="4"
                              class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">{{ old('description', $sermon?->description) }}</textarea>
                </div>

                <div>
                    <label for="duration_seconds" class="block text-sm font-medium text-gray-700 mb-1">Duration (minutes)</label>
                    <input id="duration_seconds" type="number" min="0" step="1"
                           value="{{ old('duration_seconds', $sermon?->duration_seconds ? intdiv($sermon->duration_seconds, 60) : null) }}"
                           x-on:input="$refs.durationSeconds.value = $event.target.value ? $event.target.value * 60 : ''"
                           class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                    <input type="hidden" name="duration_seconds" x-ref="durationSeconds"
                           value="{{ old('duration_seconds', $sermon?->duration_seconds) }}">
                </div>
            </div>

            {{-- Media --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5 mt-6">
                <h3 class="font-semibold text-gray-900">Media</h3>

                <div>
                    <label for="cover" class="block text-sm font-medium text-gray-700 mb-1">Cover image</label>
                    @if ($sermon?->cover_url)
                        <div class="mb-2 flex items-center gap-3">
                            <img src="{{ $sermon->cover_url }}" alt="Cover" class="h-16 w-24 rounded object-cover border border-gray-200">
                            <span class="text-xs text-gray-500">Uploading a new image replaces this one.</span>
                        </div>
                    @endif
                    <input id="cover" name="cover" type="file" accept="image/*"
                           class="w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-church-50 file:px-4 file:py-2 file:text-church-700">
                </div>

                <div>
                    <label for="video_url" class="block text-sm font-medium text-gray-700 mb-1">YouTube link</label>
                    <input id="video_url" name="video_url" type="url"
                           value="{{ old('video_url', $sermon?->video_url) }}"
                           placeholder="https://www.youtube.com/watch?v=… or https://youtu.be/…"
                           class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                    <p class="mt-1 text-xs text-gray-500">
                        If the message is on YouTube, paste the link and it plays inside the app.
                        No audio upload needed.
                    </p>
                    @if ($sermon?->youtube_id)
                        <p class="mt-1 text-xs text-church-700">Video recognised · id {{ $sermon->youtube_id }}</p>
                    @endif
                </div>

                <div>
                    <label for="recording" class="block text-sm font-medium text-gray-700 mb-1">Audio recording</label>
                    @if ($sermon?->recording_url)
                        <div class="mb-2">
                            <audio controls preload="none" src="{{ $sermon->recording_url }}" class="w-full"></audio>
                        </div>
                    @endif
                    <input id="recording" name="recording" type="file" accept="audio/*,video/mp4"
                           class="w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-church-50 file:px-4 file:py-2 file:text-church-700">
                    <p class="mt-1 text-xs text-gray-500">Audio (mp3, m4a, wav) or mp4 video, up to 500MB.</p>
                </div>

                <div>
                    <label for="slides" class="block text-sm font-medium text-gray-700 mb-1">Slides</label>
                    @if ($sermon !== null && $sermon->getMedia('slides')->isNotEmpty())
                        <ul class="mb-2 space-y-1">
                            @foreach ($sermon->getMedia('slides') as $slide)
                                <li class="flex items-center justify-between rounded border border-gray-200 px-3 py-2 text-sm">
                                    <a href="{{ $slide->getUrl() }}" target="_blank" rel="noopener"
                                       class="text-church-600 hover:text-church-700">{{ $slide->file_name }}</a>
                                    <button type="button"
                                            x-on:click="$refs['removeSlide{{ $slide->id }}'].submit()"
                                            class="text-xs font-medium text-red-600 hover:text-red-700">Remove</button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <input id="slides" name="slides[]" type="file" multiple accept=".pdf,image/*"
                           class="w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-church-50 file:px-4 file:py-2 file:text-church-700">
                    <p class="mt-1 text-xs text-gray-500">PDF or images. Add as many as you need.</p>
                </div>
            </div>

            {{-- Passages --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6 mt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-900">Bible passages</h3>
                        <p class="text-sm text-gray-500">Members can toggle these in the app to read along.</p>
                    </div>
                    <button type="button" x-on:click="addPassage()"
                            class="px-3 py-1.5 rounded-lg border border-church-300 text-sm font-medium text-church-700 hover:bg-church-50">
                        + Add passage
                    </button>
                </div>

                <template x-if="passages.length === 0">
                    <p class="mt-4 text-sm text-gray-500">No passages added yet.</p>
                </template>

                <div class="mt-4 space-y-3">
                    <template x-for="(passage, index) in passages" :key="index">
                        <div class="grid gap-3 sm:grid-cols-12 items-end rounded-lg border border-gray-200 p-3">
                            <div class="sm:col-span-5">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Reference *</label>
                                <input type="text" x-model="passage.reference"
                                       :name="`passages[${index}][reference]`"
                                       placeholder="Psalm 1:1-3"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-church-500 focus:ring-church-500">
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Book</label>
                                <input type="text" x-model="passage.book" :name="`passages[${index}][book]`"
                                       placeholder="Psalm"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-church-500 focus:ring-church-500">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Chapter</label>
                                <input type="number" min="1" x-model="passage.chapter" :name="`passages[${index}][chapter]`"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-church-500 focus:ring-church-500">
                            </div>
                            <div class="sm:col-span-1">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Verses</label>
                                <input type="text" x-model="passage.verses" :name="`passages[${index}][verses]`"
                                       placeholder="1-3"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-church-500 focus:ring-church-500">
                            </div>
                            <div class="sm:col-span-1 text-right">
                                <button type="button" x-on:click="removePassage(index)"
                                        class="text-xs font-medium text-red-600 hover:text-red-700">Remove</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Publishing --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4 mt-6">
                <h3 class="font-semibold text-gray-900">Publishing</h3>

                <label class="flex items-start gap-3">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $sermon?->is_published))
                           class="mt-1 rounded border-gray-300 text-church-600 focus:ring-church-500">
                    <span>
                        <span class="block text-sm font-medium text-gray-900">Published</span>
                        <span class="block text-xs text-gray-500">Visible in the member app.</span>
                    </span>
                </label>

                <label class="flex items-start gap-3">
                    <input type="hidden" name="is_live" value="0">
                    <input type="checkbox" name="is_live" value="1" x-model="isLive" @checked(old('is_live', $sermon?->is_live))
                           class="mt-1 rounded border-gray-300 text-church-600 focus:ring-church-500">
                    <span>
                        <span class="block text-sm font-medium text-gray-900">Streaming live</span>
                        <span class="block text-xs text-gray-500">Shows a live badge and opens the stream instead of the recording.</span>
                    </span>
                </label>

                <div x-show="isLive" x-cloak>
                    <label for="live_url" class="block text-sm font-medium text-gray-700 mb-1">Stream URL</label>
                    <input id="live_url" name="live_url" type="url"
                           value="{{ old('live_url', $sermon?->live_url) }}"
                           placeholder="https://..."
                           class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 mt-6">
                <div>
                    @if ($sermon !== null)
                        @can('delete', $sermon)
                            <button type="button"
                                    x-on:click="if (confirm('Delete this sermon? Members will no longer see it.')) $refs.deleteForm.submit()"
                                    class="px-4 py-2 rounded-lg border border-red-300 text-red-700 font-medium hover:bg-red-50">
                                Delete
                            </button>
                        @endcan
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('pastor.sermons') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-church-500 text-white font-semibold hover:bg-church-600">
                        {{ $sermon !== null ? 'Save changes' : 'Create sermon' }}
                    </button>
                </div>
            </div>
        </form>

        {{-- Out-of-band forms (cannot nest inside the main form) --}}
        @if ($sermon !== null)
            @can('delete', $sermon)
                <form x-ref="deleteForm" method="POST" action="{{ route('pastor.sermons.destroy', $sermon) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endcan
            @foreach ($sermon->getMedia('slides') as $slide)
                <form x-ref="removeSlide{{ $slide->id }}" method="POST"
                      action="{{ route('pastor.sermons.media.destroy', [$sermon, $slide->id]) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        @endif
    </div>

    @push('scripts')
        <script>
            function sermonForm(initialPassages) {
                return {
                    passages: initialPassages ?? [],
                    isLive: {{ old('is_live', $sermon?->is_live) ? 'true' : 'false' }},
                    addPassage() {
                        this.passages.push({ reference: '', book: '', chapter: '', verses: '' });
                    },
                    removePassage(index) {
                        this.passages.splice(index, 1);
                    },
                };
            }
        </script>
    @endpush
</x-sidebar-layout>

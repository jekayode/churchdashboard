<x-sidebar-layout :title="$series !== null ? __('Edit series') : __('Add series')">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $series !== null ? __('Edit series') : __('Add series') }}
            </h2>
            <a href="{{ route('pastor.series') }}" class="text-sm font-medium text-church-600 hover:text-church-700">
                {{ __('Back to series') }}
            </a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
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
              action="{{ $series !== null ? route('pastor.series.update', $series) : route('pastor.series.store') }}"
              enctype="multipart/form-data">
            @csrf
            @if ($series !== null)
                @method('PUT')
            @endif

            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input id="name" name="name" type="text" required
                           value="{{ old('name', $series?->name) }}"
                           placeholder="e.g. Grow Deep"
                           class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">{{ old('description', $series?->description) }}</textarea>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="starts_on" class="block text-sm font-medium text-gray-700 mb-1">Starts on</label>
                        <input id="starts_on" name="starts_on" type="date"
                               value="{{ old('starts_on', $series?->starts_on?->format('Y-m-d')) }}"
                               class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                    </div>
                    <div>
                        <label for="ends_on" class="block text-sm font-medium text-gray-700 mb-1">Ends on</label>
                        <input id="ends_on" name="ends_on" type="date"
                               value="{{ old('ends_on', $series?->ends_on?->format('Y-m-d')) }}"
                               class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                    </div>
                </div>

                <div>
                    <label for="tone" class="block text-sm font-medium text-gray-700 mb-1">Accent colour</label>
                    <select id="tone" name="tone"
                            class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                        @foreach (['orange' => 'Burnt orange', 'purple' => 'Purple', 'amber' => 'Amber', 'lemon' => 'Lemon'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('tone', $series?->tone ?? 'orange') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Used for the series card in the app.</p>
                </div>

                <div>
                    <label for="cover" class="block text-sm font-medium text-gray-700 mb-1">Cover image</label>
                    @if ($series?->cover_url)
                        <div class="mb-2 flex items-center gap-3">
                            <img src="{{ $series->cover_url }}" alt="Cover" class="h-16 w-24 rounded object-cover border border-gray-200">
                            <span class="text-xs text-gray-500">Uploading a new image replaces this one.</span>
                        </div>
                    @endif
                    <input id="cover" name="cover" type="file" accept="image/*"
                           class="w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-church-50 file:px-4 file:py-2 file:text-church-700">
                </div>

                <label class="flex items-start gap-3">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1"
                           @checked(old('is_published', $series?->is_published ?? true))
                           class="mt-1 rounded border-gray-300 text-church-600 focus:ring-church-500">
                    <span>
                        <span class="block text-sm font-medium text-gray-900">Published</span>
                        <span class="block text-xs text-gray-500">Visible in the member app.</span>
                    </span>
                </label>
            </div>

            <div class="flex items-center justify-between gap-3 mt-6">
                <div>
                    @if ($series !== null)
                        @can('delete', $series)
                            <button type="button"
                                    onclick="if (confirm('Delete this series? Its sermons are kept but will no longer belong to a series.')) document.getElementById('delete-series-form').submit()"
                                    class="px-4 py-2 rounded-lg border border-red-300 text-red-700 font-medium hover:bg-red-50">
                                Delete
                            </button>
                        @endcan
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('pastor.series') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-church-500 text-white font-semibold hover:bg-church-600">
                        {{ $series !== null ? 'Save changes' : 'Create series' }}
                    </button>
                </div>
            </div>
        </form>

        @if ($series !== null)
            @can('delete', $series)
                <form id="delete-series-form" method="POST" action="{{ route('pastor.series.destroy', $series) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endcan
        @endif
    </div>
</x-sidebar-layout>

<x-sidebar-layout title="Sermons">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Sermon Library') }}
            </h2>
            @can('create', \App\Models\Sermon::class)
                <a href="{{ route('pastor.sermons.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-church-500 text-white font-semibold hover:bg-church-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('Add Sermon') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        @if (session('success'))
            <div class="rounded-lg border border-church-200 bg-church-50 px-4 py-3 text-church-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters --}}
        <form method="GET" action="{{ route('pastor.sermons') }}"
              class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}"
                       placeholder="Title or speaker"
                       class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
            </div>
            <div class="min-w-[200px]">
                <label for="series_id" class="block text-sm font-medium text-gray-700 mb-1">Series</label>
                <select id="series_id" name="series_id"
                        class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                    <option value="">All series</option>
                    @foreach ($seriesList as $series)
                        <option value="{{ $series->id }}" @selected(request('series_id') == $series->id)>
                            {{ $series->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white font-medium hover:bg-gray-800">
                Filter
            </button>
            @if (request()->hasAny(['search', 'series_id']))
                <a href="{{ route('pastor.sermons') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                    Clear
                </a>
            @endif
        </form>

        {{-- List --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @if ($sermons->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-gray-900 font-semibold">No sermons yet</p>
                    <p class="mt-1 text-sm text-gray-500">
                        Add your first sermon so it appears in the member app.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Sermon</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Speaker</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Series</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($sermons as $sermon)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $sermon->title }}</div>
                                        @if ($sermon->duration_label)
                                            <div class="text-sm text-gray-500">{{ $sermon->duration_label }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $sermon->speaker }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $sermon->series?->name ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $sermon->preached_on?->format('d M Y') }}</td>
                                    <td class="px-6 py-4">
                                        @if ($sermon->is_live)
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Live</span>
                                        @elseif ($sermon->is_published)
                                            <span class="inline-flex items-center rounded-full bg-church-100 px-2.5 py-0.5 text-xs font-medium text-church-800">Published</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">Draft</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        @can('update', $sermon)
                                            <a href="{{ route('pastor.sermons.edit', $sermon) }}"
                                               class="font-medium text-church-600 hover:text-church-700">Edit</a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $sermons->links() }}
                </div>
            @endif
        </div>
    </div>
</x-sidebar-layout>

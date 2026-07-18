<x-sidebar-layout title="Sermon Series">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Sermon Series') }}
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('pastor.sermons') }}" class="text-sm font-medium text-church-600 hover:text-church-700">
                    {{ __('Sermons') }}
                </a>
                @can('create', \App\Models\Series::class)
                    <a href="{{ route('pastor.series.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-church-500 text-white font-semibold hover:bg-church-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('Add Series') }}
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        @if (session('success'))
            <div class="rounded-lg border border-church-200 bg-church-50 px-4 py-3 text-church-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @if ($seriesList->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-gray-900 font-semibold">No series yet</p>
                    <p class="mt-1 text-sm text-gray-500">
                        Group related sermons into a series, for example &ldquo;Grow Deep&rdquo;.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Series</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Sermons</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Dates</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($seriesList as $series)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if ($series->cover_url)
                                                <img src="{{ $series->cover_url }}" alt=""
                                                     class="h-10 w-16 rounded object-cover border border-gray-200">
                                            @else
                                                <div class="h-10 w-16 rounded bg-gray-100 border border-gray-200"></div>
                                            @endif
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $series->name }}</div>
                                                <div class="text-xs text-gray-500 capitalize">{{ $series->tone }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $series->sermons_count }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $series->starts_on?->format('d M Y') ?? '—' }}
                                        @if ($series->ends_on)
                                            &ndash; {{ $series->ends_on->format('d M Y') }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($series->is_published)
                                            <span class="inline-flex items-center rounded-full bg-church-100 px-2.5 py-0.5 text-xs font-medium text-church-800">Published</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">Draft</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        @can('update', $series)
                                            <a href="{{ route('pastor.series.edit', $series) }}"
                                               class="font-medium text-church-600 hover:text-church-700">Edit</a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $seriesList->links() }}
                </div>
            @endif
        </div>
    </div>
</x-sidebar-layout>

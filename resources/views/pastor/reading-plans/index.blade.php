<x-sidebar-layout title="Reading Plans">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Reading Plans') }}</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        @if (session('success'))
            <div class="rounded-lg border border-church-200 bg-church-50 px-4 py-3 text-church-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @if ($plans->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-gray-900 font-semibold">No reading plans yet</p>
                    <p class="mt-1 text-sm text-gray-500">
                        Import one with <code class="rounded bg-gray-100 px-1">php artisan reading-plan:import</code>.
                    </p>
                </div>
            @else
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Plan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Days</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Own questions</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($plans as $plan)
                            @php
                                $percent = $plan->days_count > 0
                                    ? (int) round($plan->rewritten_days_count / $plan->days_count * 100)
                                    : 0;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $plan->name }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $plan->is_annual ? 'Repeats yearly' : 'Fixed length' }}
                                        @if ($plan->attribution) · {{ $plan->attribution }} @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $plan->days_count }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-24 rounded-full bg-gray-200 overflow-hidden">
                                            <div class="h-full bg-church-500" style="width: {{ $percent }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-600">{{ $plan->rewritten_days_count }}/{{ $plan->days_count }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($plan->is_default)
                                        <span class="inline-flex items-center rounded-full bg-church-100 px-2.5 py-0.5 text-xs font-medium text-church-800">Default</span>
                                    @elseif ($plan->is_published)
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">Published</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">Draft</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm space-x-3">
                                    <a href="{{ route('pastor.reading-plans.days', $plan) }}"
                                       class="font-medium text-church-600 hover:text-church-700">Days</a>
                                    @can('update', $plan)
                                        <a href="{{ route('pastor.reading-plans.edit', $plan) }}"
                                           class="font-medium text-gray-600 hover:text-gray-800">Settings</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-sidebar-layout>

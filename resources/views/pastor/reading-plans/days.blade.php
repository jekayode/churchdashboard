<x-sidebar-layout :title="$plan->name.' — Days'">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $plan->name }}</h2>
            <a href="{{ route('pastor.reading-plans') }}" class="text-sm font-medium text-church-600 hover:text-church-700">
                {{ __('All plans') }}
            </a>
        </div>
    </x-slot>

    @php
        $percent = $totalCount > 0 ? (int) round($rewrittenCount / $totalCount * 100) : 0;
    @endphp

    <div class="max-w-7xl mx-auto space-y-6">
        @if (session('success'))
            <div class="rounded-lg border border-church-200 bg-church-50 px-4 py-3 text-church-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Rewrite progress --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <p class="font-semibold text-gray-900">Your own questions</p>
                    <p class="text-sm text-gray-500">
                        {{ $rewrittenCount }} of {{ $totalCount }} days rewritten
                        @if ($rewrittenCount < $totalCount)
                            · {{ $totalCount - $rewrittenCount }} still using the imported text
                        @endif
                    </p>
                </div>
                <span class="text-2xl font-bold text-church-600">{{ $percent }}%</span>
            </div>
            <div class="h-2.5 w-full rounded-full bg-gray-200 overflow-hidden">
                <div class="h-full bg-church-500 transition-all" style="width: {{ $percent }}%"></div>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('pastor.reading-plans.days', $plan) }}"
              class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input id="search" name="search" type="text" value="{{ request('search') }}"
                       placeholder="Date, reading or question text"
                       class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
            </div>
            <div class="min-w-[150px]">
                <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                <select id="month" name="month" class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                    <option value="">All months</option>
                    @foreach (range(1, 12) as $month)
                        <option value="{{ str_pad((string) $month, 2, '0', STR_PAD_LEFT) }}"
                            @selected(request('month') === str_pad((string) $month, 2, '0', STR_PAD_LEFT))>
                            {{ \Carbon\Carbon::create(null, $month, 1)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[170px]">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Questions</label>
                <select id="status" name="status" class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                    <option value="">All days</option>
                    <option value="todo" @selected(request('status') === 'todo')>Not yet rewritten</option>
                    <option value="done" @selected(request('status') === 'done')>Rewritten</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white font-medium hover:bg-gray-800">Filter</button>
            @if (request()->hasAny(['search', 'month', 'status']))
                <a href="{{ route('pastor.reading-plans.days', $plan) }}"
                   class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Clear</a>
            @endif
        </form>

        {{-- Days --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @if ($days->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-gray-900 font-semibold">No days match</p>
                    <p class="mt-1 text-sm text-gray-500">Try clearing the filters.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Day</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Readings</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Questions</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($days as $day)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">{{ $day->label ?? 'Day '.$day->day_number }}</div>
                                        <div class="text-xs text-gray-500">#{{ $day->day_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        @foreach ($day->references() as $reference)
                                            <div class="text-xs">{{ $reference['reference'] }}</div>
                                        @endforeach
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($day->hasOwnQuestions())
                                            <span class="inline-flex items-center rounded-full bg-church-100 px-2.5 py-0.5 text-xs font-medium text-church-800">Yours</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">Imported</span>
                                        @endif
                                        <span class="ml-1 text-xs text-gray-500">{{ count($day->studyQuestions()) }} question(s)</span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <a href="{{ route('pastor.reading-plans.days.edit', [$plan, $day]) }}"
                                           class="font-medium text-church-600 hover:text-church-700">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $days->links() }}
                </div>
            @endif
        </div>
    </div>
</x-sidebar-layout>

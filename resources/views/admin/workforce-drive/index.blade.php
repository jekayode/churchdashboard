<x-sidebar-layout title="Workforce Drive">
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Workforce Drive</h1>
                    <p class="text-gray-600 mt-1">Registrations from the public join form. <strong>{{ number_format($total) }}</strong> total.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('workforce-drive.leaderboard') }}" target="_blank" rel="noopener"
                       class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Open Leaderboard
                    </a>
                    <a href="{{ route('workforce-drive.admin.export', ['format' => 'csv', 'team_id' => $teamId]) }}"
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Export CSV
                    </a>
                    <a href="{{ route('workforce-drive.admin.export', ['format' => 'xlsx', 'team_id' => $teamId]) }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Export Excel
                    </a>
                </div>
            </div>
        </div>

        <!-- Per-team totals + filter -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('workforce-drive.admin.index') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium border {{ $teamId ? 'border-gray-200 text-gray-600 hover:bg-gray-50' : 'border-church-500 bg-church-50 text-church-700' }}">
                    All teams
                    <span class="ml-1 text-xs opacity-70">{{ number_format($total) }}</span>
                </a>
                @foreach ($teams as $team)
                    <a href="{{ route('workforce-drive.admin.index', ['team_id' => $team->id]) }}"
                       class="px-4 py-2 rounded-lg text-sm font-medium border {{ $teamId === $team->id ? 'border-church-500 bg-church-50 text-church-700' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                        {{ $team->name }}
                        <span class="ml-1 text-xs opacity-70">{{ number_format($team->signups_count) }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Phone</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Team</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($signups as $signup)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $signup->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $signup->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $signup->phone }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $signup->team?->name ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $signup->created_at?->format('M j, Y g:ia') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">No registrations yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($signups->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $signups->links() }}
                </div>
            @endif
        </div>
    </div>
</x-sidebar-layout>

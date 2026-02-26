<x-sidebar-layout title="Registration Attempts">
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Guest Registration Attempts</h1>
                    <p class="text-gray-600 mt-1">All form submissions (including failed) so you can recover guest data when submission did not complete.</p>
                </div>
                <a href="{{ route('guests.index') }}"
                   class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300 transition-colors">
                    Back to Guest Management
                </a>
            </div>
        </div>

        <!-- Status filter -->
        <div class="bg-white rounded-lg shadow-sm p-4">
            <form method="get" action="{{ route('guests.attempts') }}" class="flex flex-wrap items-center gap-3">
                <label for="status" class="text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="">All</option>
                    <option value="success" {{ ($statusFilter ?? '') === 'success' ? 'selected' : '' }}>Success</option>
                    <option value="validation_failed" {{ ($statusFilter ?? '') === 'validation_failed' ? 'selected' : '' }}>Validation failed</option>
                    <option value="database_error" {{ ($statusFilter ?? '') === 'database_error' ? 'selected' : '' }}>Database error</option>
                    <option value="error" {{ ($statusFilter ?? '') === 'error' ? 'selected' : '' }}>Error</option>
                    <option value="started" {{ ($statusFilter ?? '') === 'started' ? 'selected' : '' }}>Started (in progress)</option>
                </select>
                <button type="submit" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">Filter</button>
            </form>
        </div>

        <!-- Attempts table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Error</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($attempts as $attempt)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    {{ $attempt->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ trim(($attempt->first_name ?? '') . ' ' . ($attempt->surname ?? '')) ?: '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $attempt->email ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $attempt->phone ?? '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @php
                                        $statusClass = match ($attempt->status) {
                                            'success' => 'bg-green-100 text-green-800',
                                            'validation_failed' => 'bg-amber-100 text-amber-800',
                                            'database_error', 'error' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded {{ $statusClass }}">
                                        {{ str_replace('_', ' ', ucfirst($attempt->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate" title="{{ $attempt->error_message }}">
                                    {{ $attempt->error_message ? Str::limit($attempt->error_message, 40) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($attempt->payload)
                                        <details class="cursor-pointer">
                                            <summary class="text-indigo-600 hover:text-indigo-800">View payload</summary>
                                            <pre class="mt-2 p-3 bg-gray-50 rounded text-xs overflow-x-auto max-h-48 overflow-y-auto">{{ json_encode($attempt->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </details>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">
                                    No registration attempts found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($attempts->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $attempts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-sidebar-layout>

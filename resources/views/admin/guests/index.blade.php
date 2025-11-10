<x-sidebar-layout title="Guest Management">
    <div class="space-y-6" x-data="guestManagement()">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Guest Management</h1>
                    <p class="text-gray-600 mt-1">View and manage guests registered via the public registration form</p>
                </div>
                <div class="flex gap-3">
                    <a :href="`{{ route('guests.export') }}?format=csv&${getFilterParams()}`" 
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Export CSV
                    </a>
                    <a :href="`{{ route('guests.export') }}?format=xlsx&${getFilterParams()}`" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Export Excel
                    </a>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <form @submit.prevent="applyFilters()" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" 
                               x-model="filters.search" 
                               placeholder="Name, email, or phone..."
                               class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Date From -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Registration From</label>
                        <input type="date" 
                               x-model="filters.date_from" 
                               class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Registration To</label>
                        <input type="date" 
                               x-model="filters.date_to" 
                               class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Staying Intention -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Staying Intention</label>
                        <select x-model="filters.staying_intention" 
                                class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All</option>
                            <option value="yes-for-sure">Yes, For Sure</option>
                            <option value="visit-when-in-town">Visit When In Town</option>
                            <option value="just-visiting">Just Visiting</option>
                            <option value="weighing-options">Weighing Options</option>
                        </select>
                    </div>

                    <!-- Discovery Source -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Discovery Source</label>
                        <select x-model="filters.discovery_source" 
                                class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All</option>
                            <option value="social-media">Social Media</option>
                            <option value="word-of-mouth">Word of Mouth</option>
                            <option value="billboard">Billboard</option>
                            <option value="email">Email</option>
                            <option value="website">Website</option>
                            <option value="promotional-material">Promotional Material</option>
                            <option value="radio-tv">Radio/TV</option>
                            <option value="outreach">Outreach</option>
                        </select>
                    </div>

                    <!-- Gender -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                        <select x-model="filters.gender" 
                                class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="prefer-not-to-say">Prefer Not to Say</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <button type="button" 
                            @click="clearFilters()" 
                            class="text-gray-600 hover:text-gray-800 text-sm">
                        Clear Filters
                    </button>
                    <button type="submit" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Guests Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staying Intention</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($guests as $guest)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $guest->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $guest->email ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $guest->phone ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $guest->branch->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $guest->created_at->format('M d, Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        @if($guest->staying_intention)
                                            {{ ucfirst(str_replace('-', ' ', $guest->staying_intention)) }}
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('guests.show', $guest) }}" 
                                       class="text-indigo-600 hover:text-indigo-900">View Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No guests found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($guests->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $guests->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        function guestManagement() {
            return {
                filters: {
                    search: @js($filters['search'] ?? ''),
                    date_from: @js($filters['date_from'] ?? ''),
                    date_to: @js($filters['date_to'] ?? ''),
                    staying_intention: @js($filters['staying_intention'] ?? ''),
                    discovery_source: @js($filters['discovery_source'] ?? ''),
                    gender: @js($filters['gender'] ?? ''),
                },
                applyFilters() {
                    const params = new URLSearchParams();
                    Object.keys(this.filters).forEach(key => {
                        if (this.filters[key]) {
                            params.append(key, this.filters[key]);
                        }
                    });
                    window.location.href = '{{ route('guests.index') }}?' + params.toString();
                },
                clearFilters() {
                    this.filters = {
                        search: '',
                        date_from: '',
                        date_to: '',
                        staying_intention: '',
                        discovery_source: '',
                        gender: '',
                    };
                    window.location.href = '{{ route('guests.index') }}';
                },
                getFilterParams() {
                    const params = new URLSearchParams();
                    Object.keys(this.filters).forEach(key => {
                        if (this.filters[key]) {
                            params.append(key, this.filters[key]);
                        }
                    });
                    return params.toString();
                }
            }
        }
    </script>
</x-sidebar-layout>


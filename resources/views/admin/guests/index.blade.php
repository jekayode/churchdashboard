<x-sidebar-layout title="{{ ($viewType ?? 'guests') === 'members' ? 'Member Management' : 'Guest Management' }}">
    <div class="space-y-6" x-data="guestManagement()">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ ($viewType ?? 'guests') === 'members' ? 'Member Management' : 'Guest Management' }}</h1>
                    <p class="text-gray-600 mt-1">
                        @if(($viewType ?? 'guests') === 'members')
                            View and manage all members
                        @else
                            View and manage guests registered via the public registration form
                        @endif
                    </p>
                </div>
                <div class="flex gap-3">
                    @if(($viewType ?? 'guests') === 'members')
                        <button @click="showCreateModal = true" 
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors">
                            Add Member
                        </button>
                    @endif
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

        <!-- View Toggle -->
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex gap-2">
                <a href="{{ route('guests.index') }}" 
                   class="px-4 py-2 rounded-lg transition-colors {{ ($viewType ?? 'guests') === 'guests' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Guests
                </a>
                <a href="{{ route('guests.members') }}" 
                   class="px-4 py-2 rounded-lg transition-colors {{ ($viewType ?? 'guests') === 'members' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    All Members
                </a>
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

                    <!-- Date Range Preset -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                        <select x-model="filters.date_range" 
                                @change="handleDateRangeChange()"
                                class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Custom Range</option>
                            <option value="last_week">Last Week</option>
                            <option value="last_month">Last Month</option>
                            <option value="last_quarter">Last Quarter</option>
                        </select>
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

                    @if(($viewType ?? 'guests') === 'members')
                        <!-- Member Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Member Status</label>
                            <select x-model="filters.member_status" 
                                    class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All</option>
                                <option value="visitor">Visitor</option>
                                <option value="member">Member</option>
                                <option value="volunteer">Volunteer</option>
                                <option value="leader">Leader</option>
                                <option value="minister">Minister</option>
                            </select>
                        </div>
                    @endif

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

        <!-- Table -->
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
                            @if(($viewType ?? 'guests') === 'members')
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            @else
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staying Intention</th>
                            @endif
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $items = ($viewType ?? 'guests') === 'members' ? ($members ?? collect()) : ($guests ?? collect());
                        @endphp
                        @forelse($items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $item->email ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $item->phone ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $item->branch->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $item->created_at->format('M d, Y') }}</div>
                                </td>
                                @if(($viewType ?? 'guests') === 'members')
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ $item->member_status === 'visitor' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $item->member_status === 'member' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $item->member_status === 'volunteer' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $item->member_status === 'leader' ? 'bg-purple-100 text-purple-800' : '' }}
                                            {{ $item->member_status === 'minister' ? 'bg-indigo-100 text-indigo-800' : '' }}">
                                            {{ ucfirst($item->member_status ?? 'N/A') }}
                                        </span>
                                    </td>
                                @else
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            @if($item->staying_intention)
                                                {{ ucfirst(str_replace('-', ' ', $item->staying_intention)) }}
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                    </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex gap-2">
                                        @if(($viewType ?? 'guests') === 'members')
                                            <button @click="editMember({{ $item->id }})" 
                                                    class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                            <button @click="deleteMember({{ $item->id }})" 
                                                    class="text-red-600 hover:text-red-900">Delete</button>
                                        @else
                                            <a href="{{ route('guests.show', $item) }}" 
                                               class="text-indigo-600 hover:text-indigo-900">View Details</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ ($viewType ?? 'guests') === 'members' ? '7' : '7' }}" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No {{ ($viewType ?? 'guests') === 'members' ? 'members' : 'guests' }} found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(isset($items) && $items->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        function guestManagement() {
            return {
                showCreateModal: false,
                filters: {
                    search: @js($filters['search'] ?? ''),
                    date_range: @js($filters['date_range'] ?? ''),
                    date_from: @js($filters['date_from'] ?? ''),
                    date_to: @js($filters['date_to'] ?? ''),
                    staying_intention: @js($filters['staying_intention'] ?? ''),
                    discovery_source: @js($filters['discovery_source'] ?? ''),
                    gender: @js($filters['gender'] ?? ''),
                    member_status: @js($filters['member_status'] ?? ''),
                    view_type: @js($viewType ?? 'guests'),
                },
                handleDateRangeChange() {
                    if (!this.filters.date_range) {
                        return;
                    }
                    
                    const now = new Date();
                    let startDate;
                    
                    switch(this.filters.date_range) {
                        case 'last_week':
                            startDate = new Date(now);
                            startDate.setDate(now.getDate() - 7);
                            startDate.setDate(startDate.getDate() - startDate.getDay()); // Start of week
                            break;
                        case 'last_month':
                            startDate = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                            break;
                        case 'last_quarter':
                            const quarter = Math.floor(now.getMonth() / 3);
                            startDate = new Date(now.getFullYear(), (quarter - 1) * 3, 1);
                            break;
                        default:
                            return;
                    }
                    
                    this.filters.date_from = startDate.toISOString().split('T')[0];
                    this.filters.date_to = now.toISOString().split('T')[0];
                },
                applyFilters() {
                    const params = new URLSearchParams();
                    Object.keys(this.filters).forEach(key => {
                        if (this.filters[key]) {
                            params.append(key, this.filters[key]);
                        }
                    });
                    const baseUrl = this.filters.view_type === 'members' 
                        ? '{{ route('guests.members') }}' 
                        : '{{ route('guests.index') }}';
                    window.location.href = baseUrl + '?' + params.toString();
                },
                clearFilters() {
                    this.filters = {
                        search: '',
                        date_range: '',
                        date_from: '',
                        date_to: '',
                        staying_intention: '',
                        discovery_source: '',
                        gender: '',
                        member_status: '',
                        view_type: this.filters.view_type,
                    };
                    const baseUrl = this.filters.view_type === 'members' 
                        ? '{{ route('guests.members') }}' 
                        : '{{ route('guests.index') }}';
                    window.location.href = baseUrl;
                },
                getFilterParams() {
                    const params = new URLSearchParams();
                    Object.keys(this.filters).forEach(key => {
                        if (this.filters[key]) {
                            params.append(key, this.filters[key]);
                        }
                    });
                    return params.toString();
                },
                editMember(id) {
                    // TODO: Implement edit modal or redirect to edit page
                    window.location.href = `/guests/members/${id}/edit`;
                },
                deleteMember(id) {
                    if (confirm('Are you sure you want to delete this member?')) {
                        fetch(`/guests/members/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            } else {
                                alert('Failed to delete member: ' + (data.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while deleting the member');
                        });
                    }
                }
            }
        }
    </script>
</x-sidebar-layout>

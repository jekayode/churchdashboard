@props([
    'placeholder' => 'Search members...',
    'members' => [],
    'showFilters' => true
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-church border border-church-100 overflow-hidden']) }}>
    <!-- Header -->
    <div class="bg-gradient-brand px-6 py-4">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-white">Member Directory</h3>
                <p class="text-white/80 text-sm">Search and connect with church members</p>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="p-6" x-data="memberSearch()">
        <!-- Search Input -->
        <div class="mb-6">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <input 
                    type="text" 
                    x-model="searchTerm"
                    @input="debounceSearch()"
                    placeholder="{{ $placeholder }}"
                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-church-500 focus:border-church-500">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <div x-show="isSearching" class="animate-spin h-4 w-4 border-2 border-church-500 border-t-transparent rounded-full"></div>
                </div>
            </div>
        </div>

        @if($showFilters)
            <!-- Filters -->
            <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select x-model="filters.role" @change="applyFilters()" class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                        <option value="">All Roles</option>
                        <option value="super_admin">Super Admin</option>
                        <option value="branch_pastor">Branch Pastor</option>
                        <option value="ministry_leader">Ministry Leader</option>
                        <option value="department_leader">Department Leader</option>
                        <option value="church_member">Church Member</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ministry</label>
                    <select x-model="filters.ministry" @change="applyFilters()" class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                        <option value="">All Ministries</option>
                        <option value="youth">Youth Ministry</option>
                        <option value="worship">Worship Team</option>
                        <option value="children">Children's Ministry</option>
                        <option value="outreach">Outreach</option>
                        <option value="prayer">Prayer Ministry</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select x-model="filters.status" @change="applyFilters()" class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="new">New Member</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button 
                        @click="clearFilters()" 
                        class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Clear Filters
                    </button>
                </div>
            </div>
        @endif

        <!-- Results Count -->
        <div class="mb-4 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                <span x-text="`${filteredMembers.length} member${filteredMembers.length !== 1 ? 's' : ''} found`"></span>
            </div>
            <div class="flex items-center space-x-2">
                <button 
                    @click="viewMode = 'grid'" 
                    :class="viewMode === 'grid' ? 'bg-church-100 text-church-700' : 'bg-gray-100 text-gray-600'"
                    class="p-2 rounded-lg hover:bg-church-50 transition-colors">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </button>
                <button 
                    @click="viewMode = 'list'" 
                    :class="viewMode === 'list' ? 'bg-church-100 text-church-700' : 'bg-gray-100 text-gray-600'"
                    class="p-2 rounded-lg hover:bg-church-50 transition-colors">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Member Results -->
        <div class="max-h-96 overflow-y-auto">
            <!-- Grid View -->
            <div x-show="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <template x-for="member in filteredMembers" :key="member.id">
                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors cursor-pointer" @click="selectMember(member)">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-gradient-church rounded-full flex items-center justify-center text-white font-medium">
                                <span x-text="member.name.charAt(0).toUpperCase()"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-gray-900 truncate" x-text="member.name"></div>
                                <div class="text-sm text-gray-500 truncate" x-text="member.email"></div>
                                <div class="text-xs text-church-600 font-medium" x-text="member.role_display"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- List View -->
            <div x-show="viewMode === 'list'" class="space-y-2">
                <template x-for="member in filteredMembers" :key="member.id">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer" @click="selectMember(member)">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-church rounded-full flex items-center justify-center text-white font-medium">
                                <span x-text="member.name.charAt(0).toUpperCase()"></span>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900" x-text="member.name"></div>
                                <div class="text-sm text-gray-500" x-text="member.email"></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-church-600" x-text="member.role_display"></div>
                            <div class="text-xs text-gray-500" x-text="member.ministry || 'No Ministry'"></div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- No Results -->
            <div x-show="filteredMembers.length === 0" class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No members found</h3>
                <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filters.</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function memberSearch() {
    return {
        members: @json($members),
        searchTerm: '',
        isSearching: false,
        viewMode: 'grid',
        filters: {
            role: '',
            ministry: '',
            status: ''
        },
        searchTimeout: null,

        get filteredMembers() {
            let filtered = this.members;

            // Apply search filter
            if (this.searchTerm) {
                const term = this.searchTerm.toLowerCase();
                filtered = filtered.filter(member => 
                    member.name.toLowerCase().includes(term) ||
                    member.email.toLowerCase().includes(term) ||
                    (member.phone && member.phone.includes(term))
                );
            }

            // Apply role filter
            if (this.filters.role) {
                filtered = filtered.filter(member => member.role === this.filters.role);
            }

            // Apply ministry filter
            if (this.filters.ministry) {
                filtered = filtered.filter(member => member.ministry === this.filters.ministry);
            }

            // Apply status filter
            if (this.filters.status) {
                filtered = filtered.filter(member => member.status === this.filters.status);
            }

            return filtered;
        },

        debounceSearch() {
            this.isSearching = true;
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.isSearching = false;
            }, 300);
        },

        applyFilters() {
            // Filters are applied automatically via computed property
        },

        clearFilters() {
            this.filters = {
                role: '',
                ministry: '',
                status: ''
            };
            this.searchTerm = '';
        },

        selectMember(member) {
            this.$dispatch('member-selected', { member });
        }
    }
}
</script>
@endpush 
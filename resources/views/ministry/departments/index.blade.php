<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Departments') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="departmentManager()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-medium">Department Management</h3>
                            <p class="text-gray-600">Manage departments within your ministry.</p>
                        </div>
                        <button @click="openCreateModal()" 
                                class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Department
                        </button>
                    </div>

                    <!-- Filters and Search -->
                    <div class="flex flex-col sm:flex-row gap-4 mb-6">
                        <div class="flex-1">
                            <input type="text" 
                                   x-model="search" 
                                   @input="loadDepartments()"
                                   placeholder="Search departments..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div class="flex gap-2">
                            @if($isSuperAdmin)
                            <select x-model="branchFilter" @change="loadDepartments()" 
                                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                                <option value="">All Branches</option>
                                <template x-for="branch in branches" :key="branch.id">
                                    <option :value="branch.id" x-text="branch.name"></option>
                                </template>
                            </select>
                            @endif
                            <select x-model="ministryFilter" @change="loadDepartments()" 
                                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                                <option value="">All Ministries</option>
                                <template x-for="ministry in ministries" :key="ministry.id">
                                    <option :value="ministry.id" x-text="ministry.name"></option>
                                </template>
                            </select>
                            <select x-model="statusFilter" @change="loadDepartments()" 
                                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <select x-model="sortBy" @change="loadDepartments()" 
                                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                                <option value="name">Sort by Name</option>
                                <option value="created_at">Sort by Date</option>
                                <option value="status">Sort by Status</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Departments List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Loading State -->
                    <div x-show="loading" class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                        <p class="mt-2 text-gray-600">Loading departments...</p>
                    </div>

                    <!-- Empty State -->
                    <div x-show="!loading && departments.length === 0" class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m0 0h2M7 7h10M7 11h6"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No departments found</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new department.</p>
                        <div class="mt-6">
                            <button @click="openCreateModal()" 
                                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg">
                                Add Department
                            </button>
                        </div>
                    </div>

                    <!-- Departments Grid -->
                    <div x-show="!loading && departments.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <template x-for="department in departments" :key="department.id">
                            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900" x-text="department.name"></h4>
                                        <p class="text-sm text-gray-600" x-text="department.description"></p>
                                    </div>
                                    <span :class="department.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                                          class="px-2 py-1 text-xs font-medium rounded-full" x-text="department.status"></span>
                                </div>

                                <div class="space-y-2 mb-4">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m0 0h2M7 7h10M7 11h6"></path>
                                        </svg>
                                        <span>Branch: </span>
                                        <span x-text="department.ministry?.branch ? department.ministry.branch.name : 'No branch assigned'" 
                                              :class="department.ministry?.branch ? 'text-gray-900' : 'text-gray-400'"></span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m0 0h2M7 7h10M7 11h6"></path>
                                        </svg>
                                        <span>Ministry: </span>
                                        <span x-text="department.ministry ? department.ministry.name : 'No ministry assigned'" 
                                              :class="department.ministry ? 'text-gray-900' : 'text-gray-400'"></span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <span>Leader: </span>
                                        <span x-text="department.leader ? department.leader.name : 'No leader assigned'" 
                                              :class="department.leader ? 'text-gray-900' : 'text-gray-400'"></span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <span x-text="`${department.members_count || 0} members`"></span>
                                    </div>
                                </div>

                                <div class="flex justify-between items-center">
                                    <button @click="viewDepartment(department)" 
                                            class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                                        View Details
                                    </button>
                                    <div class="flex space-x-2">
                                        <button @click="editDepartment(department)" 
                                                class="text-gray-600 hover:text-gray-800">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button @click="deleteDepartment(department)" 
                                                class="text-red-600 hover:text-red-800">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Pagination -->
                    <div x-show="!loading && pagination.total > pagination.per_page" class="mt-6 flex justify-between items-center">
                        <div class="text-sm text-gray-700">
                            Showing <span x-text="pagination.from"></span> to <span x-text="pagination.to"></span> of <span x-text="pagination.total"></span> results
                        </div>
                        <div class="flex space-x-2">
                            <button @click="loadPage(pagination.current_page - 1)" 
                                    :disabled="pagination.current_page <= 1"
                                    :class="pagination.current_page <= 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                                    class="px-3 py-2 border border-gray-300 rounded-lg">
                                Previous
                            </button>
                            <button @click="loadPage(pagination.current_page + 1)" 
                                    :disabled="pagination.current_page >= pagination.last_page"
                                    :class="pagination.current_page >= pagination.last_page ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                                    class="px-3 py-2 border border-gray-300 rounded-lg">
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Department Modal -->
        <div x-show="showModal" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4" x-text="editingDepartment ? 'Edit Department' : 'Create New Department'"></h3>
                    
                    <form @submit.prevent="saveDepartment()">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                <input type="text" 
                                       x-model="form.name" 
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                                <p x-show="errors.name" x-text="errors.name" class="mt-1 text-sm text-red-600"></p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea x-model="form.description" 
                                          rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"></textarea>
                                <p x-show="errors.description" x-text="errors.description" class="mt-1 text-sm text-red-600"></p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ministry</label>
                                <select x-model="form.ministry_id" 
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">Select a ministry</option>
                                    <template x-for="ministry in ministries" :key="ministry.id">
                                        <option :value="ministry.id" x-text="ministry.name"></option>
                                    </template>
                                </select>
                                <p x-show="errors.ministry_id" x-text="errors.ministry_id" class="mt-1 text-sm text-red-600"></p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select x-model="form.status" 
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <p x-show="errors.status" x-text="errors.status" class="mt-1 text-sm text-red-600"></p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Leader (Optional)</label>
                                <div class="relative">
                                    <!-- Search Input -->
                                    <div class="relative">
                                        <input type="text"
                                               x-model="leaderSearchTerm"
                                               @focus="showLeaderDropdown = true"
                                               @input="handleLeaderSearch"
                                               @keydown.escape="showLeaderDropdown = false"
                                               @keydown.arrow-down.prevent="highlightNextLeader"
                                               @keydown.arrow-up.prevent="highlightPreviousLeader"
                                               @keydown.enter.prevent="selectHighlightedLeader"
                                               :placeholder="getSelectedLeaderName() || 'Search for a leader...'"
                                               class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                                        
                                        <!-- Dropdown Arrow -->
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <!-- Dropdown -->
                                    <div x-show="showLeaderDropdown"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         @click.away="showLeaderDropdown = false"
                                         class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                                        
                                        <!-- Loading State -->
                                        <div x-show="loadingLeaders" class="px-3 py-2 text-sm text-gray-500 text-center">
                                            <div class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-purple-600 mr-2"></div>
                                            Searching...
                                        </div>
                                        
                                        <!-- No Results -->
                                        <div x-show="!loadingLeaders && filteredLeaders.length === 0" class="px-3 py-2 text-sm text-gray-500 text-center">
                                            No leaders found
                                        </div>
                                        
                                        <!-- Clear Selection Option -->
                                        <div x-show="!loadingLeaders && form.leader_id"
                                             @click="clearLeaderSelection()"
                                             class="px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer border-b border-gray-200">
                                            <span class="text-red-600">✕ Clear selection</span>
                                        </div>
                                        
                                        <!-- Leaders -->
                                        <template x-for="(leader, index) in filteredLeaders" :key="leader.id">
                                            <div @click="selectLeader(leader)"
                                                 :class="leaderHighlightedIndex === index ? 'bg-purple-100' : 'hover:bg-gray-100'"
                                                 class="px-3 py-2 text-sm text-gray-700 cursor-pointer">
                                                <div class="font-medium" x-text="leader.name"></div>
                                                <div x-show="leader.email" class="text-xs text-gray-500" x-text="leader.email"></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <p x-show="errors.leader_id" x-text="errors.leader_id" class="mt-1 text-sm text-red-600"></p>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" 
                                    @click="closeModal()" 
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" 
                                    :disabled="saving"
                                    :class="saving ? 'opacity-50 cursor-not-allowed' : 'hover:bg-purple-700'"
                                    class="px-4 py-2 bg-purple-600 text-white rounded-lg">
                                <span x-show="!saving" x-text="editingDepartment ? 'Update Department' : 'Create Department'"></span>
                                <span x-show="saving">Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Department Details Modal -->
        <div x-show="showDetailsModal" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3" x-show="selectedDepartment">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900" x-text="selectedDepartment?.name"></h3>
                        <button @click="closeDetailsModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-medium text-gray-900">Description</h4>
                            <p class="text-gray-600" x-text="selectedDepartment?.description || 'No description provided'"></p>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900">Ministry</h4>
                            <p class="text-gray-600" x-text="selectedDepartment?.ministry?.name || 'No ministry assigned'"></p>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900">Status</h4>
                            <span :class="selectedDepartment?.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                                  class="px-2 py-1 text-xs font-medium rounded-full" x-text="selectedDepartment?.status"></span>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900">Leader</h4>
                            <p class="text-gray-600" x-text="selectedDepartment?.leader?.name || 'No leader assigned'"></p>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900">Members</h4>
                            <p class="text-gray-600" x-text="`${selectedDepartment?.members_count || 0} members`"></p>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900">Created</h4>
                            <p class="text-gray-600" x-text="formatDate(selectedDepartment?.created_at)"></p>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button @click="editDepartment(selectedDepartment)" 
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            Edit Department
                        </button>
                        <button @click="closeDetailsModal()" 
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function departmentManager() {
            return {
                departments: [],
                ministries: [],
                branches: [],
                loading: false,
                saving: false,
                showModal: false,
                showDetailsModal: false,
                editingDepartment: null,
                selectedDepartment: null,
                search: '',
                branchFilter: '',
                ministryFilter: '',
                statusFilter: '',
                sortBy: 'name',
                pagination: {
                    current_page: 1,
                    last_page: 1,
                    per_page: 15,
                    total: 0,
                    from: 0,
                    to: 0
                },
                form: {
                    name: '',
                    description: '',
                    ministry_id: '',
                    status: 'active',
                    leader_id: ''
                },
                errors: {},
                availableLeaders: [],
                
                // Leader selection properties
                leaderSearchTerm: '',
                showLeaderDropdown: false,
                loadingLeaders: false,
                filteredLeaders: [],
                leaderHighlightedIndex: -1,
                selectedLeader: null,

                async init() {
                    await this.loadBranches();
                    this.loadDepartments();
                    this.loadMinistries();
                    this.loadAvailableLeaders();
                    
                    // Watch for ministry changes to reload leaders
                    this.$watch('form.ministry_id', () => {
                        if (this.form.ministry_id) {
                            this.loadAvailableLeaders();
                            this.filteredLeaders = [];
                            this.clearLeaderSelection();
                        } else {
                            this.availableLeaders = [];
                            this.filteredLeaders = [];
                            this.clearLeaderSelection();
                        }
                    });
                },

                async loadDepartments(page = 1) {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams({
                            page: page,
                            search: this.search,
                            ministry_id: this.ministryFilter,
                            branch_id: this.branchFilter,
                            status: this.statusFilter,
                            sort_by: this.sortBy,
                            sort_direction: 'asc'
                        });

                        const response = await fetch(`/api/departments?${params}`, {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            credentials: 'same-origin'
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.departments = data.data.data || data.data;
                            this.pagination = {
                                current_page: data.data.current_page || 1,
                                last_page: data.data.last_page || 1,
                                per_page: data.data.per_page || 15,
                                total: data.data.total || 0,
                                from: data.data.from || 0,
                                to: data.data.to || 0
                            };
                        }
                    } catch (error) {
                        console.error('Error loading departments:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async loadBranches() {
                    try {
                        const response = await fetch('/api/branches', {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            credentials: 'same-origin'
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.branches = data.data.data || data.data || [];
                        }
                    } catch (error) {
                        console.error('Error loading branches:', error);
                    }
                },

                async loadMinistries() {
                    try {
                        const response = await fetch('/api/ministries', {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            credentials: 'same-origin'
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.ministries = data.data.data || data.data;
                        }
                    } catch (error) {
                        console.error('Error loading ministries:', error);
                    }
                },

                async loadAvailableLeaders() {
                    this.loadingLeaders = true;
                    try {
                        // Build URL with branch filter based on selected ministry
                        let url = '/api/departments/leaders/available';
                        const params = new URLSearchParams();
                        
                        // Get branch from selected ministry if available
                        if (this.form.ministry_id) {
                            const selectedMinistry = this.ministries.find(m => m.id == this.form.ministry_id);
                            if (selectedMinistry && selectedMinistry.branch_id) {
                                params.append('branch_id', selectedMinistry.branch_id);
                            }
                        }
                        
                        if (params.toString()) {
                            url += '?' + params.toString();
                        }

                        const response = await fetch(url, {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            credentials: 'same-origin'
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.availableLeaders = data.data || [];
                            this.filteredLeaders = this.availableLeaders;
                        } else {
                            console.error('Failed to load leaders:', response.status, response.statusText);
                        }
                    } catch (error) {
                        console.error('Error loading available leaders:', error);
                        this.availableLeaders = [];
                        this.filteredLeaders = [];
                    } finally {
                        this.loadingLeaders = false;
                    }
                },

                loadPage(page) {
                    if (page >= 1 && page <= this.pagination.last_page) {
                        this.loadDepartments(page);
                    }
                },

                openCreateModal() {
                    this.editingDepartment = null;
                    this.form = {
                        name: '',
                        description: '',
                        ministry_id: '',
                        status: 'active',
                        leader_id: ''
                    };
                    this.errors = {};
                    this.clearLeaderSelection();
                    this.showModal = true;
                },

                editDepartment(department) {
                    this.editingDepartment = department;
                    this.form = {
                        name: department.name,
                        description: department.description || '',
                        ministry_id: department.ministry_id || '',
                        status: department.status,
                        leader_id: department.leader_id || ''
                    };
                    this.errors = {};
                    
                    // Set selected leader if editing
                    if (department.leader_id && department.leader) {
                        this.selectedLeader = department.leader;
                        this.leaderSearchTerm = department.leader.name;
                    } else {
                        this.clearLeaderSelection();
                    }
                    
                    this.showModal = true;
                    this.showDetailsModal = false;
                },

                viewDepartment(department) {
                    this.selectedDepartment = department;
                    this.showDetailsModal = true;
                },

                closeModal() {
                    this.showModal = false;
                    this.editingDepartment = null;
                    this.form = {};
                    this.errors = {};
                },

                closeDetailsModal() {
                    this.showDetailsModal = false;
                    this.selectedDepartment = null;
                },

                async saveDepartment() {
                    this.saving = true;
                    this.errors = {};

                    try {
                        const url = this.editingDepartment 
                            ? `/api/departments/${this.editingDepartment.id}`
                            : '/api/departments';
                        
                        const method = this.editingDepartment ? 'PUT' : 'POST';

                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify(this.form)
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.closeModal();
                            this.loadDepartments();
                            this.loadAvailableLeaders();
                            // Show success notification
                            window.dispatchEvent(new CustomEvent('notify', {
                                detail: {
                                    message: this.editingDepartment ? 'Department updated successfully!' : 'Department created successfully!',
                                    type: 'success'
                                }
                            }));
                        } else {
                            this.errors = data.errors || {};
                            // Show error notification
                            window.dispatchEvent(new CustomEvent('notify', {
                                detail: {
                                    message: data.message || 'Failed to save department. Please check the form and try again.',
                                    type: 'error'
                                }
                            }));
                        }
                    } catch (error) {
                        console.error('Error saving department:', error);
                        // Show error notification
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: {
                                message: 'Network error. Please check your connection and try again.',
                                type: 'error'
                            }
                        }));
                    } finally {
                        this.saving = false;
                    }
                },

                async deleteDepartment(department) {
                    if (!confirm(`Are you sure you want to delete "${department.name}"?`)) {
                        return;
                    }

                    try {
                        const response = await fetch(`/api/departments/${department.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            credentials: 'same-origin'
                        });

                        if (response.ok) {
                            this.loadDepartments();
                        }
                    } catch (error) {
                        console.error('Error deleting department:', error);
                    }
                },

                formatDate(dateString) {
                    if (!dateString) return '';
                    return new Date(dateString).toLocaleDateString();
                },

                // Leader selection methods
                async handleLeaderSearch() {
                    this.leaderHighlightedIndex = -1;
                    
                    if (!this.form.ministry_id) {
                        this.filteredLeaders = [];
                        return;
                    }
                    
                    // Local search first
                    if (this.leaderSearchTerm.length === 0) {
                        this.filteredLeaders = this.availableLeaders;
                    } else {
                        this.filteredLeaders = this.availableLeaders.filter(leader => 
                            leader.name.toLowerCase().includes(this.leaderSearchTerm.toLowerCase()) ||
                            (leader.email && leader.email.toLowerCase().includes(this.leaderSearchTerm.toLowerCase()))
                        );
                    }
                    
                    // Remote search for more results
                    if (this.leaderSearchTerm.length >= 2) {
                        await this.loadLeadersWithSearch();
                    }
                },

                async loadLeadersWithSearch() {
                    if (!this.form.ministry_id) {
                        this.availableLeaders = [];
                        this.filteredLeaders = [];
                        return;
                    }
                    
                    this.loadingLeaders = true;
                    try {
                        const params = new URLSearchParams();
                        
                        // Get branch from selected ministry
                        const selectedMinistry = this.ministries.find(m => m.id == this.form.ministry_id);
                        if (selectedMinistry && selectedMinistry.branch_id) {
                            params.append('branch_id', selectedMinistry.branch_id);
                        }
                        
                        if (this.leaderSearchTerm) {
                            params.append('search', this.leaderSearchTerm);
                        }
                        
                        const response = await fetch(`/api/departments/leaders/available?${params}`, {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            credentials: 'same-origin'
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            this.availableLeaders = data.data || [];
                            this.filteredLeaders = this.availableLeaders;
                        }
                    } catch (error) {
                        console.error('Error loading leaders:', error);
                        this.availableLeaders = [];
                        this.filteredLeaders = [];
                    } finally {
                        this.loadingLeaders = false;
                    }
                },
                
                selectLeader(leader) {
                    this.selectedLeader = leader;
                    this.form.leader_id = leader.id;
                    this.leaderSearchTerm = leader.name;
                    this.showLeaderDropdown = false;
                    this.leaderHighlightedIndex = -1;
                },
                
                clearLeaderSelection() {
                    this.selectedLeader = null;
                    this.form.leader_id = '';
                    this.leaderSearchTerm = '';
                    this.showLeaderDropdown = false;
                    this.leaderHighlightedIndex = -1;
                },
                
                getSelectedLeaderName() {
                    return this.selectedLeader ? this.selectedLeader.name : '';
                },
                
                highlightNextLeader() {
                    if (this.filteredLeaders.length === 0) return;
                    this.leaderHighlightedIndex = Math.min(this.leaderHighlightedIndex + 1, this.filteredLeaders.length - 1);
                },
                
                highlightPreviousLeader() {
                    if (this.filteredLeaders.length === 0) return;
                    this.leaderHighlightedIndex = Math.max(this.leaderHighlightedIndex - 1, -1);
                },
                
                selectHighlightedLeader() {
                    if (this.leaderHighlightedIndex >= 0 && this.leaderHighlightedIndex < this.filteredLeaders.length) {
                        this.selectLeader(this.filteredLeaders[this.leaderHighlightedIndex]);
                    }
                }
            }
        }


    </script>
</x-app-layout> 
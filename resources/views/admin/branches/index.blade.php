<x-sidebar-layout>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Expressions') }}
            </h2>
            <button id="createBranchBtn" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ __('Add Branch') }}
            </button>
        </div>
    </x-slot>

    <!-- Cache-busting meta tags -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <div class="py-12" x-data="branchManager()">
        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search and Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex flex-col lg:flex-row gap-4 items-end">
                        <!-- Search takes half the width -->
                        <div class="flex-1 lg:w-1/2">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Branches</label>
                            <input type="text" id="search" x-model="search" placeholder="Search by name, address, or pastor..." 
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        <!-- Sort By takes half of remaining space -->
                        <div class="lg:w-1/4">
                            <label for="sortBy" class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                            <select id="sortBy" x-model="sortBy" @change="filterBranches()" 
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="name">Name</option>
                                <option value="venue">Address</option>
                                <option value="pastor">Pastor</option>
                                <option value="created_at">Created Date</option>
                            </select>
                        </div>
                        <!-- Order takes the last quarter -->
                        <div class="lg:w-1/4">
                            <label for="sortOrder" class="block text-sm font-medium text-gray-700 mb-2">Order</label>
                            <select id="sortOrder" x-model="sortOrder" @change="filterBranches()" 
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="asc">A-Z</option>
                                <option value="desc">Z-A</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Branches Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <template x-for="branch in filteredBranches" :key="branch.id">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-lg font-semibold text-gray-900" x-text="branch.name"></h3>
                                <div class="flex space-x-2">
                                    <button @click="openEditModal(branch)" 
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Edit
                                    </button>
                                    <button @click="deleteBranch(branch)" 
                                        class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        Delete
                                    </button>
                                </div>
                            </div>
                            
                            <div class="space-y-2 text-sm text-gray-600">
                                <p><strong>Address:</strong> <span x-text="branch.venue"></span></p>
                                <p><strong>Service Time:</strong> <span x-text="branch.service_time || 'Not provided'"></span></p>
                                <p><strong>Phone:</strong> <span x-text="branch.phone || 'Not provided'"></span></p>
                                <p><strong>Email:</strong> <span x-text="branch.email || 'Not provided'"></span></p>
                                <p><strong>Pastor:</strong> <span x-text="branch.pastor ? branch.pastor.name : 'Not assigned'"></span></p>
                                <p><strong>Status:</strong> 
                                    <span x-text="branch.status" 
                                          :class="{
                                              'text-green-600': branch.status === 'active',
                                              'text-yellow-600': branch.status === 'inactive', 
                                              'text-red-600': branch.status === 'suspended'
                                          }"
                                          class="capitalize font-medium"></span>
                                </p>
                            </div>
                            
                            <div class="mt-4 flex justify-between items-center text-xs text-gray-500">
                                <span x-text="`${branch.members_count || 0} members`"></span>
                                <span x-text="`${branch.ministries_count || 0} ministries`"></span>
                                <span x-text="`${branch.events_count || 0} events`"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- No Results Message -->
            <div x-show="filteredBranches.length === 0" class="text-center py-8">
                <div class="text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No branches found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new branch.</p>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <template x-if="showModal && currentBranch">
            <div class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                    
                    <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                        <form @submit.prevent="saveBranch()">
                            <div class="mb-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" x-text="editMode ? 'Edit Branch' : 'Create New Branch'"></h3>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label for="branch-name" class="block text-sm font-medium text-gray-700">Branch Name</label>
                                        <input type="text" id="branch-name" x-model="currentBranch.name" required 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    
                                    <div>
                                        <label for="branch-venue" class="block text-sm font-medium text-gray-700">Address/Venue</label>
                                        <textarea id="branch-venue" x-model="currentBranch.venue" required rows="2"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="branch-phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                            <input type="tel" id="branch-phone" x-model="currentBranch.phone" 
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                        
                                        <div>
                                            <label for="branch-email" class="block text-sm font-medium text-gray-700">Email</label>
                                            <input type="email" id="branch-email" x-model="currentBranch.email" 
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label for="branch-service-time" class="block text-sm font-medium text-gray-700">Service Time</label>
                                        <input type="text" id="branch-service-time" x-model="currentBranch.service_time" required 
                                            placeholder="e.g., Sunday 9:00 AM"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="branch-status" class="block text-sm font-medium text-gray-700">Status</label>
                                            <select id="branch-status" x-model="currentBranch.status" required 
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                <option value="">Select Status</option>
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                                <option value="suspended">Suspended</option>
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label for="branch-pastor" class="block text-sm font-medium text-gray-700">Pastor</label>
                                            <select id="branch-pastor" x-model="currentBranch.pastor_id" 
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                <option value="">Select Pastor (Optional)</option>
                                                <template x-for="pastor in availablePastors" :key="pastor.id">
                                                    <option :value="pastor.id" x-text="pastor.name"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label for="branch-description" class="block text-sm font-medium text-gray-700">Description</label>
                                        <textarea id="branch-description" x-model="currentBranch.description" rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-end space-x-3">
                                <button type="button" @click="showModal = false" 
                                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Cancel
                                </button>
                                <button type="submit" 
                                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <span x-text="editMode ? 'Update Branch' : 'Create Branch'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>

            <script>
        // CACHE BUSTER: {{ now()->timestamp }} - PASTOR DROPDOWN FIXED
        function branchManager() {
            return {
                showModal: false,
                editMode: false,
                currentBranch: {
                    name: '',
                    venue: '',
                    service_time: '',
                    phone: '',
                    email: '',
                    description: '',
                    status: 'active',
                    pastor_id: null
                },
                branches: @json($branches ?? []),
                filteredBranches: [],
                availablePastors: [],
                search: '',
                sortBy: 'name',
                sortOrder: 'asc',
                
                init() {
                    console.log('COMPLETELY FRESH COMPONENT LOADED {{ now()->timestamp }} - Branches:', this.branches.length);
                    this.filterBranches();
                    this.loadAvailablePastors();
                    this.$watch('search', () => this.filterBranches());
                    
                    window.addEventListener('open-create-modal', () => {
                        this.openCreateModal();
                    });
                },
                    
                                    async loadAvailablePastors() {
                    try {
                        const response = await fetch('/api/branches/pastors/available', {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });
                        
                        const data = await response.json();
                        if (data.success) {
                            this.availablePastors = data.data;
                            console.log('Available pastors loaded:', this.availablePastors.length);
                        } else {
                            console.error('Failed to load pastors:', data.message);
                        }
                    } catch (error) {
                        console.error('Error loading available pastors:', error);
                    }
                },
                
                filterBranches() {
                    if (!this.branches || !Array.isArray(this.branches)) {
                        this.filteredBranches = [];
                        return;
                    }
                    
                    let filtered = this.branches.filter(branch => {
                        if (!branch) return false;
                        const searchLower = (this.search || '').toLowerCase();
                        return (
                            (branch.name && branch.name.toLowerCase().includes(searchLower)) ||
                            (branch.venue && branch.venue.toLowerCase().includes(searchLower)) ||
                            (branch.email && branch.email.toLowerCase().includes(searchLower)) ||
                            (branch.pastor && branch.pastor.name && branch.pastor.name.toLowerCase().includes(searchLower))
                        );
                    });
                    
                    filtered.sort((a, b) => {
                        if (!a || !b) return 0;
                        
                        let aVal = a[this.sortBy] || '';
                        let bVal = b[this.sortBy] || '';
                        
                        if (this.sortBy === 'pastor') {
                            aVal = (a.pastor && a.pastor.name) ? a.pastor.name : '';
                            bVal = (b.pastor && b.pastor.name) ? b.pastor.name : '';
                        }
                        
                        if (this.sortOrder === 'asc') {
                            return aVal.toString().localeCompare(bVal.toString());
                        } else {
                            return bVal.toString().localeCompare(aVal.toString());
                        }
                    });
                    
                    this.filteredBranches = filtered;
                    console.log('Filtered branches:', this.filteredBranches.length);
                },
                    
                                    openCreateModal() {
                    console.log('Opening create modal');
                    this.editMode = false;
                    this.currentBranch = {
                        name: '',
                        venue: '',
                        service_time: '',
                        phone: '',
                        email: '',
                        description: '',
                        status: 'active',
                        pastor_id: null
                    };
                    this.showModal = true;
                },
                    
                                    openEditModal(branch) {
                    console.log('Opening edit modal for branch:', branch);
                    this.editMode = true;
                    this.currentBranch = { 
                        ...branch,
                        pastor_id: branch.pastor_id || null
                    };
                    this.showModal = true;
                },
                    
                    async saveBranch() {
                        try {
                            const url = this.editMode ? `/api/branches/${this.currentBranch.id}` : '/api/branches';
                            const method = this.editMode ? 'PUT' : 'POST';
                            
                            const response = await fetch(url, {
                                method: method,
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                credentials: 'same-origin',
                                body: JSON.stringify(this.currentBranch)
                            });
                            
                            const data = await response.json();
                            if (data.success) {
                                this.showModal = false;
                                this.showNotification(this.editMode ? 'Branch updated successfully!' : 'Branch created successfully!', 'success');
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            } else {
                                this.showNotification(data.message || 'An error occurred', 'error');
                            }
                        } catch (error) {
                            console.error('Error saving branch:', error);
                            this.showNotification('An error occurred', 'error');
                        }
                    },
                    
                    async deleteBranch(branch) {
                        if (!confirm('Are you sure you want to delete this branch? This action cannot be undone.')) {
                            return;
                        }
                        
                        try {
                            const response = await fetch(`/api/branches/${branch.id}`, {
                                method: 'DELETE',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                credentials: 'same-origin'
                            });
                            
                            const data = await response.json();
                            if (data.success) {
                                this.showNotification('Branch deleted successfully!', 'success');
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            } else {
                                this.showNotification(data.message || 'An error occurred', 'error');
                            }
                        } catch (error) {
                            console.error('Error deleting branch:', error);
                            this.showNotification('An error occurred', 'error');
                        }
                    },
                    
                    showNotification(message, type = 'info') {
                        alert(message);
                    }
                };
            }

            // Connect the header button
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(() => {
                    const createBtn = document.getElementById('createBranchBtn');
                    if (createBtn) {
                        createBtn.addEventListener('click', function() {
                            window.dispatchEvent(new CustomEvent('open-create-modal'));
                        });
                        console.log('Header button connected successfully');
                    }
                }, 100);
            });
        </script>
    </div>
</x-sidebar-layout> 
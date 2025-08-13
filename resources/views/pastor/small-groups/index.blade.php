<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Small Groups Management') }}
            </h2>
            <button onclick="openAddGroupModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                Add Small Group
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search and Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                        <div>
                            <input type="text" id="searchInput" placeholder="Search groups..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div>
                            <select id="leaderFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Groups</option>
                                <option value="has_leader">Has Leader</option>
                                <option value="no_leader">No Leader</option>
                            </select>
                        </div>
                        @if($isSuperAdmin ?? false)
                        <div>
                            <select id="branchFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Branches</option>
                            </select>
                        </div>
                        @endif
                    </div>
                    <div class="flex justify-between items-center">
                        <button onclick="clearFilters()" class="text-gray-600 hover:text-gray-800">
                            Clear Filters
                        </button>
                        <div class="text-sm text-gray-600">
                            Total: <span id="totalGroups">0</span> groups
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-2xl font-bold text-blue-600" id="totalGroupsCount">0</div>
                    <div class="text-gray-600">Total Groups</div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-2xl font-bold text-green-600" id="activeGroupsCount">0</div>
                    <div class="text-gray-600">Active Groups</div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-2xl font-bold text-purple-600" id="totalMembersCount">0</div>
                    <div class="text-gray-600">Total Members</div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-2xl font-bold text-orange-600" id="groupsWithoutLeaderCount">0</div>
                    <div class="text-gray-600">Groups Without Leader</div>
                </div>
            </div>

            <!-- Groups List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div id="loadingSpinner" class="text-center py-8 hidden">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-gray-600">Loading groups...</p>
                    </div>
                    
                    <div id="groupsContainer">
                        <!-- Groups will be loaded here -->
                    </div>
                    
                    <div id="paginationContainer" class="mt-6">
                        <!-- Pagination will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Group Modal -->
    <div id="groupModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 id="modalTitle" class="text-lg font-medium">Add Small Group</h3>
                        <button onclick="closeGroupModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <form id="groupForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                                <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                            @if($isSuperAdmin ?? false)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
                                <select name="branch_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Branch</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Select the branch this group belongs to</p>
                            </div>
                            @else
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                                <input type="text" value="Your Branch" disabled class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                                <p class="text-xs text-gray-500 mt-1">Groups will be automatically assigned to your branch</p>
                            </div>
                            @endif
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Meeting Day</label>
                                <select name="meeting_day" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Day</option>
                                    <option value="Monday">Monday</option>
                                    <option value="Tuesday">Tuesday</option>
                                    <option value="Wednesday">Wednesday</option>
                                    <option value="Thursday">Thursday</option>
                                    <option value="Friday">Friday</option>
                                    <option value="Saturday">Saturday</option>
                                    <option value="Sunday">Sunday</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Meeting Time</label>
                                <input type="time" name="meeting_time" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                                <input type="text" name="location" placeholder="e.g., Church Hall A, Pastor's Office, etc." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Leader</label>
                                <select name="leader_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">No Leader Assigned</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Choose a member to lead this group (optional)</p>
                            </div>
                        </div>
                        
                        <div class="flex justify-end gap-2 mt-6">
                            <button type="button" onclick="closeGroupModal()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Save Group
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Member Management Modal -->
    <div id="memberModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 id="memberModalTitle" class="text-lg font-medium">Manage Members</h3>
                        <button onclick="closeMemberModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Available Members -->
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Available Members</h4>
                            <div class="mb-3">
                                <input type="text" id="availableMembersSearch" placeholder="Search available members..." 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            </div>
                            <div id="availableMembersContainer" class="border rounded-md max-h-64 overflow-y-auto">
                                <!-- Available members will be loaded here -->
                            </div>
                            <button onclick="assignSelectedMembers()" class="mt-3 w-full bg-blue-600 text-white px-3 py-2 rounded-md text-sm hover:bg-blue-700">
                                Add Selected Members
                            </button>
                        </div>
                        
                        <!-- Current Members -->
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Current Members</h4>
                            <div id="currentMembersContainer" class="border rounded-md max-h-64 overflow-y-auto">
                                <!-- Current members will be loaded here -->
                            </div>
                            <button onclick="removeSelectedMembers()" class="mt-3 w-full bg-red-600 text-white px-3 py-2 rounded-md text-sm hover:bg-red-700">
                                Remove Selected Members
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let currentGroupId = null;
        let isSuperAdmin = {{ $isSuperAdmin ?? 'false' ? 'true' : 'false' }};
        let selectedAvailableMembers = [];
        let selectedCurrentMembers = [];

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded, isSuperAdmin:', isSuperAdmin);
            console.log('API token available:', !!document.querySelector('meta[name="api-token"]'));
            console.log('CSRF token available:', !!document.querySelector('meta[name="csrf-token"]'));
            
            loadGroups();
            loadStatistics();
            if (isSuperAdmin) {
                loadBranches();
            }
            setupEventListeners();
        });

        function setupEventListeners() {
            // Search input with debounce
            let searchTimeout;
            document.getElementById('searchInput').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentPage = 1;
                    loadGroups();
                }, 300);
            });

            // Filter changes
            ['statusFilter', 'leaderFilter', 'branchFilter'].forEach(filterId => {
                const element = document.getElementById(filterId);
                if (element) {
                    element.addEventListener('change', () => {
                        currentPage = 1;
                        loadGroups();
                    });
                }
            });

            // Group form submission
            document.getElementById('groupForm').addEventListener('submit', handleGroupSubmit);
        }

        async function loadGroups() {
            showLoading(true);
            
            try {
                const params = new URLSearchParams({
                    page: currentPage,
                    per_page: 10,
                    search: document.getElementById('searchInput').value,
                    status: document.getElementById('statusFilter').value,
                    has_leader: document.getElementById('leaderFilter').value,
                });

                if (isSuperAdmin) {
                    const branchFilter = document.getElementById('branchFilter').value;
                    if (branchFilter) params.append('branch_id', branchFilter);
                }

                const response = await fetch(`/api/small-groups?${params}`, {
                    headers: {
                        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to load groups');
                }

                const data = await response.json();
                
                displayGroups(data.data.data);
                updatePagination(data.data);
                document.getElementById('totalGroups').textContent = data.data.total;
                
            } catch (error) {
                console.error('Error loading groups:', error);
                showError('Failed to load groups. Please try again.');
            } finally {
                showLoading(false);
            }
        }

        async function loadStatistics() {
            try {
                const response = await fetch('/api/small-groups/statistics', {
                    headers: {
                        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    const stats = data.data;
                    document.getElementById('totalGroupsCount').textContent = stats.total_groups || 0;
                    document.getElementById('activeGroupsCount').textContent = stats.active_groups || 0;
                    document.getElementById('totalMembersCount').textContent = stats.total_members || 0;
                    document.getElementById('groupsWithoutLeaderCount').textContent = stats.groups_without_leader || 0;
                }
            } catch (error) {
                console.error('Error loading statistics:', error);
            }
        }

        async function loadBranches() {
            try {
                const response = await fetch('/api/branches', {
                    headers: {
                        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    const branchFilter = document.getElementById('branchFilter');
                    const branchSelect = document.querySelector('select[name="branch_id"]');
                    
                    if (branchFilter) {
                        branchFilter.innerHTML = '<option value="">All Branches</option>' +
                            data.data.data.map(branch => `<option value="${branch.id}">${branch.name}</option>`).join('');
                    }
                    
                    if (branchSelect) {
                        branchSelect.innerHTML = '<option value="">Select Branch</option>' +
                            data.data.data.map(branch => `<option value="${branch.id}">${branch.name}</option>`).join('');
                    }
                }
            } catch (error) {
                console.error('Error loading branches:', error);
            }
        }

        function displayGroups(groups) {
            const container = document.getElementById('groupsContainer');
            
            if (groups.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM9 9a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="mt-2">No small groups found.</p>
                        <button onclick="openAddGroupModal()" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Create Your First Group
                        </button>
                    </div>
                `;
                return;
            }

            container.innerHTML = groups.map(group => `
                <div class="border border-gray-200 rounded-lg p-4 mb-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h4 class="font-medium text-gray-900">${group.name}</h4>
                                <span class="bg-${group.is_active ? 'green' : 'red'}-100 text-${group.is_active ? 'green' : 'red'}-800 px-2 py-1 rounded text-xs">
                                    ${group.is_active ? 'Active' : 'Inactive'}
                                </span>
                                ${!group.has_leader ? '<span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs">No Leader</span>' : ''}
                            </div>
                            <p class="text-sm text-gray-600 mb-2">${group.description || 'No description available'}</p>
                            <div class="flex flex-wrap gap-2 text-xs">
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                    ${group.members_count} member${group.members_count !== 1 ? 's' : ''}
                                </span>
                                ${group.leader ? `<span class="bg-purple-100 text-purple-800 px-2 py-1 rounded">Leader: ${group.leader.name}</span>` : ''}
                                ${group.meeting_day ? `<span class="bg-gray-100 text-gray-800 px-2 py-1 rounded">${group.meeting_day}s</span>` : ''}
                                ${group.meeting_time ? `<span class="bg-gray-100 text-gray-800 px-2 py-1 rounded">${group.meeting_time}</span>` : ''}
                                ${group.location ? `<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded">${group.location}</span>` : ''}
                                ${isSuperAdmin && group.branch ? `<span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded">${group.branch.name}</span>` : ''}
                            </div>
                        </div>
                        <div class="flex gap-2 ml-4">
                            <button onclick="editGroup(${group.id})" class="text-blue-600 hover:text-blue-800 text-sm">
                                Edit
                            </button>
                            <button onclick="openMemberModal(${group.id})" class="text-green-600 hover:text-green-800 text-sm">
                                Members
                            </button>
                            <button onclick="deleteGroup(${group.id})" class="text-red-600 hover:text-red-800 text-sm">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function openAddGroupModal() {
            currentGroupId = null;
            document.getElementById('modalTitle').textContent = 'Add Small Group';
            document.getElementById('groupForm').reset();
            loadAvailableLeaders();
            document.getElementById('groupModal').classList.remove('hidden');
        }

        function closeGroupModal() {
            document.getElementById('groupModal').classList.add('hidden');
        }

        async function loadAvailableLeaders() {
            try {
                const apiToken = document.querySelector('meta[name="api-token"]');
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                
                if (!apiToken || !csrfToken) {
                    console.error('Missing authentication tokens');
                    return;
                }

                const response = await fetch('/api/small-groups/leaders/available', {
                    headers: {
                        'Authorization': 'Bearer ' + apiToken.content,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.content
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    const leaderSelect = document.querySelector('select[name="leader_id"]');
                    
                    if (leaderSelect && data.data) {
                        leaderSelect.innerHTML = '<option value="">No Leader Assigned</option>' +
                            data.data.map(member => `<option value="${member.id}">${member.name}</option>`).join('');
                    } else {
                        console.error('Invalid response format or missing leader select element');
                        if (leaderSelect) {
                            leaderSelect.innerHTML = '<option value="">No Leader Assigned</option>';
                        }
                    }
                } else {
                    console.error('Failed to load leaders:', response.status, response.statusText);
                    const leaderSelect = document.querySelector('select[name="leader_id"]');
                    if (leaderSelect) {
                        leaderSelect.innerHTML = '<option value="">No Leader Assigned</option>';
                    }
                }
            } catch (error) {
                console.error('Error loading available leaders:', error);
                const leaderSelect = document.querySelector('select[name="leader_id"]');
                if (leaderSelect) {
                    leaderSelect.innerHTML = '<option value="">No Leader Assigned</option>';
                }
            }
        }

        async function editGroup(groupId) {
            try {
                const response = await fetch(`/api/small-groups/${groupId}`, {
                    headers: {
                        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to load group details');
                }

                const data = await response.json();
                const group = data.data;
                
                currentGroupId = groupId;
                document.getElementById('modalTitle').textContent = 'Edit Small Group';
                
                // Populate form fields
                document.querySelector('input[name="name"]').value = group.name || '';
                document.querySelector('textarea[name="description"]').value = group.description || '';
                document.querySelector('select[name="status"]').value = group.status || 'active';
                document.querySelector('select[name="meeting_day"]').value = group.meeting_day || '';
                document.querySelector('input[name="meeting_time"]').value = group.meeting_time || '';
                document.querySelector('input[name="location"]').value = group.location || '';
                
                if (isSuperAdmin) {
                    document.querySelector('select[name="branch_id"]').value = group.branch_id || '';
                }
                
                // Load leaders and then set the selected leader
                await loadAvailableLeaders();
                document.querySelector('select[name="leader_id"]').value = group.leader_id || '';
                
                document.getElementById('groupModal').classList.remove('hidden');
                
            } catch (error) {
                console.error('Error loading group details:', error);
                showError('Failed to load group details. Please try again.');
            }
        }

        async function deleteGroup(groupId) {
            if (!confirm('Are you sure you want to delete this group? This action cannot be undone.')) {
                return;
            }

            try {
                const response = await fetch(`/api/small-groups/${groupId}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to delete group');
                }

                showSuccess('Group deleted successfully!');
                loadGroups();
                loadStatistics();
                
            } catch (error) {
                console.error('Error deleting group:', error);
                showError(error.message || 'Failed to delete group. Please try again.');
            }
        }

        async function handleGroupSubmit(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());

            try {
                const url = currentGroupId ? `/api/small-groups/${currentGroupId}` : '/api/small-groups';
                const method = currentGroupId ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to save group');
                }

                showSuccess(currentGroupId ? 'Group updated successfully!' : 'Group created successfully!');
                closeGroupModal();
                loadGroups();
                loadStatistics();
                
            } catch (error) {
                console.error('Error saving group:', error);
                showError(error.message || 'Failed to save group. Please try again.');
            }
        }

        function updatePagination(data) {
            const container = document.getElementById('paginationContainer');
            
            if (data.last_page <= 1) {
                container.innerHTML = '';
                return;
            }

            let paginationHTML = '<div class="flex justify-center items-center space-x-2">';
            
            // Previous button
            if (data.current_page > 1) {
                paginationHTML += `<button onclick="changePage(${data.current_page - 1})" class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Previous</button>`;
            }
            
            // Page numbers
            for (let i = Math.max(1, data.current_page - 2); i <= Math.min(data.last_page, data.current_page + 2); i++) {
                const isActive = i === data.current_page;
                paginationHTML += `<button onclick="changePage(${i})" class="px-3 py-2 text-sm border ${isActive ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 hover:bg-gray-50'} rounded-md">${i}</button>`;
            }
            
            // Next button
            if (data.current_page < data.last_page) {
                paginationHTML += `<button onclick="changePage(${data.current_page + 1})" class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Next</button>`;
            }
            
            paginationHTML += '</div>';
            container.innerHTML = paginationHTML;
        }

        function changePage(page) {
            currentPage = page;
            loadGroups();
        }

        function showLoading(show) {
            document.getElementById('loadingSpinner').classList.toggle('hidden', !show);
        }

        function showError(message) {
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => document.body.removeChild(toast), 5000);
        }

        function showSuccess(message) {
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => document.body.removeChild(toast), 5000);
        }

        function manageMembers(groupId) {
            // For now, redirect to a dedicated member management page
            // This could be enhanced with a modal in the future
            window.location.href = `/pastor/small-groups/${groupId}/members`;
        }

        function clearFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('leaderFilter').value = '';
            if (isSuperAdmin) {
                document.getElementById('branchFilter').value = '';
            }
            currentPage = 1;
            loadGroups();
        }

        // Member Assignment Functions
        let currentManageMembersGroupId = null;

        function openMemberModal(groupId) {
            currentManageMembersGroupId = groupId;
            document.getElementById('memberModalTitle').textContent = 'Manage Group Members';
            selectedAvailableMembers = [];
            selectedCurrentMembers = [];
            loadGroupMembers(groupId);
            loadAvailableMembersForGroup(groupId);
            document.getElementById('memberModal').classList.remove('hidden');
        }

        function closeMemberModal() {
            document.getElementById('memberModal').classList.add('hidden');
            currentManageMembersGroupId = null;
        }

        async function loadGroupMembers(groupId) {
            try {
                const response = await fetch(`/api/small-groups/${groupId}`, {
                    headers: {
                        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    const currentMembersContainer = document.getElementById('currentMembersContainer');
                    
                    if (data.data.members && data.data.members.length > 0) {
                        currentMembersContainer.innerHTML = data.data.members.map(member => `
                            <div class="flex items-center p-2 hover:bg-gray-50">
                                <input type="checkbox" value="${member.id}" onchange="toggleCurrentMemberSelection(${member.id})" 
                                       class="mr-2 current-member-checkbox">
                                <span class="text-sm">${member.name}</span>
                            </div>
                        `).join('');
                    } else {
                        currentMembersContainer.innerHTML = '<div class="p-4 text-center text-gray-500">No members assigned</div>';
                    }
                }
            } catch (error) {
                console.error('Error loading group members:', error);
            }
        }

        async function loadAvailableMembersForGroup(groupId) {
            try {
                const response = await fetch(`/api/small-groups/${groupId}/available-members`, {
                    headers: {
                        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    const availableMembersContainer = document.getElementById('availableMembersContainer');
                    
                    if (data.data && data.data.length > 0) {
                        availableMembersContainer.innerHTML = data.data.map(member => `
                            <div class="flex items-center p-2 hover:bg-gray-50">
                                <input type="checkbox" value="${member.id}" onchange="toggleAvailableMemberSelection(${member.id})" 
                                       class="mr-2 available-member-checkbox">
                                <span class="text-sm">${member.name}</span>
                            </div>
                        `).join('');
                    } else {
                        availableMembersContainer.innerHTML = '<div class="p-4 text-center text-gray-500">No available members</div>';
                    }
                }
            } catch (error) {
                console.error('Error loading available members:', error);
            }
        }

        function toggleAvailableMemberSelection(memberId) {
            const index = selectedAvailableMembers.indexOf(memberId);
            if (index > -1) {
                selectedAvailableMembers.splice(index, 1);
            } else {
                selectedAvailableMembers.push(memberId);
            }
        }

        function toggleCurrentMemberSelection(memberId) {
            const index = selectedCurrentMembers.indexOf(memberId);
            if (index > -1) {
                selectedCurrentMembers.splice(index, 1);
            } else {
                selectedCurrentMembers.push(memberId);
            }
        }

        async function assignSelectedMembers() {
            if (selectedAvailableMembers.length === 0) {
                showError('Please select at least one member to assign.');
                return;
            }

            try {
                const response = await fetch(`/api/small-groups/${currentManageMembersGroupId}/assign-members`, {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        member_ids: selectedAvailableMembers
                    })
                });

                if (response.ok) {
                    showSuccess('Members assigned successfully!');
                    selectedAvailableMembers = [];
                    loadGroupMembers(currentManageMembersGroupId);
                    loadAvailableMembersForGroup(currentManageMembersGroupId);
                    loadGroups(); // Refresh the groups list
                } else {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to assign members');
                }
            } catch (error) {
                console.error('Error assigning members:', error);
                showError(error.message || 'Failed to assign members.');
            }
        }

        async function removeSelectedMembers() {
            if (selectedCurrentMembers.length === 0) {
                showError('Please select at least one member to remove.');
                return;
            }

            try {
                const response = await fetch(`/api/small-groups/${currentManageMembersGroupId}/remove-members`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        member_ids: selectedCurrentMembers
                    })
                });

                if (response.ok) {
                    showSuccess('Members removed successfully!');
                    selectedCurrentMembers = [];
                    loadGroupMembers(currentManageMembersGroupId);
                    loadAvailableMembersForGroup(currentManageMembersGroupId);
                    loadGroups(); // Refresh the groups list
                } else {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to remove members');
                }
            } catch (error) {
                console.error('Error removing members:', error);
                showError(error.message || 'Failed to remove members.');
            }
        }
    </script>
</x-app-layout> 
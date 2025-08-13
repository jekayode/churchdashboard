<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Small Groups') }}
            </h2>
            <a href="{{ route('member.groups.reports') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                Meeting Reports
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search and Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <input type="text" id="searchInput" placeholder="Search groups..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Groups</option>
                                <option value="active">Active Groups</option>
                                <option value="inactive">Inactive Groups</option>
                            </select>
                        </div>
                        <div>
                            <select id="membershipFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Groups</option>
                                <option value="my_groups">My Groups</option>
                                <option value="available">Available to Join</option>
                            </select>
                        </div>
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

            <!-- My Groups Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-4 text-green-800">My Small Groups</h3>
                    <div id="myGroupsContainer">
                        <!-- My groups will be loaded here -->
                    </div>
                </div>
            </div>

            <!-- Available Groups Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-4">Available Small Groups</h3>
                    
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

    <!-- Group Details Modal -->
    <div id="groupModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 id="modalTitle" class="text-lg font-medium">Group Details</h3>
                        <button onclick="closeGroupModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div id="groupDetails">
                        <!-- Group details will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let currentMemberId = {{ auth()->user()->member?->id ?? 'null' }};
        let myGroups = [];
        let allGroups = [];

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            if (!currentMemberId) {
                showError('You need to have a member profile to view small groups.');
                return;
            }
            
            loadGroups();
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
            document.getElementById('statusFilter').addEventListener('change', () => {
                currentPage = 1;
                loadGroups();
            });

            document.getElementById('membershipFilter').addEventListener('change', () => {
                currentPage = 1;
                loadGroups();
            });
        }

        async function loadGroups() {
            showLoading(true);
            
            try {
                const params = new URLSearchParams({
                    page: currentPage,
                    per_page: 10,
                    search: document.getElementById('searchInput').value,
                    status: document.getElementById('statusFilter').value,
                });

                const response = await fetch(`/api/small-groups?${params}`, {
                    headers: {
                        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]')?.content,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to load groups');
                }

                const data = await response.json();
                allGroups = data.data.data;
                
                // Filter groups based on membership
                filterAndDisplayGroups();
                updatePagination(data.data);
                document.getElementById('totalGroups').textContent = data.data.total;
                
            } catch (error) {
                console.error('Error loading groups:', error);
                showError('Failed to load groups. Please try again.');
            } finally {
                showLoading(false);
            }
        }

        function filterAndDisplayGroups() {
            const membershipFilter = document.getElementById('membershipFilter').value;
            
            // Separate groups into my groups and available groups
            myGroups = allGroups.filter(group => 
                group.members && group.members.some(member => member.id === currentMemberId)
            );
            
            const availableGroups = allGroups.filter(group => 
                !group.members || !group.members.some(member => member.id === currentMemberId)
            );

            // Apply membership filter
            let groupsToShow = allGroups;
            if (membershipFilter === 'my_groups') {
                groupsToShow = myGroups;
            } else if (membershipFilter === 'available') {
                groupsToShow = availableGroups;
            }

            displayMyGroups();
            displayGroups(membershipFilter === 'my_groups' ? [] : (membershipFilter === 'available' ? groupsToShow : availableGroups));
        }

        function displayMyGroups() {
            const container = document.getElementById('myGroupsContainer');
            
            if (myGroups.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM9 9a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="mt-2">You're not part of any small groups yet.</p>
                        <p class="text-sm">Browse available groups below to join one!</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = myGroups.map(group => `
                <div class="border border-green-200 rounded-lg p-4 mb-4 bg-green-50">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="font-medium text-green-900">${group.name}</h4>
                            <p class="text-sm text-green-700 mt-1">${group.description || 'No description available'}</p>
                            <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded">
                                    ${group.members_count} member${group.members_count !== 1 ? 's' : ''}
                                </span>
                                ${group.leader ? `<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">Leader: ${group.leader.name}</span>` : ''}
                                ${group.meeting_day ? `<span class="bg-gray-100 text-gray-800 px-2 py-1 rounded">${group.meeting_day}s</span>` : ''}
                                ${group.meeting_time ? `<span class="bg-gray-100 text-gray-800 px-2 py-1 rounded">${group.meeting_time}</span>` : ''}
                            </div>
                        </div>
                        <div class="flex gap-2 ml-4">
                            <button onclick="viewGroup(${group.id})" class="text-green-600 hover:text-green-800 text-sm">
                                View Details
                            </button>
                            <button onclick="leaveGroup(${group.id})" class="text-red-600 hover:text-red-800 text-sm">
                                Leave Group
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function displayGroups(groups) {
            const container = document.getElementById('groupsContainer');
            
            if (groups.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM9 9a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="mt-2">No groups available to join at this time.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = groups.map(group => `
                <div class="border border-gray-200 rounded-lg p-4 mb-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900">${group.name}</h4>
                            <p class="text-sm text-gray-600 mt-1">${group.description || 'No description available'}</p>
                            <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                    ${group.members_count} member${group.members_count !== 1 ? 's' : ''}
                                </span>
                                <span class="bg-${group.is_active ? 'green' : 'red'}-100 text-${group.is_active ? 'green' : 'red'}-800 px-2 py-1 rounded">
                                    ${group.is_active ? 'Active' : 'Inactive'}
                                </span>
                                ${group.leader ? `<span class="bg-purple-100 text-purple-800 px-2 py-1 rounded">Leader: ${group.leader.name}</span>` : ''}
                                ${group.meeting_day ? `<span class="bg-gray-100 text-gray-800 px-2 py-1 rounded">${group.meeting_day}s</span>` : ''}
                                ${group.meeting_time ? `<span class="bg-gray-100 text-gray-800 px-2 py-1 rounded">${group.meeting_time}</span>` : ''}
                                ${group.location ? `<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded">${group.location}</span>` : ''}
                            </div>
                        </div>
                        <div class="flex gap-2 ml-4">
                            <button onclick="viewGroup(${group.id})" class="text-blue-600 hover:text-blue-800 text-sm">
                                View Details
                            </button>
                            ${group.is_active ? `<button onclick="joinGroup(${group.id})" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">Join Group</button>` : ''}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        async function viewGroup(groupId) {
            try {
                const response = await fetch(`/api/small-groups/${groupId}`, {
                    headers: {
                        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]')?.content,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to load group details');
                }

                const data = await response.json();
                const group = data.data;
                
                document.getElementById('modalTitle').textContent = group.name;
                document.getElementById('groupDetails').innerHTML = `
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-medium text-gray-900">Description</h4>
                            <p class="text-gray-600">${group.description || 'No description available'}</p>
                        </div>
                        
                        ${group.leader ? `
                        <div>
                            <h4 class="font-medium text-gray-900">Group Leader</h4>
                            <p class="text-gray-600">${group.leader.name}</p>
                        </div>
                        ` : ''}
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-medium text-gray-900">Meeting Day</h4>
                                <p class="text-gray-600">${group.meeting_day || 'Not specified'}</p>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900">Meeting Time</h4>
                                <p class="text-gray-600">${group.meeting_time || 'Not specified'}</p>
                            </div>
                        </div>
                        
                        ${group.location ? `
                        <div>
                            <h4 class="font-medium text-gray-900">Location</h4>
                            <p class="text-gray-600">${group.location}</p>
                        </div>
                        ` : ''}
                        
                        <div>
                            <h4 class="font-medium text-gray-900">Status</h4>
                            <span class="bg-${group.is_active ? 'green' : 'red'}-100 text-${group.is_active ? 'green' : 'red'}-800 px-2 py-1 rounded text-sm">
                                ${group.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900">Members (${group.members_count})</h4>
                            ${group.members && group.members.length > 0 ? `
                                <div class="mt-2 space-y-1">
                                    ${group.members.map(member => `
                                        <div class="flex items-center justify-between py-1">
                                            <span class="text-gray-600">${member.name}</span>
                                            ${member.id === group.leader?.id ? '<span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded">Leader</span>' : ''}
                                        </div>
                                    `).join('')}
                                </div>
                            ` : '<p class="text-gray-500 text-sm">No members yet</p>'}
                        </div>
                        
                        <div class="flex gap-2 pt-4 border-t">
                            ${group.is_active && !group.members?.some(member => member.id === currentMemberId) ? `
                                <button onclick="joinGroup(${group.id})" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                                    Join Group
                                </button>
                            ` : ''}
                            ${group.members?.some(member => member.id === currentMemberId) ? `
                                <button onclick="leaveGroup(${group.id})" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                                    Leave Group
                                </button>
                            ` : ''}
                        </div>
                    </div>
                `;
                
                document.getElementById('groupModal').classList.remove('hidden');
                
            } catch (error) {
                console.error('Error loading group details:', error);
                showError('Failed to load group details. Please try again.');
            }
        }

        async function joinGroup(groupId) {
            if (!confirm('Are you sure you want to join this group?')) {
                return;
            }

            try {
                const response = await fetch(`/api/small-groups/${groupId}/assign-members`, {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]')?.content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        member_ids: [currentMemberId]
                    })
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to join group');
                }

                showSuccess('Successfully joined the group!');
                closeGroupModal();
                loadGroups(); // Refresh the groups list
                
            } catch (error) {
                console.error('Error joining group:', error);
                showError(error.message || 'Failed to join group. Please try again.');
            }
        }

        async function leaveGroup(groupId) {
            if (!confirm('Are you sure you want to leave this group?')) {
                return;
            }

            try {
                const response = await fetch(`/api/small-groups/${groupId}/remove-members`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]')?.content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        member_ids: [currentMemberId]
                    })
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to leave group');
                }

                showSuccess('Successfully left the group!');
                closeGroupModal();
                loadGroups(); // Refresh the groups list
                
            } catch (error) {
                console.error('Error leaving group:', error);
                showError(error.message || 'Failed to leave group. Please try again.');
            }
        }

        function closeGroupModal() {
            document.getElementById('groupModal').classList.add('hidden');
        }

        function clearFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('membershipFilter').value = '';
            currentPage = 1;
            loadGroups();
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
            // Create a simple toast notification
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 5000);
        }

        function showSuccess(message) {
            // Create a simple toast notification
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 5000);
        }
    </script>
</x-app-layout> 
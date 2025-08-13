<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Members') }}
            </h2>
            <button onclick="openAddMemberModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                Add Member
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
                            <input type="text" id="searchInput" placeholder="Search members..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Statuses</option>
                                <option value="visitor">Visitor</option>
                                <option value="member">Member</option>
                                <option value="volunteer">Volunteer</option>
                                <option value="leader">Leader</option>
                                <option value="minister">Minister</option>
                            </select>
                        </div>
                        <div>
                            <select id="growthLevelFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Growth Levels</option>
                                <option value="new_believer">New Believer</option>
                                <option value="growing">Growing</option>
                                <option value="core">Core</option>
                                <option value="pastor">Pastor</option>
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
                            Total: <span id="totalMembers">0</span> members
                        </div>
                    </div>
                </div>
            </div>

            <!-- Members List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div id="loadingSpinner" class="text-center py-8 hidden">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-gray-600">Loading members...</p>
                    </div>
                    
                    <div id="membersContainer">
                        <!-- Members will be loaded here -->
                    </div>
                    
                    <div id="paginationContainer" class="mt-6">
                        <!-- Pagination will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Member Modal -->
    <div id="memberModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 id="modalTitle" class="text-lg font-medium">Add Member</h3>
                        <button onclick="closeMemberModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <form id="memberForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                                <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input type="tel" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                                <select name="gender" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Member Status</label>
                                <select name="member_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="visitor">Visitor</option>
                                    <option value="member">Member</option>
                                    <option value="volunteer">Volunteer</option>
                                    <option value="leader">Leader</option>
                                    <option value="minister">Minister</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Growth Level</label>
                                <select name="growth_level" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="new_believer">New Believer</option>
                                    <option value="growing">Growing</option>
                                    <option value="core">Core</option>
                                    <option value="pastor">Pastor</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">TECI Status</label>
                                <select name="teci_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="not_started">Not Started</option>
                                    <option value="100_level">100 Level</option>
                                    <option value="200_level">200 Level</option>
                                    <option value="300_level">300 Level</option>
                                    <option value="400_level">400 Level</option>
                                    <option value="500_level">500 Level</option>
                                    <option value="graduated">Graduated</option>
                                    <option value="paused">Paused</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Marital Status</label>
                                <select name="marital_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Status</option>
                                    <option value="single">Single</option>
                                    <option value="married">Married</option>
                                    <option value="divorced">Divorced</option>
                                    <option value="separated">Separated</option>
                                    <option value="widowed">Widowed</option>
                                    <option value="in_a_relationship">In A Relationship</option>
                                    <option value="engaged">Engaged</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Occupation</label>
                                <input type="text" name="occupation" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nearest Bus Stop</label>
                                <input type="text" name="nearest_bus_stop" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Anniversary</label>
                                <input type="date" name="anniversary" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date Joined</label>
                                <input type="date" name="date_joined" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Leadership Trainings</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="leadership_trainings[]" value="ELP" class="mr-2">
                                        <span class="text-sm">ELP (Emerging Leaders Program)</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="leadership_trainings[]" value="MLCC" class="mr-2">
                                        <span class="text-sm">MLCC (Ministry Leadership Core Course)</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="leadership_trainings[]" value="MLCP Basic" class="mr-2">
                                        <span class="text-sm">MLCP Basic</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="leadership_trainings[]" value="MLCP Advanced" class="mr-2">
                                        <span class="text-sm">MLCP Advanced</span>
                                    </label>
                                </div>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
                                <select name="branch_id" id="modalBranchSelect" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Branch</option>
                                </select>
                                <p id="branchNote" class="text-sm text-blue-600 mt-1 hidden"></p>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeMemberModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Save Member
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let currentFilters = {};
        let branches = [];
        const isSuperAdmin = {{ $isSuperAdmin ? 'true' : 'false' }};

        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, starting initialization');
            console.log('isSuperAdmin:', isSuperAdmin);
            loadMembers();
            loadBranches(); // Always load branches for member creation
            setupEventListeners();
            
            // Make functions globally accessible
            window.openAddMemberModal = openAddMemberModal;
            window.closeMemberModal = closeMemberModal;
            window.editMember = editMember;
            window.deleteMember = deleteMember;
            window.viewMember = viewMember;
            window.changePage = changePage;
            window.clearFilters = clearFilters;
            
            console.log('Functions made global, openAddMemberModal exists:', typeof window.openAddMemberModal);
        });

        function setupEventListeners() {
            // Search input with debounce
            let searchTimeout;
            document.getElementById('searchInput').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentPage = 1;
                    loadMembers();
                }, 300);
            });

            // Filter changes
            ['statusFilter', 'growthLevelFilter', 'branchFilter'].forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('change', function() {
                        currentPage = 1;
                        loadMembers();
                    });
                }
            });

            // Form submission
            document.getElementById('memberForm').addEventListener('submit', handleFormSubmit);
        }

        async function loadBranches() {
            try {
                const response = await fetch('/api/branches?per_page=1000', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                });

                if (response.ok) {
                    const data = await response.json();
                    console.log('Branches API response:', data); // Debug log
                    
                    // Handle paginated response structure
                    if (data.success && data.data) {
                        branches = data.data.data || data.data;
                    } else {
                        branches = data;
                    }
                    
                    console.log('Extracted branches:', branches); // Debug log
                    
                    // Ensure branches is an array
                    if (!Array.isArray(branches)) {
                        console.error('Branches data is not an array:', branches);
                        branches = [];
                        return;
                    }
                    
                    // Populate branch filter (only for super admins)
                    const branchFilter = document.getElementById('branchFilter');
                    if (branchFilter && isSuperAdmin) {
                        branchFilter.innerHTML = '<option value="">All Branches</option>';
                        branches.forEach(branch => {
                            branchFilter.innerHTML += `<option value="${branch.id}">${branch.name}</option>`;
                        });
                    }

                    // Populate modal branch select (always available for member creation)
                    const modalBranchSelect = document.getElementById('modalBranchSelect');
                    if (modalBranchSelect) {
                        if (isSuperAdmin) {
                            // Super admins can select any branch
                            modalBranchSelect.innerHTML = '<option value="">Select Branch</option>';
                            branches.forEach(branch => {
                                modalBranchSelect.innerHTML += `<option value="${branch.id}">${branch.name}</option>`;
                            });
                        } else {
                            // Branch pastors: show all branches but backend will enforce their branch
                            // This provides better UX - they can see all branches but can only assign to their own
                            modalBranchSelect.innerHTML = '<option value="">Select Branch</option>';
                            branches.forEach(branch => {
                                modalBranchSelect.innerHTML += `<option value="${branch.id}">${branch.name}</option>`;
                            });
                            
                            // Add a note for branch pastors
                            const branchNote = document.getElementById('branchNote');
                            if (branchNote) {
                                branchNote.textContent = 'Note: Members will be automatically assigned to your branch.';
                                branchNote.classList.remove('hidden');
                            }
                        }
                    }
                } else {
                    const errorData = await response.json().catch(() => ({ message: 'Unknown error' }));
                    console.error('Failed to load branches:', response.status, errorData);
                    showNotification('Failed to load branches: ' + (errorData.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Error loading branches:', error);
                showNotification('Error loading branches: ' + error.message, 'error');
            }
        }

        async function loadMembers() {
            console.log('loadMembers called');
            showLoading(true);
            
            try {
                const params = new URLSearchParams({
                    page: currentPage,
                    per_page: 15
                });

                // Add search
                const search = document.getElementById('searchInput').value;
                if (search) params.append('search', search);

                // Add filters
                const status = document.getElementById('statusFilter').value;
                if (status) params.append('member_status', status);

                const growthLevel = document.getElementById('growthLevelFilter').value;
                if (growthLevel) params.append('growth_level', growthLevel);

                if (isSuperAdmin) {
                    const branch = document.getElementById('branchFilter').value;
                    if (branch) params.append('branch_id', branch);
                }

                const url = `/api/members?${params}`;
                console.log('Making API call to:', url);

                const response = await fetch(url, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                });

                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);

                if (response.ok) {
                    const data = await response.json();
                    console.log('Full API response:', data);
                    console.log('data.data:', data.data);
                    console.log('data.data.data:', data.data.data);
                    console.log('Members array length:', data.data.data?.length);
                    
                    // Pass the actual members array to displayMembers
                    const membersData = {
                        data: data.data.data || []
                    };
                    console.log('Passing to displayMembers:', membersData);
                    displayMembers(membersData);
                    updatePagination(data.data);
                    document.getElementById('totalMembers').textContent = data.data.total || 0;
                } else {
                    const errorText = await response.text();
                    console.error('API error response:', errorText);
                    throw new Error('Failed to load members');
                }
            } catch (error) {
                console.error('Error loading members:', error);
                showNotification('Error loading members', 'error');
            } finally {
                showLoading(false);
            }
        }

        function displayMembers(data) {
            console.log('displayMembers called with:', data);
            console.log('data.data:', data.data);
            console.log('data.data.length:', data.data?.length);
            
            const container = document.getElementById('membersContainer');
            
            if (!data.data || data.data.length === 0) {
                console.log('No members to display - showing empty message');
                container.innerHTML = `
                    <div class="text-center py-8">
                        <p class="text-gray-500">No members found</p>
                    </div>
                `;
                return;
            }

            console.log('Displaying', data.data.length, 'members');

            container.innerHTML = `
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Growth Level</th>
                                ${isSuperAdmin ? '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>' : ''}
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${data.data.map(member => `
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-gray-700">${member.name.charAt(0)}</span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">${member.name}</div>
                                                <div class="text-sm text-gray-500">Age: ${member.age || 'N/A'}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${member.email || 'N/A'}</div>
                                        <div class="text-sm text-gray-500">${member.phone || 'N/A'}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(member.member_status)}">
                                            ${member.member_status}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${member.growth_level.replace('_', ' ')}
                                    </td>
                                    ${isSuperAdmin ? `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${member.branch?.name || 'N/A'}</td>` : ''}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="viewMember(${member.id})" class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                        <button onclick="editMember(${member.id})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                        <button onclick="deleteMember(${member.id})" class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }

        function getStatusColor(status) {
            const colors = {
                'visitor': 'bg-gray-100 text-gray-800',
                'member': 'bg-green-100 text-green-800',
                'volunteer': 'bg-blue-100 text-blue-800',
                'leader': 'bg-purple-100 text-purple-800',
                'minister': 'bg-yellow-100 text-yellow-800'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        }

        function updatePagination(data) {
            const container = document.getElementById('paginationContainer');
            
            if (data.last_page <= 1) {
                container.innerHTML = '';
                return;
            }

            let pagination = '<div class="flex justify-between items-center">';
            
            // Previous button
            if (data.current_page > 1) {
                pagination += `<button onclick="changePage(${data.current_page - 1})" class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">Previous</button>`;
            } else {
                pagination += '<span class="px-3 py-2 text-sm text-gray-400">Previous</span>';
            }

            // Page info
            pagination += `<span class="text-sm text-gray-700">Page ${data.current_page} of ${data.last_page}</span>`;

            // Next button
            if (data.current_page < data.last_page) {
                pagination += `<button onclick="changePage(${data.current_page + 1})" class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">Next</button>`;
            } else {
                pagination += '<span class="px-3 py-2 text-sm text-gray-400">Next</span>';
            }

            pagination += '</div>';
            container.innerHTML = pagination;
        }

        function changePage(page) {
            currentPage = page;
            loadMembers();
        }

        function clearFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('growthLevelFilter').value = '';
            if (document.getElementById('branchFilter')) {
                document.getElementById('branchFilter').value = '';
            }
            currentPage = 1;
            loadMembers();
        }

        function openAddMemberModal() {
            console.log('openAddMemberModal called');
            document.getElementById('modalTitle').textContent = 'Add Member';
            document.getElementById('memberForm').reset();
            document.getElementById('memberForm').removeAttribute('data-member-id');
            document.getElementById('memberModal').classList.remove('hidden');
        }

        function closeMemberModal() {
            document.getElementById('memberModal').classList.add('hidden');
        }

        async function handleFormSubmit(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            const memberId = e.target.getAttribute('data-member-id');
            
            // Handle leadership trainings checkboxes
            const leadershipTrainings = [];
            const checkboxes = e.target.querySelectorAll('input[name="leadership_trainings[]"]:checked');
            checkboxes.forEach(checkbox => {
                leadershipTrainings.push(checkbox.value);
            });
            data.leadership_trainings = leadershipTrainings;
            
            try {
                const url = memberId ? `/api/members/${memberId}` : '/api/members';
                const method = memberId ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(data)
                });

                if (response.ok) {
                    const result = await response.json();
                    showNotification(result.message || 'Member saved successfully', 'success');
                    closeMemberModal();
                    loadMembers();
                } else {
                    const error = await response.json();
                    throw new Error(error.message || 'Failed to save member');
                }
            } catch (error) {
                console.error('Error saving member:', error);
                showNotification(error.message || 'Error saving member', 'error');
            }
        }

        async function editMember(id) {
            try {
                const response = await fetch(`/api/members/${id}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                });

                if (response.ok) {
                    const result = await response.json();
                    const member = result.data;
                    
                    // Populate form
                    document.getElementById('modalTitle').textContent = 'Edit Member';
                    const form = document.getElementById('memberForm');
                    form.setAttribute('data-member-id', id);
                    
                    // Reset form first
                    form.reset();
                    
                    // Fill form fields
                    Object.keys(member).forEach(key => {
                        if (key === 'leadership_trainings' && Array.isArray(member[key])) {
                            // Handle leadership trainings checkboxes
                            member[key].forEach(training => {
                                const checkbox = form.querySelector(`input[name="leadership_trainings[]"][value="${training}"]`);
                                if (checkbox) {
                                    checkbox.checked = true;
                                }
                            });
                        } else {
                            const input = form.querySelector(`[name="${key}"]`);
                            if (input && member[key] !== null && member[key] !== undefined && member[key] !== '') {
                                // Special handling for date fields
                                if (input.type === 'date' && member[key]) {
                                    // Ensure date is in YYYY-MM-DD format
                                    const dateValue = typeof member[key] === 'string' && member[key].includes('T') 
                                        ? member[key].split('T')[0] 
                                        : member[key];
                                    input.value = dateValue;
                                } else {
                                    input.value = member[key];
                                }
                            }
                        }
                    });
                    
                    document.getElementById('memberModal').classList.remove('hidden');
                } else {
                    throw new Error('Failed to load member details');
                }
            } catch (error) {
                console.error('Error loading member:', error);
                showNotification('Error loading member details', 'error');
            }
        }

        async function deleteMember(id) {
            if (!confirm('Are you sure you want to delete this member?')) {
                return;
            }

            try {
                const response = await fetch(`/api/members/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                });

                if (response.ok) {
                    const result = await response.json();
                    showNotification(result.message || 'Member deleted successfully', 'success');
                    loadMembers();
                } else {
                    const error = await response.json();
                    throw new Error(error.message || 'Failed to delete member');
                }
            } catch (error) {
                console.error('Error deleting member:', error);
                showNotification(error.message || 'Error deleting member', 'error');
            }
        }

        function viewMember(id) {
            // For now, just edit - could be expanded to a read-only view
            editMember(id);
        }

        function showLoading(show) {
            const spinner = document.getElementById('loadingSpinner');
            const container = document.getElementById('membersContainer');
            
            if (show) {
                spinner.classList.remove('hidden');
                container.classList.add('hidden');
            } else {
                spinner.classList.add('hidden');
                container.classList.remove('hidden');
            }
        }

        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white' : 
                type === 'error' ? 'bg-red-500 text-white' : 
                'bg-blue-500 text-white'
            }`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
</x-app-layout> 
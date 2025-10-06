<x-sidebar-layout title="Members Management">
    <div class="flex justify-between items-center mb-6">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Members') }}
        </h2>
        <div class="flex space-x-3">
            <a href="{{ route('pastor.import-export') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                Import Members
            </a>
            <button onclick="openAddMemberModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                Add Member
            </button>
        </div>
    </div>

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

            <!-- Member Statistics -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Member Statistics</h3>
                        <button onclick="toggleStatistics()" id="toggleStatsBtn" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors duration-200">
                            <svg id="statsChevron" class="w-4 h-4 mr-2 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                            <span id="toggleStatsText">Show Statistics</span>
                        </button>
                    </div>
                    <div id="statisticsContent" class="hidden transition-all duration-300 ease-in-out">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <!-- Total Members -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-blue-600">Total Members</p>
                                    <p class="text-2xl font-bold text-blue-900" id="totalMembersStat">-</p>
                                </div>
                            </div>
                        </div>

                        <!-- Visitors -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-yellow-600">Visitors</p>
                                    <p class="text-2xl font-bold text-yellow-900" id="visitorCount">-</p>
                                </div>
                            </div>
                        </div>

                        <!-- Leaders -->
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-purple-600">Leaders</p>
                                    <p class="text-2xl font-bold text-purple-900" id="leaderCount">-</p>
                                </div>
                            </div>
                        </div>

                        <!-- Volunteers -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-green-600">Volunteers</p>
                                    <p class="text-2xl font-bold text-green-900" id="volunteerCount">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TECI Status Breakdown -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-900 mb-3">TECI Status Breakdown</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3">
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-center">
                                <p class="text-xs text-gray-600">Not Started</p>
                                <p class="text-lg font-bold text-gray-900" id="teciNotStarted">-</p>
                            </div>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
                                <p class="text-xs text-blue-600">100 Level</p>
                                <p class="text-lg font-bold text-blue-900" id="teci100">-</p>
                            </div>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
                                <p class="text-xs text-blue-600">200 Level</p>
                                <p class="text-lg font-bold text-blue-900" id="teci200">-</p>
                            </div>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
                                <p class="text-xs text-blue-600">300 Level</p>
                                <p class="text-lg font-bold text-blue-900" id="teci300">-</p>
                            </div>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
                                <p class="text-xs text-blue-600">400 Level</p>
                                <p class="text-lg font-bold text-blue-900" id="teci400">-</p>
                            </div>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
                                <p class="text-xs text-blue-600">500 Level</p>
                                <p class="text-lg font-bold text-blue-900" id="teci500">-</p>
                            </div>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-center">
                                <p class="text-xs text-green-600">Graduated</p>
                                <p class="text-lg font-bold text-green-900" id="teciGraduated">-</p>
                            </div>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-center">
                                <p class="text-xs text-yellow-600">Paused</p>
                                <p class="text-lg font-bold text-yellow-900" id="teciPaused">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Leadership Training Breakdown -->
                    <div>
                        <h4 class="text-md font-medium text-gray-900 mb-3">Leadership Training Breakdown</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 text-center">
                                <p class="text-xs text-purple-600">ELP</p>
                                <p class="text-lg font-bold text-purple-900" id="trainingELP">-</p>
                                <p class="text-xs text-gray-500">Emerging Leaders</p>
                            </div>
                            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-3 text-center">
                                <p class="text-xs text-indigo-600">MLCC</p>
                                <p class="text-lg font-bold text-indigo-900" id="trainingMLCC">-</p>
                                <p class="text-xs text-gray-500">Ministry Leadership</p>
                            </div>
                            <div class="bg-pink-50 border border-pink-200 rounded-lg p-3 text-center">
                                <p class="text-xs text-pink-600">MLCP Basic</p>
                                <p class="text-lg font-bold text-pink-900" id="trainingMLCPBasic">-</p>
                                <p class="text-xs text-gray-500">Ministry Leadership</p>
                            </div>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-center">
                                <p class="text-xs text-red-600">MLCP Advanced</p>
                                <p class="text-lg font-bold text-red-900" id="trainingMLCPAdvanced">-</p>
                                <p class="text-xs text-gray-500">Advanced Leadership</p>
                            </div>
                        </div>
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
                        <!-- Member Form Component -->
                        <x-member-form 
                            context="admin" 
                            :show-required="true" 
                            :show-optional="true" />
                        
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
            // Don't load statistics by default - they'll be loaded when the user clicks "Show Statistics"
            setupEventListeners();
            
            // Make functions globally accessible
            window.openAddMemberModal = openAddMemberModal;
            window.closeMemberModal = closeMemberModal;
            window.editMember = editMember;
            window.deleteMember = deleteMember;
            window.viewMember = viewMember;
            window.changePage = changePage;
            window.clearFilters = clearFilters;
            window.toggleStatistics = toggleStatistics;
            
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

        async function loadMemberStatistics() {
            console.log('Loading member statistics...');
            try {
                const response = await fetch('/api/members/statistics', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                });
                
                console.log('Statistics response status:', response.status);
                
                if (response.ok) {
                    const data = await response.json();
                    console.log('Statistics data:', data);
                    if (data.success) {
                        updateStatisticsDisplay(data.data);
                    } else {
                        console.error('Failed to load statistics:', data.message);
                    }
                } else {
                    console.error('Failed to load statistics:', response.status, response.statusText);
                }
            } catch (error) {
                console.error('Error loading statistics:', error);
            }
        }

        function updateStatisticsDisplay(stats) {
            console.log('Updating statistics display with data:', stats);
            
            // Update main statistics
            document.getElementById('totalMembersStat').textContent = stats.total_members || 0;
            document.getElementById('visitorCount').textContent = stats.visitor_count || 0;
            document.getElementById('leaderCount').textContent = stats.leader_count || 0;
            document.getElementById('volunteerCount').textContent = stats.volunteer_count || 0;

            // Update TECI status breakdown
            const teciStats = stats.teci_stats || {};
            document.getElementById('teciNotStarted').textContent = teciStats.not_started || 0;
            document.getElementById('teci100').textContent = teciStats['100_level'] || 0;
            document.getElementById('teci200').textContent = teciStats['200_level'] || 0;
            document.getElementById('teci300').textContent = teciStats['300_level'] || 0;
            document.getElementById('teci400').textContent = teciStats['400_level'] || 0;
            document.getElementById('teci500').textContent = teciStats['500_level'] || 0;
            document.getElementById('teciGraduated').textContent = teciStats.graduated || 0;
            document.getElementById('teciPaused').textContent = teciStats.paused || 0;

            // Update leadership training breakdown
            const trainingStats = stats.training_stats || {};
            document.getElementById('trainingELP').textContent = trainingStats.ELP || 0;
            document.getElementById('trainingMLCC').textContent = trainingStats.MLCC || 0;
            document.getElementById('trainingMLCPBasic').textContent = trainingStats['MLCP Basic'] || 0;
            document.getElementById('trainingMLCPAdvanced').textContent = trainingStats['MLCP Advanced'] || 0;
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
                                        <button onclick="deleteMember(${member.id})" class="text-red-600 hover:text-red-900 mr-3">Delete</button>
                                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isBranchPastor())
                                        <form method="POST" action="{{ route('impersonate.start', ['user' => 0]) }}" class="inline" onsubmit="return impersonateSubmit(this, ${member.user_id});">
                                            @csrf
                                            <button type="submit" class="text-gray-600 hover:text-gray-900">Impersonate</button>
                                        </form>
                                        @endif
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
                        } else if (key === 'name') {
                            // Handle the old 'name' field by splitting into first_name and surname
                            const nameParts = member[key] ? member[key].split(' ') : ['', ''];
                            const firstNameInput = form.querySelector('[name="first_name"]');
                            const surnameInput = form.querySelector('[name="surname"]');
                            
                            if (firstNameInput && nameParts[0]) {
                                firstNameInput.value = nameParts[0];
                            }
                            if (surnameInput && nameParts.length > 1) {
                                surnameInput.value = nameParts.slice(1).join(' ');
                            }
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

        function toggleStatistics() {
            const content = document.getElementById('statisticsContent');
            const chevron = document.getElementById('statsChevron');
            const text = document.getElementById('toggleStatsText');
            
            if (content.classList.contains('hidden')) {
                // Show statistics
                content.classList.remove('hidden');
                chevron.style.transform = 'rotate(180deg)';
                text.textContent = 'Hide Statistics';
                
                // Load statistics if not already loaded
                loadMemberStatistics();
            } else {
                // Hide statistics
                content.classList.add('hidden');
                chevron.style.transform = 'rotate(0deg)';
                text.textContent = 'Show Statistics';
            }
        }

        function impersonateSubmit(formEl, userId) {
            if (!userId) { alert('No user account linked to this member.'); return false; }
            formEl.action = formEl.action.replace('/0', '/' + userId).replace('__USER_ID__', userId);
            return true;
        }
    </script>
</x-sidebar-layout> 
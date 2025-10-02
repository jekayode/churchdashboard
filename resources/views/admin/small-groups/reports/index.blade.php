<x-sidebar-layout title="Small Group Meeting Reports">
    <div class="flex justify-between items-center mb-6">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Small Group Meeting Reports') }}
        </h2>
        <div class="flex gap-2">
            <button onclick="openReportModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                Submit Report
            </button>
            <button onclick="showStatistics()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                View Statistics
            </button>
        </div>
    </div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters and Search -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text" id="searchInput" placeholder="Search reports..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                            <select id="dateFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="this_week" selected>This Week</option>
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="this_quarter">This Quarter</option>
                                <option value="this_year">This Year</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Statuses</option>
                                <option value="submitted">Submitted</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Small Group</label>
                            <select id="groupFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Groups</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                            <select id="branchFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Branches</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Custom Date Range (Hidden by default) -->
                    <div id="customDateRange" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 hidden">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                            <input type="date" id="fromDate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                            <input type="date" id="toDate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <button onclick="clearFilters()" class="text-gray-600 hover:text-gray-800">
                            Clear Filters
                        </button>
                        <div class="text-sm text-gray-600">
                            Total: <span id="totalReports">0</span> reports
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-2xl font-bold text-blue-600" id="totalAttendance">0</div>
                    <div class="text-gray-600">Total Attendance</div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-2xl font-bold text-green-600" id="totalGuests">0</div>
                    <div class="text-gray-600">Total Guests</div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-2xl font-bold text-purple-600" id="totalConverts">0</div>
                    <div class="text-gray-600">Total Converts</div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-2xl font-bold text-orange-600" id="activeGroups">0</div>
                    <div class="text-gray-600">Active Groups</div>
                </div>
            </div>

            <!-- Reports List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div id="loadingSpinner" class="text-center py-8 hidden">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-gray-600">Loading reports...</p>
                    </div>
                    
                    <div id="reportsContainer">
                        <!-- Reports will be loaded here -->
                    </div>
                    
                    <div id="paginationContainer" class="mt-6">
                        <!-- Pagination will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Modal -->
    <div id="reportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 id="reportModalTitle" class="text-lg font-medium">Submit Meeting Report</h3>
                        <button onclick="closeReportModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <form id="reportForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Small Group *</label>
                                <select name="small_group_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Small Group</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Meeting Date *</label>
                                <input type="date" name="meeting_date" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Meeting Time</label>
                                <input type="time" name="meeting_time" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Male Attendance *</label>
                                <input type="number" name="male_attendance" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Female Attendance *</label>
                                <input type="number" name="female_attendance" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Children Attendance</label>
                                <input type="number" name="children_attendance" min="0" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Guests</label>
                                <input type="number" name="guests_count" min="0" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">First Time Visitors</label>
                                <input type="number" name="first_time_visitors" min="0" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Converts</label>
                                <input type="number" name="converts_count" min="0" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Meeting Topic</label>
                                <input type="text" name="meeting_topic" placeholder="e.g., Prayer and Fasting, Bible Study" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea name="notes" rows="3" placeholder="Additional notes about the meeting..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                        </div>
                        
                        <div class="flex justify-end gap-2 mt-6">
                            <button type="button" onclick="closeReportModal()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Submit Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Modal -->
    <div id="statisticsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Meeting Statistics & Analytics</h3>
                        <button onclick="closeStatisticsModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div id="statisticsContent">
                        <!-- Statistics content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>

    <script>
        let currentPage = 1;
        let currentFilters = {};
        let editingReportId = null;

        document.addEventListener('DOMContentLoaded', function() {
            console.log('Admin small groups reports page loaded');
            console.log('User authenticated:', {{ auth()->check() ? 'true' : 'false' }});
            console.log('User ID:', {{ auth()->id() ?? 'null' }});
            console.log('Is Super Admin:', {{ auth()->user()?->isSuperAdmin() ? 'true' : 'false' }});
            
            // Test basic API connectivity first
            testApiConnectivity();
            
            // Initialize currentFilters with default values
            currentFilters = {
                date_filter: document.getElementById('dateFilter').value || 'this_week',
                status: document.getElementById('statusFilter').value || '',
                small_group_id: document.getElementById('groupFilter').value || '',
            };
            
            const branchFilter = document.getElementById('branchFilter');
            if (branchFilter) {
                currentFilters.branch_id = branchFilter.value || '';
            }
            
            // Remove empty filters
            Object.keys(currentFilters).forEach(key => {
                if (!currentFilters[key]) {
                    delete currentFilters[key];
                }
            });
            
            loadReports();
            loadSummaryStatistics();
            loadSmallGroups();
            loadBranches();
            
            // Setup filter change handlers
            ['searchInput', 'dateFilter', 'statusFilter', 'groupFilter', 'branchFilter'].forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('change', handleFilterChange);
                    if (id === 'searchInput') {
                        element.addEventListener('input', debounce(handleFilterChange, 300));
                    }
                }
            });
            
            // Setup date filter change handler
            document.getElementById('dateFilter').addEventListener('change', function() {
                const customDateRange = document.getElementById('customDateRange');
                if (this.value === 'custom') {
                    customDateRange.classList.remove('hidden');
                } else {
                    customDateRange.classList.add('hidden');
                }
                handleFilterChange();
            });
            
            // Setup custom date change handlers
            ['fromDate', 'toDate'].forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('change', handleFilterChange);
                }
            });
            
            // Setup form submission
            document.getElementById('reportForm').addEventListener('submit', handleReportSubmit);
        });

        function testApiConnectivity() {
            console.log('Testing API connectivity...');
            fetch('/api/user', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('API test response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('API test successful, user data:', data);
            })
            .catch(error => {
                console.error('API test failed:', error);
            });
        }

        function loadReports(page = 1) {
            currentPage = page;
            showLoading(true);
            
            const params = new URLSearchParams({
                page: page,
                ...currentFilters
            });
            
            console.log('Admin loading reports with params:', params.toString());
            console.log('Current filters:', currentFilters);
            
            fetch(`/api/small-group-reports?${params}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(response => {
                    console.log('API response status:', response.status);
                    console.log('API response ok:', response.ok);
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('API error response:', text);
                            throw new Error(`HTTP ${response.status}: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('API response data:', data);
                    if (data.success) {
                        displayReports(data.data);
                        displayPagination(data.pagination);
                        document.getElementById('totalReports').textContent = data.pagination.total;
                    } else {
                        console.error('API returned success=false:', data);
                        showError('Failed to load reports: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error loading reports:', error);
                    console.error('Full error details:', error);
                    showError('Failed to load reports: ' + error.message);
                })
                .finally(() => {
                    showLoading(false);
                });
        }

        function loadSummaryStatistics() {
            const params = new URLSearchParams(currentFilters);
            console.log('Loading statistics with params:', params.toString());
            console.log('Current filters object:', currentFilters);
            
            fetch(`/api/small-group-reports/statistics?${params}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(response => {
                    console.log('Statistics API response status:', response.status);
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('Statistics API error response:', text);
                            throw new Error(`HTTP ${response.status}: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Statistics API response data:', data);
                    if (data.success) {
                        const stats = data.data;
                        console.log('Updating statistics with:', {
                            total_attendance: stats.total_attendance,
                            total_guests: stats.total_guests,
                            total_converts: stats.total_converts,
                            active_groups: stats.active_groups
                        });
                        
                        document.getElementById('totalAttendance').textContent = stats.total_attendance || 0;
                        document.getElementById('totalGuests').textContent = stats.total_guests || 0;
                        document.getElementById('totalConverts').textContent = stats.total_converts || 0;
                        document.getElementById('activeGroups').textContent = stats.active_groups || 0;
                        console.log('Statistics updated successfully');
                    } else {
                        console.error('Statistics API returned success=false:', data);
                    }
                })
                .catch(error => {
                    console.error('Error loading statistics:', error);
                });
        }

        function loadSmallGroups() {
            fetch('/api/small-groups', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const groupSelect = document.querySelector('select[name="small_group_id"]');
                        const groupFilter = document.getElementById('groupFilter');
                        
                        // Clear existing options
                        groupSelect.innerHTML = '<option value="">Select Small Group</option>';
                        groupFilter.innerHTML = '<option value="">All Groups</option>';
                        
                        data.data.forEach(group => {
                            groupSelect.innerHTML += `<option value="${group.id}">${group.name}</option>`;
                            groupFilter.innerHTML += `<option value="${group.id}">${group.name}</option>`;
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading groups:', error);
                });
        }

        function loadBranches() {
            console.log('Loading branches...');
            fetch('/api/branches', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(response => {
                    console.log('Branches API response status:', response.status);
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('Branches API error response:', text);
                            throw new Error(`HTTP ${response.status}: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Branches API response data:', data);
                    if (data.success) {
                        const branchFilter = document.getElementById('branchFilter');
                        
                        // Clear existing options (keep "All Branches")
                        branchFilter.innerHTML = '<option value="">All Branches</option>';
                        
                        // Add branch options
                        data.data.forEach(branch => {
                            branchFilter.innerHTML += `<option value="${branch.id}">${branch.name}</option>`;
                        });
                        console.log('Branches loaded successfully, count:', data.data.length);
                    } else {
                        console.error('Branches API returned success=false:', data);
                    }
                })
                .catch(error => {
                    console.error('Error loading branches:', error);
                });
        }

        function displayReports(reports) {
            const container = document.getElementById('reportsContainer');
            
            if (reports.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <div class="text-gray-500 text-lg">No reports found</div>
                        <div class="text-gray-400 text-sm mt-2">Try adjusting your filters or submit a new report</div>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = reports.map(report => `
                <div class="border-b border-gray-200 py-4 last:border-b-0">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="font-medium text-gray-900">${report.small_group?.name || 'Unknown Group'}</h3>
                                ${report.small_group?.branch?.name ? `<span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">${report.small_group.branch.name}</span>` : ''}
                                <span class="px-2 py-1 text-xs font-medium rounded-full ${getStatusBadgeClass(report.status)}">
                                    ${report.status.charAt(0).toUpperCase() + report.status.slice(1)}
                                </span>
                            </div>
                            <div class="text-sm text-gray-600 mb-2">
                                <span class="font-medium">Date:</span> ${formatDate(report.meeting_date)}
                                ${report.meeting_time ? ` at ${formatTime(report.meeting_time)}` : ''}
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500">Total:</span>
                                    <span class="font-medium">${report.total_attendance}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Guests:</span>
                                    <span class="font-medium">${report.first_time_guests}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Converts:</span>
                                    <span class="font-medium">${report.converts}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Submitted:</span>
                                    <span class="font-medium">${formatDate(report.created_at)}</span>
                                </div>
                            </div>
                            ${report.meeting_notes ? `
                                <div class="text-sm text-gray-600 mt-2">
                                    <span class="font-medium">Notes:</span> ${report.meeting_notes}
                                </div>
                            ` : ''}
                        </div>
                        <div class="flex gap-2 ml-4">
                            <button onclick="viewReport(${report.id})" class="text-blue-600 hover:text-blue-800 text-sm">
                                View
                            </button>
                            ${canEditReport(report) ? `
                                <button onclick="editReport(${report.id})" class="text-green-600 hover:text-green-800 text-sm">
                                    Edit
                                </button>
                            ` : ''}
                            ${canApproveReport(report) ? `
                                <button onclick="approveReport(${report.id})" class="text-green-600 hover:text-green-800 text-sm">
                                    Approve
                                </button>
                                <button onclick="rejectReport(${report.id})" class="text-red-600 hover:text-red-800 text-sm">
                                    Reject
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function displayPagination(pagination) {
            const container = document.getElementById('paginationContainer');
            
            if (pagination.last_page <= 1) {
                container.innerHTML = '';
                return;
            }
            
            let paginationHTML = '<div class="flex justify-center items-center gap-2">';
            
            // Previous button
            if (pagination.current_page > 1) {
                paginationHTML += `<button onclick="loadReports(${pagination.current_page - 1})" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800">Previous</button>`;
            }
            
            // Page numbers
            for (let i = Math.max(1, pagination.current_page - 2); i <= Math.min(pagination.last_page, pagination.current_page + 2); i++) {
                const isActive = i === pagination.current_page;
                paginationHTML += `
                    <button onclick="loadReports(${i})" class="px-3 py-2 text-sm ${isActive ? 'bg-blue-600 text-white' : 'text-gray-600 hover:text-gray-800'} rounded">
                        ${i}
                    </button>
                `;
            }
            
            // Next button
            if (pagination.current_page < pagination.last_page) {
                paginationHTML += `<button onclick="loadReports(${pagination.current_page + 1})" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800">Next</button>`;
            }
            
            paginationHTML += '</div>';
            container.innerHTML = paginationHTML;
        }

        function handleFilterChange() {
            currentFilters = {
                search: document.getElementById('searchInput').value,
                date_filter: document.getElementById('dateFilter').value,
                status: document.getElementById('statusFilter').value,
                small_group_id: document.getElementById('groupFilter').value,
            };
            
            const branchFilter = document.getElementById('branchFilter');
            if (branchFilter) {
                currentFilters.branch_id = branchFilter.value;
            }
            
            if (currentFilters.date_filter === 'custom') {
                currentFilters.from_date = document.getElementById('fromDate').value;
                currentFilters.to_date = document.getElementById('toDate').value;
            }
            
            // Remove empty filters
            Object.keys(currentFilters).forEach(key => {
                if (!currentFilters[key]) {
                    delete currentFilters[key];
                }
            });
            
            currentPage = 1;
            loadReports();
            loadSummaryStatistics();
        }

        function clearFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('dateFilter').value = 'this_week';
            document.getElementById('statusFilter').value = '';
            document.getElementById('groupFilter').value = '';
            document.getElementById('fromDate').value = '';
            document.getElementById('toDate').value = '';
            document.getElementById('customDateRange').classList.add('hidden');
            
            const branchFilter = document.getElementById('branchFilter');
            if (branchFilter) {
                branchFilter.value = '';
            }
            
            // Reset currentFilters with default values
            currentFilters = {
                date_filter: 'this_week'
            };
            
            loadReports();
            loadSummaryStatistics();
        }

        function openReportModal() {
            editingReportId = null;
            document.getElementById('reportModalTitle').textContent = 'Submit Meeting Report';
            document.getElementById('reportForm').reset();
            document.getElementById('reportModal').classList.remove('hidden');
        }

        function closeReportModal() {
            document.getElementById('reportModal').classList.add('hidden');
        }

        function handleReportSubmit(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const rawData = Object.fromEntries(formData.entries());
            
            // Map form fields to database fields
            const data = {
                small_group_id: rawData.small_group_id,
                meeting_date: rawData.meeting_date,
                meeting_time: rawData.meeting_time || null,
                male_attendance: parseInt(rawData.male_attendance) || 0,
                female_attendance: parseInt(rawData.female_attendance) || 0,
                children_attendance: parseInt(rawData.children_attendance) || 0,
                first_time_guests: parseInt(rawData.guests_count) || parseInt(rawData.first_time_visitors) || 0,
                converts: parseInt(rawData.converts_count) || 0,
                meeting_notes: rawData.notes || rawData.meeting_topic || null,
                status: 'submitted'
            };
            
            const url = editingReportId 
                ? `/api/small-group-reports/${editingReportId}`
                : '/api/small-group-reports';
            
            const method = editingReportId ? 'PUT' : 'POST';
            
            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(editingReportId ? 'Report updated successfully' : 'Report submitted successfully');
                    closeReportModal();
                    loadReports();
                    loadSummaryStatistics();
                } else {
                    showError(data.message || 'Failed to save report');
                }
            })
            .catch(error => {
                console.error('Error saving report:', error);
                showError('Failed to save report');
            });
        }

        function editReport(reportId) {
            fetch(`/api/small-group-reports/${reportId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const report = data.data;
                        editingReportId = reportId;
                        
                        // Populate form
                        const form = document.getElementById('reportForm');
                        form.querySelector('[name="small_group_id"]').value = report.small_group_id;
                        form.querySelector('[name="meeting_date"]').value = report.meeting_date.split('T')[0];
                        form.querySelector('[name="meeting_time"]').value = report.meeting_time || '';
                        form.querySelector('[name="male_attendance"]').value = report.male_attendance;
                        form.querySelector('[name="female_attendance"]').value = report.female_attendance;
                        form.querySelector('[name="children_attendance"]').value = report.children_attendance;
                        form.querySelector('[name="guests_count"]').value = report.first_time_guests || 0;
                        form.querySelector('[name="first_time_visitors"]').value = report.first_time_guests || 0;
                        form.querySelector('[name="converts_count"]').value = report.converts || 0;
                        form.querySelector('[name="meeting_topic"]').value = report.meeting_notes || '';
                        form.querySelector('[name="notes"]').value = report.meeting_notes || '';
                        
                        document.getElementById('reportModalTitle').textContent = 'Edit Meeting Report';
                        document.getElementById('reportModal').classList.remove('hidden');
                    } else {
                        showError('Failed to load report details');
                    }
                })
                .catch(error => {
                    console.error('Error loading report:', error);
                    showError('Failed to load report details');
                });
        }

        function approveReport(reportId) {
            if (!confirm('Are you sure you want to approve this report?')) return;
            
            fetch(`/api/small-group-reports/${reportId}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Report approved successfully');
                    loadReports();
                } else {
                    showError(data.message || 'Failed to approve report');
                }
            })
            .catch(error => {
                console.error('Error approving report:', error);
                showError('Failed to approve report');
            });
        }

        function rejectReport(reportId) {
            const reason = prompt('Please provide a reason for rejection:');
            if (!reason) return;
            
            fetch(`/api/small-group-reports/${reportId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ reason: reason })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Report rejected');
                    loadReports();
                } else {
                    showError(data.message || 'Failed to reject report');
                }
            })
            .catch(error => {
                console.error('Error rejecting report:', error);
                showError('Failed to reject report');
            });
        }

        function viewReport(reportId) {
            // For now, just edit the report. In the future, could create a read-only view
            editReport(reportId);
        }

        function showStatistics() {
            document.getElementById('statisticsModal').classList.remove('hidden');
            loadStatistics();
        }

        function closeStatisticsModal() {
            document.getElementById('statisticsModal').classList.add('hidden');
        }

        function loadStatistics() {
            const params = new URLSearchParams(currentFilters);
            
            const options = {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            };
            
            Promise.all([
                fetch(`/api/small-group-reports/statistics?${params}`, options).then(r => r.json()),
                fetch(`/api/small-group-reports/trends?${params}`, options).then(r => r.json()),
                fetch(`/api/small-group-reports/comparison?${params}`, options).then(r => r.json())
            ])
            .then(([statsData, trendsData, comparisonData]) => {
                displayStatistics(statsData.data, trendsData.data, comparisonData.data);
            })
            .catch(error => {
                console.error('Error loading statistics:', error);
                showError('Failed to load statistics');
            });
        }

        function displayStatistics(stats, trends, comparison) {
            const content = document.getElementById('statisticsContent');
            
            content.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">${stats.total_attendance || 0}</div>
                        <div class="text-sm text-blue-800">Total Attendance</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">${stats.total_guests || 0}</div>
                        <div class="text-sm text-green-800">Total Guests</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">${stats.total_converts || 0}</div>
                        <div class="text-sm text-purple-800">Total Converts</div>
                    </div>
                    <div class="bg-orange-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-orange-600">${stats.active_groups || 0}</div>
                        <div class="text-sm text-orange-800">Active Groups</div>
                    </div>
                </div>
                
                ${comparison ? `
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h4 class="font-medium text-gray-900 mb-3">Period Comparison</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <div class="text-gray-600">Attendance Change</div>
                                <div class="font-medium ${comparison.attendance_change >= 0 ? 'text-green-600' : 'text-red-600'}">
                                    ${comparison.attendance_change >= 0 ? '+' : ''}${comparison.attendance_change}%
                                </div>
                            </div>
                            <div>
                                <div class="text-gray-600">Guests Change</div>
                                <div class="font-medium ${comparison.guests_change >= 0 ? 'text-green-600' : 'text-red-600'}">
                                    ${comparison.guests_change >= 0 ? '+' : ''}${comparison.guests_change}%
                                </div>
                            </div>
                            <div>
                                <div class="text-gray-600">Converts Change</div>
                                <div class="font-medium ${comparison.converts_change >= 0 ? 'text-green-600' : 'text-red-600'}">
                                    ${comparison.converts_change >= 0 ? '+' : ''}${comparison.converts_change}%
                                </div>
                            </div>
                        </div>
                    </div>
                ` : ''}
                
                ${stats.top_groups && stats.top_groups.length > 0 ? `
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-900 mb-3">Top Performing Groups</h4>
                        <div class="space-y-2">
                            ${stats.top_groups.map((group, index) => `
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <span class="font-medium">${index + 1}. ${group.small_group ? group.small_group.name : 'Unknown Group'}</span>
                                        <span class="text-sm text-gray-600 ml-2">(${group.small_group && group.small_group.branch ? group.small_group.branch.name : 'Unknown Branch'})</span>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        ${group.total_attendance} attendance, ${group.total_converts} converts
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
            `;
        }

        // Utility functions
        function getStatusBadgeClass(status) {
            const classes = {
                submitted: 'bg-yellow-100 text-yellow-800',
                approved: 'bg-green-100 text-green-800',
                rejected: 'bg-red-100 text-red-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        }

        function canEditReport(report) {
            // Logic to determine if current user can edit this report
            return report.status === 'submitted' || report.status === 'rejected';
        }

        function canApproveReport(report) {
            // Logic to determine if current user can approve this report
            // Both Super Admins and Branch Pastors can approve reports
            return report.status === 'submitted';
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString();
        }

        function formatTime(timeString) {
            if (!timeString) return '';
            return new Date(`2000-01-01 ${timeString}`).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        }

        function showLoading(show) {
            document.getElementById('loadingSpinner').classList.toggle('hidden', !show);
        }

        function showSuccess(message) {
            // You can implement a toast notification system here
            alert(message);
        }

        function showError(message) {
            // You can implement a toast notification system here
            alert(message);
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    </script>
</x-sidebar-layout> 
<x-sidebar-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Group Meeting Reports') }}
            </h2>
            <div class="flex gap-2">
                <button onclick="openReportModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Submit New Report
                </button>
                <a href="{{ route('member.groups') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                    Back to Groups
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="text-2xl font-bold text-blue-600" id="totalReports">0</div>
                        <div class="text-sm text-gray-600">Total Reports</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="text-2xl font-bold text-green-600" id="avgAttendance">0</div>
                        <div class="text-sm text-gray-600">Avg Attendance</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="text-2xl font-bold text-purple-600" id="totalGuests">0</div>
                        <div class="text-sm text-gray-600">Total Guests</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="text-2xl font-bold text-orange-600" id="totalConverts">0</div>
                        <div class="text-sm text-gray-600">Total Converts</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                            <select id="dateFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="all">All Time</option>
                                <option value="this_week">This Week</option>
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="this_quarter">This Quarter</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Reports</option>
                                <option value="submitted">Submitted</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Small Group</label>
                            <select id="groupFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All My Groups</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="customDateRange" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 hidden">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                            <input type="date" id="fromDate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                            <input type="date" id="toDate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reports List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Meeting Reports</h3>
                        <div class="text-sm text-gray-600">
                            <span id="reportCount">0</span> reports found
                        </div>
                    </div>
                    
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
                                <label class="block text-sm font-medium text-gray-700 mb-1">First Time Guests</label>
                                <input type="number" name="first_time_guests" min="0" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Converts</label>
                                <input type="number" name="converts" min="0" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Meeting Location</label>
                                <input type="text" name="meeting_location" placeholder="e.g., Church Hall, Online, Home" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Meeting Notes</label>
                                <textarea name="meeting_notes" rows="3" placeholder="What was discussed, prayer points, testimonies, etc." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prayer Requests</label>
                                <textarea name="prayer_requests" rows="2" placeholder="Prayer requests from the group..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Testimonies</label>
                                <textarea name="testimonies" rows="2" placeholder="Testimonies and praise reports..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
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

    <script>
        let currentPage = 1;
        let currentFilters = {};
        let editingReportId = null;

        document.addEventListener('DOMContentLoaded', function() {
            loadReports();
            loadSummaryStats();
            loadMyGroups();
            
            // Setup filter change handlers
            ['dateFilter', 'statusFilter', 'groupFilter'].forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('change', handleFilterChange);
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

        function handleFilterChange() {
            currentFilters = {
                date_filter: document.getElementById('dateFilter').value,
                status: document.getElementById('statusFilter').value,
                small_group_id: document.getElementById('groupFilter').value,
            };
            
            if (currentFilters.date_filter === 'custom') {
                currentFilters.from_date = document.getElementById('fromDate').value;
                currentFilters.to_date = document.getElementById('toDate').value;
            }
            
            currentPage = 1;
            loadReports();
            loadSummaryStats();
        }

        function loadReports(page = 1) {
            currentPage = page;
            showLoading(true);
            
            const params = new URLSearchParams({
                page: page,
                per_page: 10,
                ...currentFilters
            });
            
            fetch(`/api/small-group-reports?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayReports(data.data);
                        displayPagination(data.pagination);
                        document.getElementById('reportCount').textContent = data.pagination.total;
                    } else {
                        showError('Failed to load reports');
                    }
                })
                .catch(error => {
                    console.error('Error loading reports:', error);
                    showError('Failed to load reports');
                })
                .finally(() => {
                    showLoading(false);
                });
        }

        function loadSummaryStats() {
            const params = new URLSearchParams(currentFilters);
            
            fetch(`/api/small-group-reports/statistics?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const stats = data.data;
                        document.getElementById('totalReports').textContent = stats.total_reports || 0;
                        document.getElementById('avgAttendance').textContent = Math.round(stats.avg_attendance) || 0;
                        document.getElementById('totalGuests').textContent = stats.total_guests || 0;
                        document.getElementById('totalConverts').textContent = stats.total_converts || 0;
                    }
                })
                .catch(error => {
                    console.error('Error loading statistics:', error);
                });
        }

        function loadMyGroups() {
            fetch('/api/small-group-reports/my-groups')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const groupSelect = document.querySelector('select[name="small_group_id"]');
                        const groupFilter = document.getElementById('groupFilter');
                        
                        // Clear existing options
                        groupSelect.innerHTML = '<option value="">Select Small Group</option>';
                        groupFilter.innerHTML = '<option value="">All My Groups</option>';
                        
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

        function displayReports(reports) {
            const container = document.getElementById('reportsContainer');
            
            if (reports.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <div class="text-gray-500 text-lg">No reports found</div>
                        <div class="text-gray-400 text-sm mt-2">Submit your first meeting report to get started</div>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = reports.map(report => `
                <div class="border-b border-gray-200 py-4 last:border-b-0">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="font-medium text-gray-900">${report.small_group.name}</h3>
                                <span class="px-2 py-1 text-xs font-medium rounded-full ${getStatusBadgeClass(report.status)}">
                                    ${report.status.charAt(0).toUpperCase() + report.status.slice(1)}
                                </span>
                            </div>
                            <div class="text-sm text-gray-600 mb-2">
                                <span class="font-medium">Date:</span> ${formatDate(report.meeting_date)}
                                ${report.meeting_time ? ` at ${formatTime(report.meeting_time)}` : ''}
                                ${report.meeting_location ? ` - ${report.meeting_location}` : ''}
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500">Total:</span>
                                    <span class="font-medium">${report.total_attendance}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Adults:</span>
                                    <span class="font-medium">${report.male_attendance + report.female_attendance}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Guests:</span>
                                    <span class="font-medium">${report.first_time_guests}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Converts:</span>
                                    <span class="font-medium">${report.converts}</span>
                                </div>
                            </div>
                            ${report.meeting_notes ? `
                                <div class="text-sm text-gray-600 mt-2">
                                    <span class="font-medium">Notes:</span> ${report.meeting_notes.substring(0, 100)}${report.meeting_notes.length > 100 ? '...' : ''}
                                </div>
                            ` : ''}
                            ${report.rejection_reason ? `
                                <div class="text-sm text-red-600 mt-2 bg-red-50 p-2 rounded">
                                    <span class="font-medium">Rejection Reason:</span> ${report.rejection_reason}
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
                                <button onclick="deleteReport(${report.id})" class="text-red-600 hover:text-red-800 text-sm">
                                    Delete
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
            
            let paginationHTML = '<div class="flex justify-center gap-1">';
            
            // Previous button
            if (pagination.current_page > 1) {
                paginationHTML += `<button onclick="loadReports(${pagination.current_page - 1})" class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Previous</button>`;
            }
            
            // Page numbers
            for (let i = Math.max(1, pagination.current_page - 2); i <= Math.min(pagination.last_page, pagination.current_page + 2); i++) {
                if (i === pagination.current_page) {
                    paginationHTML += `<button class="px-3 py-2 text-sm bg-blue-600 text-white rounded-md">${i}</button>`;
                } else {
                    paginationHTML += `<button onclick="loadReports(${i})" class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50">${i}</button>`;
                }
            }
            
            // Next button
            if (pagination.current_page < pagination.last_page) {
                paginationHTML += `<button onclick="loadReports(${pagination.current_page + 1})" class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Next</button>`;
            }
            
            paginationHTML += '</div>';
            container.innerHTML = paginationHTML;
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
            const data = Object.fromEntries(formData.entries());
            
            // Convert numeric fields
            ['male_attendance', 'female_attendance', 'children_attendance', 'first_time_guests', 'converts'].forEach(field => {
                data[field] = parseInt(data[field]) || 0;
            });
            
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
                    loadSummaryStats();
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
            fetch(`/api/small-group-reports/${reportId}`)
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
                        form.querySelector('[name="meeting_location"]').value = report.meeting_location || '';
                        form.querySelector('[name="male_attendance"]').value = report.male_attendance;
                        form.querySelector('[name="female_attendance"]').value = report.female_attendance;
                        form.querySelector('[name="children_attendance"]').value = report.children_attendance;
                        form.querySelector('[name="first_time_guests"]').value = report.first_time_guests;
                        form.querySelector('[name="converts"]').value = report.converts;
                        form.querySelector('[name="meeting_notes"]').value = report.meeting_notes || '';
                        form.querySelector('[name="prayer_requests"]').value = report.prayer_requests || '';
                        form.querySelector('[name="testimonies"]').value = report.testimonies || '';
                        
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

        function viewReport(reportId) {
            // For now, just edit the report. In the future, could create a read-only view
            editReport(reportId);
        }

        function deleteReport(reportId) {
            if (!confirm('Are you sure you want to delete this report? This action cannot be undone.')) return;
            
            fetch(`/api/small-group-reports/${reportId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Report deleted successfully');
                    loadReports();
                    loadSummaryStats();
                } else {
                    showError(data.message || 'Failed to delete report');
                }
            })
            .catch(error => {
                console.error('Error deleting report:', error);
                showError('Failed to delete report');
            });
        }

        function canEditReport(report) {
            return report.status !== 'approved';
        }

        function getStatusBadgeClass(status) {
            switch (status) {
                case 'submitted':
                    return 'bg-yellow-100 text-yellow-800';
                case 'approved':
                    return 'bg-green-100 text-green-800';
                case 'rejected':
                    return 'bg-red-100 text-red-800';
                default:
                    return 'bg-gray-100 text-gray-800';
            }
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString();
        }

        function formatTime(timeString) {
            if (!timeString) return '';
            return new Date(`2000-01-01T${timeString}`).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        }

        function showLoading(show) {
            const spinner = document.getElementById('loadingSpinner');
            if (show) {
                spinner.classList.remove('hidden');
            } else {
                spinner.classList.add('hidden');
            }
        }

        function showSuccess(message) {
            // Simple alert for now - could be enhanced with a toast notification
            alert(message);
        }

        function showError(message) {
            // Simple alert for now - could be enhanced with a toast notification
            alert('Error: ' + message);
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
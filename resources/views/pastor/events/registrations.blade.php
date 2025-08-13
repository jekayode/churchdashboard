<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Event Registrations') }}
            </h2>
            <a href="{{ route('pastor.events') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Events
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Event Details Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div id="eventDetails" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Event details will be loaded here -->
                        <div class="animate-pulse">
                            <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                            <div class="h-6 bg-gray-300 rounded w-1/2"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM9 9a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Total Registrations</div>
                                <div class="text-2xl font-semibold text-gray-900" id="totalRegistrations">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Checked In</div>
                                <div class="text-2xl font-semibold text-gray-900" id="checkedInCount">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Pending</div>
                                <div class="text-2xl font-semibold text-gray-900" id="pendingCount">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Attendance Rate</div>
                                <div class="text-2xl font-semibold text-gray-900" id="attendanceRate">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input type="text" id="searchInput" placeholder="Search by name or email..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Statuses</option>
                                <option value="registered">Registered</option>
                                <option value="checked_in">Checked In</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Registration Date</label>
                            <select id="dateFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Dates</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button id="exportBtn" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Export CSV
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registrations Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Registrant
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Contact
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Registration Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Check-in Time
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="registrationsTableBody" class="bg-white divide-y divide-gray-200">
                                <!-- Registrations will be loaded here -->
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        Loading registrations...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div id="pagination" class="mt-6 flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <button id="prevPageMobile" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Previous
                            </button>
                            <button id="nextPageMobile" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Next
                            </button>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing <span id="showingFrom">0</span> to <span id="showingTo">0</span> of <span id="totalResults">0</span> results
                                </p>
                            </div>
                            <div>
                                <nav id="paginationNav" class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <!-- Pagination buttons will be generated here -->
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Check-in Modal -->
    <div id="checkinModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Check-in Confirmation</h3>
                <div id="checkinDetails" class="text-sm text-gray-600 mb-6">
                    <!-- Check-in details will be loaded here -->
                </div>
                <div class="flex justify-center space-x-3">
                    <button id="confirmCheckinBtn" class="px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700">
                        Confirm Check-in
                    </button>
                    <button id="cancelCheckinBtn" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let totalPages = 1;
        let eventId = {{ $eventId }};
        let registrations = [];
        let currentRegistrationId = null;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadEventDetails();
            loadRegistrations();
            setupEventListeners();
        });

        function setupEventListeners() {
            // Search and filters
            document.getElementById('searchInput').addEventListener('input', debounce(loadRegistrations, 300));
            document.getElementById('statusFilter').addEventListener('change', loadRegistrations);
            document.getElementById('dateFilter').addEventListener('change', loadRegistrations);

            // Export button
            document.getElementById('exportBtn').addEventListener('click', exportRegistrations);

            // Modal controls
            document.getElementById('cancelCheckinBtn').addEventListener('click', closeCheckinModal);
            document.getElementById('confirmCheckinBtn').addEventListener('click', confirmCheckin);
        }

        async function loadEventDetails() {
            try {
                const response = await fetch(`/api/events/${eventId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                const data = await response.json();
                
                if (data.success) {
                    const event = data.data;
                    const startDate = new Date(event.start_date_time || event.start_date);
                    const endDate = (event.end_date_time || event.end_date) ? new Date(event.end_date_time || event.end_date) : null;
                    
                    document.getElementById('eventDetails').innerHTML = `
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">${event.name}</h3>
                            <p class="text-sm text-gray-600">${event.description || 'No description'}</p>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Date & Time</div>
                            <div class="text-sm text-gray-900">${startDate.toLocaleString()}</div>
                            ${endDate ? `<div class="text-sm text-gray-600">to ${endDate.toLocaleString()}</div>` : ''}
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Location</div>
                            <div class="text-sm text-gray-900">${event.location}</div>
                            <div class="text-sm text-gray-600">Capacity: ${event.max_capacity || 'Unlimited'}</div>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading event details:', error);
                showNotification('Error loading event details', 'error');
            }
        }

        async function loadRegistrations(page = 1) {
            try {
                const params = new URLSearchParams({
                    page: page,
                    search: document.getElementById('searchInput').value,
                    status: document.getElementById('statusFilter').value,
                    date_filter: document.getElementById('dateFilter').value
                });

                const response = await fetch(`/api/events/${eventId}/registrations?${params}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                const data = await response.json();
                
                if (data.success) {
                    registrations = data.data.data;
                    currentPage = data.data.current_page;
                    totalPages = data.data.last_page;
                    
                    renderRegistrationsTable(registrations);
                    renderPagination(data.data);
                    updateStatistics(data.data.statistics || {});
                }
            } catch (error) {
                console.error('Error loading registrations:', error);
                showNotification('Error loading registrations', 'error');
            }
        }

        function renderRegistrationsTable(registrations) {
            const tbody = document.getElementById('registrationsTableBody');
            
            if (registrations.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No registrations found
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = registrations.map(registration => {
                const registrationDate = new Date(registration.registration_date || registration.created_at);
                const checkinTime = registration.checkin_time ? new Date(registration.checkin_time) : null;
                const status = registration.checked_in ? 'checked_in' : 'registered';
                
                return `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-700">
                                            ${(registration.user?.name || registration.name || 'N/A').substring(0, 2).toUpperCase()}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        ${registration.user?.name || registration.name || 'N/A'}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        ID: ${registration.id}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">${registration.user?.email || registration.email || 'N/A'}</div>
                            <div class="text-sm text-gray-500">${registration.user?.phone || registration.phone || 'N/A'}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${registrationDate.toLocaleDateString()}
                            <div class="text-sm text-gray-500">${registrationDate.toLocaleTimeString()}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            ${getStatusBadge(status)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${checkinTime ? `
                                <div>${checkinTime.toLocaleDateString()}</div>
                                <div class="text-sm text-gray-500">${checkinTime.toLocaleTimeString()}</div>
                            ` : '-'}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            ${!registration.checked_in ? `
                                <button onclick="openCheckinModal(${registration.id})" 
                                        class="text-green-600 hover:text-green-900 mr-3">
                                    Check In
                                </button>
                            ` : `
                                <span class="text-gray-400">Checked In</span>
                            `}
                            <button onclick="viewRegistration(${registration.id})" 
                                    class="text-indigo-600 hover:text-indigo-900">
                                View
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function getStatusBadge(status) {
            const badges = {
                'registered': '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Registered</span>',
                'checked_in': '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Checked In</span>'
            };
            return badges[status] || '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>';
        }

        function updateStatistics(stats) {
            document.getElementById('totalRegistrations').textContent = stats.total || 0;
            document.getElementById('checkedInCount').textContent = stats.checked_in || 0;
            document.getElementById('pendingCount').textContent = stats.pending || 0;
            document.getElementById('attendanceRate').textContent = (stats.attendance_rate || 0) + '%';
        }

        function renderPagination(paginationData) {
            // Update showing info
            document.getElementById('showingFrom').textContent = paginationData.from || 0;
            document.getElementById('showingTo').textContent = paginationData.to || 0;
            document.getElementById('totalResults').textContent = paginationData.total || 0;

            // Generate pagination buttons
            const nav = document.getElementById('paginationNav');
            let paginationHTML = '';

            // Previous button
            if (paginationData.prev_page_url) {
                paginationHTML += `
                    <button onclick="loadRegistrations(${paginationData.current_page - 1})" 
                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        Previous
                    </button>
                `;
            }

            // Page numbers
            for (let i = 1; i <= paginationData.last_page; i++) {
                if (i === paginationData.current_page) {
                    paginationHTML += `
                        <button class="relative inline-flex items-center px-4 py-2 border border-indigo-500 bg-indigo-50 text-sm font-medium text-indigo-600">
                            ${i}
                        </button>
                    `;
                } else {
                    paginationHTML += `
                        <button onclick="loadRegistrations(${i})" 
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                            ${i}
                        </button>
                    `;
                }
            }

            // Next button
            if (paginationData.next_page_url) {
                paginationHTML += `
                    <button onclick="loadRegistrations(${paginationData.current_page + 1})" 
                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        Next
                    </button>
                `;
            }

            nav.innerHTML = paginationHTML;
        }

        function openCheckinModal(registrationId) {
            currentRegistrationId = registrationId;
            const registration = registrations.find(r => r.id === registrationId);
            
            if (registration) {
                document.getElementById('checkinDetails').innerHTML = `
                    <p><strong>Name:</strong> ${registration.user?.name || registration.name || 'N/A'}</p>
                    <p><strong>Email:</strong> ${registration.user?.email || registration.email || 'N/A'}</p>
                    <p><strong>Registration Date:</strong> ${new Date(registration.registration_date || registration.created_at).toLocaleString()}</p>
                `;
                document.getElementById('checkinModal').classList.remove('hidden');
            }
        }

        function closeCheckinModal() {
            document.getElementById('checkinModal').classList.add('hidden');
            currentRegistrationId = null;
        }

        async function confirmCheckin() {
            if (!currentRegistrationId) return;

            try {
                const response = await fetch(`/api/events/${eventId}/registrations/${currentRegistrationId}/check-in`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Registration checked in successfully', 'success');
                    closeCheckinModal();
                    loadRegistrations(currentPage);
                } else {
                    showNotification(data.message || 'Error checking in registration', 'error');
                }
            } catch (error) {
                console.error('Error checking in registration:', error);
                showNotification('Error checking in registration', 'error');
            }
        }

        function viewRegistration(registrationId) {
            const registration = registrations.find(r => r.id === registrationId);
            if (registration) {
                // For now, just show an alert with registration details
                // In a real app, you might open a detailed modal or navigate to a detail page
                alert(`Registration Details:\n\nName: ${registration.user?.name || registration.name || 'N/A'}\nEmail: ${registration.user?.email || registration.email || 'N/A'}\nPhone: ${registration.user?.phone || registration.phone || 'N/A'}\nRegistration Date: ${new Date(registration.registration_date || registration.created_at).toLocaleString()}\nStatus: ${registration.checked_in ? 'Checked In' : 'Registered'}`);
            }
        }

        async function exportRegistrations() {
            try {
                const params = new URLSearchParams({
                    export: 'csv',
                    search: document.getElementById('searchInput').value,
                    status: document.getElementById('statusFilter').value,
                    date_filter: document.getElementById('dateFilter').value
                });

                const response = await fetch(`/api/events/${eventId}/registrations?${params}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'text/csv',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = `event-${eventId}-registrations.csv`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    showNotification('Registrations exported successfully', 'success');
                } else {
                    showNotification('Error exporting registrations', 'error');
                }
            } catch (error) {
                console.error('Error exporting registrations:', error);
                showNotification('Error exporting registrations', 'error');
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
                document.body.removeChild(notification);
            }, 3000);
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
</x-app-layout> 
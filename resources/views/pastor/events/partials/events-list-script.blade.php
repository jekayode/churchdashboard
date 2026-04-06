    <script>
        let currentPage = 1;
        let totalPages = 1;
        let events = [];
        let branches = [];
        let isSuperAdmin = {{ $isSuperAdmin ? 'true' : 'false' }};

        document.addEventListener('DOMContentLoaded', function() {
            loadBranches();
            if (isSuperAdmin) {
                document.getElementById('branchFilterDiv').classList.remove('hidden');
                document.getElementById('branchColumnHeader').classList.remove('hidden');
            }
            loadStatistics();
            loadEvents();
            setupEventListeners();
        });

        function setupEventListeners() {
            const closeViewModal = document.getElementById('closeViewModal');
            if (closeViewModal) closeViewModal.addEventListener('click', () => closeViewEventModal());

            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const typeFilter = document.getElementById('typeFilter');
            const dateFilter = document.getElementById('dateFilter');

            if (searchInput) {
                searchInput.addEventListener('input', debounce(() => {
                    loadEvents();
                    loadStatistics();
                }, 300));
            }
            if (statusFilter) {
                statusFilter.addEventListener('change', () => {
                    loadEvents();
                    loadStatistics();
                });
            }
            if (typeFilter) {
                typeFilter.addEventListener('change', () => {
                    loadEvents();
                    loadStatistics();
                });
            }
            if (dateFilter) {
                dateFilter.addEventListener('change', () => {
                    loadEvents();
                    loadStatistics();
                });
            }

            const branchFilter = document.getElementById('branchFilter');
            if (branchFilter) {
                branchFilter.addEventListener('change', () => {
                    loadEvents();
                    loadStatistics();
                });
            }
        }

        function closeViewEventModal() {
            const modal = document.getElementById('viewEventModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        async function loadBranches() {
            try {
                const response = await fetch('/api/events/branches', {
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
                    branches = data.data;
                    populateBranchDropdowns();
                }
            } catch (error) {
                console.error('Error loading branches:', error);
            }
        }

        function populateBranchDropdowns() {
            const branchFilter = document.getElementById('branchFilter');
            const eventBranch = document.getElementById('eventBranch');
            
            if (branchFilter && branches.length > 0) {
                branchFilter.innerHTML = '<option value="">All Branches</option>' + 
                    branches.map(branch => `<option value="${branch.id}">${branch.name}</option>`).join('');
            }
            
            if (eventBranch && branches.length > 0) {
                eventBranch.innerHTML = '<option value="">Select Branch</option>' + 
                    branches.map(branch => `<option value="${branch.id}">${branch.name}</option>`).join('');
            }
        }

        async function loadStatistics() {
            try {
                // Build the same parameters as the events list
                const params = new URLSearchParams({
                    search: document.getElementById('searchInput').value,
                    status: document.getElementById('statusFilter').value,
                    type: document.getElementById('typeFilter').value,
                    date_filter: document.getElementById('dateFilter').value
                });

                // Add branch filter if super admin
                const branchFilter = document.getElementById('branchFilter');
                if (isSuperAdmin && branchFilter && branchFilter.value) {
                    params.append('branch_id', branchFilter.value);
                }



                const response = await fetch(`/api/events/statistics?${params}`, {
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
                    const stats = data.data;
                    document.getElementById('totalEvents').textContent = stats.total_events || 0;
                    document.getElementById('activeEvents').textContent = stats.active_events || 0;
                    document.getElementById('totalRegistrations').textContent = stats.total_registrations || 0;
                    document.getElementById('avgAttendance').textContent = stats.average_attendance || '0%';
                } else {
                    console.error('Statistics API error:', data.message);
                }
            } catch (error) {
                console.error('Error loading statistics:', error);
            }
        }

        async function loadEvents(page = 1) {
            try {
                const params = new URLSearchParams({
                    page: page,
                    search: document.getElementById('searchInput').value,
                    status: document.getElementById('statusFilter').value,
                    type: document.getElementById('typeFilter').value,
                    date_filter: document.getElementById('dateFilter').value,
                    sort_by: 'created_at',
                    sort_order: 'desc',
                });

                // Add branch filter if super admin
                const branchFilter = document.getElementById('branchFilter');
                if (isSuperAdmin && branchFilter && branchFilter.value) {
                    params.append('branch_id', branchFilter.value);
                }

                const response = await fetch(`/api/events?${params}`, {
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
                    events = data.data.data;
                    currentPage = data.data.current_page;
                    totalPages = data.data.last_page;
                    
                    renderEventsTable(events);
                    renderPagination(data.data);
                } else {
                    console.error('Events API error:', data.message);
                }
            } catch (error) {
                console.error('Error loading events:', error);
                showNotification('Error loading events', 'error');
            }
        }

        function renderEventsTable(events) {
            const tbody = document.getElementById('eventsTableBody');
            
            if (events.length === 0) {
                const colspan = isSuperAdmin ? "7" : "6";
                tbody.innerHTML = `
                    <tr>
                        <td colspan="${colspan}" class="px-6 py-4 text-center text-gray-500">
                            No events found
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = events.map(event => {
                // Extract date and time without timezone conversion
                const startDateTime = event.start_date_time;
                let displayDate = '';
                let displayTime = '';
                
                if (startDateTime) {
                    if (startDateTime.includes('T')) {
                        const [datePart, timePart] = startDateTime.split('T');
                        displayDate = new Date(datePart + 'T00:00:00').toLocaleDateString();
                        displayTime = timePart.slice(0, 5); // Extract HH:MM without timezone conversion
                    } else {
                        displayDate = new Date(startDateTime).toLocaleDateString();
                        displayTime = startDateTime.includes(':') ? startDateTime.slice(11, 16) : '';
                    }
                }
                
                const statusBadge = getStatusBadge(event.status);
                const typeBadge = getTypeBadge(event.type);
                
                const branchColumn = isSuperAdmin ? `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${event.branch ? event.branch.name : 'N/A'}</div>
                    </td>
                ` : '';

                return `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-gray-900">${event.name}</div>
                                <div class="text-sm text-gray-500">${event.location}</div>
                            </div>
                        </td>
                        ${branchColumn}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">${displayDate}</div>
                            <div class="text-sm text-gray-500">${displayTime}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            ${typeBadge}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="font-medium">${event.registrations_count || 0}</span>
                            ${event.max_capacity ? `/ ${event.max_capacity}` : ''}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            ${statusBadge}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <button onclick="viewEvent(${event.id})" class="text-indigo-600 hover:text-indigo-900">View</button>
                            <button onclick="editEvent(${event.id})" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                            <button onclick="deleteEvent(${event.id})" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function getStatusBadge(status) {
            const badges = {
                'published': 'bg-green-100 text-green-800',
                'draft': 'bg-yellow-100 text-yellow-800',
                'cancelled': 'bg-red-100 text-red-800',
                'completed': 'bg-blue-100 text-blue-800',
                'active': 'bg-green-100 text-green-800'
            };
            
            return `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${badges[status] || 'bg-gray-100 text-gray-800'}">${status}</span>`;
        }

        function getTypeBadge(type) {
            const badges = {
                'service': 'bg-purple-100 text-purple-800',
                'conference': 'bg-blue-100 text-blue-800',
                'workshop': 'bg-yellow-100 text-yellow-800',
                'outreach': 'bg-green-100 text-green-800',
                'social': 'bg-pink-100 text-pink-800',
                'other': 'bg-gray-100 text-gray-800'
            };
            
            return `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${badges[type] || 'bg-gray-100 text-gray-800'}">${type}</span>`;
        }

        function renderPagination(paginationData) {
            const showingFrom = ((paginationData.current_page - 1) * paginationData.per_page) + 1;
            const showingTo = Math.min(paginationData.current_page * paginationData.per_page, paginationData.total);
            
            document.getElementById('showingFrom').textContent = showingFrom;
            document.getElementById('showingTo').textContent = showingTo;
            document.getElementById('totalRecords').textContent = paginationData.total;

            const paginationNav = document.getElementById('paginationNav');
            let paginationHTML = '';

            // Previous button
            if (paginationData.current_page > 1) {
                paginationHTML += `<button onclick="loadEvents(${paginationData.current_page - 1})" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Previous</button>`;
            }

            // Page numbers
            for (let i = 1; i <= paginationData.last_page; i++) {
                if (i === paginationData.current_page) {
                    paginationHTML += `<button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-indigo-50 text-sm font-medium text-indigo-600">${i}</button>`;
                } else {
                    paginationHTML += `<button onclick="loadEvents(${i})" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">${i}</button>`;
                }
            }

            // Next button
            if (paginationData.current_page < paginationData.last_page) {
                paginationHTML += `<button onclick="loadEvents(${paginationData.current_page + 1})" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Next</button>`;
            }

            paginationNav.innerHTML = paginationHTML;
        }

        async function viewEvent(eventId) {
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
                    document.getElementById('viewEventTitle').textContent = event.name;
                    
                    // Extract date and time without timezone conversion
                    const startDateTime = event.start_date_time || event.start_date;
                    const endDateTime = event.end_date_time || event.end_date;
                    
                    let startDisplay = '';
                    let endDisplay = '';
                    
                    if (startDateTime) {
                        if (startDateTime.includes('T')) {
                            const [datePart, timePart] = startDateTime.split('T');
                            const displayDate = new Date(datePart + 'T00:00:00').toLocaleDateString();
                            const displayTime = timePart.slice(0, 5);
                            startDisplay = `${displayDate} ${displayTime}`;
                        } else {
                            startDisplay = new Date(startDateTime).toLocaleDateString();
                        }
                    }
                    
                    if (endDateTime) {
                        if (endDateTime.includes('T')) {
                            const [datePart, timePart] = endDateTime.split('T');
                            const displayDate = new Date(datePart + 'T00:00:00').toLocaleDateString();
                            const displayTime = timePart.slice(0, 5);
                            endDisplay = `${displayDate} ${displayTime}`;
                        } else {
                            endDisplay = new Date(endDateTime).toLocaleDateString();
                        }
                    }
                    
                    document.getElementById('eventDetails').innerHTML = `
                        ${event.cover_image_url ? `
                        <div class="mb-4">
                            <img src="${event.cover_image_url.replace(/"/g, '&quot;')}" alt="" class="h-20 w-20 rounded-lg border border-gray-200 object-cover shrink-0">
                        </div>` : ''}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-medium text-gray-900 mb-2">Event Information</h4>
                                <dl class="space-y-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Type</dt>
                                        <dd class="text-sm text-gray-900">${event.type}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                                        <dd class="text-sm text-gray-900">${getStatusBadge(event.status)}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Location</dt>
                                        <dd class="text-sm text-gray-900">${event.location}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Capacity</dt>
                                        <dd class="text-sm text-gray-900">${event.max_capacity || 'Unlimited'}</dd>
                                    </div>
                                    ${isSuperAdmin && event.branch ? `
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Branch</dt>
                                        <dd class="text-sm text-gray-900">${event.branch.name}</dd>
                                    </div>
                                    ` : ''}
                                </dl>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 mb-2">Schedule</h4>
                                <dl class="space-y-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Start Date & Time</dt>
                                        <dd class="text-sm text-gray-900">${startDisplay}</dd>
                                    </div>
                                    ${endDisplay ? `
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">End Date & Time</dt>
                                        <dd class="text-sm text-gray-900">${endDisplay}</dd>
                                    </div>
                                    ` : ''}
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Registration Type</dt>
                                        <dd class="text-sm text-gray-900">${event.registration_type}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Public Event</dt>
                                        <dd class="text-sm text-gray-900">${event.is_public ? 'Yes' : 'No'}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                        ${event.public_detail_url ? `
                        <div class="mt-4 flex flex-wrap items-start gap-6 border-t border-gray-100 pt-4">
                            <div class="flex-1 min-w-[200px]">
                                <h4 class="font-medium text-gray-900 mb-2">Public page & QR</h4>
                                <p class="text-xs text-gray-500 mb-1">Share this link or scan the code (opens the public event page).</p>
                                <a href=${JSON.stringify(event.public_detail_url)} class="text-sm text-indigo-600 hover:underline break-all" target="_blank" rel="noopener">${event.public_detail_url}</a>
                            </div>
                            <div class="flex-shrink-0 flex flex-col items-center gap-2">
                                <img src="/api/events/${eventId}/public-page-qr" width="96" height="96" alt="QR code for public event page" class="h-24 w-24 border border-gray-200 rounded-md bg-white">
                                <a href="/api/events/${eventId}/public-page-qr/download?pixels=2048" class="text-xs text-indigo-600 hover:underline">Download high-res PNG</a>
                            </div>
                        </div>` : ''}
                        ${event.description ? `
                        <div class="mt-6">
                            <h4 class="font-medium text-gray-900 mb-2">Description</h4>
                            <p class="text-sm text-gray-700">${event.description}</p>
                        </div>
                        ` : ''}
                        <div class="mt-6">
                            <h4 class="font-medium text-gray-900 mb-2">Registration Statistics</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-gray-50 p-3 rounded">
                                    <div class="text-sm font-medium text-gray-500">Total Registrations</div>
                                    <div class="text-lg font-semibold text-gray-900">${event.registrations_count || 0}</div>
                                </div>
                                <div class="bg-gray-50 p-3 rounded">
                                    <div class="text-sm font-medium text-gray-500">Check-ins</div>
                                    <div class="text-lg font-semibold text-gray-900">${event.checkins_count || 0}</div>
                                </div>
                                <div class="bg-gray-50 p-3 rounded">
                                    <div class="text-sm font-medium text-gray-500">Attendance Rate</div>
                                    <div class="text-lg font-semibold text-gray-900">${event.attendance_rate || '0%'}</div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Set up action buttons
                    document.getElementById('editEventBtn').onclick = () => {
                        closeViewEventModal();
                        window.location.href = `/pastor/events/${eventId}/edit`;
                    };
                    
                    document.getElementById('viewRegistrationsBtn').onclick = () => {
                        window.location.href = `/pastor/events/${eventId}/registrations`;
                    };
                    
                    document.getElementById('viewEventModal').classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error loading event details:', error);
                showNotification('Error loading event details', 'error');
            }
        }

        function editEvent(eventId) {
            window.location.href = `/pastor/events/${eventId}/edit`;
        }

        async function deleteEvent(eventId) {
            if (!confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
                return;
            }
            
            try {
                const response = await fetch(`/api/events/${eventId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Event deleted successfully', 'success');
                    loadEvents(currentPage);
                    loadStatistics();
                } else {
                    showNotification(data.message || 'Error deleting event', 'error');
                }
            } catch (error) {
                console.error('Error deleting event:', error);
                showNotification('Error deleting event', 'error');
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

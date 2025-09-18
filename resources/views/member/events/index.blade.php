<x-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Church Events') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Filters -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Upcoming Events</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input type="text" id="searchInput" placeholder="Search events..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                            <select id="typeFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Types</option>
                                <option value="service">Service</option>
                                <option value="conference">Conference</option>
                                <option value="workshop">Workshop</option>
                                <option value="outreach">Outreach</option>
                                <option value="social">Social</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                            <select id="dateFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="upcoming">Upcoming</option>
                                <option value="this_week">This Week</option>
                                <option value="this_month">This Month</option>
                                <option value="all">All Events</option>
                            </select>
                        </div>
                    </div>

                    <!-- Events Grid -->
                    <div id="eventsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Events will be loaded here -->
                    </div>

                    <!-- Pagination -->
                    <div id="pagination" class="mt-8 flex items-center justify-center">
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" id="paginationNav">
                            <!-- Pagination buttons will be generated here -->
                        </nav>
                    </div>
                </div>
            </div>

            <!-- My Registrations -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">My Registrations</h3>
                </div>
                <div class="p-6">
                    <div id="myRegistrations" class="space-y-4">
                        <!-- User's registrations will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div id="eventModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Event Details</h3>
                    <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div id="eventDetails" class="space-y-4">
                    <!-- Event details will be loaded here -->
                </div>

                <div class="flex justify-end space-x-3 pt-6">
                    <button id="registerBtn" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700">
                        Register for Event
                    </button>
                    <button id="unregisterBtn" class="px-4 py-2 bg-red-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-red-700 hidden">
                        Cancel Registration
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Form Modal -->
    <div id="registrationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="registrationModalTitle">Event Registration</h3>
                    <button id="closeRegistrationModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="registrationForm" class="space-y-4">
                    <input type="hidden" id="eventId" name="event_id">
                    
                    <div id="customFormFields">
                        <!-- Custom form fields will be generated here -->
                    </div>

                    <div class="flex justify-end space-x-3 pt-6">
                        <button type="button" id="cancelRegistrationBtn" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700">
                            Complete Registration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let totalPages = 1;
        let events = [];
        let currentEvent = null;
        let userRegistrations = [];

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
            loadInitialData();
        });

        async function loadInitialData() {
            // Load both events and user registrations in parallel
            await Promise.all([
                loadEvents(),
                loadUserRegistrations()
            ]);
        }

        function setupEventListeners() {
            // Modal controls
            document.getElementById('closeModal').addEventListener('click', () => closeEventModal());
            document.getElementById('closeRegistrationModal').addEventListener('click', () => closeRegistrationModal());
            document.getElementById('cancelRegistrationBtn').addEventListener('click', () => closeRegistrationModal());

            // Form submission
            document.getElementById('registrationForm').addEventListener('submit', handleRegistrationSubmit);

            // Filters
            document.getElementById('searchInput').addEventListener('input', debounce(loadEvents, 300));
            document.getElementById('typeFilter').addEventListener('change', loadEvents);
            document.getElementById('dateFilter').addEventListener('change', loadEvents);

            // Registration buttons
            document.getElementById('registerBtn').addEventListener('click', handleRegisterClick);
            document.getElementById('unregisterBtn').addEventListener('click', handleUnregisterClick);
        }

        async function loadEvents(page = 1) {
            try {
                const params = new URLSearchParams({
                    page: page,
                    search: document.getElementById('searchInput').value,
                    type: document.getElementById('typeFilter').value,
                    date_filter: document.getElementById('dateFilter').value,
                    status: 'active' // Only show active events for members
                });

                const response = await fetch(`/api/events?${params}`);
                const data = await response.json();
                
                if (data.success) {
                    events = data.data.data;
                    currentPage = data.data.current_page;
                    totalPages = data.data.last_page;
                    
                    renderEventsGrid(events);
                    renderPagination(data.data);
                }
            } catch (error) {
                console.error('Error loading events:', error);
                showNotification('Error loading events', 'error');
            }
        }

        async function loadUserRegistrations() {
            try {
                const response = await fetch('/api/events/my-registrations');
                const data = await response.json();
                
                if (data.success) {
                    // Handle paginated data - Laravel pagination has data in data.data
                    if (data.data && data.data.data) {
                        userRegistrations = data.data.data;
                    } else if (Array.isArray(data.data)) {
                        userRegistrations = data.data;
                    } else {
                        userRegistrations = [];
                    }
                    renderUserRegistrations(userRegistrations);
                } else {
                    console.error('Failed to load registrations:', data.message);
                    userRegistrations = [];
                }
            } catch (error) {
                console.error('Error loading registrations:', error);
                userRegistrations = [];
            }
        }

        function renderEventsGrid(events) {
            const grid = document.getElementById('eventsGrid');
            
            if (events.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a4 4 0 11-8 0v-4m0 0V7a4 4 0 118 0v4"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No events found</h3>
                        <p class="mt-1 text-sm text-gray-500">Try adjusting your search criteria.</p>
                    </div>
                `;
                return;
            }

            grid.innerHTML = events.map(event => {
                const startDate = new Date(event.start_date_time);
                // Ensure userRegistrations is an array before using .some()
                const isRegistered = Array.isArray(userRegistrations) && userRegistrations.some(reg => reg.event_id === event.id);
                const registrationButton = getRegistrationButton(event, isRegistered);
                
                return `
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getTypeBadgeClass(event.type)}">
                                    ${event.type}
                                </span>
                                ${isRegistered ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Registered</span>' : ''}
                            </div>
                            
                            <h3 class="text-lg font-medium text-gray-900 mb-2">${event.name}</h3>
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">${event.description || 'No description available'}</p>
                            
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                    ${startDate.toLocaleDateString()} at ${startDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                    </svg>
                                    ${event.location}
                                </div>
                                ${event.max_capacity ? `
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                    </svg>
                                    ${event.registrations_count || 0} / ${event.max_capacity} registered
                                </div>
                                ` : ''}
                            </div>
                            
                            <div class="flex space-x-3">
                                <button onclick="viewEvent(${event.id})" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded text-sm">
                                    View Details
                                </button>
                                ${registrationButton}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function getTypeBadgeClass(type) {
            const classes = {
                'service': 'bg-purple-100 text-purple-800',
                'conference': 'bg-blue-100 text-blue-800',
                'workshop': 'bg-yellow-100 text-yellow-800',
                'outreach': 'bg-green-100 text-green-800',
                'social': 'bg-pink-100 text-pink-800',
                'other': 'bg-gray-100 text-gray-800'
            };
            return classes[type] || 'bg-gray-100 text-gray-800';
        }

        function getRegistrationButton(event, isRegistered) {
            if (event.registration_type === 'none') {
                return '<span class="flex-1 text-center text-sm text-gray-500 py-2">No registration required</span>';
            }
            
            if (event.registration_type === 'link') {
                return `<a href="${event.registration_link}" target="_blank" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded text-sm text-center">Register</a>`;
            }
            
            if (isRegistered) {
                return '<button onclick="unregisterFromEvent(' + event.id + ')" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded text-sm">Cancel Registration</button>';
            }
            
            // Check if event is full
            if (event.max_capacity && event.registrations_count >= event.max_capacity) {
                return '<span class="flex-1 text-center text-sm text-gray-500 py-2">Event Full</span>';
            }
            
            return '<button onclick="registerForEvent(' + event.id + ')" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded text-sm">Register</button>';
        }

        function renderUserRegistrations(registrations) {
            const container = document.getElementById('myRegistrations');
            
            // Ensure registrations is an array
            if (!Array.isArray(registrations)) {
                console.warn('renderUserRegistrations: registrations is not an array:', registrations);
                registrations = [];
            }
            
            if (registrations.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No registrations</h3>
                        <p class="mt-1 text-sm text-gray-500">You haven't registered for any events yet.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = registrations.map(registration => {
                const event = registration.event;
                const startDate = new Date(event.start_date_time);
                const registrationDate = new Date(registration.created_at);
                
                return `
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h4 class="text-lg font-medium text-gray-900">${event.name}</h4>
                                <p class="text-sm text-gray-600">${event.location}</p>
                                <p class="text-sm text-gray-500">${startDate.toLocaleDateString()} at ${startDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</p>
                                <p class="text-xs text-gray-400 mt-1">Registered on ${registrationDate.toLocaleDateString()}</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                ${registration.checked_in ? 
                                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Checked In</span>' : 
                                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Registered</span>'
                                }
                                <button onclick="viewEvent(${event.id})" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">View</button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function renderPagination(paginationData) {
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
                const response = await fetch(`/api/events/${eventId}`);
                const data = await response.json();
                
                if (data.success) {
                    currentEvent = data.data;
                    const event = currentEvent;
                    // Ensure userRegistrations is an array before using .some()
                    const isRegistered = Array.isArray(userRegistrations) && userRegistrations.some(reg => reg.event_id === event.id);
                    
                    document.getElementById('modalTitle').textContent = event.name;
                    
                    const startDate = new Date(event.start_date_time);
                    const endDate = event.end_date_time ? new Date(event.end_date_time) : null;
                    
                    document.getElementById('eventDetails').innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-medium text-gray-900 mb-2">Event Information</h4>
                                <dl class="space-y-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Type</dt>
                                        <dd class="text-sm text-gray-900">${event.type}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Location</dt>
                                        <dd class="text-sm text-gray-900">${event.location}</dd>
                                    </div>
                                    ${event.max_capacity ? `
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Capacity</dt>
                                        <dd class="text-sm text-gray-900">${event.registrations_count || 0} / ${event.max_capacity}</dd>
                                    </div>
                                    ` : ''}
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Registration</dt>
                                        <dd class="text-sm text-gray-900">${event.registration_type === 'none' ? 'Not required' : 'Required'}</dd>
                                    </div>
                                </dl>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 mb-2">Schedule</h4>
                                <dl class="space-y-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Start Date & Time</dt>
                                        <dd class="text-sm text-gray-900">${startDate.toLocaleString()}</dd>
                                    </div>
                                    ${endDate ? `
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">End Date & Time</dt>
                                        <dd class="text-sm text-gray-900">${endDate.toLocaleString()}</dd>
                                    </div>
                                    ` : ''}
                                </dl>
                            </div>
                        </div>
                        ${event.description ? `
                        <div class="mt-6">
                            <h4 class="font-medium text-gray-900 mb-2">Description</h4>
                            <p class="text-sm text-gray-700">${event.description}</p>
                        </div>
                        ` : ''}
                    `;
                    
                    // Update registration buttons
                    const registerBtn = document.getElementById('registerBtn');
                    const unregisterBtn = document.getElementById('unregisterBtn');
                    
                    if (event.registration_type === 'none') {
                        registerBtn.style.display = 'none';
                        unregisterBtn.style.display = 'none';
                    } else if (event.registration_type === 'link') {
                        registerBtn.textContent = 'Register (External)';
                        registerBtn.onclick = () => window.open(event.registration_link, '_blank');
                        registerBtn.style.display = 'block';
                        unregisterBtn.style.display = 'none';
                    } else if (isRegistered) {
                        registerBtn.style.display = 'none';
                        unregisterBtn.style.display = 'block';
                    } else {
                        registerBtn.textContent = 'Register for Event';
                        registerBtn.style.display = 'block';
                        unregisterBtn.style.display = 'none';
                        
                        // Check if event is full
                        if (event.max_capacity && event.registrations_count >= event.max_capacity) {
                            registerBtn.textContent = 'Event Full';
                            registerBtn.disabled = true;
                            registerBtn.classList.add('bg-gray-400');
                            registerBtn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
                        }
                    }
                    
                    document.getElementById('eventModal').classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error loading event details:', error);
                showNotification('Error loading event details', 'error');
            }
        }

        function closeEventModal() {
            document.getElementById('eventModal').classList.add('hidden');
            currentEvent = null;
        }

        function closeRegistrationModal() {
            document.getElementById('registrationModal').classList.add('hidden');
        }

        async function registerForEvent(eventId) {
            const event = events.find(e => e.id === eventId) || currentEvent;
            
            if (event.registration_type === 'simple') {
                // Simple registration - just call API
                try {
                    const response = await fetch(`/api/events/${eventId}/register`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        showNotification('Registration successful!', 'success');
                        loadEvents(currentPage);
                        loadUserRegistrations();
                        if (currentEvent) {
                            closeEventModal();
                        }
                    } else {
                        showNotification(data.message || 'Registration failed', 'error');
                    }
                } catch (error) {
                    console.error('Error registering for event:', error);
                    showNotification('Error registering for event', 'error');
                }
            } else if (event.registration_type === 'form') {
                // Custom form registration
                openRegistrationModal(event);
            }
        }

        function openRegistrationModal(event) {
            document.getElementById('registrationModalTitle').textContent = `Register for ${event.name}`;
            document.getElementById('eventId').value = event.id;
            
            // Generate form fields based on registration type
            const fieldsContainer = document.getElementById('customFormFields');
            let formFields = [];
            
            try {
                // Handle both cases: already parsed array or JSON string
                if (Array.isArray(event.custom_form_fields)) {
                    formFields = event.custom_form_fields;
                } else if (typeof event.custom_form_fields === 'string') {
                    formFields = JSON.parse(event.custom_form_fields || '[]');
                } else if (event.custom_form_fields) {
                    // If it's an object, try to convert it
                    formFields = Object.values(event.custom_form_fields);
                } else {
                    formFields = [];
                }
            } catch (e) {
                console.error('Error parsing custom form fields:', e);
                console.log('Raw custom_form_fields:', event.custom_form_fields);
                formFields = [];
            }
            
            let formHTML = '';
            
            if (event.registration_type === 'form') {
                // For form registration, add standard fields first
                formHTML += `
                    <div>
                        <label for="reg_name" class="block text-sm font-medium text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" id="reg_name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="reg_email" class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                        <input type="email" id="reg_email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="reg_phone" class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                        <input type="tel" id="reg_phone" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                `;
                
                // Filter out standard fields from custom fields to avoid duplication
                const standardFieldNames = ['name', 'email', 'phone'];
                formFields = formFields.filter(field => !standardFieldNames.includes(field.name));
            }
            
            // Add custom fields
            if (formFields.length > 0) {
                formHTML += formFields.map(field => {
                    const required = field.required ? 'required' : '';
                    const fieldId = `field_${field.name}`;
                    const requiredMark = field.required ? '<span class="text-red-500">*</span>' : '';
                    
                    switch (field.type) {
                        case 'text':
                        case 'email':
                        case 'tel':
                        case 'number':
                        case 'date':
                            return `
                                <div>
                                    <label for="${fieldId}" class="block text-sm font-medium text-gray-700 mb-2">${field.label} ${requiredMark}</label>
                                    <input type="${field.type}" id="${fieldId}" name="${field.name}" ${required} class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            `;
                        case 'textarea':
                            return `
                                <div>
                                    <label for="${fieldId}" class="block text-sm font-medium text-gray-700 mb-2">${field.label} ${requiredMark}</label>
                                    <textarea id="${fieldId}" name="${field.name}" rows="3" ${required} class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                </div>
                            `;
                        case 'select':
                            const options = field.options || [];
                            return `
                                <div>
                                    <label for="${fieldId}" class="block text-sm font-medium text-gray-700 mb-2">${field.label} ${requiredMark}</label>
                                    <select id="${fieldId}" name="${field.name}" ${required} class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">Select an option</option>
                                        ${options.map(option => `<option value="${option}">${option}</option>`).join('')}
                                    </select>
                                </div>
                            `;
                        case 'radio':
                            const radioOptions = field.options || [];
                            return `
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">${field.label} ${requiredMark}</label>
                                    <div class="space-y-2">
                                        ${radioOptions.map((option, index) => `
                                            <label class="flex items-center">
                                                <input type="radio" id="${fieldId}_${index}" name="${field.name}" value="${option}" ${required} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                                <span class="ml-2 text-sm text-gray-700">${option}</span>
                                            </label>
                                        `).join('')}
                                    </div>
                                </div>
                            `;
                        case 'checkbox':
                            return `
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" id="${fieldId}" name="${field.name}" value="1" ${required} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">${field.label} ${requiredMark}</span>
                                    </label>
                                </div>
                            `;
                        default:
                            return `
                                <div>
                                    <label for="${fieldId}" class="block text-sm font-medium text-gray-700 mb-2">${field.label} ${requiredMark}</label>
                                    <input type="text" id="${fieldId}" name="${field.name}" ${required} class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            `;
                    }
                }).join('');
            }
            
            if (formHTML === '') {
                fieldsContainer.innerHTML = '<p class="text-sm text-gray-600">No additional information required.</p>';
            } else {
                fieldsContainer.innerHTML = formHTML;
            }
            
            document.getElementById('registrationModal').classList.remove('hidden');
        }

        async function handleRegistrationSubmit(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const eventId = formData.get('event_id');
            
            // Find the current event to check registration type
            const event = currentEvent || await getEventDetails(eventId);
            const isSimpleRegistration = event.registration_type === 'simple';
            
            // Prepare registration data based on registration type
            let registrationData = {
                event_id: eventId
            };
            
            if (isSimpleRegistration) {
                // For simple registration, only collect custom fields
                const customFields = {};
                
                // Collect all form fields except event_id as custom fields
                for (let [key, value] of formData.entries()) {
                    if (key !== 'event_id') {
                        customFields[key] = value;
                    }
                }
                
                if (Object.keys(customFields).length > 0) {
                    registrationData.custom_fields = customFields;
                }
            } else {
                // For form registration, collect standard fields and custom fields separately
                registrationData.name = formData.get('name');
                registrationData.email = formData.get('email'); 
                registrationData.phone = formData.get('phone');
                
                // Collect custom fields (fields that are not standard name/email/phone)
                const customFields = {};
                const standardFields = ['event_id', 'name', 'email', 'phone'];
                
                for (let [key, value] of formData.entries()) {
                    if (!standardFields.includes(key)) {
                        customFields[key] = value;
                    }
                }
                
                if (Object.keys(customFields).length > 0) {
                    registrationData.custom_fields = customFields;
                }
            }
            
            try {
                const response = await fetch(`/api/events/${eventId}/register`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(registrationData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Registration successful!', 'success');
                    closeRegistrationModal();
                    loadEvents(currentPage);
                    loadUserRegistrations();
                    if (currentEvent) {
                        closeEventModal();
                    }
                } else {
                    console.error('Registration failed:', data);
                    showNotification(data.message || 'Registration failed', 'error');
                    
                    // Show validation errors if available
                    if (data.errors) {
                        const errorMessages = Object.values(data.errors).flat().join(', ');
                        showNotification(`Validation errors: ${errorMessages}`, 'error');
                    }
                }
            } catch (error) {
                console.error('Error registering for event:', error);
                showNotification('Error registering for event', 'error');
            }
        }
        
        async function getEventDetails(eventId) {
            try {
                const response = await fetch(`/api/events/${eventId}`);
                const data = await response.json();
                return data.success ? data.data : null;
            } catch (error) {
                console.error('Error fetching event details:', error);
                return null;
            }
        }

        function handleRegisterClick() {
            if (currentEvent) {
                registerForEvent(currentEvent.id);
            }
        }

        async function handleUnregisterClick() {
            if (!currentEvent || !confirm('Are you sure you want to cancel your registration?')) {
                return;
            }
            
            await unregisterFromEvent(currentEvent.id);
        }

        async function unregisterFromEvent(eventId) {
            try {
                const registration = userRegistrations.find(reg => reg.event_id === eventId);
                if (!registration) {
                    showNotification('Registration not found', 'error');
                    return;
                }
                
                const response = await fetch(`/api/events/${eventId}/registrations/${registration.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Registration cancelled successfully', 'success');
                    loadEvents(currentPage);
                    loadUserRegistrations();
                    if (currentEvent) {
                        closeEventModal();
                    }
                } else {
                    showNotification(data.message || 'Failed to cancel registration', 'error');
                }
            } catch (error) {
                console.error('Error cancelling registration:', error);
                showNotification('Error cancelling registration', 'error');
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
</x-sidebar-layout> 
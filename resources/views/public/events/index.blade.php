
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Church Dashboard') }} - Events</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-20">
                    <div class="flex items-center">
                        <a href="/" class="flex-shrink-0 flex items-center">
                            <img src="https://lifepointeng.org/wp-content/uploads/2023/10/Lifepointe-Logo-White.png" alt="LifePointe" class="h-12 w-auto"/>
                        </a>
                    </div>
                    <div class="flex items-center space-x-8">
                        <a href="{{ route('public.events') }}" class="text-[#F1592A] hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                            Events
                        </a>
                        <a href="{{ route('public.lifegroups') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                            LifeGroups
                        </a>
                        <a href="{{ route('login') }}" class="bg-[#F1592A] hover:bg-[#E54A1A] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">Login</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="mb-6">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2 font-display">Upcoming Events</h1>
                    <p class="text-gray-600">Join us for these exciting upcoming events. Registration is quick and easy!</p>
                </div>

                <!-- Search and Filters -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <input 
                                type="text" 
                                id="searchInput" 
                                placeholder="Search events..." 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>
                        <div>
                            <select id="branchFilter" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Expressions</option>
                            </select>
                        </div>
                        <div>
                            <select id="dateFilter" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                            </select>
                        </div>
                        <div>
                            <select id="serviceTypeFilter" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Events</option>
                                <option value="Sunday Service">Sunday Service</option>
                                <option value="MidWeek">MidWeek</option>
                                <option value="Conferences">Conferences</option>
                                <option value="Outreach">Outreach</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Events Grid -->
                <div id="eventsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Events will be loaded here -->
                </div>

                <!-- Loading State -->
                <div id="loadingState" class="text-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
                    <p class="mt-2 text-gray-600">Loading events...</p>
                </div>

                <!-- No Events State -->
                <div id="noEventsState" class="text-center py-12 hidden">
                    <div class="text-gray-400 text-6xl mb-4">📅</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No events found</h3>
                    <p class="text-gray-600">There are no upcoming events matching your criteria.</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-900">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <!-- Our Locations -->
                <div class="mb-12">
                    <h3 class="text-lg font-semibold text-white mb-6">Our Locations</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" id="locationsGrid">
                        <!-- Locations will be loaded here -->
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Church Info -->
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-4">LifePointe Church</h3>
                        <p class="text-gray-300 text-sm mb-4">
                            We're a resting place for the weary and a signpost for the lost. 
                            Join us as we grow together in faith, community, and purpose.
                        </p>
                        <div class="flex space-x-4">
                            <a href="{{ route('public.events') }}" class="text-gray-400 hover:text-white transition-colors">
                                Events
                            </a>
                            <a href="{{ route('public.lifegroups') }}" class="text-gray-400 hover:text-white transition-colors">
                                LifeGroups
                            </a>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-4">Quick Links</h3>
                        <ul class="space-y-2">
                            <li><a href="{{ route('login') }}" class="text-gray-300 hover:text-white text-sm transition-colors">Login</a></li>
                            <li><a href="{{ route('register') }}" class="text-gray-300 hover:text-white text-sm transition-colors">Register</a></li>
                            <li><a href="{{ route('public.guest-register') }}" class="text-gray-300 hover:text-white text-sm transition-colors">Guest Registration</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Copyright -->
                <div class="border-t border-gray-700 mt-8 pt-8 text-center">
                    <p class="text-gray-400 text-sm">© LifePointe Church 2025. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>

    <!-- Registration Modal -->
    <div id="registrationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="registrationModalTitle">Register for Event</h3>
                    <button onclick="closeRegistrationModal()" class="text-gray-400 hover:text-gray-600">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form id="registrationForm" onsubmit="handleRegistrationSubmit(event)" class="space-y-4">
                    <input type="hidden" id="eventId" name="event_id">
                    
                    <div id="customFormFields">
                        <!-- Form fields will be generated here -->
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeRegistrationModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            Complete Registration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let currentEvent = null;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadBranches();
            loadEvents();
            loadLocations();
            
            // Add event listeners for filters
            document.getElementById('searchInput').addEventListener('input', debounce(loadEvents, 300));
            document.getElementById('branchFilter').addEventListener('change', loadEvents);
            document.getElementById('dateFilter').addEventListener('change', loadEvents);
            document.getElementById('serviceTypeFilter').addEventListener('change', loadEvents);
        });

        async function loadBranches() {
            try {
                const response = await fetch('/api/welcome/branches');
                const branches = await response.json();
                
                const branchFilter = document.getElementById('branchFilter');
                branchFilter.innerHTML = '<option value="">All Expressions</option>';
                
                branches.forEach(branch => {
                    const option = document.createElement('option');
                    option.value = branch.id;
                    option.textContent = branch.name;
                    branchFilter.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading branches:', error);
            }
        }

        async function loadEvents() {
            try {
                document.getElementById('loadingState').classList.remove('hidden');
                document.getElementById('noEventsState').classList.add('hidden');
                
                const params = new URLSearchParams();

                // Add filters
                const search = document.getElementById('searchInput').value;
                if (search) params.append('q', search);

                const branchId = document.getElementById('branchFilter').value;
                if (branchId) params.append('branch_id', branchId);

                const dateFilter = document.getElementById('dateFilter').value;
                if (dateFilter) {
                    if (dateFilter === 'today') {
                        params.append('when', 'today');
                    } else if (dateFilter === 'week') {
                        params.append('when', 'this_week');
                    } else if (dateFilter === 'month') {
                        params.append('when', 'upcoming');
                    }
                } else {
                    params.append('when', 'upcoming');
                }

                const serviceType = document.getElementById('serviceTypeFilter').value;
                if (serviceType) params.append('service_type', serviceType);

                const response = await fetch(`/api/welcome/events?${params}`);
                const events = await response.json();

                renderEventsGrid(events);
            } catch (error) {
                console.error('Error loading events:', error);
                showNotification('Error loading events', 'error');
            } finally {
                document.getElementById('loadingState').classList.add('hidden');
            }
        }

        async function loadLocations() {
            try {
                const response = await fetch('/api/welcome/branches');
                const branches = await response.json();
                
                const locationsGrid = document.getElementById('locationsGrid');
                locationsGrid.innerHTML = branches.map(branch => `
                    <div class="text-gray-300">
                        <h4 class="font-semibold text-white mb-2">${branch.name}</h4>
                        <p class="text-sm mb-1">${branch.venue || ''}</p>
                        <p class="text-xs text-gray-400">${branch.service_time ? 'Service: ' + branch.service_time : ''}</p>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error loading locations:', error);
            }
        }

        function renderEventsGrid(events) {
            const grid = document.getElementById('eventsGrid');
            
            if (events.length === 0) {
                grid.innerHTML = '';
                document.getElementById('noEventsState').classList.remove('hidden');
                return;
            }

            document.getElementById('noEventsState').classList.add('hidden');
            
            grid.innerHTML = events.map(event => {
                const eventDate = new Date(event.start_date);
                const formattedDate = eventDate.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                const formattedTime = event.start_time || '';

                return `
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="text-lg font-semibold text-gray-900">${event.name}</h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    ${event.service_type || 'Event'}
                                </span>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4">${event.branch?.name || 'Join us for this exciting event!'}</p>
                            
                            <div class="space-y-2 text-sm text-gray-500 mb-4">
                                <div class="flex items-center">
                                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    ${formattedDate}
                                </div>
                                ${formattedTime ? `
                                    <div class="flex items-center">
                                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        ${formattedTime}
                                    </div>
                                ` : ''}
                                ${event.location ? `
                                    <div class="flex items-center">
                                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        ${event.location}
                                    </div>
                                ` : ''}
                            </div>
                            
                            <button 
                                onclick="openRegistrationModal(${JSON.stringify(event).replace(/"/g, '&quot;')})"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md text-sm font-medium transition-colors"
                            >
                                Register Now
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function openRegistrationModal(event) {
            currentEvent = event;
            document.getElementById('registrationModalTitle').textContent = `Register for ${event.name}`;
            document.getElementById('eventId').value = event.id;
            
            // Generate form fields
            const fieldsContainer = document.getElementById('customFormFields');
            let formFields = [];
            
            try {
                // Handle both cases: already parsed array or JSON string
                if (Array.isArray(event.custom_form_fields)) {
                    formFields = event.custom_form_fields;
                } else if (typeof event.custom_form_fields === 'string') {
                    formFields = JSON.parse(event.custom_form_fields || '[]');
                } else if (event.custom_form_fields) {
                    formFields = Object.values(event.custom_form_fields);
                } else {
                    formFields = [];
                }
            } catch (e) {
                console.error('Error parsing custom form fields:', e);
                formFields = [];
            }
            
            let formHTML = '';
            
            // Always add standard fields for public registration
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
            
            fieldsContainer.innerHTML = formHTML;
            document.getElementById('registrationModal').classList.remove('hidden');
        }

        function closeRegistrationModal() {
            document.getElementById('registrationModal').classList.add('hidden');
            document.getElementById('registrationForm').reset();
            currentEvent = null;
        }

        async function handleRegistrationSubmit(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const eventId = formData.get('event_id');
            
            // Prepare registration data
            let registrationData = {
                event_id: eventId,
                name: formData.get('name'),
                email: formData.get('email'),
                phone: formData.get('phone')
            };
            
            // Collect custom fields
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
            
            try {
                const response = await fetch(`/public-api/events/${eventId}/register`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(registrationData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Registration successful! An account has been created for you.', 'success');
                    closeRegistrationModal();
                    
                    // Show login info
                    if (data.data.user_created) {
                        setTimeout(() => {
                            showNotification('You can now log in with your email address to access more features.', 'info');
                        }, 3000);
                    }
                } else {
                    console.error('Registration failed:', data);
                    showNotification(data.message || 'Registration failed', 'error');
                    
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

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white' :
                type === 'error' ? 'bg-red-500 text-white' :
                'bg-blue-500 text-white'
            }`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 5000);
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
</body>
</html>

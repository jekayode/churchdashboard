<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Event Management') }}
            </h2>
            <button id="createEventBtn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                Create Event
            </button>
        </div>
    </x-slot>

    <style>
        /* Form Builder Styles */
        .form-field-item {
            transition: all 0.2s ease;
        }
        .form-field-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .drag-handle {
            cursor: grab;
        }
        .drag-handle:active {
            cursor: grabbing;
        }
        .form-builder-empty {
            background: linear-gradient(45deg, #f8f9fa 25%, transparent 25%), 
                        linear-gradient(-45deg, #f8f9fa 25%, transparent 25%), 
                        linear-gradient(45deg, transparent 75%, #f8f9fa 75%), 
                        linear-gradient(-45deg, transparent 75%, #f8f9fa 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
        }
        .field-type-icon {
            width: 16px;
            height: 16px;
        }
        .add-option-btn {
            transition: all 0.15s ease;
        }
        .add-option-btn:hover {
            transform: scale(1.05);
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" id="statisticsCards">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-indigo-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Events</dt>
                                    <dd class="text-lg font-medium text-gray-900" id="totalEvents">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Active Events</dt>
                                    <dd class="text-lg font-medium text-gray-900" id="activeEvents">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Registrations</dt>
                                    <dd class="text-lg font-medium text-gray-900" id="totalRegistrations">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Avg. Attendance</dt>
                                    <dd class="text-lg font-medium text-gray-900" id="avgAttendance">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Events</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input type="text" id="searchInput" placeholder="Search events..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div id="branchFilterDiv" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                            <select id="branchFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Branches</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
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
                                <option value="">All Dates</option>
                                <option value="upcoming">Upcoming</option>
                                <option value="this_week">This Week</option>
                                <option value="this_month">This Month</option>
                                <option value="past">Past Events</option>
                            </select>
                        </div>
                    </div>

                    <!-- Events Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                                    <th id="branchColumnHeader" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden">Branch</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrations</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="eventsTableBody" class="bg-white divide-y divide-gray-200">
                                <!-- Events will be loaded here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div id="pagination" class="mt-6 flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <button id="prevPageMobile" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</button>
                            <button id="nextPageMobile" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</button>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing <span id="showingFrom" class="font-medium">1</span> to <span id="showingTo" class="font-medium">10</span> of <span id="totalRecords" class="font-medium">0</span> results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" id="paginationNav">
                                    <!-- Pagination buttons will be generated here -->
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Event Modal -->
    <div id="eventModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Create New Event</h3>
                    <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="eventForm" class="space-y-6">
                    <input type="hidden" id="eventId" name="id">
                    
                    <div id="branchSelectionDiv" class="hidden mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Branch *</label>
                        <select id="eventBranch" name="branch_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Branch</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Event Name *</label>
                            <input type="text" id="eventName" name="name" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Event Type *</label>
                            <select id="eventType" name="type" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select Type</option>
                                <option value="service">Service</option>
                                <option value="conference">Conference</option>
                                <option value="workshop">Workshop</option>
                                <option value="outreach">Outreach</option>
                                <option value="social">Social</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <!-- Service Details (shown when event type is "service") -->
                    <div id="serviceDetailsDiv" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Service Type *</label>
                        <select id="serviceType" name="service_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Service Type</option>
                            <option value="Sunday Service">Sunday Service</option>
                            <option value="MidWeek">Midweek Service</option>
                            <option value="Conferences">Conferences</option>
                            <option value="Outreach">Outreach</option>
                            <option value="Evangelism (Beautiful Feet)">Evangelism (Beautiful Feet)</option>
                            <option value="Water Baptism">Water Baptism</option>
                            <option value="TECi">TECi</option>
                            <option value="Membership Class">Membership Class</option>
                            <option value="LifeGroup Meeting">LifeGroup Meeting</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="eventDescription" name="description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date & Time *</label>
                            <input type="datetime-local" id="eventStartDateTime" name="start_date_time" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">End Date & Time</label>
                            <input type="datetime-local" id="eventEndDateTime" name="end_date_time"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location *</label>
                            <input type="text" id="eventLocation" name="location" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Capacity</label>
                            <input type="number" id="eventCapacity" name="max_capacity" min="1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <!-- Recurring Event Settings -->
                    <div class="border-t pt-6">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Event Settings</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex items-center">
                                <input type="checkbox" id="isRecurring" name="is_recurring" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="isRecurring" class="ml-2 block text-sm text-gray-900">Make this a recurring event</label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select id="eventStatus" name="status"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <!-- Recurring Event Configuration -->
                        <div id="recurringConfigDiv" class="hidden mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <h5 class="text-sm font-medium text-blue-900 mb-3">Recurring Event Configuration</h5>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Frequency</label>
                                    <select id="recurrenceFrequency" name="frequency" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="quarterly">Quarterly</option>
                                        <option value="annually">Annually</option>
                                        <option value="recurrent">Recurrent</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                    <input type="date" id="recurrenceEndDate" name="recurrence_end_date"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Occurrences</label>
                                    <input type="number" id="maxOccurrences" name="max_occurrences" min="1" max="100"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                           placeholder="Leave blank for no limit">
                                </div>
                            </div>
                        </div>

                        <!-- Sunday Service Multiple Services -->
                        <div id="sundayServiceConfigDiv" class="hidden mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <h5 class="text-sm font-medium text-green-900 mb-3">Sunday Service Configuration</h5>
                            
                            <div class="mb-4">
                                <div class="flex items-center">
                                    <input type="checkbox" id="hasMultipleServices" name="has_multiple_services" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label for="hasMultipleServices" class="ml-2 block text-sm text-gray-900">This Sunday has multiple services</label>
                                </div>
                            </div>

                            <!-- First Service -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Service Name</label>
                                    <input type="text" id="serviceName" name="service_name" placeholder="e.g., First Service"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Service Time</label>
                                    <input type="time" id="serviceTime" name="service_time"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                                    <input type="time" id="serviceEndTime" name="service_end_time"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>

                            <!-- Second Service (shown when multiple services is checked) -->
                            <div id="secondServiceDiv" class="hidden">
                                <h6 class="text-sm font-medium text-gray-700 mb-3">Second Service</h6>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Service Name</label>
                                        <input type="text" id="secondServiceName" name="second_service_name" placeholder="e.g., Second Service"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Service Time</label>
                                        <input type="time" id="secondServiceTime" name="second_service_time"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                                        <input type="time" id="secondServiceEndTime" name="second_service_end_time"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Registration Type *</label>
                            <select id="registrationType" name="registration_type" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="none">No Registration Required</option>
                                <option value="simple">Simple Registration</option>
                                <option value="form">Custom Form</option>
                                <option value="link">External Link</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Day of Week (for recurring events)</label>
                            <select id="dayOfWeek" name="day_of_week"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select Day</option>
                                <option value="0">Sunday</option>
                                <option value="1">Monday</option>
                                <option value="2">Tuesday</option>
                                <option value="3">Wednesday</option>
                                <option value="4">Thursday</option>
                                <option value="5">Friday</option>
                                <option value="6">Saturday</option>
                            </select>
                        </div>
                    </div>

                    <div id="registrationLinkDiv" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Registration Link</label>
                        <input type="url" id="registrationLink" name="registration_link"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div id="customFormDiv" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-4">
                            Custom Registration Form Fields
                        </label>
                        
                        <!-- Form Builder Interface -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-sm font-medium text-gray-700">Form Fields</h4>
                                <button type="button" id="addFieldBtn" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                    + Add Field
                                </button>
                            </div>
                            
                            <!-- Form Fields Container -->
                            <div id="formFieldsContainer" class="space-y-3">
                                <!-- Fields will be dynamically added here -->
                            </div>
                            
                            <!-- Empty State -->
                            <div id="emptyFieldsState" class="text-center text-gray-500 py-8">
                                <p>No custom fields added yet.</p>
                                <p class="text-sm">Click "Add Field" to create your registration form.</p>
                            </div>
                        </div>
                        
                        <!-- Form Preview -->
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Form Preview</h4>
                            <div id="formPreview" class="space-y-3">
                                <p class="text-gray-500 text-sm">Preview will appear here as you add fields</p>
                            </div>
                        </div>
                        
                        <!-- Hidden input for JSON data -->
                        <input type="hidden" id="customFormFields" name="custom_form_fields" />
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="isPublic" name="is_public" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="isPublic" class="ml-2 block text-sm text-gray-900">Make this event public</label>
                    </div>

                    <div class="flex justify-end space-x-3 pt-6">
                        <button type="button" id="cancelBtn" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700">
                            <span id="submitBtnText">Create Event</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Event Modal -->
    <div id="viewEventModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="viewEventTitle">Event Details</h3>
                    <button id="closeViewModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div id="eventDetails" class="space-y-4">
                    <!-- Event details will be loaded here -->
                </div>

                <div class="flex justify-end space-x-3 pt-6">
                    <button id="editEventBtn" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700">
                        Edit Event
                    </button>
                    <button id="viewRegistrationsBtn" class="px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700">
                        View Registrations
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let totalPages = 1;
        let currentEventId = null;
        let events = [];
        let branches = [];
        let isSuperAdmin = {{ $isSuperAdmin ? 'true' : 'false' }};

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Show/hide branch-related elements based on role
            if (isSuperAdmin) {
                document.getElementById('branchFilterDiv').classList.remove('hidden');
                document.getElementById('branchColumnHeader').classList.remove('hidden');
                document.getElementById('branchSelectionDiv').classList.remove('hidden');
                document.getElementById('eventBranch').setAttribute('required', 'required');
                loadBranches();
            } else {
                // Remove required attribute for non-super admin users
                document.getElementById('eventBranch').removeAttribute('required');
            }
            
            loadStatistics();
            loadEvents();
            setupEventListeners();
        });

        function setupEventListeners() {
            // Modal controls - add null checks to prevent errors
            const createEventBtn = document.getElementById('createEventBtn');
            const closeModal = document.getElementById('closeModal');
            const cancelBtn = document.getElementById('cancelBtn');
            const closeViewModal = document.getElementById('closeViewModal');
            
            if (createEventBtn) createEventBtn.addEventListener('click', () => openEventModal());
            if (closeModal) closeModal.addEventListener('click', () => closeEventModal());
            if (cancelBtn) cancelBtn.addEventListener('click', () => closeEventModal());
            if (closeViewModal) closeViewModal.addEventListener('click', () => closeViewEventModal());

            // Form submission
            const eventForm = document.getElementById('eventForm');
            if (eventForm) eventForm.addEventListener('submit', handleEventSubmit);

            // Filters - add null checks
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
            
            // Branch filter (only for super admin)
            const branchFilter = document.getElementById('branchFilter');
            if (branchFilter) {
                branchFilter.addEventListener('change', () => {
                    loadEvents();
                    loadStatistics();
                });
            }

            // Registration type change
            const registrationType = document.getElementById('registrationType');
            if (registrationType) {
                registrationType.addEventListener('change', function() {
                    const type = this.value;
                    const linkDiv = document.getElementById('registrationLinkDiv');
                    const formDiv = document.getElementById('customFormDiv');
                    
                    if (linkDiv) linkDiv.classList.toggle('hidden', type !== 'link');
                    if (formDiv) formDiv.classList.toggle('hidden', type !== 'form');
                });
            }

            // Event type change - show service details and Sunday Service config if type is service
            const eventType = document.getElementById('eventType');
            if (eventType) {
                eventType.addEventListener('change', function() {
                    const type = this.value;
                    const serviceDetailsDiv = document.getElementById('serviceDetailsDiv');
                    const sundayServiceDiv = document.getElementById('sundayServiceConfigDiv');
                    const serviceType = document.getElementById('serviceType');
                    const hasMultipleServices = document.getElementById('hasMultipleServices');
                    const secondServiceDiv = document.getElementById('secondServiceDiv');
                    
                    if (type === 'service') {
                        if (serviceDetailsDiv) serviceDetailsDiv.classList.remove('hidden');
                        // Set required attribute for service type
                        if (serviceType) serviceType.setAttribute('required', 'required');
                    } else {
                        if (serviceDetailsDiv) serviceDetailsDiv.classList.add('hidden');
                        if (sundayServiceDiv) sundayServiceDiv.classList.add('hidden');
                        // Remove required attribute for service type
                        if (serviceType) {
                            serviceType.removeAttribute('required');
                            serviceType.value = '';
                        }
                        // Reset service fields
                        if (hasMultipleServices) hasMultipleServices.checked = false;
                        if (secondServiceDiv) secondServiceDiv.classList.add('hidden');
                    }
                });
            }

            // Service type change - show Sunday Service config if service type is Sunday Service
            const serviceType = document.getElementById('serviceType');
            if (serviceType) {
                serviceType.addEventListener('change', function() {
                    const serviceTypeValue = this.value;
                    const sundayServiceDiv = document.getElementById('sundayServiceConfigDiv');
                    const hasMultipleServices = document.getElementById('hasMultipleServices');
                    const secondServiceDiv = document.getElementById('secondServiceDiv');
                    
                    if (serviceTypeValue === 'Sunday Service') {
                        if (sundayServiceDiv) sundayServiceDiv.classList.remove('hidden');
                    } else {
                        if (sundayServiceDiv) sundayServiceDiv.classList.add('hidden');
                        // Reset Sunday Service fields
                        if (hasMultipleServices) hasMultipleServices.checked = false;
                        if (secondServiceDiv) secondServiceDiv.classList.add('hidden');
                    }
                });
            }

            // Recurring event checkbox
            const isRecurring = document.getElementById('isRecurring');
            if (isRecurring) {
                isRecurring.addEventListener('change', function() {
                    const recurringDiv = document.getElementById('recurringConfigDiv');
                    if (recurringDiv) recurringDiv.classList.toggle('hidden', !this.checked);
                    
                    // Set day of week based on start date if recurring is enabled
                    if (this.checked) {
                        const startDateTime = document.getElementById('eventStartDateTime');
                        const dayOfWeek = document.getElementById('dayOfWeek');
                        if (startDateTime && startDateTime.value && dayOfWeek) {
                            const date = new Date(startDateTime.value);
                            dayOfWeek.value = date.getDay();
                        }
                    }
                });
            }

            // Multiple services checkbox
            const hasMultipleServices = document.getElementById('hasMultipleServices');
            if (hasMultipleServices) {
                hasMultipleServices.addEventListener('change', function() {
                    const secondServiceDiv = document.getElementById('secondServiceDiv');
                    if (secondServiceDiv) secondServiceDiv.classList.toggle('hidden', !this.checked);
                });
            }

            // Set day of week automatically when start date changes
            const eventStartDateTime = document.getElementById('eventStartDateTime');
            if (eventStartDateTime) {
                eventStartDateTime.addEventListener('change', function() {
                    const isRecurringEl = document.getElementById('isRecurring');
                    const dayOfWeekEl = document.getElementById('dayOfWeek');
                    if (isRecurringEl && isRecurringEl.checked && this.value && dayOfWeekEl) {
                        const date = new Date(this.value);
                        dayOfWeekEl.value = date.getDay();
                    }
                });
            }

            // Form builder functionality
            const addFieldBtn = document.getElementById('addFieldBtn');
            if (addFieldBtn) addFieldBtn.addEventListener('click', addFormField);
        }

        // Form Builder Functions
        let formFields = [];
        let fieldCounter = 0;

        function addFormField() {
            fieldCounter++;
            const fieldId = `field_${fieldCounter}`;
            
            const field = {
                id: fieldId,
                name: '',
                label: '',
                type: 'text',
                required: false
            };
            
            formFields.push(field);
            renderFormField(field);
            updateFormPreview();
            updateEmptyState();
        }

        function renderFormField(field) {
            const container = document.getElementById('formFieldsContainer');
            const fieldDiv = document.createElement('div');
            fieldDiv.className = 'bg-white border border-gray-200 rounded-lg p-4';
            fieldDiv.setAttribute('data-field-id', field.id);
            
            fieldDiv.innerHTML = `
                <div class="flex justify-between items-start mb-3">
                    <h5 class="text-sm font-medium text-gray-700">Field ${fieldCounter}</h5>
                    <div class="flex space-x-2">
                        <button type="button" onclick="moveFieldUp('${field.id}')" class="text-gray-400 hover:text-gray-600" title="Move up">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                        </button>
                        <button type="button" onclick="moveFieldDown('${field.id}')" class="text-gray-400 hover:text-gray-600" title="Move down">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <button type="button" onclick="removeFormField('${field.id}')" class="text-red-400 hover:text-red-600" title="Remove field">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Field Type</label>
                        <select onchange="updateFieldProperty('${field.id}', 'type', this.value)" class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="text" ${field.type === 'text' ? 'selected' : ''}>Text</option>
                            <option value="email" ${field.type === 'email' ? 'selected' : ''}>Email</option>
                            <option value="tel" ${field.type === 'tel' ? 'selected' : ''}>Phone</option>
                            <option value="number" ${field.type === 'number' ? 'selected' : ''}>Number</option>
                            <option value="textarea" ${field.type === 'textarea' ? 'selected' : ''}>Textarea</option>
                            <option value="select" ${field.type === 'select' ? 'selected' : ''}>Dropdown</option>
                            <option value="radio" ${field.type === 'radio' ? 'selected' : ''}>Radio Buttons</option>
                            <option value="checkbox" ${field.type === 'checkbox' ? 'selected' : ''}>Checkbox</option>
                            <option value="date" ${field.type === 'date' ? 'selected' : ''}>Date</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Field Name</label>
                        <input type="text" value="${field.name}" onchange="updateFieldProperty('${field.id}', 'name', this.value)" 
                               placeholder="field_name" class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Field Label</label>
                        <input type="text" value="${field.label}" onchange="updateFieldProperty('${field.id}', 'label', this.value)" 
                               placeholder="Field Label" class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" ${field.required ? 'checked' : ''} onchange="updateFieldProperty('${field.id}', 'required', this.checked)" 
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label class="ml-2 block text-xs text-gray-700">Required field</label>
                    </div>
                </div>
                
                <div id="options_${field.id}" class="mt-3 ${field.type === 'select' || field.type === 'radio' ? '' : 'hidden'}">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Options (one per line)</label>
                    <textarea onchange="updateFieldProperty('${field.id}', 'options', this.value.split('\\n').filter(o => o.trim()))" 
                              placeholder="Option 1&#10;Option 2&#10;Option 3" rows="3"
                              class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">${(field.options || []).join('\n')}</textarea>
                </div>
            `;
            
            container.appendChild(fieldDiv);
        }

        function updateFieldProperty(fieldId, property, value) {
            const field = formFields.find(f => f.id === fieldId);
            if (field) {
                field[property] = value;
                
                // Auto-generate field name from label if name is empty
                if (property === 'label' && !field.name) {
                    field.name = value.toLowerCase().replace(/[^a-z0-9]/g, '_').replace(/_+/g, '_').replace(/^_|_$/g, '');
                    const nameInput = document.querySelector(`[data-field-id="${fieldId}"] input[placeholder="field_name"]`);
                    if (nameInput) nameInput.value = field.name;
                }
                
                // Show/hide options for select and radio fields
                if (property === 'type') {
                    const optionsDiv = document.getElementById(`options_${fieldId}`);
                    if (optionsDiv) {
                        optionsDiv.classList.toggle('hidden', value !== 'select' && value !== 'radio');
                    }
                }
                
                updateFormPreview();
                updateHiddenInput();
            }
        }

        function removeFormField(fieldId) {
            formFields = formFields.filter(f => f.id !== fieldId);
            const fieldDiv = document.querySelector(`[data-field-id="${fieldId}"]`);
            if (fieldDiv) fieldDiv.remove();
            updateFormPreview();
            updateEmptyState();
            updateHiddenInput();
        }

        function moveFieldUp(fieldId) {
            const index = formFields.findIndex(f => f.id === fieldId);
            if (index > 0) {
                [formFields[index], formFields[index - 1]] = [formFields[index - 1], formFields[index]];
                rerenderFormFields();
            }
        }

        function moveFieldDown(fieldId) {
            const index = formFields.findIndex(f => f.id === fieldId);
            if (index < formFields.length - 1) {
                [formFields[index], formFields[index + 1]] = [formFields[index + 1], formFields[index]];
                rerenderFormFields();
            }
        }

        function rerenderFormFields() {
            const container = document.getElementById('formFieldsContainer');
            container.innerHTML = '';
            formFields.forEach(field => renderFormField(field));
            updateFormPreview();
            updateHiddenInput();
        }

        function updateFormPreview() {
            const preview = document.getElementById('formPreview');
            
            if (formFields.length === 0) {
                preview.innerHTML = '<p class="text-gray-500 text-sm">Preview will appear here as you add fields</p>';
                return;
            }
            
            const previewHTML = formFields.map(field => {
                const required = field.required ? '<span class="text-red-500">*</span>' : '';
                let inputHTML = '';
                
                switch (field.type) {
                    case 'textarea':
                        inputHTML = `<textarea class="w-full px-3 py-2 border border-gray-300 rounded-md" rows="3" placeholder="Enter ${field.label}"></textarea>`;
                        break;
                    case 'select':
                        const options = (field.options || []).map(opt => `<option value="${opt}">${opt}</option>`).join('');
                        inputHTML = `<select class="w-full px-3 py-2 border border-gray-300 rounded-md"><option value="">Select ${field.label}</option>${options}</select>`;
                        break;
                    case 'radio':
                        const radioOptions = (field.options || []).map((opt, i) => 
                            `<label class="flex items-center space-x-2"><input type="radio" name="${field.name}" value="${opt}" class="text-blue-600"><span>${opt}</span></label>`
                        ).join('');
                        inputHTML = `<div class="space-y-2">${radioOptions}</div>`;
                        break;
                    case 'checkbox':
                        inputHTML = `<label class="flex items-center space-x-2"><input type="checkbox" class="text-blue-600"><span>${field.label}</span></label>`;
                        break;
                    default:
                        inputHTML = `<input type="${field.type}" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Enter ${field.label}">`;
                }
                
                return `
                    <div class="space-y-1">
                        ${field.type !== 'checkbox' ? `<label class="block text-sm font-medium text-gray-700">${field.label} ${required}</label>` : ''}
                        ${inputHTML}
                    </div>
                `;
            }).join('');
            
            preview.innerHTML = previewHTML;
        }

        function updateEmptyState() {
            const emptyState = document.getElementById('emptyFieldsState');
            emptyState.classList.toggle('hidden', formFields.length > 0);
        }

        function updateHiddenInput() {
            const hiddenInput = document.getElementById('customFormFields');
            const fieldsData = formFields.map(field => ({
                name: field.name || `field_${field.id}`,
                label: field.label || 'Untitled Field',
                type: field.type,
                required: field.required,
                ...(field.options && field.options.length > 0 ? { options: field.options } : {})
            }));
            hiddenInput.value = JSON.stringify(fieldsData);
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
                    date_filter: document.getElementById('dateFilter').value
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

        function openEventModal(event = null) {
            currentEventId = event ? event.id : null;
            const modal = document.getElementById('eventModal');
            const title = document.getElementById('modalTitle');
            const submitBtn = document.getElementById('submitBtnText');
            
            if (event) {
                title.textContent = 'Edit Event';
                submitBtn.textContent = 'Update Event';
                populateEventForm(event);
            } else {
                title.textContent = 'Create New Event';
                submitBtn.textContent = 'Create Event';
                document.getElementById('eventForm').reset();
                document.getElementById('eventId').value = '';
                clearFormBuilder();
            }
            
            modal.classList.remove('hidden');
        }

        function closeEventModal() {
            const eventModal = document.getElementById('eventModal');
            const eventForm = document.getElementById('eventForm');
            const registrationLinkDiv = document.getElementById('registrationLinkDiv');
            const customFormDiv = document.getElementById('customFormDiv');
            const recurringDetailsDiv = document.getElementById('recurringDetailsDiv');
            const serviceDetailsDiv = document.getElementById('serviceDetailsDiv');
            const multipleServicesDiv = document.getElementById('multipleServicesDiv');
            
            if (eventModal) eventModal.classList.add('hidden');
            if (eventForm) eventForm.reset();
            
            // Hide all conditional sections with null checks
            if (registrationLinkDiv) registrationLinkDiv.classList.add('hidden');
            if (customFormDiv) customFormDiv.classList.add('hidden');
            if (recurringDetailsDiv) recurringDetailsDiv.classList.add('hidden');
            if (serviceDetailsDiv) serviceDetailsDiv.classList.add('hidden');
            if (multipleServicesDiv) multipleServicesDiv.classList.add('hidden');
            
            clearFormBuilder();
            currentEventId = null;
        }

        function closeViewEventModal() {
            document.getElementById('viewEventModal').classList.add('hidden');
        }

        function populateEventForm(event) {
            document.getElementById('eventId').value = event.id;
            document.getElementById('eventName').value = event.name;
            document.getElementById('eventType').value = event.type;
            document.getElementById('eventDescription').value = event.description || '';
            const startDateTime = event.start_date_time || event.start_date;
            const endDateTime = event.end_date_time || event.end_date;
            document.getElementById('eventStartDateTime').value = startDateTime ? startDateTime.slice(0, 16) : '';
            document.getElementById('eventEndDateTime').value = endDateTime ? endDateTime.slice(0, 16) : '';
            document.getElementById('eventLocation').value = event.location;
            document.getElementById('eventCapacity').value = event.max_capacity || '';
            document.getElementById('registrationType').value = event.registration_type;
            document.getElementById('eventStatus').value = event.status;
            document.getElementById('registrationLink').value = event.registration_link || '';
            document.getElementById('isPublic').checked = event.is_public;

            // Set recurring event fields
            document.getElementById('isRecurring').checked = event.is_recurring || false;
            document.getElementById('recurrenceFrequency').value = event.frequency || 'weekly';
            document.getElementById('recurrenceEndDate').value = event.recurrence_end_date || '';
            document.getElementById('maxOccurrences').value = event.max_occurrences || '';
            
            // Debug logging for day_of_week
            console.log('Setting day_of_week in form:', {
                event_day_of_week: event.day_of_week,
                type: typeof event.day_of_week,
                converted_value: event.day_of_week || ''
            });
            
            const dayOfWeekSelect = document.getElementById('dayOfWeek');
            const dayOfWeekValue = event.day_of_week !== null && event.day_of_week !== undefined ? event.day_of_week.toString() : '';
            dayOfWeekSelect.value = dayOfWeekValue;
            
            // Verify the value was set correctly
            console.log('Day of week dropdown after setting:', {
                selected_value: dayOfWeekSelect.value,
                selected_text: dayOfWeekSelect.options[dayOfWeekSelect.selectedIndex]?.text || 'None'
            });

            // Set Service fields
            document.getElementById('serviceType').value = event.service_type || '';
            document.getElementById('serviceName').value = event.service_name || '';
            document.getElementById('serviceTime').value = event.service_time || '';
            document.getElementById('serviceEndTime').value = event.service_end_time || '';
            document.getElementById('hasMultipleServices').checked = event.has_multiple_services || false;
            document.getElementById('secondServiceName').value = event.second_service_name || '';
            document.getElementById('secondServiceTime').value = event.second_service_time || '';
            document.getElementById('secondServiceEndTime').value = event.second_service_end_time || '';

            // Load custom form fields into form builder
            loadCustomFormFields(event.custom_form_fields);

            // Set branch if super admin
            if (isSuperAdmin && event.branch_id) {
                document.getElementById('eventBranch').value = event.branch_id;
            }

            // Trigger form changes to show/hide relevant sections
            document.getElementById('registrationType').dispatchEvent(new Event('change'));
            document.getElementById('eventType').dispatchEvent(new Event('change'));
            document.getElementById('serviceType').dispatchEvent(new Event('change'));
            document.getElementById('isRecurring').dispatchEvent(new Event('change'));
            document.getElementById('hasMultipleServices').dispatchEvent(new Event('change'));
        }

        function loadCustomFormFields(customFormFieldsJson) {
            // Clear existing form fields
            formFields = [];
            fieldCounter = 0;
            document.getElementById('formFieldsContainer').innerHTML = '';
            
            if (customFormFieldsJson) {
                try {
                    // Handle both string and array inputs
                    let fields;
                    if (typeof customFormFieldsJson === 'string') {
                        // Skip parsing if it's an empty string or just "[]"
                        if (customFormFieldsJson.trim() === '' || customFormFieldsJson.trim() === '[]') {
                            fields = [];
                        } else {
                            fields = JSON.parse(customFormFieldsJson);
                        }
                    } else if (Array.isArray(customFormFieldsJson)) {
                        fields = customFormFieldsJson;
                    } else {
                        fields = [];
                    }
                    
                    if (Array.isArray(fields)) {
                        fields.forEach(fieldData => {
                            fieldCounter++;
                            const field = {
                                id: `field_${fieldCounter}`,
                                name: fieldData.name || '',
                                label: fieldData.label || '',
                                type: fieldData.type || 'text',
                                required: fieldData.required || false,
                                options: fieldData.options || []
                            };
                            formFields.push(field);
                            renderFormField(field);
                        });
                    }
                } catch (e) {
                    console.error('Error parsing custom form fields:', e);
                    console.log('Raw customFormFieldsJson:', customFormFieldsJson);
                    // Continue with empty fields instead of breaking
                }
            }
            
            updateFormPreview();
            updateEmptyState();
            updateHiddenInput();
        }

        function clearFormBuilder() {
            formFields = [];
            fieldCounter = 0;
            const formFieldsContainer = document.getElementById('formFieldsContainer');
            if (formFieldsContainer) {
                formFieldsContainer.innerHTML = '';
            }
            updateFormPreview();
            updateEmptyState();
            updateHiddenInput();
        }

        async function handleEventSubmit(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const eventData = Object.fromEntries(formData.entries());
            
            // Handle boolean fields explicitly
            eventData.is_public = document.getElementById('isPublic').checked;
            eventData.is_recurring = document.getElementById('isRecurring').checked;
            eventData.has_multiple_services = document.getElementById('hasMultipleServices').checked;
            
            // Debug logging
            console.log('Form data before cleanup:', {
                is_recurring: eventData.is_recurring,
                frequency: eventData.frequency,
                day_of_week: eventData.day_of_week,
                recurrence_end_date: eventData.recurrence_end_date,
                max_occurrences: eventData.max_occurrences
            });
            
            // For non-super admin users, the branch_id should be automatically set by the backend
            // Remove empty branch_id to let the backend handle it
            if (!isSuperAdmin && (!eventData.branch_id || eventData.branch_id === '')) {
                delete eventData.branch_id;
            }

            // Clean up recurring fields if not recurring
            if (!eventData.is_recurring) {
                delete eventData.frequency;
                delete eventData.recurrence_end_date;
                delete eventData.max_occurrences;
                delete eventData.day_of_week;
            } else {
                // For recurring events, ensure we have default values if fields are empty
                if (!eventData.frequency) {
                    eventData.frequency = 'weekly';
                }
                // Convert day_of_week to number if it's a string
                if (eventData.day_of_week !== undefined && eventData.day_of_week !== null && eventData.day_of_week !== '') {
                    eventData.day_of_week = parseInt(eventData.day_of_week);
                }
            }

            // Clean up service fields if not a service event
            if (eventData.type !== 'service') {
                delete eventData.service_type;
                delete eventData.service_name;
                delete eventData.service_time;
                delete eventData.service_end_time;
                delete eventData.has_multiple_services;
                delete eventData.second_service_name;
                delete eventData.second_service_time;
                delete eventData.second_service_end_time;
            }

            // Clean up multiple service fields if not enabled
            if (!eventData.has_multiple_services) {
                delete eventData.second_service_name;
                delete eventData.second_service_time;
                delete eventData.second_service_end_time;
            }
            
            // Debug logging - final data being sent
            console.log('Final event data being sent:', eventData);
            
            try {
                const url = currentEventId ? `/api/events/${currentEventId}` : '/api/events';
                const method = currentEventId ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(eventData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(currentEventId ? 'Event updated successfully' : 'Event created successfully', 'success');
                    closeEventModal();
                    loadEvents(currentPage);
                    loadStatistics();
                } else {
                    showNotification(data.message || 'Error saving event', 'error');
                }
            } catch (error) {
                console.error('Error saving event:', error);
                showNotification('Error saving event', 'error');
            }
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
                        editEvent(eventId);
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
            const event = events.find(e => e.id === eventId);
            if (event) {
                openEventModal(event);
            }
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
</x-app-layout> 
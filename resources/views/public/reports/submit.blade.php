<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Event Report - {{ $branch->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    Event Report Submission
                </h1>
                <p class="text-lg text-gray-600">
                    {{ $branch->name }}
                </p>
                <p class="text-sm text-gray-500 mt-2">
                    Submit your event report using the form below
                </p>
            </div>

            <!-- Success/Error/Info Messages -->
            <div id="messageContainer" class="hidden mb-6">
                <div id="successMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <strong>Success!</strong> <span id="successText"></span>
                </div>
                <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <strong>Error!</strong> <span id="errorText"></span>
                </div>
                <div id="infoMessage" class="hidden bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">
                    <strong>Info:</strong> <span id="infoText"></span>
                </div>
            </div>

            <!-- Report Form -->
            <div class="bg-white shadow-lg rounded-lg p-6" x-data="reportForm()" x-init="init()">
                <form @submit.prevent="submitReport" class="space-y-6">
                    <!-- Submitter Selection (for team tokens) -->
                    @if($token->isTeamToken())
                    <div class="border-b pb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Submitter Information</h3>
                        <div>
                            <label for="submitter_email" class="block text-sm font-medium text-gray-700 mb-2">
                                Select Your Role *
                            </label>
                            <select id="submitter_email" x-model="formData.submitter_email" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Choose your role...</option>
                                @foreach($token->getTeamMembers() as $email => $role)
                                    <option value="{{ $email }}">{{ $role }} ({{ $email }})</option>
                                @endforeach
                            </select>
                            <p class="text-sm text-gray-500 mt-1">
                                Please select your role from the team members authorized to submit reports.
                            </p>
                        </div>
                    </div>
                    @else
                    <!-- Individual token - show submitter info -->
                    <div class="border-b pb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Submitter Information</h3>
                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                            <p class="text-sm text-blue-800">
                                <strong>Token Owner:</strong> {{ $token->name }}
                                @if($token->email)
                                    <br><strong>Email:</strong> {{ $token->email }}
                                @endif
                            </p>
                        </div>
                    </div>
                    @endif

                    <!-- Event Selection -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="event_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Select Event *
                            </label>
                            <select id="event_id" x-model="formData.event_id" @change="loadEventDetails($event.target.value)" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Choose an event...</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}">
                                        {{ $event->name }} - {{ $event->start_date->format('M j, Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="event_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Report Date *
                            </label>
                            <input type="date" id="event_date" x-model="formData.event_date" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Event Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="event_type" class="block text-sm font-medium text-gray-700 mb-2">
                                Event Type *
                            </label>
                            <select id="event_type" x-model="formData.event_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Event Type</option>
                                <option value="Sunday Service">Sunday Service</option>
                                <option value="Mid-Week Service">Mid-Week Service</option>
                                <option value="Conferences">Conferences</option>
                                <option value="Outreach">Outreach</option>
                                <option value="Evangelism (Beautiful Feet)">Evangelism (Beautiful Feet)</option>
                                <option value="Water Baptism">Water Baptism</option>
                                <option value="TECi">TECi</option>
                                <option value="Membership Class">Membership Class</option>
                                <option value="LifeGroup Meeting">LifeGroup Meeting</option>
                                <option value="Prayer Meeting">Prayer Meeting</option>
                                <option value="Youth Service">Youth Service</option>
                                <option value="Women Ministry">Women Ministry</option>
                                <option value="Men Ministry">Men Ministry</option>
                                <option value="Children Service">Children Service</option>
                                <option value="Leadership Meeting">Leadership Meeting</option>
                                <option value="Community Outreach">Community Outreach</option>
                                <option value="Baby Dedication">Baby Dedication</option>
                                <option value="Holy Ghost Baptism">Holy Ghost Baptism</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label for="service_type" class="block text-sm font-medium text-gray-700 mb-2">
                                Service Type
                            </label>
                            <select id="service_type" x-model="formData.service_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                    </div>

                    <!-- Time Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
                                Start Time *
                            </label>
                            <input type="time" id="start_time" x-model="formData.start_time" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
                                End Time *
                            </label>
                            <input type="time" id="end_time" x-model="formData.end_time" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- First Service Attendance -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">First Service Attendance</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label for="male_attendance" class="block text-sm font-medium text-gray-700 mb-2">
                                    Male *
                                </label>
                                <input type="number" id="male_attendance" x-model="formData.male_attendance" required min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="female_attendance" class="block text-sm font-medium text-gray-700 mb-2">
                                    Female *
                                </label>
                                <input type="number" id="female_attendance" x-model="formData.female_attendance" required min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="children_attendance" class="block text-sm font-medium text-gray-700 mb-2">
                                    Children *
                                </label>
                                <input type="number" id="children_attendance" x-model="formData.children_attendance" required min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="online_attendance" class="block text-sm font-medium text-gray-700 mb-2">
                                    Online
                                </label>
                                <input type="number" id="online_attendance" x-model="formData.online_attendance" min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label for="first_time_guests" class="block text-sm font-medium text-gray-700 mb-2">
                                    First Time Guests *
                                </label>
                                <input type="number" id="first_time_guests" x-model="formData.first_time_guests" required min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="converts" class="block text-sm font-medium text-gray-700 mb-2">
                                    Converts *
                                </label>
                                <input type="number" id="converts" x-model="formData.converts" required min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="cars" class="block text-sm font-medium text-gray-700 mb-2">
                                    Number of Cars *
                                </label>
                                <input type="number" id="cars" x-model="formData.cars" required min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Second Service Toggle -->
                    <div class="border-t pt-6">
                        <div class="flex items-center mb-4">
                            <input type="checkbox" id="has_second_service" x-model="formData.has_second_service"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="has_second_service" class="ml-2 block text-sm font-medium text-gray-700">
                                This event had a second service
                            </label>
                        </div>

                        <!-- Second Service Details -->
                        <div x-show="formData.has_second_service" x-transition class="space-y-4">
                            <h4 class="text-md font-semibold text-gray-900">Second Service Details</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="second_service_start_time" class="block text-sm font-medium text-gray-700 mb-2">
                                        Start Time *
                                    </label>
                                    <input type="time" id="second_service_start_time" x-model="formData.second_service_start_time"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="second_service_end_time" class="block text-sm font-medium text-gray-700 mb-2">
                                        End Time *
                                    </label>
                                    <input type="time" id="second_service_end_time" x-model="formData.second_service_end_time"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <label for="second_male_attendance" class="block text-sm font-medium text-gray-700 mb-2">
                                        Male *
                                    </label>
                                    <input type="number" id="second_male_attendance" x-model="formData.second_male_attendance" min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="second_female_attendance" class="block text-sm font-medium text-gray-700 mb-2">
                                        Female *
                                    </label>
                                    <input type="number" id="second_female_attendance" x-model="formData.second_female_attendance" min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="second_children_attendance" class="block text-sm font-medium text-gray-700 mb-2">
                                        Children *
                                    </label>
                                    <input type="number" id="second_children_attendance" x-model="formData.second_children_attendance" min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="second_online_attendance" class="block text-sm font-medium text-gray-700 mb-2">
                                        Online
                                    </label>
                                    <input type="number" id="second_online_attendance" x-model="formData.second_online_attendance" min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="second_first_time_guests" class="block text-sm font-medium text-gray-700 mb-2">
                                        First Time Guests *
                                    </label>
                                    <input type="number" id="second_first_time_guests" x-model="formData.second_first_time_guests" min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="second_converts" class="block text-sm font-medium text-gray-700 mb-2">
                                        Converts *
                                    </label>
                                    <input type="number" id="second_converts" x-model="formData.second_converts" min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="second_cars" class="block text-sm font-medium text-gray-700 mb-2">
                                        Number of Cars *
                                    </label>
                                    <input type="number" id="second_cars" x-model="formData.second_cars" min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            <div>
                                <label for="second_service_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Second Service Notes
                                </label>
                                <textarea id="second_service_notes" x-model="formData.second_service_notes" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Any additional notes about the second service..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            General Notes
                        </label>
                        <textarea id="notes" x-model="formData.notes" rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Any additional notes about the event..."></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-center pt-6">
                        <button type="submit" :disabled="isSubmitting"
                            class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting">Submit Report</span>
                            <span x-show="isSubmitting">Submitting...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Existing reports data for auto-population
        const existingReports = @json($existingReports);
        
        function reportForm() {
            return {
                isSubmitting: false,
                formData: {
                    submitter_email: '',
                    event_id: '',
                    event_date: '',
                    event_type: '',
                    service_type: '',
                    start_time: '',
                    end_time: '',
                    male_attendance: 0,
                    female_attendance: 0,
                    children_attendance: 0,
                    online_attendance: 0,
                    first_time_guests: 0,
                    converts: 0,
                    cars: 0,
                    has_second_service: false,
                    second_service_start_time: '',
                    second_service_end_time: '',
                    second_male_attendance: 0,
                    second_female_attendance: 0,
                    second_children_attendance: 0,
                    second_online_attendance: 0,
                    second_first_time_guests: 0,
                    second_converts: 0,
                    second_cars: 0,
                    second_service_notes: '',
                    notes: ''
                },

                init() {
                    // Initialize form without auto-population
                    // Auto-population will happen when an event is selected
                },

                autoPopulateFormForEvent(eventId) {
                    // Check if there are existing reports for this specific event
                    if (existingReports[eventId] && existingReports[eventId].length > 0) {
                        // Get the most recent report for this event
                        const mostRecentReport = existingReports[eventId][0];
                        
                        if (mostRecentReport) {
                            console.log('Auto-populating form with existing report for event:', eventId, mostRecentReport);
                            
                            // Populate basic fields
                            this.formData.event_date = mostRecentReport.report_date;
                            this.formData.event_type = mostRecentReport.event_type;
                            this.formData.service_type = mostRecentReport.service_type;
                            this.formData.start_time = this.formatTime(mostRecentReport.start_time);
                            this.formData.end_time = this.formatTime(mostRecentReport.end_time);
                            
                            // Populate first service attendance
                            this.formData.male_attendance = mostRecentReport.attendance_male || 0;
                            this.formData.female_attendance = mostRecentReport.attendance_female || 0;
                            this.formData.children_attendance = mostRecentReport.attendance_children || 0;
                            this.formData.online_attendance = mostRecentReport.attendance_online || 0;
                            this.formData.first_time_guests = mostRecentReport.first_time_guests || 0;
                            this.formData.converts = mostRecentReport.converts || 0;
                            this.formData.cars = mostRecentReport.number_of_cars || 0;
                            
                            // Populate second service if it exists
                            if (mostRecentReport.is_multi_service) {
                                this.formData.has_second_service = true;
                                this.formData.second_service_start_time = this.formatTime(mostRecentReport.second_service_start_time);
                                this.formData.second_service_end_time = this.formatTime(mostRecentReport.second_service_end_time);
                                this.formData.second_male_attendance = mostRecentReport.second_service_attendance_male || 0;
                                this.formData.second_female_attendance = mostRecentReport.second_service_attendance_female || 0;
                                this.formData.second_children_attendance = mostRecentReport.second_service_attendance_children || 0;
                                this.formData.second_online_attendance = mostRecentReport.second_service_attendance_online || 0;
                                this.formData.second_first_time_guests = mostRecentReport.second_service_first_time_guests || 0;
                                this.formData.second_converts = mostRecentReport.second_service_converts || 0;
                                this.formData.second_cars = mostRecentReport.second_service_number_of_cars || 0;
                                this.formData.second_service_notes = mostRecentReport.second_service_notes || '';
                            } else {
                                // Reset second service fields if not multi-service
                                this.formData.has_second_service = false;
                                this.formData.second_service_start_time = '';
                                this.formData.second_service_end_time = '';
                                this.formData.second_male_attendance = 0;
                                this.formData.second_female_attendance = 0;
                                this.formData.second_children_attendance = 0;
                                this.formData.second_online_attendance = 0;
                                this.formData.second_first_time_guests = 0;
                                this.formData.second_converts = 0;
                                this.formData.second_cars = 0;
                                this.formData.second_service_notes = '';
                            }
                            
                            this.formData.notes = mostRecentReport.notes || '';
                            
                            // Show a message to the user
                            this.showMessage('info', 'Form pre-populated with existing report data for this event. You can modify any fields as needed.');
                        }
                    } else {
                        // No existing report for this event, reset form to defaults
                        this.resetFormToDefaults();
                        this.showMessage('info', 'No existing report found for this event. Please fill in the form with new data.');
                    }
                },

                resetFormToDefaults() {
                    // Reset form to default values
                    this.formData.event_date = '';
                    this.formData.event_type = '';
                    this.formData.service_type = '';
                    this.formData.start_time = '';
                    this.formData.end_time = '';
                    this.formData.male_attendance = 0;
                    this.formData.female_attendance = 0;
                    this.formData.children_attendance = 0;
                    this.formData.online_attendance = 0;
                    this.formData.first_time_guests = 0;
                    this.formData.converts = 0;
                    this.formData.cars = 0;
                    this.formData.has_second_service = false;
                    this.formData.second_service_start_time = '';
                    this.formData.second_service_end_time = '';
                    this.formData.second_male_attendance = 0;
                    this.formData.second_female_attendance = 0;
                    this.formData.second_children_attendance = 0;
                    this.formData.second_online_attendance = 0;
                    this.formData.second_first_time_guests = 0;
                    this.formData.second_converts = 0;
                    this.formData.second_cars = 0;
                    this.formData.second_service_notes = '';
                    this.formData.notes = '';
                },

                formatTime(datetimeString) {
                    if (!datetimeString) return '';
                    const date = new Date(datetimeString);
                    return date.toTimeString().slice(0, 5); // HH:MM format
                },

                async loadEventDetails(eventId) {
                    if (!eventId) {
                        // Reset form when no event is selected
                        this.resetFormToDefaults();
                        return;
                    }
                    
                    try {
                        const response = await fetch(`/public/reports/events/{{ $token->token }}`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            const event = result.data.find(e => e.id == eventId);
                            if (event) {
                                // Auto-populate event type and service type from the event
                                this.formData.event_type = event.service_type || '';
                                this.formData.service_type = event.service_type || '';
                                
                                // Auto-populate form with existing report data for this event
                                this.autoPopulateFormForEvent(eventId);
                            }
                        }
                    } catch (error) {
                        console.error('Error loading event details:', error);
                        this.showMessage('error', 'Failed to load event details. Please try again.');
                    }
                },

                async submitReport() {
                    this.isSubmitting = true;
                    
                    try {
                        const response = await fetch(`/public/reports/submit/{{ $token->token }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.formData)
                        });

                        const result = await response.json();

                        if (result.success) {
                            this.showMessage('success', result.message);
                            // Reset form
                            this.formData = {
                                submitter_email: '',
                                event_id: '',
                                event_date: '',
                                event_type: '',
                                service_type: '',
                                start_time: '',
                                end_time: '',
                                male_attendance: 0,
                                female_attendance: 0,
                                children_attendance: 0,
                                online_attendance: 0,
                                first_time_guests: 0,
                                converts: 0,
                                cars: 0,
                                has_second_service: false,
                                second_service_start_time: '',
                                second_service_end_time: '',
                                second_male_attendance: 0,
                                second_female_attendance: 0,
                                second_children_attendance: 0,
                                second_online_attendance: 0,
                                second_first_time_guests: 0,
                                second_converts: 0,
                                second_cars: 0,
                                second_service_notes: '',
                                notes: ''
                            };
                        } else {
                            this.showMessage('error', result.message || 'An error occurred while submitting the report.');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showMessage('error', 'An error occurred while submitting the report. Please try again.');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                showMessage(type, message) {
                    const container = document.getElementById('messageContainer');
                    const successDiv = document.getElementById('successMessage');
                    const errorDiv = document.getElementById('errorMessage');
                    const infoDiv = document.getElementById('infoMessage');
                    const successText = document.getElementById('successText');
                    const errorText = document.getElementById('errorText');
                    const infoText = document.getElementById('infoText');

                    // Hide all messages first
                    successDiv.classList.add('hidden');
                    errorDiv.classList.add('hidden');
                    infoDiv.classList.add('hidden');
                    container.classList.add('hidden');

                    if (type === 'success') {
                        successText.textContent = message;
                        successDiv.classList.remove('hidden');
                    } else if (type === 'error') {
                        errorText.textContent = message;
                        errorDiv.classList.remove('hidden');
                    } else if (type === 'info') {
                        infoText.textContent = message;
                        infoDiv.classList.remove('hidden');
                    }

                    container.classList.remove('hidden');

                    // Scroll to top to show message
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        }
    </script>
</body>
</html>

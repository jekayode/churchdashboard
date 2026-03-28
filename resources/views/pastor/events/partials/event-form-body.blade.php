                @php
                    $editingEvent = $event ?? null;
                @endphp
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Public URL slug *</label>
                            <input type="text" id="eventPublicSlug" name="public_slug" required
                                   pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                                   title="Lowercase letters, numbers, and hyphens only"
                                   placeholder="e.g. easter-potluck"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm">
                            <p class="mt-1 text-xs text-gray-500">Public page: <span class="font-mono">/event/<span id="branchCodeInSlugHint">…</span>/</span><span class="font-mono" id="slugPreviewTail">your-slug</span>. Changing the slug later breaks old links and printed QR codes.</p>
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

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Event image (optional)</label>
                        <input type="file" id="coverImage" name="cover_image" accept="image/jpeg,image/png,image/webp,image/gif"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <div id="coverImagePreview" class="mt-3 hidden">
                            <p class="text-xs text-gray-500 mb-1">Current image</p>
                            <img id="coverImagePreviewImg" src="" alt="" class="h-20 w-20 object-cover rounded-md border border-gray-200 shrink-0">
                        </div>
                        <div id="removeCoverWrap" class="mt-2 hidden">
                            <label class="inline-flex items-center text-sm text-gray-700">
                                <input type="checkbox" id="removeCover" name="remove_cover" value="1" class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                <span class="ml-2">Remove current image</span>
                            </label>
                        </div>
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
                        <a href="{{ route('pastor.events') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700">
                            <span id="submitBtnText">{{ $editingEvent ? 'Update Event' : 'Create Event' }}</span>
                        </button>
                    </div>
                </form>

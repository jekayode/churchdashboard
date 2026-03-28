    <script>
        let currentEventId = null;
        let branches = [];
        let isSuperAdmin = {{ $isSuperAdmin ? 'true' : 'false' }};
        let originalPublicSlug = '';
        const eventsIndexUrl = @json(route('pastor.events'));

        function slugifyName(s) {
            return String(s || '').toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '') || 'event';
        }

        function updateBranchSlugHint() {
            const span = document.getElementById('branchCodeInSlugHint');
            if (!span) {
                return;
            }
            if (!isSuperAdmin) {
                const b = branches[0];
                span.textContent = b && b.public_code ? b.public_code : '…';
                return;
            }
            const sel = document.getElementById('eventBranch');
            const id = sel && sel.value;
            const b = branches.find(x => String(x.id) === String(id));
            span.textContent = b && b.public_code ? b.public_code : '…';
        }

        function updateSlugPreviewTail() {
            const tail = document.getElementById('slugPreviewTail');
            const inp = document.getElementById('eventPublicSlug');
            if (tail && inp) {
                tail.textContent = inp.value || 'your-slug';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            (async () => {
                await loadBranches();
                if (isSuperAdmin) {
                    document.getElementById('branchSelectionDiv')?.classList.remove('hidden');
                    document.getElementById('eventBranch')?.setAttribute('required', 'required');
                    const eventBranch = document.getElementById('eventBranch');
                    if (eventBranch) {
                        eventBranch.addEventListener('change', () => {
                            updateBranchSlugHint();
                        });
                    }
                } else {
                    document.getElementById('eventBranch')?.removeAttribute('required');
                }
                setupEventListeners();
                const initialEventId = @json($event?->id);
                if (initialEventId) {
                    await fetchAndPopulateEvent(initialEventId);
                } else {
                    resetCreateForm();
                }
            })();
        });

        function setupEventListeners() {
            const eventForm = document.getElementById('eventForm');
            if (eventForm) {
                eventForm.addEventListener('submit', handleEventSubmit);
            }

            const eventNameInput = document.getElementById('eventName');
            const eventPublicSlugInput = document.getElementById('eventPublicSlug');
            if (eventNameInput && eventPublicSlugInput) {
                eventNameInput.addEventListener('blur', function () {
                    if (!currentEventId && !eventPublicSlugInput.dataset.userTouched) {
                        eventPublicSlugInput.value = slugifyName(eventNameInput.value);
                        updateSlugPreviewTail();
                    }
                });
                eventPublicSlugInput.addEventListener('input', function () {
                    if (!currentEventId) {
                        eventPublicSlugInput.dataset.userTouched = '1';
                    }
                    updateSlugPreviewTail();
                });
            }

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
                        if (serviceType) serviceType.setAttribute('required', 'required');
                    } else {
                        if (serviceDetailsDiv) serviceDetailsDiv.classList.add('hidden');
                        if (sundayServiceDiv) sundayServiceDiv.classList.add('hidden');
                        if (serviceType) {
                            serviceType.removeAttribute('required');
                            serviceType.value = '';
                        }
                        if (hasMultipleServices) hasMultipleServices.checked = false;
                        if (secondServiceDiv) secondServiceDiv.classList.add('hidden');
                    }
                });
            }

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
                        if (hasMultipleServices) hasMultipleServices.checked = false;
                        if (secondServiceDiv) secondServiceDiv.classList.add('hidden');
                    }
                });
            }

            const isRecurring = document.getElementById('isRecurring');
            if (isRecurring) {
                isRecurring.addEventListener('change', function() {
                    const recurringDiv = document.getElementById('recurringConfigDiv');
                    if (recurringDiv) recurringDiv.classList.toggle('hidden', !this.checked);
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

            const hasMultipleServices = document.getElementById('hasMultipleServices');
            if (hasMultipleServices) {
                hasMultipleServices.addEventListener('change', function() {
                    const secondServiceDiv = document.getElementById('secondServiceDiv');
                    if (secondServiceDiv) secondServiceDiv.classList.toggle('hidden', !this.checked);
                });
            }

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

            const addFieldBtn = document.getElementById('addFieldBtn');
            if (addFieldBtn) addFieldBtn.addEventListener('click', addFormField);

            const coverImage = document.getElementById('coverImage');
            if (coverImage) {
                coverImage.addEventListener('change', function() {
                    const f = this.files && this.files[0];
                    const preview = document.getElementById('coverImagePreview');
                    const previewImg = document.getElementById('coverImagePreviewImg');
                    if (f && preview && previewImg) {
                        previewImg.src = URL.createObjectURL(f);
                        preview.classList.remove('hidden');
                    }
                });
            }
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
                    updateBranchSlugHint();
                }
            } catch (error) {
                console.error('Error loading branches:', error);
            }
        }

        function populateBranchDropdowns() {
            const eventBranch = document.getElementById('eventBranch');
            if (eventBranch && branches.length > 0) {
                eventBranch.innerHTML = '<option value="">Select Branch</option>' +
                    branches.map(branch => `<option value="${branch.id}">${branch.name}</option>`).join('');
            }
        }

        async function fetchAndPopulateEvent(id) {
            try {
                const response = await fetch(`/api/events/${id}`, {
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
                    currentEventId = id;
                    populateEventForm(data.data);
                } else {
                    showNotification(data.message || 'Could not load event', 'error');
                }
            } catch (error) {
                console.error('Error loading event:', error);
                showNotification('Error loading event', 'error');
            }
        }

        function resetCreateForm() {
            currentEventId = null;
            originalPublicSlug = '';
            document.getElementById('eventForm').reset();
            document.getElementById('eventId').value = '';
            const slugInp = document.getElementById('eventPublicSlug');
            if (slugInp) {
                delete slugInp.dataset.userTouched;
            }
            updateSlugPreviewTail();
            updateBranchSlugHint();
            clearFormBuilder();
            const cImg = document.getElementById('coverImage');
            if (cImg) {
                cImg.value = '';
            }
            document.getElementById('coverImagePreview')?.classList.add('hidden');
            document.getElementById('removeCoverWrap')?.classList.add('hidden');
            const removeCk = document.getElementById('removeCover');
            if (removeCk) {
                removeCk.checked = false;
            }
        }

        function populateEventForm(event) {
            document.getElementById('eventId').value = event.id;
            document.getElementById('eventName').value = event.name;
            originalPublicSlug = event.public_slug || '';
            const slugField = document.getElementById('eventPublicSlug');
            if (slugField) {
                slugField.value = event.public_slug || '';
                delete slugField.dataset.userTouched;
                updateSlugPreviewTail();
            }
            updateBranchSlugHint();
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
const dayOfWeekSelect = document.getElementById('dayOfWeek');
            const dayOfWeekValue = event.day_of_week !== null && event.day_of_week !== undefined ? event.day_of_week.toString() : '';
            dayOfWeekSelect.value = dayOfWeekValue;
            
            // Verify the value was set correctly
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

            const coverInput = document.getElementById('coverImage');
            if (coverInput) {
                coverInput.value = '';
            }
            const preview = document.getElementById('coverImagePreview');
            const previewImg = document.getElementById('coverImagePreviewImg');
            const removeWrap = document.getElementById('removeCoverWrap');
            const removeCk = document.getElementById('removeCover');
            if (event.cover_image_url) {
                previewImg.src = event.cover_image_url;
                preview.classList.remove('hidden');
                removeWrap.classList.remove('hidden');
                if (removeCk) {
                    removeCk.checked = false;
                }
            } else {
                preview.classList.add('hidden');
                removeWrap.classList.add('hidden');
            }

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

            const form = document.getElementById('eventForm');
            const fd = new FormData(form);

            const newSlug = (fd.get('public_slug') || '').toString();
            if (currentEventId && originalPublicSlug && newSlug !== originalPublicSlug) {
                if (!window.confirm('Changing the public URL slug will break old links and any QR codes that pointed to the previous address. Continue?')) {
                    return;
                }
            }

            fd.set('is_public', document.getElementById('isPublic').checked ? '1' : '0');
            fd.set('is_recurring', document.getElementById('isRecurring').checked ? '1' : '0');
            fd.set('has_multiple_services', document.getElementById('hasMultipleServices').checked ? '1' : '0');

            const customFieldsHidden = document.getElementById('customFormFields');
            if (customFieldsHidden && customFieldsHidden.value) {
                fd.set('custom_form_fields', customFieldsHidden.value);
            }

            const removeCoverEl = document.getElementById('removeCover');
            if (!removeCoverEl || !removeCoverEl.checked) {
                fd.delete('remove_cover');
            }

            const url = currentEventId ? `/api/events/${currentEventId}` : '/api/events';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    credentials: 'same-origin',
                    body: fd,
                });

                const data = await response.json();

                if (data.success) {
                    showNotification(currentEventId ? 'Event updated successfully' : 'Event created successfully', 'success');
                    window.location.href = eventsIndexUrl;
                } else {
                    const msg = data.message || 'Error saving event';
                    const extra = data.errors ? ' ' + Object.values(data.errors).flat().join(' ') : '';
                    showNotification(msg + extra, 'error');
                }
            } catch (error) {
                console.error('Error saving event:', error);
                showNotification('Error saving event', 'error');
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
                document.body.removeChild(notification);
            }, 3000);
        }
    </script>

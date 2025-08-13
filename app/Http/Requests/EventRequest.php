<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

final class EventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->isSuperAdmin() || Auth::user()->isBranchPastor();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get the event ID for updates (handle both ID and model binding)
        $eventId = null;
        if ($this->route('event')) {
            $eventId = is_object($this->route('event')) 
                ? $this->route('event')->id 
                : $this->route('event');
        }

        return [
            'branch_id' => [
                Auth::user()->isSuperAdmin() ? 'required' : 'nullable',
                'integer',
                'exists:branches,id',
                function ($attribute, $value, $fail) {
                    $user = Auth::user();
                    if (!$user->isSuperAdmin()) {
                        $userBranch = $user->getPrimaryBranch();
                        if ($value && $userBranch && $userBranch->id !== (int) $value) {
                            $fail('You can only create events for your assigned branch.');
                        }
                    }
                },
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($eventId) {
                    // Get branch_id from request or user's primary branch
                    $branchId = $this->input('branch_id');
                    
                    // If branch_id is not in the request, get it from the user's primary branch
                    if (!$branchId && !Auth::user()->isSuperAdmin()) {
                        $userBranch = Auth::user()->getPrimaryBranch();
                        $branchId = $userBranch ? $userBranch->id : null;
                    }
                    
                    // If we still don't have a branch_id, we can't validate uniqueness
                    if (!$branchId) {
                        return; // Skip validation if no branch context
                    }
                    
                    // Check for existing events with the same name in the same branch
                    $query = \App\Models\Event::where('name', $value)
                                              ->where('branch_id', $branchId)
                                              ->whereNull('deleted_at');
                    
                    // If we're updating an event, exclude the current event
                    if ($eventId) {
                        $query->where('id', '!=', $eventId);
                    }
                    
                    if ($query->exists()) {
                        $fail('An event with this name already exists in the selected branch. Please choose a different name or add additional details (e.g., date, time) to make it unique.');
                    }
                },
            ],
            'description' => 'nullable|string|max:2000',
            'type' => [
                'required',
                Rule::in(['service', 'conference', 'workshop', 'outreach', 'social', 'other']),
            ],
            'location' => 'required|string|max:255',
            'start_date_time' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    // Only require future dates for new events
                    if (!$this->route('event') && strtotime($value) <= time()) {
                        $fail('Event start date must be in the future for new events.');
                    }
                },
            ],
            'end_date_time' => [
                'nullable',
                'date',
                'after:start_date_time',
            ],
            'max_capacity' => 'nullable|integer|min:1',
            'registration_type' => [
                'required',
                Rule::in(['none', 'simple', 'form', 'link']),
            ],
            'registration_link' => [
                'nullable',
                'url',
                'max:500',
                'required_if:registration_type,link',
            ],
            'custom_form_fields' => [
                'nullable',
                'json',
                'required_if:registration_type,form',
            ],
            'status' => [
                'required',
                Rule::in(['active', 'completed', 'cancelled']),
            ],
            'is_public' => 'boolean',
            
            // Recurring event fields
            'is_recurring' => 'boolean',
            'frequency' => [
                'nullable',
                Rule::in(['once', 'weekly', 'monthly', 'quarterly', 'annually', 'recurrent']),
                'required_if:is_recurring,true',
            ],
            'day_of_week' => [
                'nullable',
                'integer',
                'between:0,6',
                'required_if:is_recurring,true',
            ],
            'recurrence_end_date' => [
                'nullable',
                'date',
                'after:start_date_time',
            ],
            'max_occurrences' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            
            // Service-specific fields
            'service_type' => [
                'nullable',
                'string',
                Rule::in(['Sunday Service', 'MidWeek', 'Conferences', 'Outreach', 'Evangelism (Beautiful Feet)', 'Water Baptism', 'TECi', 'Membership Class', 'LifeGroup Meeting', 'other']),
            ],
            'service_name' => 'nullable|string|max:255',
            'service_time' => 'nullable|date_format:H:i',
            'service_end_time' => [
                'nullable',
                'date_format:H:i',
                'after:service_time',
            ],
            
            // Multiple services fields
            'has_multiple_services' => 'boolean',
            'second_service_name' => [
                'nullable',
                'string',
                'max:255',
                'required_if:has_multiple_services,true',
            ],
            'second_service_time' => [
                'nullable',
                'date_format:H:i',
                'required_if:has_multiple_services,true',
                'after:service_end_time',
            ],
            'second_service_end_time' => [
                'nullable',
                'date_format:H:i',
                'after:second_service_time',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'branch_id.required' => 'Please select a branch for the event.',
            'branch_id.exists' => 'The selected branch does not exist.',
            'name.required' => 'Event name is required.',
            'name.unique' => 'An event with this name already exists in the selected branch.',
            'location.required' => 'Event location is required.',
            'start_date_time.required' => 'Event start date is required.',
            'start_date_time.after' => 'Event start date must be in the future.',
            'end_date_time.after' => 'Event end date must be after the start date.',
            'registration_type.required' => 'Please select registration type.',
            'registration_type.in' => 'Invalid registration type selected.',
            'registration_link.required_if' => 'Registration link is required when registration type is "link".',
            'registration_link.url' => 'Please provide a valid URL for the registration link.',
            'custom_form_fields.required_if' => 'Custom form fields are required when registration type is "form".',
            'status.required' => 'Event status is required.',
            'status.in' => 'Invalid event status selected.',
            
            // Recurring event messages
            'frequency.required_if' => 'Frequency is required for recurring events.',
            'frequency.in' => 'Invalid frequency selected. Choose weekly, bi-weekly, or monthly.',
            'day_of_week.required_if' => 'Day of week is required for recurring events.',
            'day_of_week.between' => 'Day of week must be between 0 (Sunday) and 6 (Saturday).',
            'recurrence_end_date.after' => 'Recurrence end date must be after the event start date.',
            'max_occurrences.min' => 'Maximum occurrences must be at least 1.',
            'max_occurrences.max' => 'Maximum occurrences cannot exceed 100.',
            
            // Service-specific messages
            'service_type.in' => 'Invalid service type selected.',
            'service_time.date_format' => 'Service time must be in HH:MM format.',
            'service_end_time.date_format' => 'Service end time must be in HH:MM format.',
            'service_end_time.after' => 'Service end time must be after service start time.',
            
            // Multiple services messages
            'second_service_name.required_if' => 'Second service name is required when multiple services is enabled.',
            'second_service_time.required_if' => 'Second service time is required when multiple services is enabled.',
            'second_service_time.date_format' => 'Second service time must be in HH:MM format.',
            'second_service_time.after' => 'Second service time must be after first service end time.',
            'second_service_end_time.date_format' => 'Second service end time must be in HH:MM format.',
            'second_service_end_time.after' => 'Second service end time must be after second service start time.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'branch_id' => 'branch',
            'name' => 'event name',
            'description' => 'event description',
            'location' => 'event location',
            'start_date_time' => 'start date',
            'end_date_time' => 'end date',
            'registration_type' => 'registration type',
            'registration_link' => 'registration link',
            'custom_form_fields' => 'custom form fields',
            'status' => 'status',
            
            // Recurring event attributes
            'is_recurring' => 'recurring event',
            'frequency' => 'frequency',
            'day_of_week' => 'day of week',
            'recurrence_end_date' => 'recurrence end date',
            'max_occurrences' => 'maximum occurrences',
            
            // Service-specific attributes
            'service_type' => 'service type',
            'service_name' => 'service name',
            'service_time' => 'service time',
            'service_end_time' => 'service end time',
            
            // Multiple services attributes
            'has_multiple_services' => 'multiple services',
            'second_service_name' => 'second service name',
            'second_service_time' => 'second service time',
            'second_service_end_time' => 'second service end time',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // If user is not super admin, set branch_id to their primary branch
        if (!Auth::user()->isSuperAdmin()) {
            $userBranch = Auth::user()->getPrimaryBranch();
            if ($userBranch) {
                $this->merge([
                    'branch_id' => $userBranch->id,
                ]);
            }
        }

        // Clean up custom form fields if registration type is link
        if ($this->registration_type === 'link') {
            $this->merge([
                'custom_form_fields' => null,
            ]);
        }

        // Clean up registration link if registration type is form
        if ($this->registration_type === 'form') {
            $this->merge([
                'registration_link' => null,
            ]);
        }

        // Set service_type based on event type
        if ($this->type === 'service') {
            // Default to Sunday Service for service events if not already set
            if (!$this->filled('service_type')) {
                $this->merge([
                    'service_type' => 'Sunday Service',
                ]);
            }
        } else {
            // Clear service-related fields for non-service events
            $this->merge([
                'service_type' => null,
                'service_name' => null,
                'service_time' => null,
                'service_end_time' => null,
                'has_multiple_services' => false,
                'second_service_name' => null,
                'second_service_time' => null,
                'second_service_end_time' => null,
            ]);
        }

        // Clear recurring fields if not recurring
        if (!$this->is_recurring) {
            $this->merge([
                'frequency' => null,
                'day_of_week' => null,
                'recurrence_end_date' => null,
                'max_occurrences' => null,
            ]);
        }

        // Clear second service fields if not multiple services
        if (!$this->has_multiple_services) {
            $this->merge([
                'second_service_name' => null,
                'second_service_time' => null,
                'second_service_end_time' => null,
            ]);
        }
    }
} 
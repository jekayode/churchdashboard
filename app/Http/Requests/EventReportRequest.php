<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class EventReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'event_id' => 'required|exists:events,id',
            'event_date' => 'required|date',
            'event_type' => 'required|string|max:100',
            'service_type' => 'nullable|string|max:100',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'notes' => 'nullable|string',
            
            // First Service (using form field names)
            'male_attendance' => 'required|integer|min:0',
            'female_attendance' => 'required|integer|min:0',
            'children_attendance' => 'required|integer|min:0',
            'online_attendance' => 'nullable|integer|min:0',
            'first_time_guests' => 'required|integer|min:0',
            'converts' => 'required|integer|min:0',
            'cars' => 'required|integer|min:0',
            
            // Second Service (optional, using form field names)
            'has_second_service' => 'boolean',
            'second_service_start_time' => 'nullable|date_format:H:i|required_if:has_second_service,true',
            'second_service_end_time' => 'nullable|date_format:H:i|after:second_service_start_time|required_if:has_second_service,true',
            'second_male_attendance' => 'nullable|integer|min:0|required_if:has_second_service,true',
            'second_female_attendance' => 'nullable|integer|min:0|required_if:has_second_service,true',
            'second_children_attendance' => 'nullable|integer|min:0|required_if:has_second_service,true',
            'second_online_attendance' => 'nullable|integer|min:0',
            'second_first_time_guests' => 'nullable|integer|min:0|required_if:has_second_service,true',
            'second_converts' => 'nullable|integer|min:0|required_if:has_second_service,true',
            'second_cars' => 'nullable|integer|min:0|required_if:has_second_service,true',
        ];
    }

    public function messages(): array
    {
        return [
            'event_id.required' => 'Please select an event.',
            'event_id.exists' => 'The selected event does not exist.',
            'event_date.required' => 'Event date is required.',
            'event_type.required' => 'Event type is required.',
            'service_type.required_if' => 'Service type is required when event type is service.',
            'start_time.required' => 'Start time is required.',
            'end_time.required' => 'End time is required.',
            'end_time.after' => 'End time must be after start time.',
            
            'male_attendance.required' => 'Male attendance is required.',
            'female_attendance.required' => 'Female attendance is required.',
            'children_attendance.required' => 'Children attendance is required.',
            'online_attendance.required' => 'Online attendance is required.',
            'first_time_guests.required' => 'First time guests count is required.',
            'converts.required' => 'Converts count is required.',
            'cars.required' => 'Cars count is required.',
            
            'second_service_start_time.required_if' => 'Second service start time is required when second service is enabled.',
            'second_service_end_time.required_if' => 'Second service end time is required when second service is enabled.',
            'second_service_end_time.after' => 'Second service end time must be after start time.',
            'second_male_attendance.required_if' => 'Second service male attendance is required when second service is enabled.',
            'second_female_attendance.required_if' => 'Second service female attendance is required when second service is enabled.',
            'second_children_attendance.required_if' => 'Second service children attendance is required when second service is enabled.',
            'second_online_attendance.required_if' => 'Second service online attendance is required when second service is enabled.',
            'second_first_time_guests.required_if' => 'Second service first time guests count is required when second service is enabled.',
            'second_converts.required_if' => 'Second service converts count is required when second service is enabled.',
            'second_cars.required_if' => 'Second service cars count is required when second service is enabled.',
        ];
    }

    public function attributes(): array
    {
        return [
            'male_attendance' => 'male attendance',
            'female_attendance' => 'female attendance',
            'children_attendance' => 'children attendance',
            'online_attendance' => 'online attendance',
            'first_time_guests' => 'first time guests',
            'converts' => 'converts',
            'cars' => 'cars',
            'second_male_attendance' => 'second service male attendance',
            'second_female_attendance' => 'second service female attendance',
            'second_children_attendance' => 'second service children attendance',
            'second_online_attendance' => 'second service online attendance',
            'second_first_time_guests' => 'second service first time guests',
            'second_converts' => 'second service converts',
            'second_cars' => 'second service cars',
        ];
    }
} 
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SmallGroupMeetingReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'small_group_id' => 'required|integer|exists:small_groups,id',
            'meeting_date' => 'required|date|before_or_equal:today',
            'meeting_time' => 'nullable|date_format:H:i',
            'meeting_location' => 'nullable|string|max:255',
            
            // Attendance counts
            'male_attendance' => 'required|integer|min:0|max:1000',
            'female_attendance' => 'required|integer|min:0|max:1000',
            'children_attendance' => 'required|integer|min:0|max:1000',
            'first_time_guests' => 'required|integer|min:0|max:1000',
            'converts' => 'required|integer|min:0|max:1000',
            
            // Meeting details
            'meeting_notes' => 'nullable|string|max:5000',
            'prayer_requests' => 'nullable|string|max:5000',
            'testimonies' => 'nullable|string|max:5000',
            'attendee_names' => 'nullable|array',
            'attendee_names.*' => 'string|max:255',
            
            // Status
            'status' => 'nullable|in:draft,submitted',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'small_group_id.required' => 'Small group is required.',
            'small_group_id.exists' => 'Selected small group does not exist.',
            'meeting_date.required' => 'Meeting date is required.',
            'meeting_date.before_or_equal' => 'Meeting date cannot be in the future.',
            'meeting_time.date_format' => 'Meeting time must be in HH:MM format.',
            
            'male_attendance.required' => 'Male attendance count is required.',
            'male_attendance.min' => 'Male attendance cannot be negative.',
            'male_attendance.max' => 'Male attendance seems unusually high.',
            
            'female_attendance.required' => 'Female attendance count is required.',
            'female_attendance.min' => 'Female attendance cannot be negative.',
            'female_attendance.max' => 'Female attendance seems unusually high.',
            
            'children_attendance.required' => 'Children attendance count is required.',
            'children_attendance.min' => 'Children attendance cannot be negative.',
            'children_attendance.max' => 'Children attendance seems unusually high.',
            
            'first_time_guests.required' => 'First time guests count is required.',
            'first_time_guests.min' => 'First time guests cannot be negative.',
            'first_time_guests.max' => 'First time guests count seems unusually high.',
            
            'converts.required' => 'Converts count is required.',
            'converts.min' => 'Converts cannot be negative.',
            'converts.max' => 'Converts count seems unusually high.',
            
            'meeting_notes.max' => 'Meeting notes cannot exceed 5000 characters.',
            'prayer_requests.max' => 'Prayer requests cannot exceed 5000 characters.',
            'testimonies.max' => 'Testimonies cannot exceed 5000 characters.',
            
            'attendee_names.array' => 'Attendee names must be a list.',
            'attendee_names.*.string' => 'Each attendee name must be text.',
            'attendee_names.*.max' => 'Attendee names cannot exceed 255 characters.',
            
            'status.in' => 'Status must be either draft or submitted.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'small_group_id' => 'small group',
            'meeting_date' => 'meeting date',
            'meeting_time' => 'meeting time',
            'meeting_location' => 'meeting location',
            'male_attendance' => 'male attendance',
            'female_attendance' => 'female attendance',
            'children_attendance' => 'children attendance',
            'first_time_guests' => 'first time guests',
            'converts' => 'converts',
            'meeting_notes' => 'meeting notes',
            'prayer_requests' => 'prayer requests',
            'testimonies' => 'testimonies',
            'attendee_names' => 'attendee names',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate that converts don't exceed first time guests + regular attendees
            $maleAttendance = (int) $this->input('male_attendance', 0);
            $femaleAttendance = (int) $this->input('female_attendance', 0);
            $childrenAttendance = (int) $this->input('children_attendance', 0);
            $firstTimeGuests = (int) $this->input('first_time_guests', 0);
            $converts = (int) $this->input('converts', 0);
            
            $totalAttendance = $maleAttendance + $femaleAttendance + $childrenAttendance;
            
            if ($converts > $totalAttendance) {
                $validator->errors()->add('converts', 'Converts cannot exceed total attendance.');
            }
            
            if ($firstTimeGuests > $totalAttendance) {
                $validator->errors()->add('first_time_guests', 'First time guests cannot exceed total attendance.');
            }
        });
    }
}

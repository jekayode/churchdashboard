<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseMemberRequest extends FormRequest
{
    /**
     * Get common validation rules for all member forms.
     */
    protected function getCommonRules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'branch_id' => 'required|exists:branches,id',
            'gender' => 'nullable|in:male,female,prefer-not-to-say',
            'preferred_call_time' => 'nullable|in:anytime,morning,afternoon,evening',
            'home_address' => 'nullable|string|max:1000',
            'date_of_birth' => 'nullable|date|before:today',
            'age_group' => 'nullable|in:15-20,21-25,26-30,31-35,36-40,above-40',
            'marital_status' => 'nullable|in:single,in-relationship,engaged,married,separated,divorced',
            'prayer_request' => 'nullable|string|max:2000',
            'discovery_source' => 'nullable|in:social-media,word-of-mouth,billboard,email,website,promotional-material,radio-tv,outreach',
            'staying_intention' => 'nullable|in:yes-for-sure,visit-when-in-town,just-visiting,weighing-options',
            'closest_location' => 'nullable|string|max:255',
            'additional_info' => 'nullable|string|max:2000',
            'member_status' => 'nullable|in:visitor,member,volunteer,leader,minister',
            'growth_level' => 'nullable|in:new_believer,growing,mature,leader',
            'teci_status' => 'nullable|in:not_started,100_level,200_level,300_level,400_level,500_level,graduated,paused',
            'occupation' => 'nullable|string|max:255',
            'nearest_bus_stop' => 'nullable|string|max:255',
            'anniversary' => 'nullable|date',
            'date_joined' => 'nullable|date',
            'leadership_trainings' => 'nullable|array',
            'leadership_trainings.*' => 'string|in:ELP,MLCC,MLCP Basic,MLCP Advanced',
        ];
    }

    /**
     * Get common validation messages.
     */
    protected function getCommonMessages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'surname.required' => 'Surname is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'phone.required' => 'Phone number is required.',
            'branch_id.required' => 'Please select a branch.',
            'branch_id.exists' => 'The selected branch is invalid.',
            'gender.in' => 'Please select a valid gender option.',
            'preferred_call_time.in' => 'Please select a valid call time preference.',
            'age_group.in' => 'Please select a valid age group.',
            'marital_status.in' => 'Please select a valid marital status.',
            'discovery_source.in' => 'Please select how you found out about LifePointe.',
            'staying_intention.in' => 'Please select your staying intention.',
            'member_status.in' => 'Please select a valid member status.',
            'growth_level.in' => 'Please select a valid growth level.',
            'teci_status.in' => 'Please select a valid TECI status.',
        ];
    }
}

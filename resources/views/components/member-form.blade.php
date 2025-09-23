@props([
    'member' => null,
    'context' => 'guest', // 'guest', 'admin', 'profile'
    'showRequired' => true,
    'showOptional' => true,
    'requiredFields' => [],
    'optionalFields' => [],
    'selectedBranchId' => null
])

@php
    // Define field configurations based on context
    $fieldConfigs = [
        'guest' => [
            'required' => ['first_name', 'surname', 'email', 'phone', 'branch_id'],
            'optional' => ['gender', 'preferred_call_time', 'home_address', 'date_of_birth', 'age_group', 'marital_status', 'discovery_source', 'staying_intention', 'closest_location', 'prayer_request', 'additional_info']
        ],
        'admin' => [
            'required' => ['first_name', 'surname', 'email', 'phone', 'branch_id'],
            'optional' => ['gender', 'preferred_call_time', 'home_address', 'date_of_birth', 'age_group', 'marital_status', 'discovery_source', 'staying_intention', 'closest_location', 'prayer_request', 'additional_info', 'member_status', 'growth_level', 'teci_status', 'occupation', 'nearest_bus_stop', 'anniversary', 'date_joined', 'leadership_trainings']
        ],
        'profile' => [
            'required' => [],
            'optional' => ['gender', 'preferred_call_time', 'home_address', 'date_of_birth', 'age_group', 'marital_status', 'discovery_source', 'staying_intention', 'closest_location', 'prayer_request', 'additional_info']
        ]
    ];

    $config = $fieldConfigs[$context] ?? $fieldConfigs['guest'];
    $fieldsToShow = array_merge($config['required'], $config['optional']);
    
    // Override with provided fields if specified
    if (!empty($requiredFields)) {
        $config['required'] = $requiredFields;
    }
    if (!empty($optionalFields)) {
        $config['optional'] = $optionalFields;
    }
@endphp

<div class="space-y-6">
    @if($showRequired && !empty($config['required']))
        <!-- Required Fields Section -->
        <div class="border-b border-gray-200 pb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
            <p class="text-sm text-gray-600 mb-6">Please provide the following required information.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if(in_array('first_name', $config['required']))
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700">First Name <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="first_name" 
                               id="first_name" 
                               value="{{ old('first_name', $member?->first_name) }}"
                               required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                @if(in_array('surname', $config['required']))
                    <div>
                        <label for="surname" class="block text-sm font-medium text-gray-700">Surname <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="surname" 
                               id="surname" 
                               value="{{ old('surname', $member?->surname) }}"
                               required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        @error('surname')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                @if(in_array('email', $config['required']))
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               value="{{ old('email', $member?->email) }}"
                               required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                @if(in_array('phone', $config['required']))
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number <span class="text-red-500">*</span></label>
                        <input type="tel" 
                               name="phone" 
                               id="phone" 
                               value="{{ old('phone', $member?->phone) }}"
                               required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                @if(in_array('branch_id', $config['required']))
                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch <span class="text-red-500">*</span></label>
                        <select name="branch_id" 
                                id="branch_id" 
                                required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select a branch</option>
                            @foreach(\App\Models\Branch::all() as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id', $member?->branch_id ?? $selectedBranchId) == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($showOptional && !empty($config['optional']))
        <!-- Optional Fields Section -->
        <div class="border-b border-gray-200 pb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
            <p class="text-sm text-gray-600 mb-6">You can fill these out now or complete them later in your profile.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if(in_array('gender', $config['optional']))
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                        <select name="gender" 
                                id="gender" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select gender</option>
                            <option value="male" {{ old('gender', $member?->gender) == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $member?->gender) == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="prefer-not-to-say" {{ old('gender', $member?->gender) == 'prefer-not-to-say' ? 'selected' : '' }}>Prefer not to say</option>
                        </select>
                    </div>
                @endif

                @if(in_array('preferred_call_time', $config['optional']))
                    <div>
                        <label for="preferred_call_time" class="block text-sm font-medium text-gray-700">When do you prefer that we call you?</label>
                        <select name="preferred_call_time" 
                                id="preferred_call_time" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select preferred time</option>
                            <option value="anytime" {{ old('preferred_call_time', $member?->preferred_call_time) == 'anytime' ? 'selected' : '' }}>Anytime</option>
                            <option value="morning" {{ old('preferred_call_time', $member?->preferred_call_time) == 'morning' ? 'selected' : '' }}>Morning</option>
                            <option value="afternoon" {{ old('preferred_call_time', $member?->preferred_call_time) == 'afternoon' ? 'selected' : '' }}>Afternoon</option>
                            <option value="evening" {{ old('preferred_call_time', $member?->preferred_call_time) == 'evening' ? 'selected' : '' }}>Evening</option>
                        </select>
                    </div>
                @endif

                @if(in_array('home_address', $config['optional']))
                    <div class="md:col-span-2">
                        <label for="home_address" class="block text-sm font-medium text-gray-700">Home Address</label>
                        <textarea name="home_address" 
                                  id="home_address" 
                                  rows="3"
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('home_address', $member?->home_address) }}</textarea>
                    </div>
                @endif

                @if(in_array('date_of_birth', $config['optional']))
                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Birthday</label>
                        <input type="date" 
                               name="date_of_birth" 
                               id="date_of_birth" 
                               value="{{ old('date_of_birth', $member?->date_of_birth?->format('Y-m-d')) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                @endif

                @if(in_array('age_group', $config['optional']))
                    <div>
                        <label for="age_group" class="block text-sm font-medium text-gray-700">Age Group</label>
                        <select name="age_group" 
                                id="age_group" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select age group</option>
                            <option value="15-20" {{ old('age_group', $member?->age_group) == '15-20' ? 'selected' : '' }}>15-20</option>
                            <option value="21-25" {{ old('age_group', $member?->age_group) == '21-25' ? 'selected' : '' }}>21-25</option>
                            <option value="26-30" {{ old('age_group', $member?->age_group) == '26-30' ? 'selected' : '' }}>26-30</option>
                            <option value="31-35" {{ old('age_group', $member?->age_group) == '31-35' ? 'selected' : '' }}>31-35</option>
                            <option value="36-40" {{ old('age_group', $member?->age_group) == '36-40' ? 'selected' : '' }}>36-40</option>
                            <option value="above-40" {{ old('age_group', $member?->age_group) == 'above-40' ? 'selected' : '' }}>Above 40</option>
                        </select>
                    </div>
                @endif

                @if(in_array('marital_status', $config['optional']))
                    <div>
                        <label for="marital_status" class="block text-sm font-medium text-gray-700">Marital Status</label>
                        <select name="marital_status" 
                                id="marital_status" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select marital status</option>
                            <option value="single" {{ old('marital_status', $member?->marital_status) == 'single' ? 'selected' : '' }}>Single</option>
                            <option value="in-relationship" {{ old('marital_status', $member?->marital_status) == 'in-relationship' ? 'selected' : '' }}>In a relationship</option>
                            <option value="engaged" {{ old('marital_status', $member?->marital_status) == 'engaged' ? 'selected' : '' }}>Engaged</option>
                            <option value="married" {{ old('marital_status', $member?->marital_status) == 'married' ? 'selected' : '' }}>Married</option>
                            <option value="separated" {{ old('marital_status', $member?->marital_status) == 'separated' ? 'selected' : '' }}>Separated</option>
                            <option value="divorced" {{ old('marital_status', $member?->marital_status) == 'divorced' ? 'selected' : '' }}>Divorced</option>
                        </select>
                    </div>
                @endif

                @if(in_array('discovery_source', $config['optional']))
                    <div>
                        <label for="discovery_source" class="block text-sm font-medium text-gray-700">How did you find out about LifePointe?</label>
                        <select name="discovery_source" 
                                id="discovery_source" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select how you found us</option>
                            <option value="social-media" {{ old('discovery_source', $member?->discovery_source) == 'social-media' ? 'selected' : '' }}>Social Media</option>
                            <option value="word-of-mouth" {{ old('discovery_source', $member?->discovery_source) == 'word-of-mouth' ? 'selected' : '' }}>Word of mouth</option>
                            <option value="billboard" {{ old('discovery_source', $member?->discovery_source) == 'billboard' ? 'selected' : '' }}>Billboard</option>
                            <option value="email" {{ old('discovery_source', $member?->discovery_source) == 'email' ? 'selected' : '' }}>Email</option>
                            <option value="website" {{ old('discovery_source', $member?->discovery_source) == 'website' ? 'selected' : '' }}>Website</option>
                            <option value="promotional-material" {{ old('discovery_source', $member?->discovery_source) == 'promotional-material' ? 'selected' : '' }}>Promotional Material</option>
                            <option value="radio-tv" {{ old('discovery_source', $member?->discovery_source) == 'radio-tv' ? 'selected' : '' }}>Radio/TV</option>
                            <option value="outreach" {{ old('discovery_source', $member?->discovery_source) == 'outreach' ? 'selected' : '' }}>Outreach</option>
                        </select>
                    </div>
                @endif

                @if(in_array('staying_intention', $config['optional']))
                    <div>
                        <label for="staying_intention" class="block text-sm font-medium text-gray-700">Here to stay?</label>
                        <select name="staying_intention" 
                                id="staying_intention" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select your intention</option>
                            <option value="yes-for-sure" {{ old('staying_intention', $member?->staying_intention) == 'yes-for-sure' ? 'selected' : '' }}>Yes, for sure!</option>
                            <option value="visit-when-in-town" {{ old('staying_intention', $member?->staying_intention) == 'visit-when-in-town' ? 'selected' : '' }}>Visit when in town</option>
                            <option value="just-visiting" {{ old('staying_intention', $member?->staying_intention) == 'just-visiting' ? 'selected' : '' }}>Just visiting</option>
                            <option value="weighing-options" {{ old('staying_intention', $member?->staying_intention) == 'weighing-options' ? 'selected' : '' }}>Weighing options</option>
                        </select>
                    </div>
                @endif

                @if(in_array('closest_location', $config['optional']))
                    <div>
                        <label for="closest_location" class="block text-sm font-medium text-gray-700">Which location is closest to you?</label>
                        <select name="closest_location" 
                                id="closest_location" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select closest location</option>
                            <option value="Ajah - DKK" {{ old('closest_location', $member?->closest_location) == 'Ajah - DKK' ? 'selected' : '' }}>Ajah - DKK</option>
                            <option value="DKK - Novare Mall" {{ old('closest_location', $member?->closest_location) == 'DKK - Novare Mall' ? 'selected' : '' }}>DKK - Novare Mall</option>
                            <option value="Crown Estate - Abijo" {{ old('closest_location', $member?->closest_location) == 'Crown Estate - Abijo' ? 'selected' : '' }}>Crown Estate - Abijo</option>
                            <option value="Abijo - Awoyaya" {{ old('closest_location', $member?->closest_location) == 'Abijo - Awoyaya' ? 'selected' : '' }}>Abijo - Awoyaya</option>
                            <option value="Eputu" {{ old('closest_location', $member?->closest_location) == 'Eputu' ? 'selected' : '' }}>Eputu</option>
                        </select>
                    </div>
                @endif

                @if(in_array('prayer_request', $config['optional']))
                    <div class="md:col-span-2">
                        <label for="prayer_request" class="block text-sm font-medium text-gray-700">Do you need prayers?</label>
                        <textarea name="prayer_request" 
                                  id="prayer_request" 
                                  rows="3"
                                  placeholder="Please tell us how we can support you in prayer..."
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('prayer_request', $member?->prayer_request) }}</textarea>
                    </div>
                @endif

                @if(in_array('additional_info', $config['optional']))
                    <div class="md:col-span-2">
                        <label for="additional_info" class="block text-sm font-medium text-gray-700">Any other information?</label>
                        <textarea name="additional_info" 
                                  id="additional_info" 
                                  rows="3"
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('additional_info', $member?->additional_info) }}</textarea>
                    </div>
                @endif

                @if(in_array('member_status', $config['optional']))
                    <div>
                        <label for="member_status" class="block text-sm font-medium text-gray-700">Member Status</label>
                        <select name="member_status" 
                                id="member_status" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select status</option>
                            <option value="visitor" {{ old('member_status', $member?->member_status) == 'visitor' ? 'selected' : '' }}>Visitor</option>
                            <option value="member" {{ old('member_status', $member?->member_status) == 'member' ? 'selected' : '' }}>Member</option>
                            <option value="volunteer" {{ old('member_status', $member?->member_status) == 'volunteer' ? 'selected' : '' }}>Volunteer</option>
                            <option value="leader" {{ old('member_status', $member?->member_status) == 'leader' ? 'selected' : '' }}>Leader</option>
                            <option value="minister" {{ old('member_status', $member?->member_status) == 'minister' ? 'selected' : '' }}>Minister</option>
                        </select>
                    </div>
                @endif

                @if(in_array('growth_level', $config['optional']))
                    <div>
                        <label for="growth_level" class="block text-sm font-medium text-gray-700">Growth Level</label>
                        <select name="growth_level" 
                                id="growth_level" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select growth level</option>
                            <option value="new_believer" {{ old('growth_level', $member?->growth_level) == 'new_believer' ? 'selected' : '' }}>New Believer</option>
                            <option value="growing" {{ old('growth_level', $member?->growth_level) == 'growing' ? 'selected' : '' }}>Growing</option>
                            <option value="mature" {{ old('growth_level', $member?->growth_level) == 'mature' ? 'selected' : '' }}>Mature</option>
                            <option value="leader" {{ old('growth_level', $member?->growth_level) == 'leader' ? 'selected' : '' }}>Leader</option>
                        </select>
                    </div>
                @endif

                @if(in_array('teci_status', $config['optional']))
                    <div>
                        <label for="teci_status" class="block text-sm font-medium text-gray-700">TECI Status</label>
                        <select name="teci_status" 
                                id="teci_status" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select TECI status</option>
                            <option value="not_started" {{ old('teci_status', $member?->teci_status) == 'not_started' ? 'selected' : '' }}>Not Started</option>
                            <option value="100_level" {{ old('teci_status', $member?->teci_status) == '100_level' ? 'selected' : '' }}>100 Level</option>
                            <option value="200_level" {{ old('teci_status', $member?->teci_status) == '200_level' ? 'selected' : '' }}>200 Level</option>
                            <option value="300_level" {{ old('teci_status', $member?->teci_status) == '300_level' ? 'selected' : '' }}>300 Level</option>
                            <option value="400_level" {{ old('teci_status', $member?->teci_status) == '400_level' ? 'selected' : '' }}>400 Level</option>
                            <option value="500_level" {{ old('teci_status', $member?->teci_status) == '500_level' ? 'selected' : '' }}>500 Level</option>
                            <option value="graduated" {{ old('teci_status', $member?->teci_status) == 'graduated' ? 'selected' : '' }}>Graduated</option>
                            <option value="paused" {{ old('teci_status', $member?->teci_status) == 'paused' ? 'selected' : '' }}>Paused</option>
                        </select>
                    </div>
                @endif

                @if(in_array('occupation', $config['optional']))
                    <div>
                        <label for="occupation" class="block text-sm font-medium text-gray-700">Occupation</label>
                        <input type="text" 
                               name="occupation" 
                               id="occupation" 
                               value="{{ old('occupation', $member?->occupation) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                @endif

                @if(in_array('nearest_bus_stop', $config['optional']))
                    <div>
                        <label for="nearest_bus_stop" class="block text-sm font-medium text-gray-700">Nearest Bus Stop</label>
                        <input type="text" 
                               name="nearest_bus_stop" 
                               id="nearest_bus_stop" 
                               value="{{ old('nearest_bus_stop', $member?->nearest_bus_stop) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                @endif

                @if(in_array('anniversary', $config['optional']))
                    <div>
                        <label for="anniversary" class="block text-sm font-medium text-gray-700">Anniversary</label>
                        <input type="date" 
                               name="anniversary" 
                               id="anniversary" 
                               value="{{ old('anniversary', $member?->anniversary?->format('Y-m-d')) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                @endif

                @if(in_array('date_joined', $config['optional']))
                    <div>
                        <label for="date_joined" class="block text-sm font-medium text-gray-700">Date Joined</label>
                        <input type="date" 
                               name="date_joined" 
                               id="date_joined" 
                               value="{{ old('date_joined', $member?->date_joined?->format('Y-m-d')) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                @endif

                @if(in_array('leadership_trainings', $config['optional']))
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Leadership Trainings</label>
                        <div class="grid grid-cols-2 gap-2">
                            @php
                                $leadershipTrainings = old('leadership_trainings', $member?->leadership_trainings ?? []);
                                if (is_string($leadershipTrainings)) {
                                    $leadershipTrainings = json_decode($leadershipTrainings, true) ?? [];
                                }
                            @endphp
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="leadership_trainings[]" 
                                       value="ELP" 
                                       {{ in_array('ELP', $leadershipTrainings) ? 'checked' : '' }}
                                       class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm">ELP (Emerging Leaders Program)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="leadership_trainings[]" 
                                       value="MLCC" 
                                       {{ in_array('MLCC', $leadershipTrainings) ? 'checked' : '' }}
                                       class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm">MLCC (Ministry Leadership Core Course)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="leadership_trainings[]" 
                                       value="MLCP Basic" 
                                       {{ in_array('MLCP Basic', $leadershipTrainings) ? 'checked' : '' }}
                                       class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm">MLCP Basic</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="leadership_trainings[]" 
                                       value="MLCP Advanced" 
                                       {{ in_array('MLCP Advanced', $leadershipTrainings) ? 'checked' : '' }}
                                       class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm">MLCP Advanced</span>
                            </label>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

<x-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Member Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php
                $user = Auth::user();
                $member = $user->member;
            @endphp

            @if($member)
                <!-- Profile Completion Status -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Profile Completion</h3>
                            <span class="text-sm font-medium text-blue-600">{{ $member->profile_completion_percentage ?? 0 }}% Complete</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $member->profile_completion_percentage ?? 0 }}%"></div>
                        </div>
                        <p class="text-sm text-gray-600 mt-2">Complete your profile to get the most out of your LifePointe experience.</p>
                    </div>
                </div>

                <!-- Tab Navigation -->
                <div class="bg-white shadow-sm sm:rounded-lg mb-6" x-data="profileTabs()">
                    <!-- Toast Notification -->
                    <div x-show="showToast" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform translate-y-2"
                         class="fixed top-4 right-4 z-50 max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
                        <div class="p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg x-show="toastType === 'success'" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <svg x-show="toastType === 'error'" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3 w-0 flex-1 pt-0.5">
                                    <p class="text-sm font-medium text-gray-900" x-text="toastMessage"></p>
                                </div>
                                <div class="ml-4 flex-shrink-0 flex">
                                    <button @click="showToast = false" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <span class="sr-only">Close</span>
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                            <button @click="activeTab = 'details'" 
                                    :class="activeTab === 'details' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Profile Details
                            </button>
                            <button @click="activeTab = 'edit'" 
                                    :class="activeTab === 'edit' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Edit Profile
                            </button>
                            <button @click="activeTab = 'security'" 
                                    :class="activeTab === 'security' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Security
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6">
                        <!-- Profile Details Tab -->
                        <div x-show="activeTab === 'details'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">Your Member Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Personal Information -->
                                <div class="space-y-4">
                                    <h4 class="font-medium text-gray-900">Personal Information</h4>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Full Name</label>
                                            <p class="text-gray-900">{{ $member->name ?? 'Not provided' }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">First Name</label>
                                            <p class="text-gray-900">{{ $member->first_name ?? 'Not provided' }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Surname</label>
                                            <p class="text-gray-900">{{ $member->surname ?? 'Not provided' }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Email</label>
                                            <p class="text-gray-900">{{ $member->email ?? 'Not provided' }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Phone</label>
                                            <p class="text-gray-900">{{ $member->phone ?? 'Not provided' }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Date of Birth</label>
                                            <p class="text-gray-900">{{ $member->date_of_birth ? $member->date_of_birth->format('M d, Y') : 'Not provided' }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Gender</label>
                                            <p class="text-gray-900">{{ ucfirst($member->gender ?? 'Not provided') }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Marital Status</label>
                                            <p class="text-gray-900">{{ ucfirst($member->marital_status ?? 'Not provided') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Church Information -->
                                <div class="space-y-4">
                                    <h4 class="font-medium text-gray-900">Church Information</h4>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Branch</label>
                                            <p class="text-gray-900">{{ $member->branch->name ?? 'Not assigned' }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Member Status</label>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($member->member_status === 'member') bg-green-100 text-green-800
                                                @elseif($member->member_status === 'leader') bg-blue-100 text-blue-800
                                                @elseif($member->member_status === 'minister') bg-purple-100 text-purple-800
                                                @elseif($member->member_status === 'visitor') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst($member->member_status ?? 'Not set') }}
                                            </span>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Growth Level</label>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($member->growth_level === 'new_believer') bg-green-100 text-green-800
                                                @elseif($member->growth_level === 'growing') bg-blue-100 text-blue-800
                                                @elseif($member->growth_level === 'mature') bg-purple-100 text-purple-800
                                                @elseif($member->growth_level === 'leader') bg-orange-100 text-orange-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst(str_replace('_', ' ', $member->growth_level ?? 'Not set')) }}
                                            </span>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">TECI Status</label>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($member->teci_status === 'completed') bg-green-100 text-green-800
                                                @elseif($member->teci_status === 'in_progress') bg-blue-100 text-blue-800
                                                @elseif($member->teci_status === 'not_started') bg-gray-100 text-gray-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst(str_replace('_', ' ', $member->teci_status ?? 'Not set')) }}
                                            </span>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Date Joined</label>
                                            <p class="text-gray-900">{{ $member->date_joined ? $member->date_joined->format('M d, Y') : 'Not provided' }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Anniversary</label>
                                            <p class="text-gray-900">{{ $member->anniversary ? $member->anniversary->format('M d') : 'Not provided' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Information -->
                                <div class="space-y-4 md:col-span-2">
                                    <h4 class="font-medium text-gray-900">Additional Information</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-3">
                                            <div>
                                                <label class="text-sm font-medium text-gray-500">Occupation</label>
                                                <p class="text-gray-900">{{ $member->occupation ?? 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-gray-500">Home Address</label>
                                                <p class="text-gray-900">{{ $member->home_address ?? 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-gray-500">Nearest Bus Stop</label>
                                                <p class="text-gray-900">{{ $member->nearest_bus_stop ?? 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-gray-500">Age Group</label>
                                                <p class="text-gray-900">{{ $member->age_group ?? 'Not provided' }}</p>
                                            </div>
                                        </div>
                                        <div class="space-y-3">
                                            <div>
                                                <label class="text-sm font-medium text-gray-500">Preferred Call Time</label>
                                                <p class="text-gray-900">{{ $member->preferred_call_time ?? 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-gray-500">Discovery Source</label>
                                                <p class="text-gray-900">{{ $member->discovery_source ?? 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-gray-500">Staying Intention</label>
                                                <p class="text-gray-900">{{ $member->staying_intention ?? 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-gray-500">Closest Location</label>
                                                <p class="text-gray-900">{{ $member->closest_location ?? 'Not provided' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($member->prayer_request)
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Prayer Request</label>
                                            <p class="text-gray-900 mt-1">{{ $member->prayer_request }}</p>
                                        </div>
                                    @endif

                                    @if($member->additional_info)
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Additional Information</label>
                                            <p class="text-gray-900 mt-1">{{ $member->additional_info }}</p>
                                        </div>
                                    @endif

                                    @if($member->leadership_trainings && count($member->leadership_trainings) > 0)
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Leadership Trainings</label>
                                            <div class="mt-1">
                                                @foreach($member->leadership_trainings as $training)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2 mb-2">
                                                        {{ $training }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Edit Profile Tab -->
                        <div x-show="activeTab === 'edit'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">Edit Your Profile</h3>
                            <p class="text-gray-600 mb-6">Update the information you can modify yourself.</p>
                            
                            <form @submit.prevent="submitForm" x-ref="editForm" class="space-y-6">
                                @csrf
                                @method('PUT')
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Personal Information -->
                                    <div class="space-y-4">
                                        <h4 class="font-medium text-gray-900">Personal Information</h4>
                                        
                                        <div>
                                            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                                            <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $member->first_name) }}" 
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            @error('first_name')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="surname" class="block text-sm font-medium text-gray-700">Surname</label>
                                            <input type="text" name="surname" id="surname" value="{{ old('surname', $member->surname) }}" 
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            @error('surname')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                            <input type="tel" name="phone" id="phone" value="{{ old('phone', $member->phone) }}" 
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            @error('phone')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                                            <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', $member->date_of_birth?->format('Y-m-d')) }}" 
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            @error('date_of_birth')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="anniversary" class="block text-sm font-medium text-gray-700">Anniversary</label>
                                            <input type="date" name="anniversary" id="anniversary" value="{{ old('anniversary', $member->anniversary?->format('Y-m-d')) }}" 
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            @error('anniversary')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                                            <select name="gender" id="gender" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                <option value="">Select Gender</option>
                                                <option value="male" {{ old('gender', $member->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                                <option value="female" {{ old('gender', $member->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                            </select>
                                            @error('gender')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="marital_status" class="block text-sm font-medium text-gray-700">Marital Status</label>
                                            <select name="marital_status" id="marital_status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                <option value="">Select Marital Status</option>
                                                <option value="single" {{ old('marital_status', $member->marital_status) === 'single' ? 'selected' : '' }}>Single</option>
                                                <option value="married" {{ old('marital_status', $member->marital_status) === 'married' ? 'selected' : '' }}>Married</option>
                                                <option value="divorced" {{ old('marital_status', $member->marital_status) === 'divorced' ? 'selected' : '' }}>Divorced</option>
                                                <option value="widowed" {{ old('marital_status', $member->marital_status) === 'widowed' ? 'selected' : '' }}>Widowed</option>
                                            </select>
                                            @error('marital_status')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Additional Information -->
                                    <div class="space-y-4">
                                        <h4 class="font-medium text-gray-900">Additional Information</h4>
                                        
                                        <div>
                                            <label for="occupation" class="block text-sm font-medium text-gray-700">Occupation</label>
                                            <input type="text" name="occupation" id="occupation" value="{{ old('occupation', $member->occupation) }}" 
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            @error('occupation')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="home_address" class="block text-sm font-medium text-gray-700">Home Address</label>
                                            <textarea name="home_address" id="home_address" rows="3" 
                                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('home_address', $member->home_address) }}</textarea>
                                            @error('home_address')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="nearest_bus_stop" class="block text-sm font-medium text-gray-700">Nearest Bus Stop</label>
                                            <input type="text" name="nearest_bus_stop" id="nearest_bus_stop" value="{{ old('nearest_bus_stop', $member->nearest_bus_stop) }}" 
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            @error('nearest_bus_stop')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="age_group" class="block text-sm font-medium text-gray-700">Age Group</label>
                                            <select name="age_group" id="age_group" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                <option value="">Select age group</option>
                                                <option value="15-20" {{ old('age_group', $member->age_group) == '15-20' ? 'selected' : '' }}>15-20</option>
                                                <option value="21-25" {{ old('age_group', $member->age_group) == '21-25' ? 'selected' : '' }}>21-25</option>
                                                <option value="26-30" {{ old('age_group', $member->age_group) == '26-30' ? 'selected' : '' }}>26-30</option>
                                                <option value="31-35" {{ old('age_group', $member->age_group) == '31-35' ? 'selected' : '' }}>31-35</option>
                                                <option value="36-40" {{ old('age_group', $member->age_group) == '36-40' ? 'selected' : '' }}>36-40</option>
                                                <option value="above-40" {{ old('age_group', $member->age_group) == 'above-40' ? 'selected' : '' }}>Above 40</option>
                                            </select>
                                            @error('age_group')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="closest_location" class="block text-sm font-medium text-gray-700">Which location is closest to you?</label>
                                            <select name="closest_location" id="closest_location" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                <option value="">Select closest location</option>
                                                <option value="Ajah - DKK" {{ old('closest_location', $member->closest_location) == 'Ajah - DKK' ? 'selected' : '' }}>Ajah - DKK</option>
                                                <option value="DKK - Novare Mall" {{ old('closest_location', $member->closest_location) == 'DKK - Novare Mall' ? 'selected' : '' }}>DKK - Novare Mall</option>
                                                <option value="Crown Estate - Abijo" {{ old('closest_location', $member->closest_location) == 'Crown Estate - Abijo' ? 'selected' : '' }}>Crown Estate - Abijo</option>
                                                <option value="Abijo - Awoyaya" {{ old('closest_location', $member->closest_location) == 'Abijo - Awoyaya' ? 'selected' : '' }}>Abijo - Awoyaya</option>
                                                <option value="Eputu" {{ old('closest_location', $member->closest_location) == 'Eputu' ? 'selected' : '' }}>Eputu</option>
                                            </select>
                                            @error('closest_location')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="prayer_request" class="block text-sm font-medium text-gray-700">Prayer Request</label>
                                            <textarea name="prayer_request" id="prayer_request" rows="3" 
                                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('prayer_request', $member->prayer_request) }}</textarea>
                                            @error('prayer_request')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="additional_info" class="block text-sm font-medium text-gray-700">Additional Information</label>
                                            <textarea name="additional_info" id="additional_info" rows="3" 
                                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('additional_info', $member->additional_info) }}</textarea>
                                            @error('additional_info')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Security Tab -->
                        <div x-show="activeTab === 'security'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">Security Settings</h3>
                            
                            <div class="space-y-8">
                                <!-- Change Password -->
                                <div class="bg-white border border-gray-200 rounded-lg p-6">
                                    <h4 class="font-medium text-gray-900 mb-4">Change Password</h4>
                                    <form @submit.prevent="submitPasswordForm" x-ref="passwordForm" class="space-y-4">
                                        @csrf
                                        @method('PUT')
                                        
                                        <div>
                                            <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                                            <input type="password" name="current_password" id="current_password" required 
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        </div>

                                        <div>
                                            <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                                            <input type="password" name="password" id="password" required 
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        </div>

                                        <div>
                                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                                            <input type="password" name="password_confirmation" id="password_confirmation" required 
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        </div>

                                        <div class="flex justify-end">
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                Update Password
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Delete Account -->
                                <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                                    <h4 class="font-medium text-red-900 mb-4">Delete Account</h4>
                                    <p class="text-red-700 text-sm mb-4">
                                        Once you delete your account, there is no going back. Please be certain.
                                    </p>
                                    <button @click="showDeleteModal = true" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Delete Account
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="text-center">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">No Member Profile Found</h3>
                            <p class="text-gray-600 mb-4">You don't have a member profile associated with your account.</p>
                            <a href="{{ route('member.profile-completion') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Create Member Profile
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div x-data="{ showDeleteModal: false }" x-show="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDeleteModal = false"></div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Account</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to delete your account? This action cannot be undone.
                                </p>
                                <div class="mt-4">
                                    <label for="delete_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                    <input type="password" name="password" id="delete_password" required 
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                           placeholder="Enter your password to confirm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form @submit.prevent="submitDeleteForm" x-ref="deleteForm" class="sm:ml-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm">
                            Delete Account
                        </button>
                    </form>
                    <button @click="showDeleteModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function profileTabs() {
            return {
                activeTab: 'details',
                showToast: false,
                toastMessage: '',
                toastType: 'success',
                submitForm() {
                    const form = this.$refs.editForm;
                    const formData = new FormData(form);
                    
                    fetch('{{ route('member.profile.update') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.toastMessage = data.message;
                            this.toastType = 'success';
                            this.showToast = true;
                            
                            // Auto-hide toast after 5 seconds
                            setTimeout(() => {
                                this.showToast = false;
                            }, 5000);
                            
                            // Update profile completion percentage if provided
                            if (data.profile_completion !== undefined) {
                                // You could update the profile completion display here if needed
                            }
                        } else {
                            this.toastMessage = data.message || 'An error occurred while updating your profile.';
                            this.toastType = 'error';
                            this.showToast = true;
                            
                            setTimeout(() => {
                                this.showToast = false;
                            }, 5000);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.toastMessage = 'An error occurred while updating your profile. Please try again.';
                        this.toastType = 'error';
                        this.showToast = true;
                        
                        setTimeout(() => {
                            this.showToast = false;
                        }, 5000);
                    });
                },
                submitPasswordForm() {
                    const form = this.$refs.passwordForm;
                    const formData = new FormData(form);
                    
                    fetch('{{ route('password.update') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.toastMessage = data.message || 'Password updated successfully.';
                            this.toastType = 'success';
                            this.showToast = true;
                            
                            // Clear the form
                            form.reset();
                            
                            // Auto-hide toast after 5 seconds
                            setTimeout(() => {
                                this.showToast = false;
                            }, 5000);
                        } else {
                            this.toastMessage = data.message || 'An error occurred while updating your password.';
                            this.toastType = 'error';
                            this.showToast = true;
                            
                            setTimeout(() => {
                                this.showToast = false;
                            }, 5000);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.toastMessage = 'An error occurred while updating your password. Please try again.';
                        this.toastType = 'error';
                        this.showToast = true;
                        
                        setTimeout(() => {
                            this.showToast = false;
                        }, 5000);
                    });
                },
                submitDeleteForm() {
                    const form = this.$refs.deleteForm;
                    const formData = new FormData(form);
                    
                    fetch('{{ route('profile.destroy') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.toastMessage = data.message || 'Account deleted successfully.';
                            this.toastType = 'success';
                            this.showToast = true;
                            
                            // Redirect to home page after 3 seconds
                            setTimeout(() => {
                                window.location.href = '/';
                            }, 3000);
                        } else {
                            this.toastMessage = data.message || 'An error occurred while deleting your account.';
                            this.toastType = 'error';
                            this.showToast = true;
                            
                            setTimeout(() => {
                                this.showToast = false;
                            }, 5000);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.toastMessage = 'An error occurred while deleting your account. Please try again.';
                        this.toastType = 'error';
                        this.showToast = true;
                        
                        setTimeout(() => {
                            this.showToast = false;
                        }, 5000);
                    });
                }
            }
        }
    </script>
</x-sidebar-layout>

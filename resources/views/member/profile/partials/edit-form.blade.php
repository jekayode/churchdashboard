@php($member = $member ?? Auth::user()->member)

<form @submit.prevent="submitForm" x-ref="editForm" class="space-y-6">
    @csrf
    @method('PUT')

    <!-- Images -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Profile Photo</label>
            <img :src="previewProfile || '{{ $member->getFirstMediaUrl('profile_image','thumb') }}'" class="h-24 w-24 rounded-full object-cover border" alt="profile">
            <input type="file" x-ref="profileFile" @change="onProfileChange" accept="image/*" class="mt-2">
        </div>
        <template x-if="maritalStatus==='married'">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Couple Photo</label>
                <img :src="previewCouple || '{{ $member->getFirstMediaUrl('couple_image','medium') }}'" class="h-24 w-24 rounded object-cover border" alt="couple">
                <input type="file" x-ref="coupleFile" @change="onCoupleChange" accept="image/*" class="mt-2">
            </div>
        </template>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
            <h4 class="font-medium text-gray-900">Personal Information</h4>
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $member->first_name) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="surname" class="block text-sm font-medium text-gray-700">Surname</label>
                <input type="text" name="surname" id="surname" value="{{ old('surname', $member->surname) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="tel" name="phone" id="phone" value="{{ old('phone', $member->phone) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', $member->date_of_birth?->format('Y-m-d')) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="anniversary" class="block text-sm font-medium text-gray-700">Anniversary</label>
                <input type="date" name="anniversary" id="anniversary" value="{{ old('anniversary', $member->anniversary?->format('Y-m-d')) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                <select name="gender" id="gender" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">Select Gender</option>
                    <option value="male" {{ old('gender', $member->gender) === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ old('gender', $member->gender) === 'female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>
            <div>
                <label for="marital_status" class="block text-sm font-medium text-gray-700">Marital Status</label>
                <select name="marital_status" id="marital_status" x-model="maritalStatus" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">Select Marital Status</option>
                    <option value="single" {{ old('marital_status', $member->marital_status) === 'single' ? 'selected' : '' }}>Single</option>
                    <option value="married" {{ old('marital_status', $member->marital_status) === 'married' ? 'selected' : '' }}>Married</option>
                    <option value="divorced" {{ old('marital_status', $member->marital_status) === 'divorced' ? 'selected' : '' }}>Divorced</option>
                    <option value="widowed" {{ old('marital_status', $member->marital_status) === 'widowed' ? 'selected' : '' }}>Widowed</option>
                </select>
            </div>

            <!-- Spouse selection (only when married) -->
            <template x-if="maritalStatus==='married'">
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700">Spouse</label>
                    <input type="hidden" name="spouse_id" :value="selectedSpouse?.id || ''">
                    <div class="mt-1">
                        <input type="text" x-model="spouseQuery" @input="queueSearchSpouses" placeholder="Search name or phone" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div class="mt-1 text-sm text-gray-600" x-show="selectedSpouse">
                        Selected: <span class="font-medium" x-text="selectedSpouse?.name"></span>
                        <button type="button" @click="clearSpouse" class="ml-2 text-blue-600 hover:underline">Change</button>
                    </div>
                    <!-- Results dropdown -->
                    <div x-show="showResults" @click.away="showResults=false" class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-md max-h-60 overflow-auto">
                        <template x-if="searching">
                            <div class="px-3 py-2 text-sm text-gray-500">Searching…</div>
                        </template>
                        <template x-for="m in spouseResults" :key="m.id">
                            <button type="button" @click="selectSpouse(m)" class="w-full text-left px-3 py-2 hover:bg-gray-50">
                                <div class="text-sm font-medium" x-text="m.name"></div>
                            </button>
                        </template>
                        <div class="px-3 py-2 text-sm text-gray-500" x-show="!searching && spouseResults.length===0">No matches</div>
                    </div>
                </div>
            </template>
        </div>

        <div class="space-y-4">
            <h4 class="font-medium text-gray-900">Additional Information</h4>
            <div>
                <label for="occupation" class="block text-sm font-medium text-gray-700">Occupation</label>
                <input type="text" name="occupation" id="occupation" value="{{ old('occupation', $member->occupation) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="home_address" class="block text-sm font-medium text-gray-700">Home Address</label>
                <textarea name="home_address" id="home_address" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('home_address', $member->home_address) }}</textarea>
            </div>
            <div>
                <label for="nearest_bus_stop" class="block text-sm font-medium text-gray-700">Nearest Bus Stop</label>
                <input type="text" name="nearest_bus_stop" id="nearest_bus_stop" value="{{ old('nearest_bus_stop', $member->nearest_bus_stop) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
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
            </div>
            <div>
                <label for="prayer_request" class="block text-sm font-medium text-gray-700">Prayer Request</label>
                <textarea name="prayer_request" id="prayer_request" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('prayer_request', $member->prayer_request) }}</textarea>
            </div>
            <div>
                <label for="additional_info" class="block text-sm font-medium text-gray-700">Additional Information</label>
                <textarea name="additional_info" id="additional_info" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('additional_info', $member->additional_info) }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Update Profile</button>
    </div>
</form>



@php
    $member = $member ?? Auth::user()->member;
@endphp

<div class="space-y-6">
    <!-- Personal & Church Info -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <section class="bg-white rounded-lg border border-gray-200 p-6">
            <h4 class="text-base font-medium text-gray-900 mb-4">Personal Information</h4>
            <div>
                <dt class="text-sm text-gray-500">Profile Photo</dt>
                <dd class="mt-2">
                    @php
                        $profileUrl = $member->getFirstMediaUrl('profile_image', 'thumb');
                    @endphp
                    @if ($profileUrl)
                        <img src="{{ $profileUrl }}" class="h-24 w-24 rounded-full object-cover border" alt="Profile photo">
                    @else
                        <div class="h-24 w-24 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 border">N/A</div>
                    @endif
                </dd>
            </div>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                <div><dt class="text-sm text-gray-500">Full Name</dt><dd class="text-sm text-gray-900">{{ $member->name ?? 'Not provided' }}</dd></div>
                <div><dt class="text-sm text-gray-500">Email</dt><dd class="text-sm text-gray-900">{{ $member->email ?? 'Not provided' }}</dd></div>
                <div><dt class="text-sm text-gray-500">Phone</dt><dd class="text-sm text-gray-900">{{ $member->phone ?? 'Not provided' }}</dd></div>
                <div><dt class="text-sm text-gray-500">Date of Birth</dt><dd class="text-sm text-gray-900">{{ $member->date_of_birth ? $member->date_of_birth->format('M d, Y') : 'Not provided' }}</dd></div>
                <div><dt class="text-sm text-gray-500">Gender</dt><dd class="text-sm text-gray-900">{{ ucfirst($member->gender ?? 'Not provided') }}</dd></div>
                <div><dt class="text-sm text-gray-500">Marital Status</dt><dd class="text-sm text-gray-900">{{ ucfirst($member->marital_status ?? 'Not provided') }}</dd></div>
            </dl>
        </section>
        <section class="bg-white rounded-lg border border-gray-200 p-6">
            <h4 class="text-base font-medium text-gray-900 mb-4">Church Information</h4>
            @php
                $memberStatusClass = match($member->member_status){
                    'member' => 'bg-green-100 text-green-800',
                    'leader' => 'bg-blue-100 text-blue-800',
                    'minister' => 'bg-purple-100 text-purple-800',
                    'visitor' => 'bg-yellow-100 text-yellow-800',
                    default => 'bg-gray-100 text-gray-800'
                };
                $growthClass = match($member->growth_level){
                    'new_believer' => 'bg-emerald-100 text-emerald-800',
                    'growing' => 'bg-sky-100 text-sky-800',
                    'core' => 'bg-secondary-100 text-secondary-800',
                    'pastor' => 'bg-church-100 text-church-800',
                    default => 'bg-gray-100 text-gray-800'
                };
                $teciClass = match($member->teci_status){
                    'completed' => 'bg-green-100 text-green-800',
                    'in_progress' => 'bg-blue-100 text-blue-800',
                    'not_started' => 'bg-gray-100 text-gray-800',
                    default => 'bg-gray-100 text-gray-800'
                };
            @endphp
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                <div><dt class="text-sm text-gray-500">Branch</dt><dd class="text-sm text-gray-900">{{ $member->branch->name ?? 'Not assigned' }}</dd></div>
                <div><dt class="text-sm text-gray-500">Member Status</dt><dd class="mt-1"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $memberStatusClass }}">{{ ucfirst($member->member_status ?? 'Not set') }}</span></dd></div>
                <div><dt class="text-sm text-gray-500">Growth Level</dt><dd class="mt-1"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $growthClass }}">{{ ucfirst(str_replace('_', ' ', $member->growth_level ?? 'Not set')) }}</span></dd></div>
                <div><dt class="text-sm text-gray-500">TECI Status</dt><dd class="mt-1"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $teciClass }}">{{ ucfirst(str_replace('_', ' ', $member->teci_status ?? 'Not set')) }}</span></dd></div>
                @if(!empty($member->leadership_trainings))
                    <div class="sm:col-span-2">
                        <dt class="text-sm text-gray-500">Leadership Trainings</dt>
                        <dd class="mt-2 flex flex-wrap gap-2">
                            @foreach(is_array($member->leadership_trainings) ? $member->leadership_trainings : (json_decode($member->leadership_trainings, true) ?? []) as $training)
                                @php
                                    $badge = match($training){
                                        'ELP' => 'bg-emerald-100 text-emerald-800',
                                        'MLCC' => 'bg-indigo-100 text-indigo-800',
                                        'MLCP Basic' => 'bg-sky-100 text-sky-800',
                                        'MLCP Advanced' => 'bg-purple-100 text-purple-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ $training }}</span>
                            @endforeach
                        </dd>
                    </div>
                @endif
                <div><dt class="text-sm text-gray-500">Date Joined</dt><dd class="text-sm text-gray-900">{{ $member->date_joined ? $member->date_joined->format('M d, Y') : 'Not provided' }}</dd></div>
                <div><dt class="text-sm text-gray-500">Anniversary</dt><dd class="text-sm text-gray-900">{{ $member->anniversary ? $member->anniversary->format('M d') : 'Not provided' }}</dd></div>
            </dl>
        </section>
    </div>

    <!-- Family -->
    <section class="bg-white rounded-lg border border-gray-200 p-6">
        <h4 class="text-base font-medium text-gray-900 mb-4">Family</h4>
        <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-3 mb-6">
            <div><dt class="text-sm text-gray-500">Marital Status</dt><dd class="text-sm text-gray-900">{{ ucfirst($member->marital_status ?? 'Not provided') }}</dd></div>
            <div><dt class="text-sm text-gray-500">Anniversary</dt><dd class="text-sm text-gray-900">{{ $member->anniversary ? $member->anniversary->format('M d') : 'Not provided' }}</dd></div>
            <div><dt class="text-sm text-gray-500">Spouse</dt><dd class="text-sm text-gray-900">{{ optional($member->spouse)->name ?? 'Not selected' }}</dd></div>
        </dl>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            
            <div>
                <dt class="text-sm text-gray-500">Couple Photo</dt>
                <dd class="mt-2">
                    @php
                        $coupleUrl = $member->getFirstMediaUrl('couple_image', 'medium');
                    @endphp
                    @if ($coupleUrl)
                        <img src="{{ $coupleUrl }}" class="h-24 w-24 rounded object-cover border" alt="Couple photo">
                    @else
                        <div class="h-24 w-24 rounded bg-gray-100 flex items-center justify-center text-gray-400 border">N/A</div>
                    @endif
                </dd>
            </div>
        </div>
    </section>

    <!-- Additional -->
    <section class="bg-white rounded-lg border border-gray-200 p-6">
        <h4 class="text-base font-medium text-gray-900 mb-4">Additional Information</h4>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
            <div><dt class="text-sm text-gray-500">Occupation</dt><dd class="text-sm text-gray-900">{{ $member->occupation ?? 'Not provided' }}</dd></div>
            <div><dt class="text-sm text-gray-500">Closest Location</dt><dd class="text-sm text-gray-900">{{ $member->closest_location ?? 'Not provided' }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-sm text-gray-500">Home Address</dt><dd class="text-sm text-gray-900">{{ $member->home_address ?? 'Not provided' }}</dd></div>
            <div><dt class="text-sm text-gray-500">Nearest Bus Stop</dt><dd class="text-sm text-gray-900">{{ $member->nearest_bus_stop ?? 'Not provided' }}</dd></div>
            <div><dt class="text-sm text-gray-500">Age Group</dt><dd class="text-sm text-gray-900">{{ $member->age_group ?? 'Not provided' }}</dd></div>
        </dl>
        @if($member->prayer_request)
            <div class="mt-4">
                <dt class="text-sm text-gray-500">Prayer Request</dt>
                <dd class="text-sm text-gray-900 mt-1">{{ $member->prayer_request }}</dd>
            </div>
        @endif
        @if($member->additional_info)
            <div class="mt-4">
                <dt class="text-sm text-gray-500">Additional Information</dt>
                <dd class="text-sm text-gray-900 mt-1">{{ $member->additional_info }}</dd>
            </div>
        @endif
    </section>
</div>



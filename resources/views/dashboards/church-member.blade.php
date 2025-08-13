<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Member Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php
                $user = Auth::user();
                $branch = $user->getPrimaryBranch();
                $member = $user->member; // Assuming user has a member relationship
            @endphp

            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-white">
                    <h3 class="text-2xl font-bold mb-2">Welcome, {{ $user->name }}!</h3>
                    <p class="text-purple-100">{{ $branch?->name ?? 'Church' }} Member - Growing in Faith</p>
                </div>
            </div>

            <!-- Personal Growth Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- TECI Status -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">TECI Status</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ $member ? ucwords(str_replace('_', ' ', $member->teci_status)) : 'Not Started' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Growth Level -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Growth Level</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ $member ? ucwords(str_replace('_', ' ', $member->growth_level)) : 'New Believer' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Events Attended -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Events Attended</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ $member ? \App\Models\EventRegistration::where('member_id', $member->id)->where('checked_in', true)->count() : 0 }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Small Group -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Small Group</p>
                                <p class="text-sm font-semibold text-gray-900">
                                    @if($member && $member->smallGroups()->exists())
                                        {{ $member->smallGroups()->first()->name }}
                                    @else
                                        Not Joined
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Events -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Upcoming Events</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($branch)
                            @foreach(\App\Models\Event::where('branch_id', $branch->id)->where('start_date', '>=', now())->orderBy('start_date')->limit(4)->get() as $event)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $event->title }}</p>
                                        <p class="text-sm text-gray-500">{{ $event->start_date->format('M d, Y g:i A') }}</p>
                                        <p class="text-xs text-gray-400">{{ $event->location }}</p>
                                    </div>
                                    <div class="flex space-x-2">
                                        @if($member && \App\Models\EventRegistration::where('event_id', $event->id)->where('member_id', $member->id)->exists())
                                            <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Registered</span>
                                        @else
                                            <a href="#" class="px-3 py-1 text-xs bg-blue-600 text-white rounded-full hover:bg-blue-700">Register</a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- My Small Groups -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">My Small Groups</h4>
                        <div class="space-y-3">
                            @if($member && $member->smallGroups()->exists())
                                @foreach($member->smallGroups as $group)
                                    <div class="p-3 bg-gray-50 rounded-lg">
                                        <p class="font-medium text-gray-900">{{ $group->name }}</p>
                                        <p class="text-sm text-gray-500">Leader: {{ $group->leader?->name ?? 'TBA' }}</p>
                                        <p class="text-xs text-gray-400">
                                            Meets: {{ ucfirst($group->meeting_day) }}s at {{ $group->meeting_time }}
                                        </p>
                                        <p class="text-xs text-gray-400">{{ $group->meeting_location }}</p>
                                    </div>
                                @endforeach
                            @else
                                <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                    <p class="text-sm text-yellow-800">You're not part of any small group yet.</p>
                                    <a href="{{ route('member.groups') }}" class="text-sm text-yellow-600 hover:text-yellow-800 font-medium">Join a Small Group</a>
                                </div>
                            @endif
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('member.groups') }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                                Browse Small Groups
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Growth Journey -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Your Growth Journey</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- TECI Progress -->
                        <div class="text-center">
                            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <h5 class="font-medium text-gray-900 mb-2">TECI Training</h5>
                            <p class="text-sm text-gray-600 mb-3">
                                Current: {{ $member ? ucwords(str_replace('_', ' ', $member->teci_status)) : 'Not Started' }}
                            </p>
                            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Continue Learning</a>
                        </div>

                        <!-- Ministry Involvement -->
                        <div class="text-center">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h5 class="font-medium text-gray-900 mb-2">Ministry Service</h5>
                            <p class="text-sm text-gray-600 mb-3">
                                @if($member && $member->departments()->exists())
                                    Serving in {{ $member->departments()->count() }} department(s)
                                @else
                                    Not serving yet
                                @endif
                            </p>
                            <a href="#" class="text-green-600 hover:text-green-800 text-sm font-medium">Find Ministry</a>
                        </div>

                        <!-- Community Connection -->
                        <div class="text-center">
                            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </div>
                            <h5 class="font-medium text-gray-900 mb-2">Community</h5>
                            <p class="text-sm text-gray-600 mb-3">
                                @if($member && $member->smallGroups()->exists())
                                    Connected in {{ $member->smallGroups()->count() }} group(s)
                                @else
                                    Ready to connect
                                @endif
                            </p>
                            <a href="{{ route('member.groups') }}" class="text-purple-600 hover:text-purple-800 text-sm font-medium">Join Community</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h4>
                    <div class="space-y-3">
                        @if($member)
                            @foreach(\App\Models\EventRegistration::where('member_id', $member->id)->with('event')->orderBy('created_at', 'desc')->limit(5)->get() as $registration)
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-shrink-0">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-gray-900">
                                            {{ $registration->checked_in ? 'Attended' : 'Registered for' }} 
                                            {{ $registration->event->title }}
                                        </p>
                                        <p class="text-xs text-gray-500">{{ $registration->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600">No recent activity. Start by registering for events!</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 
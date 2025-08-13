<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Department Leader Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php
                $user = Auth::user();
                $branch = $user->getPrimaryBranch();
                // Get departments where user is the leader
                $departments = \App\Models\Department::where('leader_id', $user->id)->with('ministry')->get();
            @endphp

            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-teal-600 to-green-600 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-white">
                    <h3 class="text-2xl font-bold mb-2">Welcome, {{ $user->name }}!</h3>
                    <p class="text-teal-100">Department Leader - Building Strong Teams</p>
                </div>
            </div>

            <!-- Department Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- My Departments -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">My Departments</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $departments->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Team Members -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Team Members</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ $departments->sum(function($dept) { return $dept->members()->count(); }) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Tasks -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Active Tasks</p>
                                <p class="text-2xl font-semibold text-gray-900">12</p>
                                <p class="text-xs text-gray-400">3 due this week</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Department Events -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Upcoming Events</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ $departments->sum(function($dept) { 
                                        return \App\Models\Event::where('department_id', $dept->id)
                                            ->where('start_date', '>=', now())
                                            ->count(); 
                                    }) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Departments -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">My Departments</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($departments as $department)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <h5 class="font-medium text-gray-900">{{ $department->name }}</h5>
                                        <p class="text-sm text-gray-500">{{ $department->ministry->name }}</p>
                                    </div>
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                                        {{ $department->members()->count() }} Members
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mb-4">{{ $department->description }}</p>
                                
                                <!-- Team Members Preview -->
                                <div class="mb-4">
                                    <h6 class="text-sm font-medium text-gray-900 mb-2">Team Members</h6>
                                    <div class="space-y-1">
                                        @foreach($department->members()->limit(3)->get() as $member)
                                            <div class="flex items-center text-sm text-gray-600">
                                                <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                                {{ $member->name }}
                                            </div>
                                        @endforeach
                                        @if($department->members()->count() > 3)
                                            <div class="text-xs text-gray-400">
                                                +{{ $department->members()->count() - 3 }} more members
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="flex space-x-2">
                                    <a href="#" class="text-blue-600 hover:text-blue-800 text-sm">Manage Team</a>
                                    <a href="#" class="text-green-600 hover:text-green-800 text-sm">View Schedule</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Team Performance & Tasks -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Team Performance -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Team Performance</h4>
                        <div class="space-y-4">
                            @foreach($departments as $department)
                                <div class="border-l-4 border-blue-500 pl-4">
                                    <h5 class="font-medium text-gray-900">{{ $department->name }}</h5>
                                    <div class="mt-2 space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Active Members</span>
                                            <span class="font-medium">{{ $department->members()->count() }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">This Month Events</span>
                                            <span class="font-medium">
                                                {{ \App\Models\Event::where('department_id', $department->id)
                                                    ->whereMonth('start_date', now()->month)
                                                    ->count() }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Attendance Rate</span>
                                            <span class="font-medium text-green-600">85%</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Upcoming Tasks -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Upcoming Tasks & Deadlines</h4>
                        <div class="space-y-3">
                            <!-- Sample tasks - in real implementation, these would come from a tasks table -->
                            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Prepare worship schedule</p>
                                    <p class="text-xs text-gray-500">Worship Department</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-yellow-600 font-medium">Due Tomorrow</p>
                                    <p class="text-xs text-gray-400">High Priority</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Youth event planning</p>
                                    <p class="text-xs text-gray-500">Youth Department</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-blue-600 font-medium">Due Friday</p>
                                    <p class="text-xs text-gray-400">Medium Priority</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Monthly team meeting</p>
                                    <p class="text-xs text-gray-500">All Departments</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-green-600 font-medium">Next Week</p>
                                    <p class="text-xs text-gray-400">Low Priority</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All Tasks</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Recent Department Activity</h4>
                    <div class="space-y-3">
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-gray-900">New member joined Worship Department</p>
                                <p class="text-xs text-gray-500">2 hours ago</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-gray-900">Youth event scheduled for next month</p>
                                <p class="text-xs text-gray-500">1 day ago</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-gray-900">Team meeting completed with 100% attendance</p>
                                <p class="text-xs text-gray-500">3 days ago</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 
<x-sidebar-layout title="Ministry Leader Dashboard">
            @php
                $user = Auth::user();
                $leaderMemberId = $user->member?->id;
                // Preload ministries, their departments and members count for totals (avoids N+1)
                $ministries = \App\Models\Ministry::query()
                    ->with(['departments' => function ($q) {
                        $q->withCount('members');
                    }])
                    ->when($leaderMemberId, fn($q) => $q->where('leader_id', $leaderMemberId))
                    ->get();

                $totalDepartments = $ministries->sum(fn ($m) => $m->departments->count());
                $totalLeaders = $ministries->sum(fn ($m) => $m->departments->whereNotNull('leader_id')->count());
                $totalTeamMembers = $ministries->sum(fn ($m) => $m->departments->sum('members_count'));
                // Events are branch-scoped; count events in the branches of these ministries
                $branchIds = $ministries->pluck('branch_id')->unique()->filter();
                $ministryEvents = $branchIds->isNotEmpty()
                    ? \App\Models\Event::whereIn('branch_id', $branchIds)->count()
                    : 0;
            @endphp

            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-white">
                    <h3 class="text-2xl font-bold mb-2">Welcome, {{ $user->name }}!</h3>
                    <p class="text-indigo-100">Ministry Leader - Equipping the Saints</p>
                </div>
            </div>

            <!-- Ministry Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Departments -->
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
                                <p class="text-sm font-medium text-gray-500">Total Departments</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $totalDepartments }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Leaders (department leaders) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Leaders</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $totalLeaders }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Team Members -->
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
                                <p class="text-sm font-medium text-gray-500">Team Members</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $totalTeamMembers }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ministry Events -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Ministry Events</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $ministryEvents }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Ministries Overview -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">My Ministries</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($ministries as $ministry)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h5 class="font-medium text-gray-900">{{ $ministry->name }}</h5>
                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                        {{ \App\Models\Department::where('ministry_id', $ministry->id)->count() }} Departments
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mb-3">{{ $ministry->description }}</p>
                                
                                <!-- Department List -->
                                <div class="space-y-2">
                                    @foreach(\App\Models\Department::where('ministry_id', $ministry->id)->with('leader')->get() as $department)
                                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $department->name }}</p>
                                                <p class="text-xs text-gray-500">
                                                    Leader: {{ $department->leader?->name ?? 'Not Assigned' }}
                                                </p>
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                {{ $department->members()->count() }} members
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="mt-4 flex space-x-2">
                                    <a href="#" class="text-blue-600 hover:text-blue-800 text-sm">Manage Departments</a>
                                    <a href="#" class="text-green-600 hover:text-green-800 text-sm">View Events</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Recent Ministry Events -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Recent & Upcoming Ministry Events</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($ministries as $ministry)
                            @foreach(\App\Models\Event::where('branch_id', $ministry->branch_id)->orderBy('start_date', 'desc')->limit(2)->get() as $event)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $event->title }}</p>
                                        <p class="text-sm text-gray-500">{{ $ministry->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $event->start_date->format('M d, Y g:i A') }}</p>
                                        <p class="text-xs text-gray-400">
                                            {{ \App\Models\EventRegistration::where('event_id', $event->id)->count() }} Registered
                                        </p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $event->start_date > now() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $event->start_date > now() ? 'Upcoming' : 'Past' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
</x-sidebar-layout> 
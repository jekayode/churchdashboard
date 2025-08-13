<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Branch Pastor Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php
                $branch = Auth::user()->getPrimaryBranch();
            @endphp

            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-green-600 to-blue-600 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-white">
                    <h3 class="text-2xl font-bold mb-2">Welcome, Pastor {{ Auth::user()->name }}!</h3>
                    <p class="text-green-100">{{ $branch?->name ?? 'Branch' }} - Leading God's People</p>
                </div>
            </div>

            <!-- Branch Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Branch Members -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Branch Members</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ $branch ? \App\Models\Member::where('branch_id', $branch->id)->count() : 0 }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Ministries -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Active Ministries</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ $branch ? \App\Models\Ministry::where('branch_id', $branch->id)->count() : 0 }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Events -->
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
                                <p class="text-sm font-medium text-gray-500">Upcoming Events</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ $branch ? \App\Models\Event::where('branch_id', $branch->id)->where('start_date', '>=', now())->count() : 0 }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Small Groups -->
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
                                <p class="text-sm font-medium text-gray-500">Small Groups</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ $branch ? \App\Models\SmallGroup::where('branch_id', $branch->id)->count() : 0 }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ministry Overview -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Ministry Overview</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($branch)
                            @foreach(\App\Models\Ministry::where('branch_id', $branch->id)->with('leader')->get() as $ministry)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $ministry->name }}</p>
                                        <p class="text-sm text-gray-500">
                                            Leader: {{ $ministry->leader?->name ?? 'Not Assigned' }}
                                        </p>
                                        <p class="text-xs text-gray-400">
                                            {{ \App\Models\Department::where('ministry_id', $ministry->id)->count() }} Departments
                                        </p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="#" class="text-blue-600 hover:text-blue-800 text-sm">Manage</a>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Ministry Overview -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Ministry Overview</h4>
                        <div class="space-y-3">
                            @if($branch)
                                @foreach(\App\Models\Ministry::where('branch_id', $branch->id)->with('leader')->get() as $ministry)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $ministry->name }}</p>
                                            <p class="text-sm text-gray-500">
                                                Leader: {{ $ministry->leader?->name ?? 'Not Assigned' }}
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                {{ \App\Models\Department::where('ministry_id', $ministry->id)->count() }} Departments
                                            </p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm">Manage</a>
                                            <a href="#" class="text-green-600 hover:text-green-800 text-sm">View</a>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <div class="mt-4">
                            <a href="#" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                Manage Ministries
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Events -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Recent & Upcoming Events</h4>
                        <div class="space-y-3">
                            @if($branch)
                                @foreach(\App\Models\Event::where('branch_id', $branch->id)->orderBy('start_date', 'desc')->limit(4)->get() as $event)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $event->title }}</p>
                                            <p class="text-sm text-gray-500">{{ $event->start_date->format('M d, Y') }}</p>
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
                            @endif
                        </div>
                        <div class="mt-4">
                            <a href="#" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700">
                                Manage Events
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Branch Statistics -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Member Growth -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Member Growth</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">New Believers</span>
                                <span class="text-sm font-medium">
                                    {{ $branch ? \App\Models\Member::where('branch_id', $branch->id)->where('growth_level', 'new_believer')->count() : 0 }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Growing</span>
                                <span class="text-sm font-medium">
                                    {{ $branch ? \App\Models\Member::where('branch_id', $branch->id)->where('growth_level', 'growing')->count() : 0 }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Core Members</span>
                                <span class="text-sm font-medium">
                                    {{ $branch ? \App\Models\Member::where('branch_id', $branch->id)->where('growth_level', 'core')->count() : 0 }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Leaders</span>
                                <span class="text-sm font-medium">
                                    {{ $branch ? \App\Models\Member::where('branch_id', $branch->id)->where('growth_level', 'pastor')->count() : 0 }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TECI Progress -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">TECI Progress</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Not Started</span>
                                <span class="text-sm font-medium">
                                    {{ $branch ? \App\Models\Member::where('branch_id', $branch->id)->where('teci_status', 'not_started')->count() : 0 }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">100 Level</span>
                                <span class="text-sm font-medium">
                                    {{ $branch ? \App\Models\Member::where('branch_id', $branch->id)->where('teci_status', '100_level')->count() : 0 }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">200 Level</span>
                                <span class="text-sm font-medium">
                                    {{ $branch ? \App\Models\Member::where('branch_id', $branch->id)->where('teci_status', '200_level')->count() : 0 }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Graduated</span>
                                <span class="text-sm font-medium">
                                    {{ $branch ? \App\Models\Member::where('branch_id', $branch->id)->where('teci_status', 'graduated')->count() : 0 }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Overview -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Financial Overview</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">This Month Expenses</span>
                                <span class="text-sm font-medium">
                                    ${{ $branch ? number_format(\App\Models\Expense::where('branch_id', $branch->id)->whereMonth('expense_date', now()->month)->sum('total_cost'), 2) : '0.00' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">YTD Expenses</span>
                                <span class="text-sm font-medium">
                                    ${{ $branch ? number_format(\App\Models\Expense::where('branch_id', $branch->id)->whereYear('expense_date', now()->year)->sum('total_cost'), 2) : '0.00' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Attendance Target</span>
                                <span class="text-sm font-medium">
                                    {{ $branch ? \App\Models\Projection::where('branch_id', $branch->id)->whereYear('year', now()->year)->sum('attendance_target') : 0 }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View Financial Reports</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 
<x-sidebar-layout title="Welcome to Our Church">
            @php
                $user = Auth::user();
                $branch = $user->getPrimaryBranch();
                // Get upcoming public events
                $upcomingEvents = \App\Models\Event::where('is_public', true)
                    ->where('start_date', '>=', now())
                    ->orderBy('start_date')
                    ->limit(6)
                    ->get();
            @endphp

            <!-- Welcome Hero Section -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-8 text-white">
                    <h3 class="text-3xl font-bold mb-2">Welcome to {{ $branch->name ?? 'Our Church' }}!</h3>
                    <p class="text-blue-100 text-lg">Discover our community, join our events, and grow in faith together.</p>
                    <div class="mt-4">
                        <a href="#events" class="bg-white text-blue-600 px-6 py-2 rounded-lg font-semibold hover:bg-blue-50 transition-colors">
                            Explore Events
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Upcoming Events -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Upcoming Events</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $upcomingEvents->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Church Branches -->
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
                                <p class="text-sm font-medium text-gray-500">Church Branches</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ \App\Models\Branch::count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Ministries -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Active Ministries</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ \App\Models\Ministry::count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Community Members -->
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
                                <p class="text-sm font-medium text-gray-500">Community Members</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ \App\Models\Member::count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- About Our Church -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">About Our Church</h4>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="border-l-4 border-blue-500 pl-4">
                            <h5 class="font-medium text-gray-900">Our Mission</h5>
                            <p class="text-sm text-gray-600 mt-1">
                                To build a community of believers who grow in faith, serve with love, and share the Gospel with the world.
                            </p>
                        </div>
                        <div class="border-l-4 border-green-500 pl-4">
                            <h5 class="font-medium text-gray-900">Our Vision</h5>
                            <p class="text-sm text-gray-600 mt-1">
                                A thriving church community where every person discovers their purpose and grows in their relationship with God.
                            </p>
                        </div>
                        <div class="border-l-4 border-purple-500 pl-4">
                            <h5 class="font-medium text-gray-900">Our Values</h5>
                            <p class="text-sm text-gray-600 mt-1">
                                Faith, Community, Service, Growth, and Love guide everything we do as a church family.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Visit Us</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <h5 class="font-medium text-gray-900 mb-1">Location</h5>
                            <p class="text-sm text-gray-600">
                                {{ $branch->address ?? '123 Church Street' }}<br>
                                {{ $branch->city ?? 'Your City' }}, {{ $branch->state ?? 'State' }} {{ $branch->postal_code ?? '12345' }}
                            </p>
                        </div>
                        
                        <div class="text-center">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h5 class="font-medium text-gray-900 mb-1">Service Times</h5>
                            <p class="text-sm text-gray-600">
                                Sunday: 9:00 AM & 11:00 AM<br>
                                Wednesday: 7:00 PM
                            </p>
                        </div>
                        
                        <div class="text-center">
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <h5 class="font-medium text-gray-900 mb-1">Contact</h5>
                            <p class="text-sm text-gray-600">
                                {{ $branch->phone ?? '(555) 123-4567' }}<br>
                                {{ $branch->email ?? 'info@church.com' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
</x-sidebar-layout>

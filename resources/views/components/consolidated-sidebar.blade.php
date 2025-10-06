@props(['user' => null])

@php
    $user = $user ?? Auth::user();
    $primaryRole = $user?->getPrimaryRole();
    $isSuperAdmin = $user?->isSuperAdmin();
    $isBranchPastor = $user?->isBranchPastor();
    $isMinistryLeader = $user?->isMinistryLeader();
    $isDepartmentLeader = $user?->isDepartmentLeader();
    $isChurchMember = $user?->isChurchMember();
    
@endphp

<!-- Navigation -->
<nav class="mt-8 px-4">
    <div class="space-y-2">
        <!-- Dashboard - Always visible -->
        <a href="{{ route('dashboard') }}" 
           class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-green-100 text-green-700' : '' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
            </svg>
            Dashboard
        </a>

        @if($isBranchPastor)
            <!-- Members Management -->
            <div x-data="{ open: false }">
                <button @click="open = !open" 
                        class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors duration-200">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        Members
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-8 mt-2 space-y-1">
                    <a href="{{ route('pastor.members') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">All Members</a>
                    <a href="{{ route('pastor.import-export') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Import Members</a>
                </div>
            </div>

            <!-- Community -->
            <div x-data="{ open: false }">
                <button @click="open = !open" 
                        class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors duration-200">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Community
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-8 mt-2 space-y-1">
                    <a href="{{ route('pastor.small-groups') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Small Groups</a>
                    <a href="{{ route('pastor.small-groups.reports') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Small Group Reports</a>
                    <a href="{{ route('pastor.events') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Events</a>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Public Events</a>
                </div>
            </div>

            <!-- Branch Management -->
            <div x-data="{ open: false }">
                <button @click="open = !open" 
                        class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors duration-200">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Manage Branch
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-8 mt-2 space-y-1">
                    <a href="{{ route('pastor.projections') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Branch Projections</a>
                    <a href="{{ route('pastor.finances') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Finance</a>
                    <a href="{{ route('pastor.departments') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Departments</a>
                    <a href="{{ route('pastor.ministries') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Ministries</a>
                    <a href="{{ route('pastor.ministry-events') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Ministry Events</a>
                    <a href="{{ route('pastor.import-export') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Import/Export</a>
                </div>
            </div>

            <!-- Communication -->
            <div x-data="{ open: false }">
                <button @click="open = !open" 
                        class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors duration-200">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Communication
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-8 mt-2 space-y-1">
                    <a href="{{ route('pastor.communication.quick-send') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Quick Send</a>
                    <a href="{{ route('pastor.communication.mass-send') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Mass Communication</a>
                    <a href="{{ route('pastor.communication.templates') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Message Templates</a>
                    <a href="{{ route('pastor.communication.campaigns') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Email Campaigns</a>
                    <a href="{{ route('pastor.communication.logs') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Message Logs</a>
                    <a href="{{ route('pastor.communication.settings') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Settings</a>
                </div>
            </div>

            <!-- Reports -->
            <a href="{{ route('pastor.reports') }}" 
               class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors duration-200 {{ request()->routeIs('pastor.reports*') ? 'bg-green-100 text-green-700' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Reports
            </a>

            <!-- QR Scanner -->
            <a href="#" 
               class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors duration-200">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                </svg>
                QR Scanner
            </a>

        @endif

        @if($isSuperAdmin)
            <!-- Super Admin Navigation -->
            <div x-data="{ open: false }">
                <button @click="open = !open" 
                        class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors duration-200">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        Members
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-8 mt-2 space-y-1">
                    <a href="{{ route('admin.members') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">All Members</a>
                    <a href="{{ route('admin.import-export') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Import/Export</a>
                </div>
            </div>

            <!-- Community -->
            <div x-data="{ open: false }">
                <button @click="open = !open" 
                        class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors duration-200">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Community
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-8 mt-2 space-y-1">
                    <a href="{{ route('admin.small-groups') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Small Groups</a>
                    <a href="{{ route('admin.small-groups.reports') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Small Group Reports</a>
                    <a href="{{ route('admin.events') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Events</a>
                </div>
            </div>

            <!-- Branch Management -->
            <div x-data="{ open: false }">
                <button @click="open = !open" 
                        class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors duration-200">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Manage Branches
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-8 mt-2 space-y-1">
                    <a href="{{ route('admin.branches') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Branches</a>
                    <a href="{{ route('admin.projections') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Projections</a>
                    <a href="{{ route('admin.finances') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Finances</a>
                    <a href="{{ route('admin.departments') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Departments</a>
                    <a href="{{ route('admin.ministries') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Ministries</a>
                    <a href="{{ route('admin.import-export') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Import/Export</a>
                </div>
            </div>

            <!-- Communication -->
            <div x-data="{ open: false }">
                <button @click="open = !open" 
                        class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors duration-200">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Communication
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-8 mt-2 space-y-1">
                    <a href="{{ route('admin.communication.quick-send') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Quick Send</a>
                    <a href="{{ route('admin.communication.mass-send') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Mass Communication</a>
                    <a href="{{ route('admin.communication.templates') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Message Templates</a>
                    <a href="{{ route('admin.communication.campaigns') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Email Campaigns</a>
                    <a href="{{ route('admin.communication.logs') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Message Logs</a>
                    <a href="{{ route('admin.communication.settings') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Settings</a>
                </div>
            </div>

            <!-- Reports -->
            <a href="{{ route('admin.reports') }}" 
               class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors duration-200 {{ request()->routeIs('admin.reports*') ? 'bg-green-100 text-green-700' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Reports
            </a>

            <!-- QR Scanner -->
            <a href="#" 
               class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors duration-200">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                </svg>
                QR Scanner
            </a>

        @endif

        @if($isMinistryLeader)
            <!-- Ministry Leader Navigation -->
            <div x-data="{ open: false }">
                <button @click="open = !open" 
                        class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors duration-200">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        Ministry
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-8 mt-2 space-y-1">
                    <a href="{{ route('minister.dashboard') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Minister Dashboard</a>
                    <a href="{{ route('ministry.departments') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Ministry Departments</a>
                </div>
            </div>

            <!-- Events -->
            <a href="{{ route('ministry.events') }}" 
               class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors duration-200 {{ request()->routeIs('ministry.events*') ? 'bg-green-100 text-green-700' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Events
            </a>

        @endif

        @if($isDepartmentLeader)
            <!-- Department Leader Navigation -->
            <div x-data="{ open: false }">
                <button @click="open = !open" 
                        class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors duration-200">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        Team
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-8 mt-2 space-y-1">
                    <a href="{{ route('department.team') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">My Team</a>
                </div>
            </div>

        @endif

        @if($isChurchMember)
            <!-- Church Member Navigation -->
            <div x-data="{ open: false }">
                <button @click="open = !open" 
                        class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors duration-200">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        My Groups
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-8 mt-2 space-y-1">
                    <a href="{{ route('member.groups') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Small Groups</a>
                    <a href="{{ route('member.groups.reports') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Group Reports</a>
                    <a href="{{ route('member.departments') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">My Departments</a>
                </div>
            </div>

            <!-- Events -->
            <a href="{{ route('member.events') }}" 
               class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors duration-200 {{ request()->routeIs('member.events*') ? 'bg-green-100 text-green-700' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Events
            </a>
        @endif

        <!-- Settings - Always visible -->
        <div x-data="{ open: false }">
            <button @click="open = !open" 
                    class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors duration-200">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Settings
                </div>
                <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-8 mt-2 space-y-1">
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Profile</a>
                @if($isChurchMember || !$isSuperAdmin && !$isBranchPastor)
                    @php
                        $member = Auth::user()->member;
                        $profileComplete = $member && $member->profile_completion_percentage >= 100;
                    @endphp
                    @if($member)
                        <a href="{{ route('member.profile') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">My Profile</a>
                    @endif
                    @if(!$profileComplete)
                        <a href="{{ route('member.profile-completion') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-700">Complete Profile</a>
                    @endif
                @endif
            </div>
        </div>
    </div>
</nav>

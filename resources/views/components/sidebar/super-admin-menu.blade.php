{{-- Super Admin Navigation Menu --}}

<!-- System Administration -->
<div x-data="{ open: false }">
    <button @click="open = !open" 
            class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
            System Admin
        </div>
        <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-8 mt-2 space-y-1">
        <a href="{{ route('admin.branches') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700">Branches</a>
        <a href="{{ route('admin.users') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700">Users</a>
        <a href="{{ route('admin.projections') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700">Projections</a>
    </div>
</div>

<!-- Members Management -->
<div x-data="{ open: false }">
    <button @click="open = !open" 
            class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200">
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
        <a href="{{ route('pastor.members') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700">All Members</a>
        <a href="{{ route('guests.index') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700 {{ request()->routeIs('guests.*') ? 'bg-purple-50 text-purple-700' : '' }}">Guest Management</a>
        <a href="{{ route('admin.import-export') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700">Import/Export</a>
    </div>
</div>

<!-- Branch Management -->
<div x-data="{ open: false }">
    <button @click="open = !open" 
            class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            Branch Management
        </div>
        <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-8 mt-2 space-y-1">
        <a href="{{ route('pastor.ministries') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700">Ministries</a>
        <a href="{{ route('pastor.small-groups') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700">Small Groups</a>
        <a href="{{ route('pastor.small-groups.reports') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700">Group Reports</a>
        <a href="{{ route('admin.projections') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700">Expression Projections</a>
        <a href="{{ route('ministry.departments') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700">Departments</a>
        <a href="{{ route('department.team') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700">My Team</a>
        <a href="{{ route('ministry.events') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700">Ministry Events</a>
        <a href="{{ route('admin.import-export') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700">Import/Export</a>
    </div>
</div>

<!-- Events -->
<a href="{{ route('pastor.events') }}" 
   class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200 {{ request()->routeIs('pastor.events*') ? 'bg-purple-100 text-purple-700' : '' }}">
    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
    </svg>
    Events
</a>

<!-- Finances -->
<a href="{{ route('pastor.finances') }}" 
   class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200 {{ request()->routeIs('pastor.finances*') ? 'bg-purple-100 text-purple-700' : '' }}">
    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
    </svg>
    Finances
</a>

<!-- Reports -->
<div x-data="{ open: false }">
    <button @click="open = !open" 
            class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200 {{ request()->routeIs('admin.reports*') || request()->routeIs('admin.performance*') ? 'bg-purple-100 text-purple-700' : '' }}">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            Reports
        </div>
        <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-8 mt-2 space-y-1">
        <a href="{{ route('admin.reports') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700 {{ request()->routeIs('admin.reports') && !request()->routeIs('admin.reports.dashboard') ? 'bg-purple-50 text-purple-700' : '' }}">Event Reports</a>
        <a href="{{ route('admin.reports.dashboard') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700 {{ request()->routeIs('admin.reports.dashboard') ? 'bg-purple-50 text-purple-700' : '' }}">Report Dashboard</a>
        <a href="{{ route('admin.performance') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700 {{ request()->routeIs('admin.performance*') ? 'bg-purple-50 text-purple-700' : '' }}">Performance</a>
    </div>
</div>

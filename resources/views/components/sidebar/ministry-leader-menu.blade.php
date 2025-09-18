{{-- Ministry Leader Navigation Menu --}}

<!-- Ministry Management -->
<div x-data="{ open: false }">
    <button @click="open = !open" 
            class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            Ministry
        </div>
        <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-8 mt-2 space-y-1">
        <a href="{{ route('ministry.departments') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700">Departments</a>
        <a href="{{ route('department.team') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700">My Team</a>
        <a href="{{ route('ministry.events') }}" class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700">Ministry Events</a>
    </div>
</div>

<!-- Events -->
<a href="{{ route('ministry.events') }}" 
   class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200 {{ request()->routeIs('ministry.events*') ? 'bg-purple-100 text-purple-700' : '' }}">
    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
    </svg>
    Events
</a>

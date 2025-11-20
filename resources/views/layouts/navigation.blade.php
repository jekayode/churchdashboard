<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @auth
                        @php
                            $user = Auth::user();
                            $primaryRole = $user->getPrimaryRole();
                        @endphp

                        {{-- Super Admin Navigation --}}
                        @if($user->isSuperAdmin())
                            {{-- Manage Expressions Dropdown --}}
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" @click.away="open = false" 
                                    class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.*', 'pastor.ministries*', 'ministry.departments*', 'department.team*', 'ministry.events*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                    {{ __('Branches') }}
                                    <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div x-show="open" x-transition:enter="transition ease-out duration-200" 
                                     x-transition:enter-start="transform opacity-0 scale-95" 
                                     x-transition:enter-end="transform opacity-100 scale-100" 
                                     x-transition:leave="transition ease-in duration-75" 
                                     x-transition:leave-start="transform opacity-100 scale-100" 
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute left-0 top-full z-50 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                    <div class="py-1">
                                        <a href="{{ route('admin.branches') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.branches*') ? 'bg-gray-50 text-gray-900' : '' }}">
                                            {{ __('All Branches') }}
                                        </a>
                                        <a href="{{ route('admin.projections') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.projections*') ? 'bg-gray-50 text-gray-900' : '' }}">
                                            {{ __('Expression Projections') }}
                                        </a>
                                        <a href="{{ route('pastor.ministries') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('pastor.ministries*') ? 'bg-gray-50 text-gray-900' : '' }}">
                                            {{ __('Ministries') }}
                                        </a>
                                        <a href="{{ route('pastor.small-groups') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('pastor.small-groups*') && !request()->routeIs('pastor.small-groups.reports*') ? 'bg-gray-50 text-gray-900' : '' }}">
                                            {{ __('Life Groups') }}
                                        </a>
                                        <a href="{{ route('pastor.small-groups.reports') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('pastor.small-groups.reports*') ? 'bg-gray-50 text-gray-900' : '' }}">
                                            {{ __('Group Reports') }}
                                        </a>
                                        <a href="{{ route('ministry.departments') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('ministry.departments*') ? 'bg-gray-50 text-gray-900' : '' }}">
                                            {{ __('Departments') }}
                                        </a>
                                        <a href="{{ route('department.team') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('department.team*') ? 'bg-gray-50 text-gray-900' : '' }}">
                                            {{ __('My Team') }}
                                        </a>
                                        <a href="{{ route('ministry.events') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('ministry.events*') ? 'bg-gray-50 text-gray-900' : '' }}">
                                            {{ __('Ministry Events') }}
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <x-nav-link :href="route('admin.users')" :active="request()->routeIs('admin.users*')">
                                {{ __('Manage Users') }}
                            </x-nav-link>
                            <x-nav-link :href="route('pastor.members')" :active="request()->routeIs('pastor.members*')">
                                {{ __('Members') }}
                            </x-nav-link>
                            <x-nav-link :href="route('admin.reports')" :active="request()->routeIs('admin.reports*')">
                                {{ __('System Reports') }}
                            </x-nav-link>
                            <x-nav-link :href="route('pastor.events')" :active="request()->routeIs('pastor.events*')">
                                {{ __('Events') }}
                            </x-nav-link>
                            <x-nav-link :href="route('admin.import-export')" :active="request()->routeIs('admin.import-export*')">
                                {{ __('Import/Export') }}
                            </x-nav-link>
                            <x-nav-link :href="route('public.scanner')" :active="request()->routeIs('public.scanner')">
                                {{ __('QR Scanner') }}
                            </x-nav-link>
                        @endif

                        {{-- Branch Pastor Navigation --}}
                        @if($user->isBranchPastor() && !$user->isSuperAdmin())
                            {{-- Manage Expressions Dropdown for Branch Pastor --}}
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" @click.away="open = false" 
                                    class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('pastor.ministries*', 'ministry.departments*', 'department.team*', 'ministry.events*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                    {{ __('Manage Expression') }}
                                    <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div x-show="open" x-transition:enter="transition ease-out duration-200" 
                                     x-transition:enter-start="transform opacity-0 scale-95" 
                                     x-transition:enter-end="transform opacity-100 scale-100" 
                                     x-transition:leave="transition ease-in duration-75" 
                                     x-transition:leave-start="transform opacity-100 scale-100" 
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute left-0 top-full z-50 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                    <div class="py-1">
                                        <a href="{{ route('pastor.ministries') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('pastor.ministries*') ? 'bg-gray-50 text-gray-900' : '' }}">
                                            {{ __('Ministries') }}
                                        </a>
                                        <a href="{{ route('pastor.small-groups') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('pastor.small-groups*') && !request()->routeIs('pastor.small-groups.reports*') ? 'bg-gray-50 text-gray-900' : '' }}">
                                            {{ __('Life Groups') }}
                                        </a>
                                        <a href="{{ route('pastor.small-groups.reports') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('pastor.small-groups.reports*') ? 'bg-gray-50 text-gray-900' : '' }}">
                                            {{ __('Group Reports') }}
                                        </a>
                                        <a href="{{ route('admin.projections') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.projections*') ? 'bg-gray-50 text-gray-900' : '' }}">
                                            {{ __('Expression Projections') }}
                                        </a>
                                        <a href="{{ route('ministry.departments') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('ministry.departments*') ? 'bg-gray-50 text-gray-900' : '' }}">
                                            {{ __('Departments') }}
                                        </a>
                                        <a href="{{ route('department.team') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('department.team*') ? 'bg-gray-50 text-gray-900' : '' }}">
                                            {{ __('My Team') }}
                                        </a>
                                        <a href="{{ route('ministry.events') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('ministry.events*') ? 'bg-gray-50 text-gray-900' : '' }}">
                                            {{ __('Ministry Events') }}
                                        </a>
                                        <a href="{{ route('pastor.import-export') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('pastor.import-export*') ? 'bg-gray-50 text-gray-900' : '' }}">
                                            {{ __('Import/Export') }}
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <x-nav-link :href="route('pastor.members')" :active="request()->routeIs('pastor.members*')">
                                {{ __('Members') }}
                            </x-nav-link>
                            <x-nav-link :href="route('pastor.small-groups')" :active="request()->routeIs('pastor.small-groups*')">
                                {{ __('Small Groups') }}
                            </x-nav-link>
                            <x-nav-link :href="route('pastor.events')" :active="request()->routeIs('pastor.events*')">
                                {{ __('Events') }}
                            </x-nav-link>
                            <x-nav-link :href="route('pastor.finances')" :active="request()->routeIs('pastor.finances*')">
                                {{ __('Finances') }}
                            </x-nav-link>
                            <x-nav-link :href="route('admin.reports')" :active="request()->routeIs('admin.reports*')">
                                {{ __('Reports') }}
                            </x-nav-link>
                            <x-nav-link :href="route('public.scanner')" :active="request()->routeIs('public.scanner')">
                                {{ __('QR Scanner') }}
                            </x-nav-link>
                        @endif

                        {{-- Ministry Leader Navigation (when not Branch Pastor or Super Admin) --}}
                        @if($user->isMinistryLeader() && !$user->isBranchPastor() && !$user->isSuperAdmin())
                            <x-nav-link :href="route('minister.dashboard')" :active="request()->routeIs('minister.dashboard')">
                                {{ __('Minister Dashboard') }}
                            </x-nav-link>
                            <x-nav-link :href="route('ministry.departments')" :active="request()->routeIs('ministry.departments*')">
                                {{ __('Departments') }}
                            </x-nav-link>
                            <x-nav-link :href="route('ministry.events')" :active="request()->routeIs('ministry.events*')">
                                {{ __('Ministry Events') }}
                            </x-nav-link>
                        @endif

                        {{-- Department Leader Navigation (when not higher role) --}}
                        @if($user->isDepartmentLeader() && !$user->isMinistryLeader() && !$user->isBranchPastor() && !$user->isSuperAdmin())
                            <x-nav-link :href="route('department.team')" :active="request()->routeIs('department.team*')">
                                {{ __('My Team') }}
                            </x-nav-link>
                        @endif

                        {{-- Small Group Leader quick link --}}
                        @php $activeBranchId = $user->getPrimaryBranch()?->id; @endphp
                        @if($user->isSmallGroupLeader($activeBranchId))
                            <x-nav-link :href="route('pastor.small-groups.reports')" :active="request()->routeIs('pastor.small-groups.reports*') || request()->routeIs('member.groups.reports')">
                                {{ __('My Group Reports') }}
                            </x-nav-link>
                        @endif

                        {{-- Communities Dropdown (for church members and above) --}}
                        @if($primaryRole?->name !== 'public_user')
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" @click.away="open = false" 
                                    class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('member.events*', 'member.groups*', 'public.events*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                    {{ __('Communities') }}
                                    <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div x-show="open" x-transition:enter="transition ease-out duration-200" 
                                     x-transition:enter-start="transform opacity-0 scale-95" 
                                     x-transition:enter-end="transform opacity-100 scale-100" 
                                     x-transition:leave="transition ease-in duration-75" 
                                     x-transition:leave-start="transform opacity-100 scale-100" 
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute left-0 top-full z-50 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                    <div class="py-1">
                                        <a href="{{ route('member.groups') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('member.groups*') ? 'bg-gray-50 text-gray-900' : '' }}">
                                            {{ __('Life Groups') }}
                                        </a>
                                        <a href="{{ route('member.events') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('member.events*') ? 'bg-gray-50 text-gray-900' : '' }}">
                                            {{ __('Events') }}
                                        </a>
                                        <a href="{{ route('public.events') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('public.events*') ? 'bg-gray-50 text-gray-900' : '' }}">
                                            {{ __('Public Events') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Public/Visitor Navigation --}}
                            <x-nav-link :href="route('public.events')" :active="request()->routeIs('public.events*')">
                                {{ __('Public Events') }}
                            </x-nav-link>
                        @endif

                        <x-nav-link :href="route('public.about')" :active="request()->routeIs('public.about*')">
                            {{ __('About Us') }}
                        </x-nav-link>
                    @endauth
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    {{-- Branch Selector for Multi-Branch Users --}}
                    @if(Auth::user()->roles()->count() > 1)
                        <x-dropdown align="right" width="48" class="me-4">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ Auth::user()->getPrimaryBranch()?->name ?? 'Select Branch' }}</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                @foreach(Auth::user()->getBranches()->get() as $branch)
                                    <x-dropdown-link href="{{ route('switch.branch', $branch->id) }}">
                                        {{ $branch->name }}
                                    </x-dropdown-link>
                                @endforeach
                            </x-slot>
                        </x-dropdown>
                    @endif

                    {{-- User Dropdown --}}
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <div class="flex flex-col items-start">
                                    <div>{{ Auth::user()->name }}</div>
                                    @if(Auth::user()->getPrimaryRole())
                                        <div class="text-xs text-gray-400">{{ Auth::user()->getPrimaryRole()->display_name }}</div>
                                    @endif
                                </div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            @if(Auth::user()->getPrimaryRole()?->name !== 'public_user')
                                <x-dropdown-link :href="route('member.profile')">
                                    {{ __('Member Profile') }}
                                </x-dropdown-link>
                            @endif
                        @php
                            $member = Auth::user()->member;
                            $profileComplete = $member && $member->profile_completion_percentage >= 100;
                        @endphp
                        @if($member)
                            <x-dropdown-link :href="route('member.profile')">
                                {{ __('My Profile') }}
                            </x-dropdown-link>
                        @endif
                        @if(!$profileComplete)
                            <x-dropdown-link :href="route('member.profile-completion')">
                                {{ __('Complete Profile') }}
                            </x-dropdown-link>
                        @endif
                        <x-dropdown-link :href="route('sidebar-sample')">
                            {{ __('Sidebar Sample') }}
                        </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @auth
                @php
                    $user = Auth::user();
                    $primaryRole = $user->getPrimaryRole();
                @endphp

                {{-- Mobile Super Admin Navigation --}}
                @if($user->isSuperAdmin())
                    <!-- Manage Expressions Section -->
                    <div class="px-4 py-2">
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Manage Expressions</div>
                    </div>
                    <x-responsive-nav-link :href="route('admin.branches')" :active="request()->routeIs('admin.branches*')">
                        {{ __('All Branches') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.projections')" :active="request()->routeIs('admin.projections*')">
                        {{ __('Expression Projections') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('pastor.ministries')" :active="request()->routeIs('pastor.ministries*')">
                        {{ __('Ministries') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('ministry.departments')" :active="request()->routeIs('ministry.departments*')">
                        {{ __('Departments') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('department.team')" :active="request()->routeIs('department.team*')">
                        {{ __('My Team') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('ministry.events')" :active="request()->routeIs('ministry.events*')">
                        {{ __('Ministry Events') }}
                    </x-responsive-nav-link>
                    
                    <x-responsive-nav-link :href="route('admin.users')" :active="request()->routeIs('admin.users*')">
                        {{ __('Manage Users') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('pastor.members')" :active="request()->routeIs('pastor.members*')">
                        {{ __('Members') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.reports')" :active="request()->routeIs('admin.reports*')">
                        {{ __('System Reports') }}
                    </x-responsive-nav-link>
                @endif

                {{-- Mobile Branch Pastor Navigation --}}
                @if($user->isBranchPastor() && !$user->isSuperAdmin())
                    <!-- Manage Expression Section -->
                    <div class="px-4 py-2">
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Manage Expression</div>
                    </div>
                    <x-responsive-nav-link :href="route('pastor.ministries')" :active="request()->routeIs('pastor.ministries*')">
                        {{ __('Ministries') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.projections')" :active="request()->routeIs('admin.projections*')">
                        {{ __('Expression Projections') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('ministry.departments')" :active="request()->routeIs('ministry.departments*')">
                        {{ __('Departments') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('department.team')" :active="request()->routeIs('department.team*')">
                        {{ __('My Team') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('ministry.events')" :active="request()->routeIs('ministry.events*')">
                        {{ __('Ministry Events') }}
                    </x-responsive-nav-link>
                    
                    <x-responsive-nav-link :href="route('pastor.members')" :active="request()->routeIs('pastor.members*')">
                        {{ __('Members') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('pastor.finances')" :active="request()->routeIs('pastor.finances*')">
                        {{ __('Finances') }}
                    </x-responsive-nav-link>
                @endif

                {{-- Mobile Ministry Leader Navigation (when not Branch Pastor or Super Admin) --}}
                @if($user->isMinistryLeader() && !$user->isBranchPastor() && !$user->isSuperAdmin())
                    <x-responsive-nav-link :href="route('minister.dashboard')" :active="request()->routeIs('minister.dashboard')">
                        {{ __('Minister Dashboard') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('ministry.departments')" :active="request()->routeIs('ministry.departments*')">
                        {{ __('Departments') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('ministry.events')" :active="request()->routeIs('ministry.events*')">
                        {{ __('Ministry Events') }}
                    </x-responsive-nav-link>
                @endif

                {{-- Mobile Department Leader Navigation (when not higher role) --}}
                @if($user->isDepartmentLeader() && !$user->isMinistryLeader() && !$user->isBranchPastor() && !$user->isSuperAdmin())
                    <x-responsive-nav-link :href="route('department.team')" :active="request()->routeIs('department.team*')">
                        {{ __('My Team') }}
                    </x-responsive-nav-link>
                @endif

                {{-- Mobile Communities Section (for church members and above) --}}
                @if($primaryRole?->name !== 'public_user')
                    <!-- Communities Section -->
                    <div class="px-4 py-2">
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Communities</div>
                    </div>
                    <x-responsive-nav-link :href="route('member.groups')" :active="request()->routeIs('member.groups*')">
                        {{ __('Small Groups') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('member.events')" :active="request()->routeIs('member.events*')">
                        {{ __('Events') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('public.events')" :active="request()->routeIs('public.events*')">
                        {{ __('Public Events') }}
                    </x-responsive-nav-link>
                @else
                    {{-- Mobile Public/Visitor Navigation --}}
                    <x-responsive-nav-link :href="route('public.events')" :active="request()->routeIs('public.events*')">
                        {{ __('Public Events') }}
                    </x-responsive-nav-link>
                @endif
                <x-responsive-nav-link :href="route('public.about')" :active="request()->routeIs('public.about*')">
                    {{ __('About Us') }}
                </x-responsive-nav-link>
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            @auth
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                    @if(Auth::user()->getPrimaryRole())
                        <div class="font-medium text-xs text-gray-400">{{ Auth::user()->getPrimaryRole()->display_name }}</div>
                    @endif
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    @if(Auth::user()->getPrimaryRole()?->name !== 'public_user')
                        <x-responsive-nav-link :href="route('member.profile')">
                            {{ __('Member Profile') }}
                        </x-responsive-nav-link>
                    @endif
                        @php
                            $member = Auth::user()->member;
                            $profileComplete = $member && $member->profile_completion_percentage >= 100;
                        @endphp
                        @if($member)
                            <x-responsive-nav-link :href="route('member.profile')">
                                {{ __('My Profile') }}
                            </x-responsive-nav-link>
                        @endif
                        @if(!$profileComplete)
                            <x-responsive-nav-link :href="route('member.profile-completion')">
                                {{ __('Complete Profile') }}
                            </x-responsive-nav-link>
                        @endif
                        <x-responsive-nav-link :href="route('sidebar-sample')">
                            {{ __('Sidebar Sample') }}
                        </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</nav>

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

        @if($isSuperAdmin)
            <!-- Super Admin Navigation -->
            @include('components.sidebar.super-admin-menu')
        @elseif($isBranchPastor)
            <!-- Branch Pastor Navigation -->
            @include('components.sidebar.branch-pastor-menu')
        @elseif($isMinistryLeader)
            <!-- Ministry Leader Navigation -->
            @include('components.sidebar.ministry-leader-menu')
        @elseif($isDepartmentLeader)
            <!-- Department Leader Navigation -->
            @include('components.sidebar.department-leader-menu')
        @elseif($isChurchMember)
            <!-- Church Member Navigation -->
            @include('components.sidebar.church-member-menu')
        @else
            <!-- Public User Navigation -->
            @include('components.sidebar.public-user-menu')
        @endif

        <!-- Common Navigation Items -->
        @include('components.sidebar.common-menu')

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
                @if($isChurchMember)
                    @php
                        $member = Auth::user()->member;
                        $profileComplete = $member && $member->profile_completion_percentage >= 100;
                        // Debug: Remove this after testing
                        // echo "<!-- DEBUG: Member ID: " . ($member?->id ?? 'null') . ", Completion: " . ($member?->profile_completion_percentage ?? 'null') . ", Complete: " . ($profileComplete ? 'true' : 'false') . " -->";
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

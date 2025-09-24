<x-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Member Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php
                $user = Auth::user();
                $member = $user->member;
            @endphp

            @if($member)
                <!-- Local nav -->
                <div class="mb-4 flex items-center gap-4">
                    <a href="{{ route('member.profile') }}" class="text-blue-600 font-medium">Details</a>
                    <a href="{{ route('member.profile.edit') }}" class="text-gray-600 hover:text-blue-600">Edit</a>
                    <a href="{{ route('member.profile.security') }}" class="text-gray-600 hover:text-blue-600">Security</a>
                </div>

                <!-- Profile Completion Status -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Profile Completion</h3>
                            <span class="text-sm font-medium text-blue-600">{{ $member->profile_completion_percentage ?? 0 }}% Complete</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $member->profile_completion_percentage ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Details -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    @include('member.profile.partials.details-content', ['member' => $member])
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="text-center">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">No Member Profile Found</h3>
                            <p class="text-gray-600 mb-4">You don't have a member profile associated with your account.</p>
                            <a href="{{ route('member.profile-completion') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Create Member Profile
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-sidebar-layout>



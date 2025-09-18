<x-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Complete Your Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Progress Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Profile Completion</h3>
                        <span class="text-sm font-medium text-blue-600">{{ $member->profile_completion_percentage }}% Complete</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                             style="width: {{ $member->profile_completion_percentage }}%"></div>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">
                        Complete your profile to get the most out of your LifePointe experience.
                    </p>
                </div>
            </div>

            <!-- Profile Completion Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8">
                    <form method="POST" action="{{ route('member.profile-completion') }}" class="space-y-6">
                        @csrf
                        
                        <!-- Member Form Component -->
                        <x-member-form 
                            :member="$member"
                            context="profile" 
                            :show-required="false" 
                            :show-optional="true" />

                        <!-- Action Buttons -->
                        <div class="flex justify-between">
                            <a href="{{ route('dashboard') }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Skip for now
                            </a>
                            
                            <button type="submit" 
                                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Save Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-sidebar-layout>

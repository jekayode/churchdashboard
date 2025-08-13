<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($role) ? $role . ' Dashboard' : __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            {{ __("Welcome, ") . Auth::user()->name }}!
                        </h3>
                        @if(isset($role))
                            <p class="text-sm text-gray-600">{{ __("You're logged in as: ") . $role }}</p>
                        @endif
                    </div>
                    
                    @if(Auth::user()->getPrimaryRole())
                        <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                            <h4 class="font-medium text-blue-900">Your Role & Branch</h4>
                            <p class="text-sm text-blue-700">
                                <strong>Role:</strong> {{ Auth::user()->getPrimaryRole()->display_name }}
                            </p>
                            @if(Auth::user()->getPrimaryBranch())
                                <p class="text-sm text-blue-700">
                                    <strong>Branch:</strong> {{ Auth::user()->getPrimaryBranch()->name }}
                                </p>
                            @endif
                        </div>
                    @endif

                    <div class="mt-6">
                        <p class="text-gray-600">
                            {{ __("This is your church dashboard. Here you can manage your church activities, view events, and connect with your community.") }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

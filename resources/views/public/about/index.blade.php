<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('About Us') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">About Our Church</h3>
                    <p class="text-gray-600 mb-4">Learn more about our church, mission, and values.</p>
                    
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <p class="text-gray-800">
                            <strong>Coming Soon:</strong> This page will allow you to:
                        </p>
                        <ul class="list-disc list-inside text-gray-700 mt-2">
                            <li>Read our mission and vision</li>
                            <li>Learn about our history</li>
                            <li>Meet our leadership team</li>
                            <li>Discover our core values</li>
                            <li>Find contact information</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 
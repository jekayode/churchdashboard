<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('First-Time Guest Registration') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-8 text-white text-center">
                    <h1 class="text-3xl font-bold mb-4">Welcome to LifePointe!</h1>
                    <p class="text-lg text-blue-100">We're excited to have you join our family. Please fill out this form to get started.</p>
                </div>
            </div>

            <!-- Registration Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8">
                    <form method="POST" action="{{ route('public.guest-register') }}" class="space-y-6">
                        @csrf
                        
                        <!-- Member Form Component -->
                        <x-member-form 
                            context="guest" 
                            :show-required="true" 
                            :show-optional="true" />

                        <!-- Consent Section -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" 
                                           name="consent_given" 
                                           id="consent_given" 
                                           value="1"
                                           class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded @error('consent_given') border-red-300 @enderror"
                                           required>
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="consent_given" class="font-medium text-gray-700">
                                        I consent to the processing of my personal data as described in the 
                                        <a href="#" class="text-blue-600 hover:text-blue-500 underline">privacy policy</a>
                                        <span class="text-red-500">*</span>
                                    </label>
                                    @error('consent_given')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Complete Registration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

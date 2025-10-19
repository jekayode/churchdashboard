<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'LifePointe Church') }} - Guest Registration</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
            <!-- Navigation -->
            <nav class="bg-gray-900" x-data="{ mobileMenuOpen: false }">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-20">
                        <!-- Logo -->
                        <div class="flex items-center">
                            <a href="/" class="flex-shrink-0 flex items-center">
                                <img src="https://lifepointeng.org/wp-content/uploads/2023/10/Lifepointe-Logo-White.png" alt="LifePointe" class="h-12 w-auto"/>
                            </a>
                        </div>
                        
                        <!-- Desktop Navigation -->
                        <div class="hidden md:flex items-center space-x-8">
                            <a href="{{ route('public.events') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                                Events
                            </a>
                            <a href="{{ route('public.lifegroups') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                                LifeGroups
                            </a>
                        </div>
                        
                        <!-- Login Button & Mobile Menu Button -->
                        <div class="flex items-center space-x-4">
                            <!-- Login Button -->
                            <a href="{{ route('login') }}" class="bg-[#F1592A] hover:bg-[#E54A1A] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">Login</a>
                            
                            <!-- Mobile menu button -->
                            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                                <span class="sr-only">Open main menu</span>
                                <!-- Hamburger icon -->
                                <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Mobile Navigation Menu -->
                    <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="md:hidden">
                        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-gray-800 rounded-lg mt-2">
                            <a href="{{ route('public.events') }}" class="text-gray-200 hover:text-white block px-3 py-2 rounded-md text-base font-medium">
                                Events
                            </a>
                            <a href="{{ route('public.lifegroups') }}" class="text-gray-200 hover:text-white block px-3 py-2 rounded-md text-base font-medium">
                                LifeGroups
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

        <!-- Main Content -->
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2 font-display">First-Time Guest Registration</h1>
                    <p class="text-gray-600">Welcome to LifePointe! Please fill out this form to get started.</p>
                </div>
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
                    <!-- Success/Error Messages -->
                    @if (session('success'))
                        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('public.guest-register') }}" class="space-y-6">
                        @csrf
                        
                        <!-- Member Form Component -->
                        <x-member-form 
                            context="guest" 
                            :show-required="true" 
                            :show-optional="true" 
                            :selected-branch-id="request('branch')" />

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

        <!-- Footer -->
        <footer class="bg-gray-900">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <!-- Our Locations -->
                <div class="mb-12">
                    <h3 class="text-lg font-semibold text-white mb-6">Our Locations</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" id="locationsGrid">
                        <!-- Locations will be loaded here -->
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Church Info -->
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-4">LifePointe Church</h3>
                        <p class="text-gray-300 text-sm mb-4">
                            We're a resting place for the weary and a signpost for the lost. 
                            Join us as we grow together in faith, community, and purpose.
                        </p>
                        <div class="flex space-x-4">
                            <a href="{{ route('public.events') }}" class="text-gray-400 hover:text-white transition-colors">
                                Events
                            </a>
                            <a href="{{ route('public.lifegroups') }}" class="text-gray-400 hover:text-white transition-colors">
                                LifeGroups
                            </a>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-4">Quick Links</h3>
                        <ul class="space-y-2">
                            <li><a href="{{ route('login') }}" class="text-gray-300 hover:text-white text-sm transition-colors">Login</a></li>
                            <li><a href="{{ route('register') }}" class="text-gray-300 hover:text-white text-sm transition-colors">Register</a></li>
                            <li><a href="{{ route('public.guest-register') }}" class="text-gray-300 hover:text-white text-sm transition-colors">Guest Registration</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Copyright -->
                <div class="border-t border-gray-700 mt-8 pt-8 text-center">
                    <p class="text-gray-400 text-sm">© LifePointe Church 2025. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>

    <script>
        // Load locations on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadLocations();
        });

        async function loadLocations() {
            try {
                const response = await fetch('/api/welcome/branches');
                const branches = await response.json();
                
                const locationsGrid = document.getElementById('locationsGrid');
                locationsGrid.innerHTML = branches.map(branch => `
                    <div class="text-gray-300">
                        <h4 class="font-semibold text-white mb-2">${branch.name}</h4>
                        <p class="text-sm mb-1">${branch.venue || ''}</p>
                        <p class="text-xs text-gray-400">${branch.service_time ? 'Service: ' + branch.service_time : ''}</p>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error loading locations:', error);
            }
        }
    </script>
</body>
</html>

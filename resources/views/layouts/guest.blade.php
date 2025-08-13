<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Church Dashboard') }} - @yield('title', 'Welcome')</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=playfair-display:400,500,600,700&family=montserrat:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <link rel="icon" type="image/png" href="/favicon.png">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Alpine.js -->
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

        @stack('styles')
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-church-500 via-secondary-500 to-church-600 relative overflow-hidden">
            <!-- Background Elements -->
            <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.05"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
            
            <!-- Floating Elements -->
            <div class="absolute top-10 left-10 w-20 h-20 bg-white/10 rounded-full blur-xl animate-pulse-slow"></div>
            <div class="absolute top-32 right-20 w-16 h-16 bg-secondary-300/20 rounded-full blur-lg animate-pulse-slow" style="animation-delay: 1s;"></div>
            <div class="absolute bottom-20 left-20 w-24 h-24 bg-church-300/15 rounded-full blur-xl animate-pulse-slow" style="animation-delay: 2s;"></div>
            <div class="absolute bottom-32 right-10 w-12 h-12 bg-white/10 rounded-full blur-lg animate-pulse-slow" style="animation-delay: 0.5s;"></div>

            <div class="relative z-10 w-full max-w-md">
                <!-- Logo/Brand Section -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl mb-4 shadow-worship">
                        <x-application-logo class="w-12 h-12 text-white" />
                    </div>
                    <h1 class="text-3xl font-display font-bold text-white text-shadow-lg mb-2">
                        {{ config('app.name', 'Church Dashboard') }}
                    </h1>
                    <p class="text-church-100 text-sm font-medium">
                        Connecting Faith • Building Community • Serving Together
                    </p>
                </div>

                <!-- Auth Card -->
                <div class="bg-white/95 backdrop-blur-sm shadow-2xl overflow-hidden rounded-2xl border border-white/20">
                    <div class="px-8 py-8">
                        {{ $slot }}
                    </div>
                    
                    <!-- Footer -->
                    <div class="px-8 py-4 bg-gray-50/80 border-t border-gray-200/50">
                        <div class="text-center">
                            <p class="text-xs text-gray-600">
                                Need help? Contact your church administrator
                            </p>
                            <div class="flex items-center justify-center space-x-4 mt-2">
                                <a href="#" class="text-xs text-church-600 hover:text-church-700 font-medium">
                                    Support
                                </a>
                                <span class="text-gray-300">•</span>
                                <a href="#" class="text-xs text-church-600 hover:text-church-700 font-medium">
                                    Privacy
                                </a>
                                <span class="text-gray-300">•</span>
                                <a href="#" class="text-xs text-church-600 hover:text-church-700 font-medium">
                                    Terms
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="mt-8 text-center">
                    <div class="inline-flex items-center space-x-2 text-white/80 text-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        <span>Secure church management platform</span>
                    </div>
                </div>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>

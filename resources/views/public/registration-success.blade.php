<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Church Dashboard') }} - Registration Successful</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <h1 class="text-xl font-bold text-gray-900">{{ config('app.name', 'Church Dashboard') }}</h1>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                        <a href="{{ route('public.events') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-md text-sm font-medium">Back to Events</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <!-- Success Icon -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
                    <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <!-- Success Message -->
                <h1 class="text-3xl font-bold text-gray-900 mb-4">Registration Successful!</h1>
                <p class="text-lg text-gray-600 mb-8">
                    You have been successfully registered for <strong>{{ $eventName ?? 'the event' }}</strong>.
                </p>

                <!-- Account Creation Notice -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3 text-left">
                            <h3 class="text-lg font-medium text-blue-900">Account Created</h3>
                            <div class="mt-2 text-blue-700">
                                <p class="mb-3">We've created an account for you to access additional church features and manage your event registrations.</p>
                                
                                <!-- Temporary Password Display (to be removed later) -->
                                @if(isset($generatedPassword))
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
                                        <p class="text-yellow-800 font-medium">Temporary Password (for testing):</p>
                                        <p class="text-yellow-900 font-mono text-lg bg-yellow-100 px-2 py-1 rounded mt-1">{{ $generatedPassword }}</p>
                                        <p class="text-yellow-700 text-sm mt-2">Note: This display is temporary and will be removed in production.</p>
                                    </div>
                                @endif

                                <div class="space-y-2">
                                    <p><strong>Email:</strong> {{ $userEmail ?? 'your registered email' }}</p>
                                    <p><strong>Next Steps:</strong></p>
                                    <ol class="list-decimal list-inside space-y-1 ml-4">
                                        <li>Check your email for your login credentials</li>
                                        <li>Use the login link below to access your account</li>
                                        <li>Update your password in your profile settings</li>
                                        <li>Explore additional church features and upcoming events</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Notice -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-8">
                    <div class="flex items-center justify-center">
                        <svg class="h-6 w-6 text-green-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <div class="text-left">
                            <h3 class="text-lg font-medium text-green-900">Check Your Email</h3>
                            <p class="text-green-700 mt-1">
                                We've sent your login credentials to <strong>{{ $userEmail ?? 'your email address' }}</strong>.
                                Please check your inbox (and spam folder) for your account details.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('login') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-6 rounded-md transition-colors">
                        Login to Your Account
                    </a>
                    <a href="{{ route('public.events') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-3 px-6 rounded-md transition-colors">
                        Browse More Events
                    </a>
                </div>

                <!-- Help Text -->
                <div class="mt-8 text-sm text-gray-500">
                    <p>Need help? Contact us at <a href="mailto:support@church.com" class="text-indigo-600 hover:text-indigo-800">support@church.com</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-redirect script (optional) -->
    <script>
        // Optional: Auto-redirect to login after 30 seconds
        // setTimeout(function() {
        //     if (confirm('Would you like to go to the login page now?')) {
        //         window.location.href = '{{ route('login') }}';
        //     }
        // }, 30000);
    </script>
</body>
</html> 
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Builders Community') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-slate-800">
    <header class="builders-site-header py-4">
        <div class="max-w-3xl mx-auto px-4 flex items-center justify-between">
            <a href="{{ route('builders.create') }}" class="flex items-center shrink-0" aria-label="Lifepointe GLK">
                <img
                    src="{{ asset('img/lifepoint-logo-white.png') }}"
                    alt="Lifepointe GLK"
                    class="h-9 w-auto max-w-[200px] object-contain"
                >
            </a>
            @auth
                <a href="{{ route('builders.account') }}" class="text-sm">My pack</a>
            @else
                <a href="{{ route('login') }}" class="text-sm">Log in</a>
            @endauth
        </div>
    </header>
    <main class="max-w-3xl mx-auto py-8 px-4 sm:px-6">
        @if(session('status'))
            <div class="mb-6 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>
    <footer class="border-t py-6 text-center text-sm text-gray-500">
        <p>&copy; {{ date('Y') }} Lifepointe Business &amp; Career Unit</p>
    </footer>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="api-token" content="{{ auth()->user()->createToken('spa')->plainTextToken }}">
    @endauth
    <title>@yield('title', 'Business Directory') — {{ config('app.name') }}</title>
    <meta name="description" content="@yield('meta_description', $settings->tagline ?? 'Discover businesses in our church community.')">
    <meta property="og:title" content="@yield('title', 'Business Directory')">
    <meta property="og:description" content="@yield('meta_description', $settings->tagline ?? 'Discover businesses in our church community.')">
    <meta property="og:image" content="@yield('og_image', $settings->logo_url ?? '')">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Business Directory')">
    <meta name="twitter:description" content="@yield('meta_description', $settings->tagline ?? 'Discover businesses in our church community.')">
    <meta name="twitter:image" content="@yield('og_image', $settings->logo_url ?? '')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --biz-primary: {{ $settings->primary_color ?? '#F1592A' }};
            --biz-secondary: {{ $settings->secondary_color ?? '#1e293b' }};
        }
        .biz-primary { color: var(--biz-primary); }
        .biz-bg-primary { background-color: var(--biz-primary); }
        .biz-border-primary { border-color: var(--biz-primary); }
        /* Yelp-style business profile: main left, contact sidebar right */
        .biz-profile-grid {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            width: 100%;
        }
        @media (min-width: 1024px) {
            .biz-profile-grid {
                display: grid;
                grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr);
                column-gap: 2.5rem;
                align-items: start;
            }
            .biz-profile-aside {
                position: sticky;
                top: 2rem;
            }
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 text-slate-800">
    <nav class="bg-[var(--biz-secondary)] text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="{{ route('biz.landing') }}" class="flex items-center shrink-0" aria-label="Church Business Directory">
                    <img
                        src="{{ $settings->logo_url ?? asset('img/lifepoint-logo-white.png') }}"
                        alt="{{ $settings->tagline ?: 'Church Business Directory' }}"
                        class="h-9 w-auto max-w-[220px] object-contain"
                    >
                </a>
                <div class="flex items-center gap-4 text-sm">
                    <a href="{{ route('biz.landing') }}" class="hover:text-orange-300">Browse</a>
                    <a href="{{ route('biz.changelog') }}" class="hover:text-orange-300">Changelog</a>
                    @auth
                        <a href="{{ route('biz.favorites') }}" class="hover:text-orange-300">Favorites</a>
                        <a href="{{ route('biz.messages') }}" class="hover:text-orange-300">Messages</a>
                        @if(auth()->user()->ownsBusinesses())
                            <a href="{{ route('biz.owner') }}" class="hover:text-orange-300">My Businesses</a>
                        @endif
                        @if(auth()->user()->isDirectoryAdmin())
                            <a href="{{ route('admin.biz.index') }}" class="hover:text-orange-300">Admin</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="biz-bg-primary px-3 py-1.5 rounded-md text-white">Login</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    @if($settings->announcement_active && $settings->announcement_title)
        <div class="biz-bg-primary text-white text-center py-2 px-4 text-sm">
            <strong>{{ $settings->announcement_title }}</strong>
            @if($settings->announcement_body)
                — {{ $settings->announcement_body }}
            @endif
            @if($settings->announcement_link)
                <a href="{{ $settings->announcement_link }}" class="underline ml-2">Learn more</a>
            @endif
        </div>
    @endif

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    <footer class="border-t mt-12 py-6 text-center text-sm text-gray-500">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }} Business Directory</p>
    </footer>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'LifePointe Church') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gradient-to-br from-church-50 via-white to-secondary-50">
            <!-- Navigation -->
            <nav class="bg-gray-900">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-22">
                        <div class="flex items-center">
                            <a href="/" class="flex-shrink-0 flex items-center">
                                <img src="https://lifepointeng.org/wp-content/uploads/2023/10/Lifepointe-Logo-White.png" alt="LifePointe" class="h-10 w-auto"/>
                            </a>
                        </div>
                        <div class="flex items-center space-x-6">
                            @if (Route::has('login'))
                                @auth
                                    <a href="{{ route('dashboard') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                                        Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                                        Login
                                    </a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="text-secondary-400 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                                            Register
                                        </a>
                                    @endif
                                @endauth
                            @endif
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Hero Section -->
            <div class="relative overflow-hidden">
                <div class="max-w-7xl mx-auto">
                    <div class="relative z-10 pb-8 bg-gradient-to-br from-church-50 to-secondary-50 sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
                        <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28 pt-6">
                            <div class="sm:text-center lg:text-left">
                                <h5 class="text-xs sm:text-sm font-semibold tracking-wide text-gray-900 uppercase">
                                    Welcome to LifePointe
                                </h5>
                                <h1 class="mt-3 text-4xl sm:text-5xl md:text-5xl font-extrabold text-gray-900 sm:max-w-xl sm:mx-auto lg:mx-0">
                                    We’re a resting place for the weary and a signpost for the lost
                                </h1>
                                <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                                    <div class="rounded-md shadow">
                                        <a href="{{ route('public.guest-register') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-church-500 hover:bg-church-600 md:py-4 md:text-lg md:px-10">
                                            First-Time Guest Registration
                                        </a>
                                    </div>
                                    <div class="mt-3 sm:mt-0 sm:ml-3">
                                        <a href="{{ route('public.events') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-church-700 bg-church-100 hover:bg-church-200 md:py-4 md:text-lg md:px-10">
                                            View Events
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </main>
                    </div>
                </div>
                <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2">
                    <img class="h-56 w-full object-cover sm:h-72 md:h-96 lg:w-full lg:h-full" src="img/home-hero.jpeg" alt="Church community">
                </div>
            </div>

            <!-- Features Section -->
            <div class="py-12 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="lg:text-center">
                        <h2 class="text-base text-church-600 font-semibold tracking-wide uppercase">What We Offer</h2>
                        <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                            A place to belong and grow
                        </p>
                    </div>

                    <div class="mt-10">
                        <div class="space-y-10 md:space-y-0 md:grid md:grid-cols-3 md:gap-x-8 md:gap-y-10">
                            <div class="relative">
                                <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-secondary-500 text-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <p class="mt-2 ml-16 text-base text-gray-500">
                                    <strong>LifeGroups</strong> - Connect with others in small groups for fellowship, study, and growth.
                                </p>
                            </div>

                            <div class="relative">
                                <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-secondary-500 text-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <p class="mt-2 ml-16 text-base text-gray-500">
                                    <strong>Membership Classes</strong> - Learn about our church, beliefs, and how to get involved.
                                </p>
                            </div>

                            <div class="relative">
                                <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-secondary-500 text-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </div>
                                <p class="mt-2 ml-16 text-base text-gray-500">
                                    <strong>Serving Opportunities</strong> - Use your gifts and talents to make a difference in our community.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LifeGroups (Small Groups) Section -->
            <section class="py-12 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="lg:text-center mb-8">
                        <h2 class="text-3xl font-extrabold tracking-tight text-gray-900">Find friends, family, and focus</h2>
                        <p class="mt-2 text-gray-600">Locate the Nearest Service to You</p>
                    </div>
                    <div x-data="lifegroups()" x-init="init()">
                        <div class="flex flex-col md:flex-row md:items-end gap-4 mb-6">
                            <div class="md:w-1/3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Expression (Branch)</label>
                                <select x-model="filters.branch_id" @change="load()" class="w-full rounded-lg border-gray-300">
                                    <option value="">All Expressions</option>
                                    <template x-for="b in branches" :key="b.id">
                                        <option :value="b.id" x-text="b.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="md:flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Search LifeGroups</label>
                                <input x-model.debounce.400ms="filters.q" @input="load()" type="text" placeholder="Search by name or location" class="w-full rounded-lg border-gray-300"/>
                            </div>
                        </div>
                        <!-- Loading State -->
                        <div x-show="loading" class="text-center py-8">
                            <div class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-church-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Loading LifeGroups...
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div x-show="!loading && groups.length === 0" class="text-center py-8">
                            <p class="text-gray-500">No LifeGroups found. Check back later!</p>
                        </div>

                        <!-- LifeGroups Grid -->
                        <div x-show="!loading && groups.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <template x-for="g in groups" :key="g.id">
                                <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <h3 class="font-semibold text-gray-900" x-text="g.name"></h3>
                                    <p class="text-sm text-gray-600" x-text="g.branch?.name"></p>
                                    <p class="text-sm text-gray-600" x-text="g.location"></p>
                                    <p class="text-xs text-gray-500 mt-1" x-text="g.meeting_day + ' ' + g.meeting_time"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Events Section -->
            <section class="py-12 bg-gray-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="eventsList()" x-init="init()">
                    <div class="flex flex-col md:flex-row md:items-end gap-4 mb-6">
                        <div class="md:w-1/3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expression (Branch)</label>
                            <select x-model="filters.branch_id" @change="load()" class="w-full rounded-lg border-gray-300">
                                <option value="">All Expressions</option>
                                <template x-for="b in branches" :key="b.id">
                                    <option :value="b.id" x-text="b.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="md:w-1/3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">When</label>
                            <select x-model="filters.when" @change="load()" class="w-full rounded-lg border-gray-300">
                                <option value="upcoming">Upcoming</option>
                                <option value="this_week">This week</option>
                                <option value="next_week">Next week</option>
                                <option value="past">Past</option>
                            </select>
                        </div>
                    </div>
                    <!-- Loading State -->
                    <div x-show="loading" class="text-center py-8">
                        <div class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-church-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Loading Events...
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div x-show="!loading && events.length === 0" class="text-center py-8">
                        <p class="text-gray-500">No events found for the selected criteria.</p>
                    </div>

                    <!-- Events Grid -->
                    <div x-show="!loading && events.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <template x-for="e in events" :key="e.id">
                            <div class="border rounded-lg p-4 bg-white hover:shadow-md transition-shadow">
                                <h3 class="font-semibold text-gray-900" x-text="e.name"></h3>
                                <p class="text-sm text-gray-600" x-text="e.branch?.name"></p>
                                <p class="text-xs text-gray-500 mt-1" x-text="e.start_date + ' ' + (e.start_time || '')"></p>
                                <p class="text-xs text-gray-500" x-text="e.location"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </section>

            <!-- CTA Section -->
            <div class="gradient-brand">
                <div class="max-w-2xl mx-auto text-center py-16 px-4 sm:py-20 sm:px-6 lg:px-8">
                    <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                        <span class="block">Ready to get started?</span>
                        <span class="block">Join us this Sunday!</span>
                    </h2>
                    <p class="mt-4 text-lg leading-6 text-church-50">
                        We'd love to have you join our community. Register as a first-time guest and we'll help you get connected.
                    </p>
                    <a href="{{ route('public.guest-register') }}" class="mt-8 w-full inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-church-700 bg-white hover:bg-church-50 sm:w-auto">
                        Register as Guest
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <footer class="bg-gray-900">
                <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8" x-data="footerExpressions()" x-init="init()">
                    <h3 class="text-lg font-semibold text-white mb-6">Expressions</h3>
                    
                    <!-- Loading State -->
                    <div x-show="loading" class="text-center py-4">
                        <div class="inline-flex items-center text-gray-400">
                            <svg class="animate-spin -ml-1 mr-3 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Loading Expressions...
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div x-show="!loading && branches.length === 0" class="text-center py-4">
                        <p class="text-gray-400">No expressions available.</p>
                    </div>

                    <!-- Branches Grid -->
                    <div x-show="!loading && branches.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <template x-for="b in branches" :key="b.id">
                            <div class="text-gray-300">
                                <h4 class="font-semibold text-white" x-text="b.name"></h4>
                                <p class="text-sm" x-text="b.venue || ''"></p>
                                <p class="text-xs text-gray-400" x-text="b.service_time ? ('Service Time: ' + b.service_time) : ''"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </footer>
        </div>

        <script>
            function lifegroups() {
                return {
                    branches: [],
                    groups: [],
                    filters: { branch_id: '', q: '' },
                    loading: false,
                    async init() { 
                        this.loading = true;
                        await this.loadBranches(); 
                        await this.load(); 
                        this.loading = false;
                    },
                    async loadBranches() {
                        try {
                            const res = await fetch('/api/welcome/branches');
                            this.branches = await res.json();
                        } catch (error) {
                            console.error('Error loading branches:', error);
                        }
                    },
                    async load() {
                        try {
                            const params = new URLSearchParams(this.filters).toString();
                            const res = await fetch(`/api/welcome/small-groups?${params}`);
                            this.groups = await res.json();
                        } catch (error) {
                            console.error('Error loading small groups:', error);
                        }
                    }
                }
            }

            function eventsList() {
                return {
                    branches: [],
                    events: [],
                    filters: { branch_id: '', when: 'upcoming' },
                    loading: false,
                    async init() { 
                        this.loading = true;
                        await this.loadBranches(); 
                        await this.load(); 
                        this.loading = false;
                    },
                    async loadBranches() {
                        try {
                            const res = await fetch('/api/welcome/branches');
                            this.branches = await res.json();
                        } catch (error) {
                            console.error('Error loading branches:', error);
                        }
                    },
                    async load() {
                        try {
                            const params = new URLSearchParams(this.filters).toString();
                            const res = await fetch(`/api/welcome/events?${params}`);
                            this.events = await res.json();
                        } catch (error) {
                            console.error('Error loading events:', error);
                        }
                    }
                }
            }

            function footerExpressions() {
                return {
                    branches: [],
                    loading: false,
                    async init() {
                        this.loading = true;
                        try {
                            const res = await fetch('/api/welcome/branches');
                            this.branches = await res.json();
                        } catch (error) {
                            console.error('Error loading branches:', error);
                        }
                        this.loading = false;
                    }
                }
            }
        </script>
    </body>
</html>
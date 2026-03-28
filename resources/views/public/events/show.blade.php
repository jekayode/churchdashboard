@php
    $branch = $event->branch;
    $typeLabel = $event->type === 'service' && $event->service_type
        ? $event->service_type
        : ucfirst(str_replace('_', ' ', $event->type ?? 'other'));
    $tz = config('app.timezone');
    $start = $event->start_date?->timezone($tz);
    $end = $event->end_date?->timezone($tz);
    $timeLine = '';
    if ($start) {
        $timeLine = $start->format('g:i A');
        if ($end) {
            $timeLine .= ' – '.$end->format('g:i A');
        }
        $timeLine .= ' '.$start->format('T');
    }
    $customFields = $event->custom_form_fields;
    if (is_string($customFields)) {
        $customFields = json_decode($customFields, true) ?? [];
    }
    $customFields = is_array($customFields) ? array_values(array_filter($customFields, fn ($f) => is_array($f))) : [];
@endphp


    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Church Dashboard') }} - Events</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="preload" href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"></noscript>
    <style>
        :root {
            --e-global-color-primary: #F1592A;
            --e-global-color-secondary: #9DC83B;
            --e-global-color-text: #606060;
            --e-global-color-accent: #FFAC93;
            --e-global-color-2df8a20: #1E1E1E;
            --e-global-color-a82e17b: #FFA589;
        }
    </style>
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
                        <a href="{{ route('public.events') }}" class="text-[#F1592A] hover:text-white px-3 py-2 rounded-md text-sm font-medium">
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
                        <a href="{{ route('public.events') }}" class="text-[#F1592A] hover:text-white block px-3 py-2 rounded-md text-base font-medium">
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
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">

            <nav class="mb-6 sm:mb-8 text-sm" aria-label="Breadcrumb">
                <a href="{{ route('public.events') }}" class="text-[#F1592A] hover:underline font-medium">Events</a>
                <span class="mx-2 text-[#606060]/60">/</span>
                <span class="text-[#1E1E1E] font-medium line-clamp-2 sm:line-clamp-none" style="color: var(--e-global-color-2df8a20);">{{ $event->name }}</span>
            </nav>

            {{-- Desktop: true two-panel layout 34% sidebar | 66% main. Mobile: sidebar stack first, then main. --}}
            <div class="flex flex-col lg:flex-row lg:items-start gap-8 sm:gap-10 lg:gap-10 xl:gap-12">
                {{-- LEFT: 34% sidebar — image, Expression, Event type --}}
                <aside class="w-full lg:w-[34%] lg:max-w-none lg:flex-shrink-0 space-y-6 sm:space-y-7">
                    {{-- A. Event image (poster / flyer) --}}
                    <div class="rounded-2xl overflow-hidden bg-white border border-[#FFA589]/40 shadow-[0_1px_3px_rgba(30,30,30,0.06),0_4px_12px_rgba(241,89,42,0.06)]">
                        @if($event->cover_image_url)
                            <img src="{{ $event->cover_image_url }}" alt="" class="w-full aspect-[3/4] object-cover object-center sm:min-h-[280px]" width="600" height="800" loading="eager">
                        @else
                            <div class="aspect-[3/4] min-h-[260px] sm:min-h-[320px] flex items-center justify-center px-6 py-10 bg-gradient-to-b from-[#FFAC93]/25 to-[#FFA589]/35">
                                <p class="text-center text-xl sm:text-2xl font-bold leading-snug text-[#1E1E1E]" style="font-family: 'Libre Baskerville', Georgia, serif;">{{ $event->name }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- B. Expression --}}
                    <div class="rounded-2xl bg-white p-5 sm:p-6 border border-[#FFA589]/35 shadow-sm">
                        <h2 class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#F1592A] mb-4">Expression</h2>
                        @if($branch)
                            <div class="space-y-3">
                                @if($branch->logo_url)
                                    <img src="{{ $branch->logo_url }}" alt="" class="h-14 w-14 rounded-full object-cover border border-[#FFAC93]/40 bg-white shadow-sm" width="56" height="56">
                                @endif
                                <p class="font-bold text-lg text-[#1E1E1E] leading-snug" style="font-family: 'Libre Baskerville', Georgia, serif;">{{ $branch->name }}</p>
                                @if($branch->venue)
                                    <p class="text-sm text-[#606060] leading-relaxed">{{ $branch->venue }}</p>
                                @endif
                                @if($branch->phone)
                                    <p class="text-sm">
                                        <a href="tel:{{ preg_replace('/\s+/', '', $branch->phone) }}" class="font-semibold text-[#F1592A] hover:text-[#D44A1F] transition-colors">{{ $branch->phone }}</a>
                                    </p>
                                @endif
                                @if($branch->email)
                                    <p class="text-sm break-all">
                                        <a href="mailto:{{ $branch->email }}" class="text-[#9DC83B] font-medium hover:underline">{{ $branch->email }}</a>
                                    </p>
                                @endif
                            </div>
                        @else
                            <p class="text-[#606060] text-sm">LifePointe Church</p>
                        @endif
                    </div>

                    {{-- C. Event type --}}
                    <div class="rounded-2xl bg-white p-5 sm:p-6 border border-[#9DC83B]/30 shadow-sm">
                        <h2 class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#9DC83B] mb-3">Event type</h2>
                        <p class="text-lg sm:text-xl font-semibold text-[#1E1E1E] leading-snug" style="font-family: 'Libre Baskerville', Georgia, serif;">{{ $typeLabel }}</p>
                        <span class="inline-flex mt-4 px-2.5 py-1 rounded-full text-[11px] font-semibold uppercase tracking-wide bg-[#9DC83B]/18 text-[#1E1E1E] border border-[#9DC83B]/25">#{{ \Illuminate\Support\Str::slug($typeLabel) }}</span>
                    </div>
                </aside>

                {{-- RIGHT: 66% main — title, meta, registration, about --}}
                <div class="w-full lg:w-[66%] lg:min-w-0 flex flex-col gap-6 sm:gap-7">
                    <header class="space-y-4 sm:space-y-5">
                        <h1 class="text-[1.75rem] sm:text-4xl lg:text-[2.5rem] lg:leading-[1.15] font-bold text-[#1E1E1E] tracking-tight" style="font-family: 'Libre Baskerville', Georgia, serif;">
                            {{ $event->name }}
                        </h1>

                        {{-- Compact meta: calendar + date, clock + time, pin + venue (primary icons) --}}
                        <div class="flex flex-col gap-3 sm:gap-3.5 pt-1 border-t border-[#FFAC93]/25">
                            @if($start)
                                <div class="flex items-start gap-3">
                                    <span class="shrink-0 w-9 h-9 rounded-lg bg-[#F1592A]/10 flex items-center justify-center text-[#F1592A]" aria-hidden="true">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </span>
                                    <div class="min-w-0 pt-0.5">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-[#606060]/75">Date</p>
                                        <p class="text-base sm:text-lg font-semibold text-[#1E1E1E]">{{ $start->format('l, F j, Y') }}</p>
                                    </div>
                                </div>
                                @if($timeLine !== '')
                                    <div class="flex items-start gap-3">
                                        <span class="shrink-0 w-9 h-9 rounded-lg bg-[#F1592A]/10 flex items-center justify-center text-[#F1592A]" aria-hidden="true">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </span>
                                        <div class="min-w-0 pt-0.5">
                                            <p class="text-xs font-semibold uppercase tracking-wider text-[#606060]/75">Time</p>
                                            <p class="text-base text-[#606060]">{{ $timeLine }}</p>
                                        </div>
                                    </div>
                                @endif
                            @endif
                            <div class="flex items-start gap-3">
                                <span class="shrink-0 w-9 h-9 rounded-lg bg-[#F1592A]/10 flex items-center justify-center text-[#F1592A]" aria-hidden="true">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </span>
                                <div class="min-w-0 pt-0.5">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-[#606060]/75">Venue</p>
                                    <p class="text-base sm:text-lg font-semibold text-[#1E1E1E]">{{ $event->location }}</p>
                                </div>
                            </div>
                            @if($event->max_capacity)
                                <div class="flex items-start gap-3">
                                    <span class="shrink-0 w-9 h-9 rounded-lg bg-[#9DC83B]/15 flex items-center justify-center text-[#F1592A]" aria-hidden="true">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </span>
                                    <div class="min-w-0 pt-0.5">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-[#606060]/75">Capacity</p>
                                        <p class="text-base text-[#606060]">Up to {{ $event->max_capacity }} guests</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </header>

                    {{-- Registration card --}}
                    <div class="rounded-2xl bg-white p-6 sm:p-7 border border-[#FFA589]/45 shadow-sm">
                        <h2 class="text-lg sm:text-xl font-bold text-[#1E1E1E] mb-1" style="font-family: 'Libre Baskerville', Georgia, serif;">Registration</h2>

                        @if(!$event->isUpcoming())
                            <p class="text-[#606060] text-sm sm:text-base mt-3">Registration is closed for this event.</p>
                        @elseif($event->registration_type === 'none')
                            <p class="text-[#606060] text-sm sm:text-base mt-3">No registration is required—we&apos;d love to see you there!</p>
                        @elseif($event->registration_type === 'link' && $event->registration_link)
                            <p class="text-[#606060] text-sm sm:text-base mt-3 mb-5">Welcome! To join the event, please register using the link below.</p>
                            <a href="{{ $event->registration_link }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center justify-center w-full px-6 py-3.5 rounded-xl font-semibold text-white bg-[#F1592A] hover:bg-[#D44A1F] focus:outline-none focus:ring-2 focus:ring-[#F1592A] focus:ring-offset-2 transition-colors shadow-sm">
                                Register
                            </a>
                        @elseif(in_array($event->registration_type, ['simple', 'form'], true))
                            <p class="text-[#606060] text-sm sm:text-base mt-3 mb-5">Welcome! To join the event, please register below.</p>
                            <form id="publicEventRegisterForm" class="space-y-4">
                                <div>
                                    <label for="reg_name" class="block text-sm font-semibold text-[#1E1E1E] mb-1.5">Full name <span class="text-[#F1592A]">*</span></label>
                                    <input type="text" id="reg_name" name="name" required
                                           class="w-full px-4 py-3 rounded-xl border border-[#FFAC93]/50 bg-white text-[#1E1E1E] placeholder:text-[#606060]/45 focus:outline-none focus:ring-2 focus:ring-[#F1592A] focus:border-[#F1592A]/50">
                                </div>
                                <div>
                                    <label for="reg_email" class="block text-sm font-semibold text-[#1E1E1E] mb-1.5">Email <span class="text-[#F1592A]">*</span></label>
                                    <input type="email" id="reg_email" name="email" required
                                           class="w-full px-4 py-3 rounded-xl border border-[#FFAC93]/50 bg-white text-[#1E1E1E] focus:outline-none focus:ring-2 focus:ring-[#F1592A] focus:border-[#F1592A]/50">
                                </div>
                                <div>
                                    <label for="reg_phone" class="block text-sm font-semibold text-[#1E1E1E] mb-1.5">Phone</label>
                                    <input type="tel" id="reg_phone" name="phone"
                                           class="w-full px-4 py-3 rounded-xl border border-[#FFAC93]/50 bg-white text-[#1E1E1E] focus:outline-none focus:ring-2 focus:ring-[#F1592A] focus:border-[#F1592A]/50">
                                </div>
                                <div id="publicCustomFields" class="space-y-4"></div>
                                <button type="submit"
                                        class="w-full px-6 py-3.5 rounded-xl font-semibold text-white bg-[#F1592A] hover:bg-[#D44A1F] focus:outline-none focus:ring-2 focus:ring-[#F1592A] focus:ring-offset-2 transition-colors shadow-sm">
                                    Register
                                </button>
                                <p id="publicRegMessage" class="text-sm hidden rounded-lg px-3 py-2"></p>
                            </form>
                        @else
                            <p class="text-[#606060] text-sm sm:text-base mt-3">Registration is not configured for this event.</p>
                        @endif
                    </div>

                    {{-- About event --}}
                    <div class="rounded-2xl bg-white p-6 sm:p-7 border border-[#FFA589]/30 shadow-sm">
                        <h2 class="text-lg sm:text-xl font-bold text-[#1E1E1E] pb-3 mb-5 border-b border-[#FFAC93]/35" style="font-family: 'Libre Baskerville', Georgia, serif;">About event</h2>
                        @if($event->description)
                            <div class="text-[#606060] whitespace-pre-wrap leading-relaxed text-sm sm:text-base">{{ $event->description }}</div>
                        @else
                            <p class="text-[#606060]/80 text-sm sm:text-base">More details coming soon.</p>
                        @endif
                    </div>
                </div>
            </div>
        

                <div class="mt-auto border-t border-[#FFA589]/20 py-8 bg-white/80">
                    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-10 text-center text-sm text-[#606060]">
                        <a href="{{ route('public.events') }}" class="text-[#F1592A] font-medium hover:underline">← Back to all events</a>
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

    @if(in_array($event->registration_type, ['simple', 'form'], true) && $event->isUpcoming())
        @php
            $registerApiUrl = ($event->branch?->public_code && $event->public_slug)
                ? url('/public-api/event/'.$event->branch->public_code.'/'.$event->public_slug.'/register')
                : '';
        @endphp
        <script>
            (function () {
                const registerUrl = @json($registerApiUrl);
                const registrationType = @json($event->registration_type);
                const customFields = @json($customFields);
                const standardNames = ['name', 'email', 'phone'];
                const labelCls = 'block text-sm font-semibold text-[#1E1E1E] mb-1.5';
                const fieldCls = 'w-full px-4 py-3 rounded-xl border border-[#FFAC93]/60 bg-white text-[#1E1E1E] focus:outline-none focus:ring-2 focus:ring-[#F1592A] focus:border-transparent';

                function buildCustomInputs() {
                    const container = document.getElementById('publicCustomFields');
                    if (!container || registrationType !== 'form' || !customFields.length) return;

                    const filtered = customFields.filter(f => f && f.name && !standardNames.includes(f.name));
                    filtered.forEach(field => {
                        const id = 'cf_' + field.name;
                        const req = field.required ? 'required' : '';
                        const label = (field.label || field.name) + (field.required ? ' *' : '');
                        let html = '<div>';
                        html += '<label for="' + id + '" class="' + labelCls + '">' + label + '</label>';

                        if (field.type === 'textarea') {
                            html += '<textarea id="' + id + '" name="' + field.name + '" rows="3" ' + req + ' class="' + fieldCls + '"></textarea>';
                        } else if (field.type === 'select') {
                            const opts = (field.options || []).map(o => '<option value="' + String(o).replace(/"/g, '&quot;') + '">' + String(o) + '</option>').join('');
                            html += '<select id="' + id + '" name="' + field.name + '" ' + req + ' class="' + fieldCls + '"><option value="">Select…</option>' + opts + '</select>';
                        } else {
                            const t = ['email', 'tel', 'number', 'date'].includes(field.type) ? field.type : 'text';
                            html += '<input type="' + t + '" id="' + id + '" name="' + field.name + '" ' + req + ' class="' + fieldCls + '">';
                        }
                        html += '</div>';
                        container.insertAdjacentHTML('beforeend', html);
                    });
                }

                buildCustomInputs();

                const form = document.getElementById('publicEventRegisterForm');
                if (!form) return;

                form.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    const msg = document.getElementById('publicRegMessage');
                    msg.classList.add('hidden');

                    if (!registerUrl) {
                        msg.textContent = 'Registration is not available for this event.';
                        msg.className = 'text-sm text-[#1E1E1E] bg-[#FFA589]/30 border border-[#F1592A]/40 rounded-lg px-3 py-2';
                        msg.classList.remove('hidden');
                        return;
                    }

                    const fd = new FormData(form);
                    const body = {
                        name: fd.get('name'),
                        email: fd.get('email'),
                        phone: fd.get('phone') || null,
                    };
                    const custom = {};
                    for (const [k, v] of fd.entries()) {
                        if (!['name', 'email', 'phone'].includes(k) && v) {
                            custom[k] = v;
                        }
                    }
                    if (Object.keys(custom).length) {
                        body.custom_fields = custom;
                    }

                    try {
                        const res = await fetch(registerUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            },
                            body: JSON.stringify(body),
                        });
                        const data = await res.json();
                        if (data.success) {
                            msg.textContent = data.message || 'Registration successful.';
                            msg.className = 'text-sm text-[#1E1E1E] bg-[#9DC83B]/25 border border-[#9DC83B]/40 rounded-lg px-3 py-2';
                            msg.classList.remove('hidden');
                            form.reset();
                        } else {
                            msg.textContent = data.message || 'Registration failed.';
                            if (data.errors) {
                                msg.textContent += ' ' + Object.values(data.errors).flat().join(' ');
                            }
                            msg.className = 'text-sm text-[#1E1E1E] bg-[#FFA589]/30 border border-[#F1592A]/40 rounded-lg px-3 py-2';
                            msg.classList.remove('hidden');
                        }
                    } catch (err) {
                        msg.textContent = 'Something went wrong. Please try again.';
                        msg.className = 'text-sm text-[#1E1E1E] bg-[#FFA589]/30 border border-[#F1592A]/40 rounded-lg px-3 py-2';
                        msg.classList.remove('hidden');
                    }
                });
            })();
        </script>
    @endif
</body>
</html>

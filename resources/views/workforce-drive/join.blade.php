<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'LifePointe Church') }} — Join the Workforce Drive</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="preload" href="https://fonts.bunny.net/css?family=quicksand:400,500,600,700&family=amaranth:400,700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.bunny.net/css?family=quicksand:400,500,600,700&family=amaranth:400,700&display=swap" rel="stylesheet"></noscript>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-cream text-ink min-h-screen">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-gray-900">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 h-20 flex items-center">
                <a href="/" class="flex items-center">
                    <img src="{{ asset('img/lifepointe-logo-white.png') }}" alt="LifePointe" class="h-10 w-auto"/>
                </a>
            </div>
        </header>

        <main class="flex-1 flex items-start justify-center px-4 py-8 sm:py-12">
            <div class="w-full max-w-lg">
                <div class="text-center mb-8">
                    <p class="font-display text-secondary-500 uppercase tracking-widest text-sm font-bold">Workforce Drive</p>
                    <h1 class="font-display text-3xl sm:text-4xl font-bold mt-1">Join a Team</h1>
                    <p class="text-ink/70 mt-2">Fill in your details and pick the team you want to serve with.</p>
                </div>

                @if (session('joined_team'))
                    <div class="mb-6 bg-community-50 border border-community-200 text-community-800 px-5 py-4 rounded-2xl text-center">
                        <p class="font-display text-lg font-bold">You've joined {{ session('joined_team') }}! 🎉</p>
                        <p class="text-sm mt-1">Thank you for stepping up. See you on the team.</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl">
                        <ul class="list-disc list-inside text-sm space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div
                    class="bg-white rounded-4xl shadow-church p-6 sm:p-8"
                    x-data="{
                        teams: {{ Illuminate\Support\Js::from($teams->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'core_value' => $t->core_value, 'sponsor_name' => $t->sponsor_name])) }},
                        selected: '{{ old('workforce_drive_team_id') }}',
                        get team() { return this.teams.find(t => String(t.id) === String(this.selected)) || null; }
                    }"
                >
                    <form method="POST" action="{{ route('workforce-drive.store') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="name" class="block text-sm font-semibold mb-1">Full name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required autocomplete="name"
                                   class="w-full rounded-xl border-gray-300 focus:border-church-500 focus:ring-church-500"
                                   placeholder="Your name">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold mb-1">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email"
                                   class="w-full rounded-xl border-gray-300 focus:border-church-500 focus:ring-church-500"
                                   placeholder="you@example.com">
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-semibold mb-1">Phone number</label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required autocomplete="tel"
                                   class="w-full rounded-xl border-gray-300 focus:border-church-500 focus:ring-church-500"
                                   placeholder="080 0000 0000">
                        </div>

                        <div>
                            <label for="workforce_drive_team_id" class="block text-sm font-semibold mb-1">Choose your team</label>
                            <select name="workforce_drive_team_id" id="workforce_drive_team_id" required x-model="selected"
                                    class="w-full rounded-xl border-gray-300 focus:border-church-500 focus:ring-church-500">
                                <option value="" disabled>Select a team…</option>
                                @foreach ($teams as $team)
                                    <option value="{{ $team->id }}" @selected(old('workforce_drive_team_id') == $team->id)>{{ $team->name }}</option>
                                @endforeach
                            </select>

                            <!-- Selected team's core value + sponsor -->
                            <div x-cloak x-show="team" x-transition
                                 class="mt-3 rounded-2xl bg-cream border border-cream-deep px-4 py-3 text-sm">
                                <p><span class="font-semibold">Core Value:</span> <span x-text="team?.core_value || '—'"></span></p>
                                <p class="mt-0.5"><span class="font-semibold">Sponsor:</span> <span x-text="team?.sponsor_name || '—'"></span></p>
                            </div>
                        </div>

                        <button type="submit"
                                class="w-full inline-flex items-center justify-center px-6 py-3.5 rounded-xl text-white text-base font-semibold bg-church-500 hover:bg-church-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-church-500 transition-colors">
                            Join this team
                        </button>
                    </form>
                </div>

                <p class="text-center text-ink/50 text-xs mt-6">One person, one team. Your details are used only for this drive.</p>
            </div>
        </main>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'LifePointe Church') }} — You're in!</title>

    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=quicksand:400,500,600,700&family=amaranth:400,700&family=pacifico:400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-cream text-ink min-h-screen">
    <div class="min-h-screen flex flex-col">
        <header class="bg-ink">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 h-20 flex items-center">
                <a href="/" class="flex items-center">
                    <img src="{{ asset('img/lifepointe-logo-white.png') }}" alt="LifePointe" class="h-10 w-auto"/>
                </a>
            </div>
        </header>

        <main class="flex-1 flex items-center justify-center px-4 py-10">
            <div class="w-full max-w-lg text-center">
                <div class="bg-white rounded-4xl shadow-church p-8 sm:p-10">
                    <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-community-100">
                        <svg class="h-10 w-10 text-community-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6 9 17l-5-5"/>
                        </svg>
                    </div>

                    <p class="font-script text-secondary-500 text-2xl leading-none">welcome to the team</p>
                    <h1 class="font-display text-3xl sm:text-4xl font-bold mt-2">You're in! 🎉</h1>
                    <p class="text-ink/70 mt-3 text-lg">
                        Thank you for joining <strong class="text-church-600">{{ $teamName }}</strong>.
                        We're glad to have you serving with us.
                    </p>

                    @if ($whatsappUrl)
                        <p class="text-ink/60 mt-6 text-sm">Join the WhatsApp group so you don't miss the next steps:</p>
                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener"
                           class="mt-3 w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl text-white text-base font-semibold transition-colors"
                           style="background:#25D366;" onmouseover="this.style.background='#1eb954'" onmouseout="this.style.background='#25D366'">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm0 18.02h-.01a8.2 8.2 0 0 1-4.18-1.15l-.3-.18-3.11.82.83-3.03-.2-.31a8.2 8.2 0 0 1-1.26-4.38c0-4.54 3.7-8.23 8.24-8.23a8.2 8.2 0 0 1 5.82 2.42 8.18 8.18 0 0 1 2.41 5.82c0 4.54-3.69 8.23-8.23 8.23Zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.13-.16.25-.64.8-.79.97-.14.16-.29.18-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.13-.14.17-.25.25-.41.08-.16.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.4-.42-.56-.43-.14 0-.31-.01-.48-.01-.16 0-.43.06-.66.31-.23.25-.86.85-.86 2.07 0 1.22.89 2.4 1.01 2.56.12.16 1.75 2.67 4.25 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.14-1.18-.06-.11-.22-.17-.47-.29Z"/>
                            </svg>
                            Join the WhatsApp group
                        </a>
                    @endif

                    <a href="{{ route('workforce-drive.join') }}" class="block mt-4 text-ink/50 text-sm hover:text-church-600">
                        Back to the join page
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

@props([
    'event',
    'showActions' => true,
    'compact' => false
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-church border border-church-100 overflow-hidden transition-all duration-300 hover:shadow-lg hover:-translate-y-1']) }}>
    @if(!$compact)
        <!-- Event Image/Header -->
        <div class="h-32 bg-gradient-to-r from-church-500 to-worship-500 relative overflow-hidden">
            <div class="absolute inset-0 bg-black/20"></div>
            <div class="absolute bottom-4 left-4 text-white">
                <div class="flex items-center space-x-2">
                    <div class="bg-white/20 backdrop-blur-sm rounded-lg px-3 py-1">
                        <span class="text-sm font-medium">{{ $event->frequency ?? 'One-time' }}</span>
                    </div>
                </div>
            </div>
            @if($event->registration_type === 'required')
                <div class="absolute top-4 right-4">
                    <div class="bg-ministry-500 text-white text-xs px-2 py-1 rounded-full font-medium">
                        Registration Required
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="p-{{ $compact ? '4' : '6' }}">
        <!-- Event Title and Date -->
        <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
                <h3 class="text-{{ $compact ? 'base' : 'lg' }} font-semibold text-gray-900 mb-1">
                    {{ $event->name }}
                </h3>
                <div class="flex items-center text-sm text-gray-600 space-x-4">
                    <div class="flex items-center space-x-1">
                        <svg class="w-4 h-4 text-church-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ $event->start_date ? \Carbon\Carbon::parse($event->start_date)->format('M j, Y') : 'TBD' }}</span>
                    </div>
                    @if($event->start_time)
                        <div class="flex items-center space-x-1">
                            <svg class="w-4 h-4 text-church-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <span>{{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }}</span>
                        </div>
                    @endif
                </div>
            </div>
            @if(!$compact)
                <div class="ml-4">
                    <div class="w-12 h-12 bg-church-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-church-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            @endif
        </div>

        @if(!$compact && $event->description)
            <!-- Event Description -->
            <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                {{ $event->description }}
            </p>
        @endif

        <!-- Event Details -->
        <div class="space-y-2 mb-4">
            @if($event->location)
                <div class="flex items-center text-sm text-gray-600">
                    <svg class="w-4 h-4 text-church-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ $event->location }}</span>
                </div>
            @endif

            @if($event->ministry && !$compact)
                <div class="flex items-center text-sm text-gray-600">
                    <svg class="w-4 h-4 text-church-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                    </svg>
                    <span>{{ $event->ministry->name }}</span>
                </div>
            @endif
        </div>

        @if($showActions)
            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <div class="flex items-center space-x-2">
                    @if($event->registration_type !== 'none')
                        <button class="inline-flex items-center px-3 py-1.5 bg-church-600 text-white text-sm font-medium rounded-lg hover:bg-church-700 transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/>
                            </svg>
                            Register
                        </button>
                    @endif
                    <button class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"/>
                        </svg>
                        Details
                    </button>
                </div>
                
                @if(!$compact)
                    <div class="flex items-center text-xs text-gray-500">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ $event->status ?? 'Active' }}</span>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div> 
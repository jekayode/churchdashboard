@props(['business'])

<a href="{{ route('biz.show', $business->slug) }}" class="block bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden border border-gray-100">
    <div class="aspect-[16/9] bg-gray-100 relative">
        @if($business->getFirstMediaUrl('cover'))
            <img src="{{ $business->getFirstMediaUrl('cover', 'medium') }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
        @elseif($business->getFirstMediaUrl('logo'))
            <img src="{{ $business->getFirstMediaUrl('logo', 'medium') }}" alt="{{ $business->name }}" class="w-full h-full object-contain p-8">
        @endif
        @if($business->is_featured)
            <span class="absolute top-2 left-2 bg-amber-500 text-white text-xs px-2 py-0.5 rounded">Featured</span>
        @endif
    </div>
    <div class="p-4">
        <h3 class="font-semibold text-slate-900">{{ $business->name }}</h3>
        @if($business->tagline)
            <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $business->tagline }}</p>
        @endif
        <div class="flex items-center justify-between mt-3 text-sm">
            @if($business->city)
                <span class="text-gray-500">{{ $business->city }}</span>
            @endif
            @if($business->average_rating > 0)
                <span class="font-medium text-amber-600">★ {{ number_format($business->average_rating, 1) }}</span>
            @endif
        </div>
    </div>
</a>

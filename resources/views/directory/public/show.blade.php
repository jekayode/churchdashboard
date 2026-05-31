@extends('layouts.biz')

@section('title', $business->name)
@section('meta_description', Str::limit($business->tagline ?? $business->description, 160))
@php
    $ogImage = $business->getFirstMediaUrl('cover') ?: $business->getFirstMediaUrl('logo');
    $logo = $business->getFirstMediaUrl('logo', 'thumb');
    $cover = $business->getFirstMediaUrl('cover', 'medium');
    $gallery = $business->getMedia('gallery');
    $galleryImages = $gallery->map(fn ($m) => ['id' => $m->id, 'url' => $m->getUrl('medium')])->values()->all();
    $allImages = array_values(array_filter(array_merge(
        $cover ? [['id' => 'cover', 'url' => $cover]] : [],
        $galleryImages
    )));
    $directionDestination = ($business->latitude && $business->longitude)
        ? $business->latitude.','.$business->longitude
        : trim($business->address ?? '');
    $directionsUrl = $directionDestination
        ? 'https://www.google.com/maps/dir/?api=1&destination='.urlencode($directionDestination)
        : null;

    $openingStatus = $business->openingStatus();
@endphp
@section('og_image', $ogImage ?? '')

@section('content')
<div class="biz-profile-grid">
    {{-- Main column (~66%) — always first in DOM so it stays on the left on desktop --}}
    <div class="biz-profile-main w-full min-w-0 space-y-8">
        {{-- Header (Yelp-style: logo + title block) --}}
        <header class="border-b border-gray-200 pb-6">
            <div class="flex gap-4 items-start w-full">
                @if($logo)
                    <img src="{{ $logo }}" alt="" class="h-[72px] w-[72px] shrink-0 rounded-full object-cover border border-gray-200 bg-white">
                @endif
                <div class="min-w-0 flex-1">
                    <h1 class="text-3xl font-bold text-slate-900 leading-tight break-words">{{ $business->name }}</h1>

                    @if($business->average_rating > 0)
                        <div class="mt-2 flex flex-row flex-wrap items-center gap-x-2 gap-y-1 text-sm">
                            <span class="inline-flex shrink-0 items-center gap-0.5 text-amber-500" aria-label="Rating {{ number_format($business->average_rating, 1) }} out of 5">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="{{ $i <= round($business->average_rating) ? 'text-amber-500' : 'text-gray-300' }}">★</span>
                                @endfor
                            </span>
                            <span class="text-slate-700 font-medium">{{ number_format($business->average_rating, 1) }}</span>
                            <span class="text-gray-500">({{ $business->reviews_count }} {{ Str::plural('review', $business->reviews_count) }})</span>
                        </div>
                    @endif

                    @if($business->categories->isNotEmpty())
                        <p class="mt-1.5 text-sm font-semibold text-slate-800">
                            {{ $business->categories->pluck('name')->join(' · ') }}
                        </p>
                    @endif

                    @if($openingStatus)
                        <p class="mt-1.5 text-sm text-gray-700">
                            <span class="font-semibold {{ $openingStatus['is_open_now'] ? 'text-green-700' : 'text-red-600' }}">
                                {{ $openingStatus['status_label'] }}
                            </span>
                            @if($openingStatus['hours_summary'])
                                <span class="text-gray-600"> · {{ $openingStatus['hours_summary'] }}</span>
                            @endif
                        </p>
                    @endif

                    @if(collect([$business->address, $business->city, $business->country])->filter()->isNotEmpty())
                        <p class="mt-1 text-sm text-gray-600 break-words">
                            {{ collect([$business->address, $business->city, $business->state, $business->country])->filter()->join(', ') }}
                        </p>
                    @endif

                    @if($business->tagline)
                        <p class="mt-2 text-gray-600 break-words">{{ $business->tagline }}</p>
                    @endif
                </div>
            </div>

            {{-- Action row --}}
            <div class="mt-5 flex flex-wrap gap-2 w-full">
                @auth
                    @unless($business->isOwnedBy(auth()->user()))
                        <a href="#write-review"
                            class="inline-flex items-center gap-2 rounded-full biz-bg-primary text-white px-4 py-2 text-sm font-semibold shadow-sm hover:opacity-90">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            Write a review
                        </a>
                    @endunless
                @endauth
                <button type="button"
                    onclick="navigator.share?.({title: @js($business->name), url: location.href})"
                    class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-gray-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    Share
                </button>
                @auth
                    <button type="button" id="like-btn" onclick="toggleLike()"
                        class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-gray-50">
                        Like
                    </button>
                @endauth
                @if($directionsUrl)
                    <a href="{{ $directionsUrl }}" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-gray-50">
                        Directions
                    </a>
                @endif
                <x-directory.whatsapp-button :number="$business->whatsapp_number" class="!rounded-full" />
            </div>
        </header>

        @if($business->posts->isNotEmpty())
            <section>
                <h2 class="text-lg font-bold text-slate-900 mb-4">Updates from this business</h2>
                <div class="space-y-4">
                    @foreach($business->posts as $post)
                        <article class="flex gap-4 rounded-lg border border-gray-200 bg-white p-4">
                            @if($post->image_url)
                                <img src="{{ $post->image_url }}" alt="" class="h-24 w-32 shrink-0 rounded-md object-cover bg-gray-100">
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-700 leading-relaxed">{{ Str::limit($post->body, 280) }}</p>
                                @if(strlen($post->body) > 280)
                                    <p class="mt-2 text-sm font-medium text-[var(--biz-primary)]">Read more</p>
                                @endif
                                @if($post->published_at)
                                    <time class="mt-2 block text-xs text-gray-500" datetime="{{ $post->published_at->toIso8601String() }}">
                                        {{ $post->published_at->format('M j, Y') }}
                                    </time>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Photos grid + lightbox --}}
        @if(count($allImages) > 0)
            <section>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <h2 class="text-lg font-bold text-slate-900">Photos</h2>
                    <span class="text-sm text-gray-500">{{ count($allImages) }} {{ Str::plural('photo', count($allImages)) }}</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($allImages as $index => $img)
                        <button type="button"
                            class="group relative aspect-[4/3] overflow-hidden rounded-lg border border-gray-200 bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--biz-primary)]"
                            data-biz-lightbox-index="{{ $index }}"
                            aria-label="View photo {{ $index + 1 }}">
                            <img src="{{ $img['url'] }}" alt="" class="h-full w-full object-cover transition group-hover:scale-105">
                        </button>
                    @endforeach
                </div>
            </section>
        @endif

        @if($business->description)
            <section class="border-t border-gray-200 pt-8">
                <h2 class="text-lg font-bold text-slate-900 mb-3">About the Business</h2>
                <div class="text-gray-700 leading-relaxed">{!! nl2br(e($business->description)) !!}</div>
            </section>
        @endif

        @if($business->services->isNotEmpty())
            <section class="border-t border-gray-200 pt-8">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Services</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($business->services as $service)
                        <div class="rounded-lg border border-gray-200 bg-white p-4">
                            <h3 class="font-semibold text-slate-900">{{ $service->name }}</h3>
                            @if($service->duration_text)
                                <p class="text-sm text-gray-500 mt-1">{{ $service->duration_text }}</p>
                            @endif
                            @if($service->price_text)
                                <p class="mt-2 text-sm font-semibold text-slate-800">{{ $service->price_text }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if($business->teamMembers->isNotEmpty())
            <section class="border-t border-gray-200 pt-8">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Team</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach($business->teamMembers as $member)
                        <div class="text-center">
                            <div class="mx-auto h-20 w-20 overflow-hidden rounded-full bg-gray-200">
                                @if($member->photo_url)
                                    <img src="{{ $member->photo_url }}" alt="{{ $member->name }}" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <p class="mt-2 text-sm font-semibold text-slate-900">{{ $member->name }}</p>
                            @if($member->role)
                                <p class="text-xs text-gray-500">{{ $member->role }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if($business->reviews->isNotEmpty())
            <section class="border-t border-gray-200 pt-8">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Reviews</h2>
                <div class="space-y-4">
                    @foreach($business->reviews as $review)
                        <article class="rounded-lg border border-gray-200 bg-white p-4">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <span class="font-medium text-slate-900">{{ $review->user->name }}</span>
                                    <time class="text-xs text-gray-500 ml-2" datetime="{{ $review->created_at?->toIso8601String() }}">
                                        {{ $review->created_at?->format('M j, Y') }}
                                    </time>
                                </div>
                                <span class="text-amber-600 text-sm font-medium">★ {{ $review->rating }}</span>
                            </div>
                            @if($review->title)
                                <p class="mt-2 font-medium text-slate-800">{{ $review->title }}</p>
                            @endif
                            @if($review->body)
                                <p class="mt-1 text-sm text-gray-600">{{ $review->body }}</p>
                            @endif
                            @if($review->reply)
                                <div class="mt-4 rounded-lg border border-indigo-100 bg-indigo-50 p-3">
                                    <p class="text-sm font-semibold text-indigo-900">Owner reply</p>
                                    <p class="mt-1 text-sm text-indigo-900/90">{{ $review->reply->body }}</p>
                                </div>
                            @elseif(auth()->check() && $business->isOwnedBy(auth()->user()))
                                <form class="mt-4 space-y-2 biz-owner-reply-form"
                                    data-business-slug="{{ $business->slug }}"
                                    data-review-id="{{ $review->id }}">
                                    <textarea name="body" rows="2" class="w-full rounded-md border-gray-300 text-sm" placeholder="Respond to this review…"></textarea>
                                    <button type="submit" class="biz-bg-primary text-white px-3 py-1.5 rounded-lg text-sm">Submit response</button>
                                </form>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @auth
            @unless($business->isOwnedBy(auth()->user()))
                <section id="write-review" class="border-t border-gray-200 pt-8 scroll-mt-24">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Write a review</h2>
                    <form id="review-form" class="rounded-lg border border-gray-200 bg-white p-4 space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                            <select name="rating" class="w-full rounded-md border-gray-300 text-sm" required>
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}">{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
                                @endfor
                            </select>
                        </div>
                        <input type="text" name="title" placeholder="Title (optional)" class="w-full rounded-md border-gray-300 text-sm">
                        <textarea name="body" rows="3" placeholder="Share your experience" class="w-full rounded-md border-gray-300 text-sm"></textarea>
                        <button type="submit" class="biz-bg-primary text-white px-4 py-2 rounded-lg text-sm font-semibold">Submit review</button>
                    </form>
                </section>
            @endunless
        @else
            <p class="border-t border-gray-200 pt-8 text-sm text-gray-600">
                <a href="{{ route('login') }}" class="text-[var(--biz-primary)] font-medium">Log in</a> to write a review or message this business.
            </p>
        @endauth

        @php
            $hasSocial = $business->social_facebook || $business->social_instagram || $business->social_twitter
                || $business->social_tiktok || $business->social_youtube || $business->social_linkedin;
        @endphp
        @if($hasSocial)
            <section class="border-t border-gray-200 pt-8">
                <h2 class="text-lg font-bold text-slate-900 mb-3">Social</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach([
                        'Facebook' => $business->social_facebook,
                        'Instagram' => $business->social_instagram,
                        'X' => $business->social_twitter,
                        'TikTok' => $business->social_tiktok,
                        'YouTube' => $business->social_youtube,
                        'LinkedIn' => $business->social_linkedin,
                    ] as $label => $url)
                        @if($url)
                            <a href="{{ $url }}" target="_blank" rel="noopener"
                                class="rounded-full border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-gray-50">
                                {{ $label }}
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        @if(isset($relatedBusinesses) && $relatedBusinesses->isNotEmpty())
            <section class="border-t border-gray-200 pt-8">
                <h2 class="text-lg font-bold text-slate-900 mb-4">You might also like</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($relatedBusinesses as $related)
                        <x-directory.business-card :business="$related" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    {{-- Sidebar (~34%) — right column on desktop, aligned with header top --}}
    <aside class="biz-profile-aside w-full min-w-0 space-y-4">
        <div class="rounded-lg border border-gray-300 bg-white p-5 text-sm space-y-3">
            @if($business->phone)
                <p class="leading-relaxed">
                    <span class="font-bold text-slate-900">Phone: </span>
                    <a href="tel:{{ preg_replace('/\s+/', '', $business->phone) }}" class="biz-primary hover:underline">{{ $business->phone }}</a>
                </p>
            @endif
            @if($business->email)
                <p class="leading-relaxed break-words">
                    <span class="font-bold text-slate-900">Email: </span>
                    <a href="mailto:{{ $business->email }}" class="biz-primary hover:underline">{{ $business->email }}</a>
                </p>
            @endif
            @if($business->website)
                <p class="leading-relaxed break-words">
                    <span class="font-bold text-slate-900">Website: </span>
                    <a href="{{ $business->website }}" target="_blank" rel="noopener" class="biz-primary hover:underline">{{ parse_url($business->website, PHP_URL_HOST) ?: $business->website }}</a>
                </p>
            @endif
            @if(!$business->phone && !$business->email && !$business->website)
                <p class="text-gray-500">No contact details listed.</p>
            @endif
        </div>

        @auth
            @unless($business->isOwnedBy(auth()->user()))
                <button type="button" onclick="openMessageModal()"
                    class="w-full rounded-md bg-slate-900 px-4 py-3.5 text-center text-base font-bold biz-primary hover:bg-slate-800 transition">
                    Send Message
                </button>
            @endunless
        @else
            <a href="{{ route('login', ['redirect' => url()->current()]) }}"
                class="block w-full rounded-md bg-slate-900 px-4 py-3.5 text-center text-base font-bold biz-primary hover:bg-slate-800 transition">
                Log in to message
            </a>
        @endauth
    </aside>
</div>

{{-- Lightbox --}}
@if(count($allImages) > 0)
    <div id="biz-lightbox" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/85 p-4" role="dialog" aria-modal="true" aria-label="Photo viewer">
        <button type="button" id="biz-lightbox-close"
            class="absolute top-4 right-4 rounded-full bg-white/90 p-2 text-slate-800 shadow hover:bg-white"
            aria-label="Close">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <button type="button" id="biz-lightbox-prev"
            class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-3 text-slate-800 shadow hover:bg-white md:left-4"
            aria-label="Previous photo">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <img id="biz-lightbox-img" src="" alt="" class="max-h-[85vh] max-w-full rounded-lg object-contain shadow-2xl">
        <button type="button" id="biz-lightbox-next"
            class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-3 text-slate-800 shadow hover:bg-white md:right-4"
            aria-label="Next photo">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
        <p id="biz-lightbox-counter" class="absolute bottom-4 left-1/2 -translate-x-1/2 text-sm text-white/90"></p>
    </div>
@endif

@php
    $localBusinessSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => $business->name,
        'description' => $business->tagline ?? $business->description,
        'image' => $ogImage,
        'url' => route('biz.show', $business->slug),
        'telephone' => $business->phone,
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $business->address,
            'addressLocality' => $business->city,
            'addressRegion' => $business->state,
            'addressCountry' => $business->country,
        ],
        'geo' => [
            '@type' => 'GeoCoordinates',
            'latitude' => $business->latitude,
            'longitude' => $business->longitude,
        ],
    ];
@endphp
<script type="application/ld+json">
    {!! json_encode($localBusinessSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

@if(count($allImages) > 0)
<script>
(function () {
    const images = @json($allImages);
    const lightbox = document.getElementById('biz-lightbox');
    const imgEl = document.getElementById('biz-lightbox-img');
    const counter = document.getElementById('biz-lightbox-counter');
    let index = 0;

    function open(i) {
        if (!images.length) return;
        index = (i + images.length) % images.length;
        imgEl.src = images[index].url;
        imgEl.alt = @js($business->name).' photo ' + (index + 1);
        if (counter) counter.textContent = (index + 1) + ' / ' + images.length;
        lightbox.classList.remove('hidden');
        lightbox.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function close() {
        lightbox.classList.add('hidden');
        lightbox.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    document.querySelectorAll('[data-biz-lightbox-index]').forEach((btn) => {
        btn.addEventListener('click', () => open(parseInt(btn.dataset.bizLightboxIndex, 10) || 0));
    });

    document.getElementById('biz-lightbox-close')?.addEventListener('click', close);
    document.getElementById('biz-lightbox-prev')?.addEventListener('click', () => open(index - 1));
    document.getElementById('biz-lightbox-next')?.addEventListener('click', () => open(index + 1));
    lightbox?.addEventListener('click', (e) => { if (e.target === lightbox) close(); });
    document.addEventListener('keydown', (e) => {
        if (lightbox.classList.contains('hidden')) return;
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowLeft') open(index - 1);
        if (e.key === 'ArrowRight') open(index + 1);
    });
})();
</script>
@endif

@auth
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf };
const token = document.querySelector('meta[name="api-token"]')?.content;
if (token) headers['Authorization'] = 'Bearer ' + token;

async function toggleLike() {
    const res = await fetch(@json(url('/api/biz/businesses/'.$business->slug.'/like')), { method: 'POST', headers });
    const json = await res.json();
    if (json.success) {
        const btn = document.getElementById('like-btn');
        if (btn) {
            btn.textContent = json.data.liked ? 'Liked ♥' : 'Like';
            btn.classList.toggle('border-[var(--biz-primary)]', json.data.liked);
            btn.classList.toggle('text-[var(--biz-primary)]', json.data.liked);
        }
    }
}

function openMessageModal() {
    const subject = prompt('Subject:');
    if (!subject) return;
    const body = prompt('Your message:');
    if (!body) return;
    fetch(@json(url('/api/biz/businesses/'.$business->slug.'/messages')), {
        method: 'POST', headers,
        body: JSON.stringify({ subject, body })
    }).then(r => r.json()).then(j => alert(j.message || (j.success ? 'Message sent.' : 'Could not send message.')));
}

document.getElementById('review-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const res = await fetch(@json(url('/api/biz/businesses/'.$business->slug.'/reviews')), {
        method: 'POST', headers,
        body: JSON.stringify({ rating: +fd.get('rating'), title: fd.get('title'), body: fd.get('body') })
    });
    const json = await res.json();
    alert(json.message || (json.success ? 'Review submitted.' : 'Could not submit review.'));
    if (json.success) location.reload();
});

document.querySelectorAll('.biz-owner-reply-form').forEach((form) => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const body = form.querySelector('textarea[name="body"]')?.value;
        if (!body) return;
        const res = await fetch(`/api/biz/businesses/${form.dataset.businessSlug}/reviews/${form.dataset.reviewId}/reply`, {
            method: 'POST', headers,
            body: JSON.stringify({ body })
        });
        const json = await res.json();
        alert(json.message || (res.status === 409 ? 'Reply already submitted.' : 'Failed to submit reply.'));
        if (json.success) location.reload();
    });
});
</script>
@endauth
@endsection

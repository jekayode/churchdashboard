@extends('layouts.biz')

@section('title', 'Business Directory')

@section('content')
    @if(($announcements ?? collect())->isNotEmpty())
        <div class="space-y-3 mb-8" x-data="{ dismissed: [] }">
            @foreach($announcements as $a)
                <div x-show="!dismissed.includes({{ $a->id }})"
                     class="flex items-start gap-4 bg-indigo-50 border border-indigo-100 rounded-xl p-4">
                    @if($a->image_url)
                        <img src="{{ $a->image_url }}" alt="" class="w-16 h-16 rounded-lg object-cover flex-shrink-0">
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-slate-900">{{ $a->title }}</p>
                        @if($a->body)
                            <p class="text-sm text-gray-700 mt-1">{{ $a->body }}</p>
                        @endif
                        @if($a->link)
                            <a href="{{ $a->link }}" target="_blank" rel="noopener" class="inline-block text-sm font-medium text-indigo-600 hover:text-indigo-800 mt-2">Learn more &rarr;</a>
                        @endif
                    </div>
                    @if($a->is_dismissible)
                        <button type="button" @click="dismissed.push({{ $a->id }})" class="text-gray-400 hover:text-gray-700 text-2xl leading-none" aria-label="Dismiss">&times;</button>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div class="text-center mb-10">
        <h1 class="text-4xl font-bold text-slate-900 mb-3">{{ $settings->tagline ?? 'Church Business Directory' }}</h1>
        <p class="text-gray-600 max-w-2xl mx-auto">Discover and support businesses owned by members of our church community.</p>
    </div>

    <form action="{{ route('biz.search') }}" method="GET" class="max-w-3xl mx-auto mb-12">
        <div class="flex gap-2 shadow-lg rounded-xl overflow-hidden bg-white p-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search businesses, services, or city..." class="flex-1 border-0 focus:ring-0 text-lg px-4">
            <button type="submit" class="biz-bg-primary text-white px-6 py-3 rounded-lg font-medium">Search</button>
        </div>
    </form>

    @if($categories->isNotEmpty())
        <section class="mb-12">
            <h2 class="text-2xl font-bold mb-6">Popular Categories</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($categories as $category)
                    <a href="{{ route('biz.category', $category->slug) }}" class="bg-white rounded-lg p-4 shadow-sm hover:shadow border text-center">
                        <p class="font-semibold">{{ $category->name }}</p>
                        <p class="text-sm text-gray-500">{{ $category->businesses_count }} listings</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if($featured->isNotEmpty())
        <section class="mb-12">
            <h2 class="text-2xl font-bold mb-6">Featured</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($featured as $business)
                    <x-directory.business-card :business="$business" />
                @endforeach
            </div>
        </section>
    @endif

    @if($recent->isNotEmpty())
        <section>
            <h2 class="text-2xl font-bold mb-6">Recently Added</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($recent as $business)
                    <x-directory.business-card :business="$business" />
                @endforeach
            </div>
        </section>
    @endif
@endsection

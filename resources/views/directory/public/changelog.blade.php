@extends('layouts.biz')

@section('title', 'Changelog')

@section('content')
    <h1 class="text-3xl font-bold mb-8">Changelog</h1>
    <div class="space-y-6">
        @forelse($entries as $entry)
            <article class="bg-white rounded-xl border p-6">
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-xs font-mono bg-gray-100 px-2 py-1 rounded">v{{ $entry->version }}</span>
                    <time class="text-sm text-gray-500">{{ $entry->published_at?->format('M j, Y') }}</time>
                </div>
                <h2 class="text-xl font-semibold">{{ $entry->title }}</h2>
                <div class="mt-3 text-gray-700 prose">{!! nl2br(e($entry->body)) !!}</div>
            </article>
        @empty
            <p class="text-gray-500">No changelog entries yet.</p>
        @endforelse
    </div>
@endsection

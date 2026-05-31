@extends('layouts.biz')

@section('title', 'Search')

@section('content')
    <h1 class="text-3xl font-bold mb-6">Search Results</h1>
    <form action="{{ route('biz.search') }}" method="GET" class="flex flex-wrap gap-3 mb-8">
        <input type="text" name="q" value="{{ $query }}" placeholder="Search..." class="rounded-lg border-gray-300 flex-1 min-w-[200px]">
        <input type="text" name="city" value="{{ $city }}" placeholder="City" class="rounded-lg border-gray-300">
        <button type="submit" class="biz-bg-primary text-white px-6 py-2 rounded-lg">Search</button>
    </form>

    @if($businesses->isEmpty())
        <p class="text-gray-500">No businesses found.</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($businesses as $business)
                <x-directory.business-card :business="$business" />
            @endforeach
        </div>
        <div class="mt-8">{{ $businesses->links() }}</div>
    @endif
@endsection

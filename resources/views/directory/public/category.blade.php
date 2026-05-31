@extends('layouts.biz')

@section('title', $category->name)

@section('content')
    <h1 class="text-3xl font-bold mb-2">{{ $category->name }}</h1>
    @if($category->description)
        <p class="text-gray-600 mb-8">{{ $category->description }}</p>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($category->businesses()->publiclyVisible()->with('categories')->get() as $business)
            <x-directory.business-card :business="$business" />
        @endforeach
    </div>
@endsection

<x-sidebar-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Directory Account</h2></x-slot>
    <div class="py-6 space-y-3">
        <a href="{{ route('biz.favorites') }}" class="block bg-white border rounded-lg p-4 hover:shadow">My Favorites</a>
        <a href="{{ route('biz.messages') }}" class="block bg-white border rounded-lg p-4 hover:shadow">Messages</a>
        <a href="{{ route('biz.landing') }}" class="block bg-white border rounded-lg p-4 hover:shadow">Browse Directory</a>
        @if(auth()->user()->ownsBusinesses())
            <a href="{{ route('biz.owner') }}" class="block bg-white border rounded-lg p-4 hover:shadow">My Businesses</a>
        @endif
    </div>
</x-sidebar-layout>

<x-sidebar-layout title="Minister Dashboard">
    <div class="container mx-auto px-4 py-6">
        @if(!$ministry)
            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded">No ministry found for your profile.</div>
        @else
            <div class="mb-6">
                <h2 class="text-xl font-medium">{{ $ministry->name }}</h2>
                <p class="text-sm text-gray-600">Category: {{ $ministry->category ?? '—' }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="p-4 border rounded">
                    <h3 class="font-medium mb-2">Departments</h3>
                    <ul class="space-y-1">
                        @foreach($ministry->departments as $dept)
                            <li class="flex justify-between">
                                <span>{{ $dept->name }}</span>
                                <span class="text-sm text-gray-600">{{ $dept->members_count }} members</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @if($ministry->category === 'operations')
                    <div class="p-4 border rounded">
                        <h3 class="font-medium mb-2">Service & Events</h3>
                        <a href="{{ route('pastor.ministry-events') }}" class="text-blue-600">Manage/Add Events</a>
                    </div>
                @endif

                @if($ministry->category === 'life_groups')
                    <div class="p-4 border rounded">
                        <h3 class="font-medium mb-2">Small Groups</h3>
                        <a href="{{ route('pastor.small-groups') }}" class="text-blue-600">Manage Small Groups</a>
                    </div>
                @endif

                @if($ministry->category === 'communications')
                    <div class="p-4 border rounded">
                        <h3 class="font-medium mb-2">Communications</h3>
                        <a href="{{ route('minister.communication.settings') }}" class="text-blue-600">Manage Settings</a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-sidebar-layout>



<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Life Group Members: {{ $smallGroup->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Life Group Details -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $smallGroup->name }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="text-sm text-gray-600">Location: {{ $smallGroup->location ?? 'Not specified' }}</p>
                                <p class="text-sm text-gray-600">Meeting Day: {{ $smallGroup->meeting_day ?? 'Not specified' }}</p>
                                <p class="text-sm text-gray-600">Meeting Time: {{ $smallGroup->meeting_time ?? 'Not specified' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Leader: {{ $smallGroup->leader->name ?? 'No leader assigned' }}</p>
                                <p class="text-sm text-gray-600">Status: {{ ucfirst($smallGroup->status) }}</p>
                                <p class="text-sm text-gray-600">Members: {{ $smallGroup->members->count() }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Members List -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-900 mb-3">Group Members</h4>
                        @if($smallGroup->members->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($smallGroup->members as $member)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ $member->name }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $member->email }}</div>
                                                    <div class="text-sm text-gray-500">{{ $member->phone }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @if($member->status === 'active') bg-green-100 text-green-800
                                                        @elseif($member->status === 'inactive') bg-red-100 text-red-800
                                                        @else bg-gray-100 text-gray-800 @endif">
                                                        {{ ucfirst($member->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $member->pivot->created_at?->format('M d, Y') ?? 'Unknown' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-600">No members have been assigned to this life group yet.</p>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-4">
                        <a href="{{ route('pastor.small-groups') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Back to Groups
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

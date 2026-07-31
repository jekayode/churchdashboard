<x-sidebar-layout title="Coverage Locations">
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Coverage Locations') }}</h2>
            <p class="text-sm text-gray-500">The areas members can pick as closest to them.</p>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        @if (session('success'))
            <div class="rounded-lg border border-church-200 bg-church-50 px-4 py-3 text-church-800">{{ session('success') }}</div>
        @endif

        <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-blue-900 text-sm">
            These feed the “which location is closest to you?” dropdown on member profiles and
            the guest flow. Retiring one keeps it for members who already chose it, but stops
            offering it to new people.
        </div>

        {{-- Add --}}
        <form method="POST" action="{{ route('pastor.coverage-locations.store') }}"
              class="bg-white rounded-xl border border-gray-200 p-5 flex items-end gap-3">
            @csrf
            <div class="flex-1">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Add a location</label>
                <input id="name" name="name" value="{{ old('name') }}" required maxlength="120"
                       placeholder="Area - Landmark, e.g. Eputu - Bogije"
                       class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <button class="px-4 py-2 rounded-lg bg-church-500 text-white font-semibold hover:bg-church-600">Add</button>
        </form>

        {{-- Manage --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @forelse ($locations as $location)
                {{-- A div, not a form: update and retire are separate posts, and
                     forms cannot nest. --}}
                <div class="flex flex-wrap items-center gap-3 p-4 border-b border-gray-100 last:border-0 {{ $location->is_active ? '' : 'bg-gray-50' }}">
                    <form method="POST" action="{{ route('pastor.coverage-locations.update', $location) }}"
                          class="flex flex-wrap items-center gap-3 flex-1">
                        @csrf @method('PUT')
                        <input type="number" name="sort_order" value="{{ $location->sort_order }}" min="0" max="999"
                               class="w-16 rounded-lg border-gray-300 text-sm focus:border-church-500 focus:ring-church-500" title="Order">
                        <input name="name" value="{{ $location->name }}" required maxlength="120"
                               class="flex-1 min-w-[180px] rounded-lg border-gray-300 text-sm focus:border-church-500 focus:ring-church-500">
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="is_active" value="1" {{ $location->is_active ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-church-500 focus:ring-church-500">
                            Active
                        </label>
                        <button class="px-3 py-1.5 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">Save</button>
                    </form>
                    @if ($location->is_active)
                        <form method="POST" action="{{ route('pastor.coverage-locations.destroy', $location) }}"
                              onsubmit="return confirm('Retire “{{ $location->name }}”? Members who already chose it keep it; it stops being offered to new people.')">
                            @csrf @method('DELETE')
                            <button class="px-3 py-1.5 rounded-lg border border-red-200 text-sm font-medium text-red-700 hover:bg-red-50">Retire</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="p-6 text-center text-gray-500 text-sm">No locations yet. Add your first above.</p>
            @endforelse
        </div>
    </div>
</x-sidebar-layout>

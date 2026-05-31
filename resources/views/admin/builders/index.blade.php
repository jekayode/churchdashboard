<x-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Builders Community Admin</h2>
    </x-slot>

    <div class="py-6" x-data="{ stats: {} }" x-init="fetch('/api/admin/builders/stats', {headers: apiHeaders()}).then(r=>r.json()).then(j=>stats=j.data)">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white p-4 rounded-lg shadow"><p class="text-sm text-gray-500">Total registrations</p><p class="text-2xl font-bold" x-text="stats.total ?? '—'"></p></div>
            <div class="bg-white p-4 rounded-lg shadow"><p class="text-sm text-gray-500">New</p><p class="text-2xl font-bold text-amber-600" x-text="stats.new ?? '—'"></p></div>
            <div class="bg-white p-4 rounded-lg shadow"><p class="text-sm text-gray-500">Contacted</p><p class="text-2xl font-bold text-green-600" x-text="stats.contacted ?? '—'"></p></div>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.builders.registrations') }}" class="bg-orange-500 text-white px-4 py-2 rounded-lg text-sm">Registrations</a>
            <a href="{{ route('admin.builders.settings') }}" class="bg-white border px-4 py-2 rounded-lg text-sm">Settings &amp; Pack Files</a>
            <a href="{{ route('builders.create') }}" target="_blank" class="bg-white border px-4 py-2 rounded-lg text-sm">View public form</a>
        </div>
    </div>
    <script>function apiHeaders(){const t=document.querySelector('meta[name="api-token"]')?.content;return{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,...(t?{'Authorization':'Bearer '+t}:{})}}</script>
</x-sidebar-layout>

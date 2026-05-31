<x-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Business Directory Admin</h2>
    </x-slot>

    <div class="py-6" x-data="{ stats: {} }" x-init="fetch('/api/admin/biz/stats', {headers: apiHeaders()}).then(r=>r.json()).then(j=>stats=j.data)">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-4 rounded-lg shadow"><p class="text-sm text-gray-500">Total</p><p class="text-2xl font-bold" x-text="stats.total ?? '—'"></p></div>
            <div class="bg-white p-4 rounded-lg shadow"><p class="text-sm text-gray-500">Pending</p><p class="text-2xl font-bold text-amber-600" x-text="stats.pending ?? '—'"></p></div>
            <div class="bg-white p-4 rounded-lg shadow"><p class="text-sm text-gray-500">Active</p><p class="text-2xl font-bold text-green-600" x-text="stats.active ?? '—'"></p></div>
            <div class="bg-white p-4 rounded-lg shadow"><p class="text-sm text-gray-500">Pending Reviews</p><p class="text-2xl font-bold" x-text="stats.pending_reviews ?? '—'"></p></div>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.biz.businesses') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">Manage Businesses</a>
            <a href="{{ route('admin.biz.categories') }}" class="bg-white border px-4 py-2 rounded-lg text-sm">Categories</a>
            <a href="{{ route('admin.biz.reviews') }}" class="bg-white border px-4 py-2 rounded-lg text-sm">Reviews</a>
            <a href="{{ route('admin.biz.settings') }}" class="bg-white border px-4 py-2 rounded-lg text-sm">Branding & Settings</a>
            <a href="{{ route('admin.biz.changelog') }}" class="bg-white border px-4 py-2 rounded-lg text-sm">Changelog</a>
            <a href="{{ route('biz.landing') }}" target="_blank" class="bg-white border px-4 py-2 rounded-lg text-sm">View Public Directory</a>
        </div>
    </div>
    <script>function apiHeaders(){const t=document.querySelector('meta[name="api-token"]')?.content;return{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,...(t?{'Authorization':'Bearer '+t}:{})}}</script>
</x-sidebar-layout>

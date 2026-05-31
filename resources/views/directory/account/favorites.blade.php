<x-sidebar-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">My Favorites</h2></x-slot>
    <div class="py-6">
        <p class="text-gray-600 mb-4"><a href="{{ route('biz.favorites') }}" class="text-indigo-600">Browse the public favorites page</a> or view liked businesses below.</p>
        <div x-data="{items:[]}" x-init="fetch('/api/biz/favorites',{headers:apiHeaders()}).then(r=>r.json()).then(j=>items=j.data.data||j.data)">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <template x-for="b in items" :key="b.id">
                    <a :href="`/biz/${b.slug}`" class="block bg-white border rounded-lg p-4" x-text="b.name"></a>
                </template>
            </div>
        </div>
    </div>
    <script>function apiHeaders(){const t=document.querySelector('meta[name="api-token"]')?.content;return{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,...(t?{'Authorization':'Bearer '+t}:{})}}</script>
</x-sidebar-layout>

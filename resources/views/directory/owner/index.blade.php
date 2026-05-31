<x-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Businesses</h2>
    </x-slot>

    <div class="py-6" x-data="ownerBusinesses()" x-init="load()">
        <div class="flex justify-between items-center mb-6">
            <p class="text-gray-600">Manage your business listings in the directory.</p>
            <a href="{{ route('biz.owner.businesses.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">Add Business</a>
        </div>
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Views</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="b in businesses" :key="b.id">
                        <tr>
                            <td class="px-4 py-3 font-medium" x-text="b.name"></td>
                            <td class="px-4 py-3"><span class="text-xs px-2 py-1 rounded bg-gray-100" x-text="statusLabel(b.status)"></span></td>
                            <td class="px-4 py-3" x-text="b.views_count"></td>
                            <td class="px-4 py-3" x-text="b.likes_count"></td>
                            <td class="px-4 py-3" x-text="b.reviews_count"></td>
                            <td class="px-4 py-3 text-right">
                                <a :href="`/biz/${b.slug}`" class="text-indigo-600 text-sm mr-3" target="_blank">View</a>
                                <a :href="`/biz/owner/businesses/${b.slug}/edit`" class="text-indigo-600 text-sm">Edit</a>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function ownerBusinesses() {
        return {
            businesses: [],
            statusLabel(status){
                if(status === 'draft') return 'Draft';
                if(status === 'pending_review') return 'Pending Review';
                return status;
            },
            headers() {
                const t = document.querySelector('meta[name="api-token"]')?.content;
                return {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    ...(t ? { 'Authorization': 'Bearer ' + t } : {})
                };
            },
            async load() {
                const res = await fetch('/api/biz/my-businesses', { headers: this.headers() });
                const json = await res.json();
                if (json.success) this.businesses = json.data.data || json.data;
            },
        }
    }
    </script>
</x-sidebar-layout>

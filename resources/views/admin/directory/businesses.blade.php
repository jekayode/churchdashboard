<x-sidebar-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Directory Businesses</h2></x-slot>
    <div class="py-6" x-data="adminBiz()" x-init="load()">
        <div class="mb-4 flex gap-2">
            <select x-model="status" @change="load()" class="rounded border-gray-300 text-sm">
                <option value="">All statuses</option>
                <option value="pending_review">Pending Review</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="bg-white shadow rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-2 text-left">Name</th><th class="px-4 py-2">Owner</th><th class="px-4 py-2">Status</th><th class="px-4 py-2">Actions</th>
                </tr></thead>
                <tbody>
                    <template x-for="b in items" :key="b.id">
                        <tr class="border-t">
                            <td class="px-4 py-2" x-text="b.name"></td>
                            <td class="px-4 py-2" x-text="b.owner?.name"></td>
                            <td class="px-4 py-2" x-text="b.status"></td>
                            <td class="px-4 py-2 space-x-2">
                                <template x-if="b.status === 'pending_review'">
                                    <button @click="approve(b.slug)" class="text-green-600">Approve</button>
                                    <button @click="reject(b.slug)" class="text-red-600">Reject</button>
                                </template>
                                <button @click="toggleFeatured(b.slug)" class="text-indigo-600">Feature</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
    <script>
    function adminBiz(){return{
        items:[],status:'pending_review',
        h(){const t=document.querySelector('meta[name="api-token"]')?.content;return{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,...(t?{'Authorization':'Bearer '+t}:{})}},
        async load(){const u=this.status?`?status=${this.status}`:'';const r=await fetch('/api/admin/biz/businesses'+u,{headers:this.h()});const j=await r.json();if(j.success)this.items=j.data.data||j.data},
        async approve(slug){await fetch(`/api/admin/biz/businesses/${slug}/approve`,{method:'POST',headers:this.h()});this.load()},
        async reject(slug){
            const reason = prompt('Rejection reason:');
            if(!reason) return;
            await fetch(`/api/admin/biz/businesses/${slug}/reject`,{
                method:'POST',
                headers:this.h(),
                body: JSON.stringify({ reason })
            });
            this.load();
        },
        async toggleFeatured(slug){await fetch(`/api/admin/biz/businesses/${slug}/toggle-featured`,{method:'POST',headers:this.h()});this.load()}
    }}
    </script>
</x-sidebar-layout>

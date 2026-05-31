<x-sidebar-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Builders Registrations</h2>
            <a href="{{ route('admin.builders.index') }}" class="text-sm text-orange-600 hover:underline">Back</a>
        </div>
    </x-slot>

    <div class="py-6" x-data="registrationsList()" x-init="load()">
        <div class="mb-4 flex flex-wrap gap-3">
            <input type="search" x-model="search" @input.debounce.400ms="load()" placeholder="Search name, email, business…" class="rounded-md border-gray-300 text-sm w-full max-w-md">
            <select x-model="status" @change="load()" class="rounded-md border-gray-300 text-sm">
                <option value="">All statuses</option>
                <option value="new">New</option>
                <option value="contacted">Contacted</option>
            </select>
        </div>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left text-gray-600">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Business</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="row in rows" :key="row.id">
                        <tr class="border-t border-gray-100 hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium" x-text="row.full_name"></td>
                            <td class="px-4 py-3" x-text="row.business_name"></td>
                            <td class="px-4 py-3" x-text="row.email"></td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs" :class="row.status === 'contacted' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'" x-text="row.status"></span></td>
                            <td class="px-4 py-3 text-gray-500" x-text="row.created_at?.substring(0,10)"></td>
                            <td class="px-4 py-3"><a :href="'/admin/builders/registrations/' + row.id" class="text-orange-600 hover:underline">View</a></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <p x-show="!rows.length" class="p-6 text-center text-gray-500">No registrations found.</p>
        </div>
    </div>
    <script>
    function apiHeaders(){const t=document.querySelector('meta[name="api-token"]')?.content;return{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,...(t?{'Authorization':'Bearer '+t}:{})}}
    function registrationsList(){return{rows:[],search:'',status:'',async load(){const p=new URLSearchParams();if(this.search)p.set('search',this.search);if(this.status)p.set('status',this.status);const r=await fetch('/api/admin/builders/registrations?'+p,{headers:apiHeaders()});const j=await r.json();this.rows=j.data?.data??[];}}}
    </script>
</x-sidebar-layout>

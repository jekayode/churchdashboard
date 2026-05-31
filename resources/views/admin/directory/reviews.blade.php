<x-sidebar-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Review Moderation</h2></x-slot>
    <div class="py-6" x-data="{items:[]}" x-init="fetch('/api/admin/biz/reviews?status=pending',{headers:apiHeaders()}).then(r=>r.json()).then(j=>items=j.data.data||j.data)">
        <template x-for="r in items" :key="r.id">
            <div class="bg-white border rounded-lg p-4 mb-3">
                <p class="font-medium" x-text="r.business?.name"></p>
                <p class="text-sm" x-text="r.user?.name + ' — ★ ' + r.rating"></p>
                <p class="text-gray-600 text-sm mt-1" x-text="r.body"></p>
                <div class="mt-2 space-x-2">
                    <button @click="moderate(r.id,'approved')" class="text-green-600 text-sm">Approve</button>
                    <button @click="moderate(r.id,'hidden')" class="text-red-600 text-sm">Hide</button>
                </div>
            </div>
        </template>
    </div>
    <script>
    function apiHeaders(){const t=document.querySelector('meta[name="api-token"]')?.content;return{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,...(t?{'Authorization':'Bearer '+t}:{})}}
    async function moderate(id,status){await fetch(`/api/admin/biz/reviews/${id}/moderate`,{method:'PUT',headers:apiHeaders(),body:JSON.stringify({status})});location.reload()}
    </script>
</x-sidebar-layout>

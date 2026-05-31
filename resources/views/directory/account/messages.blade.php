<x-sidebar-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Directory Messages</h2></x-slot>
    <div class="py-6" x-data="{threads:[]}" x-init="fetch('/api/biz/messages/threads',{headers:apiHeaders()}).then(r=>r.json()).then(j=>threads=j.data.data||j.data)">
        <template x-for="t in threads" :key="t.thread_id">
            <a :href="`/biz/messages/${t.thread_id}`" class="block bg-white border rounded-lg p-4 mb-2 hover:shadow">
                <p class="font-medium" x-text="t.business?.name"></p>
            </a>
        </template>
        <p x-show="!threads.length" class="text-gray-500">No messages yet.</p>
    </div>
    <script>function apiHeaders(){const t=document.querySelector('meta[name="api-token"]')?.content;return{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,...(t?{'Authorization':'Bearer '+t}:{})}}</script>
</x-sidebar-layout>

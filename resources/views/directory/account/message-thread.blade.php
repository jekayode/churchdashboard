<x-sidebar-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Conversation</h2></x-slot>
    <div class="py-6 max-w-2xl" x-data="threadView('{{ $threadId }}')" x-init="load()">
        <div class="space-y-3 mb-4">
            <template x-for="m in messages" :key="m.id">
                <div class="bg-white border rounded-lg p-3" :class="m.sender_user_id === {{ auth()->id() }} ? 'ml-8' : 'mr-8'">
                    <p class="text-sm font-medium" x-text="m.sender?.name"></p>
                    <p x-text="m.body"></p>
                </div>
            </template>
        </div>
        <form @submit.prevent="reply" class="flex gap-2">
            <input x-model="body" class="flex-1 rounded border-gray-300" placeholder="Type a reply..." required>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg">Send</button>
        </form>
    </div>
    <script>
    function threadView(id){return{messages:[],body:'',h(){const t=document.querySelector('meta[name="api-token"]')?.content;return{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,...(t?{'Authorization':'Bearer '+t}:{})}},async load(){const r=await fetch(`/api/biz/messages/threads/${id}`,{headers:this.h()});const j=await r.json();if(j.success)this.messages=j.data},async reply(){await fetch(`/api/biz/messages/threads/${id}/reply`,{method:'POST',headers:this.h(),body:JSON.stringify({body:this.body})});this.body='';this.load()}}}
    </script>
</x-sidebar-layout>

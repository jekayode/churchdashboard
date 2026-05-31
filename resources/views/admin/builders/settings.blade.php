<x-sidebar-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Builders Settings &amp; Pack Files</h2>
            <a href="{{ route('admin.builders.index') }}" class="text-sm text-orange-600 hover:underline">Back</a>
        </div>
    </x-slot>

    <div class="py-6 max-w-3xl space-y-8" x-data="builderSettings()" x-init="load()">
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <h3 class="font-semibold text-slate-900">Community links</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700">WhatsApp group invite link</label>
                <input type="url" x-model="settings.whatsapp_group_link" class="mt-1 w-full rounded-md border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Google Drive link (optional)</label>
                <input type="url" x-model="settings.google_drive_link" class="mt-1 w-full rounded-md border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Form intro text</label>
                <textarea x-model="settings.intro_text" rows="2" class="mt-1 w-full rounded-md border-gray-300 text-sm"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Thank-you page message</label>
                <textarea x-model="settings.confirmation_body" rows="3" class="mt-1 w-full rounded-md border-gray-300 text-sm"></textarea>
            </div>
            <button type="button" @click="saveSettings()" class="bg-orange-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-orange-600">Save settings</button>
            <p x-show="message" class="text-sm text-green-600" x-text="message"></p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-slate-900 mb-4">Starter pack PDF files</h3>
            <form @submit.prevent="uploadFile()" class="space-y-3 mb-6 border-b pb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" x-model="upload.title" required class="mt-1 w-full rounded-md border-gray-300 text-sm" placeholder="Business Plan Template">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">PDF file</label>
                    <input type="file" @change="upload.file = $event.target.files[0]" accept=".pdf" required class="mt-1 text-sm">
                </div>
                <button type="submit" class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm">Upload PDF</button>
            </form>
            <ul class="space-y-2">
                <template x-for="res in resources" :key="res.id">
                    <li class="flex items-center justify-between gap-4 rounded border border-gray-200 p-3 text-sm">
                        <span x-text="res.title"></span>
                        <button type="button" @click="deleteResource(res.id)" class="text-red-600 hover:underline">Remove</button>
                    </li>
                </template>
            </ul>
            <p x-show="!resources.length" class="text-sm text-gray-500">No pack files uploaded yet.</p>
        </div>
    </div>
    <script>
    function apiHeaders(){const t=document.querySelector('meta[name="api-token"]')?.content;return{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,...(t?{'Authorization':'Bearer '+t}:{})}}
    function builderSettings(){return{
        settings:{},resources:[],message:'',upload:{title:'',file:null},
        async load(){const r=await fetch('/api/admin/builders/settings',{headers:apiHeaders()});const j=await r.json();this.settings=j.data.settings;this.resources=j.data.resources;},
        async saveSettings(){await fetch('/api/admin/builders/settings',{method:'PUT',headers:{...apiHeaders(),'Content-Type':'application/json'},body:JSON.stringify(this.settings)});this.message='Settings saved.';setTimeout(()=>this.message='',3000);},
        async uploadFile(){const fd=new FormData();fd.append('title',this.upload.title);fd.append('file',this.upload.file);await fetch('/api/admin/builders/resources',{method:'POST',headers:apiHeaders(),body:fd});this.upload={title:'',file:null};await this.load();},
        async deleteResource(id){if(!confirm('Remove this file?'))return;await fetch('/api/admin/builders/resources/'+id,{method:'DELETE',headers:apiHeaders()});await this.load();}
    }}
    </script>
</x-sidebar-layout>

<x-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Directory Changelog</h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto" x-data="changelogAdmin()" x-init="load()">
        <div x-show="toast.message" x-transition
             class="fixed top-6 right-6 z-50 px-4 py-3 rounded-lg shadow-lg text-white"
             :class="toast.type === 'error' ? 'bg-rose-600' : 'bg-emerald-600'"
             x-text="toast.message"></div>

        <div class="flex items-center justify-between mb-4">
            <p class="text-sm text-gray-600"><span x-text="entries.length"></span> entries</p>
            <button @click="openCreate()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                + Add Entry
            </button>
        </div>

        <div class="space-y-3">
            <template x-for="e in entries" :key="e.id">
                <div class="bg-white border rounded-lg p-4 flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-mono bg-gray-100 px-2 py-0.5 rounded" x-text="'v' + e.version"></span>
                            <span class="text-xs text-gray-500" x-text="e.published_at ? new Date(e.published_at).toLocaleDateString() : 'unpublished'"></span>
                        </div>
                        <h3 class="font-semibold text-slate-900" x-text="e.title"></h3>
                        <p class="text-sm text-gray-700 mt-1 whitespace-pre-line" x-text="e.body"></p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <button @click="openEdit(e)" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Edit</button>
                        <button @click="confirmDelete(e)" class="text-rose-600 hover:text-rose-800 text-sm font-medium">Delete</button>
                    </div>
                </div>
            </template>
            <template x-if="!entries.length">
                <div class="bg-white border rounded-lg p-8 text-center text-gray-500 text-sm">No changelog entries yet.</div>
            </template>
        </div>

        {{-- Create / Edit modal --}}
        <div x-show="modal.open" x-transition.opacity
             class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50 px-4"
             @keydown.escape.window="closeModal()">
            <div @click.outside="closeModal()" class="bg-white rounded-xl shadow-2xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4" x-text="modal.mode === 'edit' ? 'Edit Entry' : 'Add Entry'"></h3>

                <form @submit.prevent="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Version</label>
                            <input x-model="form.version" type="text" required maxlength="50"
                                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="1.2.0">
                            <p x-show="errors.version" class="text-rose-600 text-xs mt-1" x-text="errors.version"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Publish date</label>
                            <input x-model="form.published_at" type="date"
                                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Title</label>
                        <input x-model="form.title" type="text" required maxlength="255"
                               class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="What changed?">
                        <p x-show="errors.title" class="text-rose-600 text-xs mt-1" x-text="errors.title"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Body</label>
                        <textarea x-model="form.body" rows="5" required class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Details about this release..."></textarea>
                        <p x-show="errors.body" class="text-rose-600 text-xs mt-1" x-text="errors.body"></p>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="closeModal()" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-gray-100">Cancel</button>
                        <button type="submit" :disabled="saving" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium disabled:opacity-60" x-text="saving ? 'Saving...' : 'Save'"></button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Delete confirm modal --}}
        <div x-show="deleting.open" x-transition.opacity
             class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50 px-4"
             @keydown.escape.window="deleting.open = false">
            <div @click.outside="deleting.open = false" class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Delete entry?</h3>
                <p class="text-sm text-gray-600 mb-4">This will remove <span class="font-semibold" x-text="'v' + (deleting.entry?.version ?? '')"></span> — <span x-text="deleting.entry?.title"></span>.</p>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="deleting.open = false" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-gray-100">Cancel</button>
                    <button type="button" @click="doDelete()" :disabled="deleting.busy" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg text-sm font-medium disabled:opacity-60" x-text="deleting.busy ? 'Deleting...' : 'Delete'"></button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function changelogAdmin() {
        return {
            entries: [],
            modal: { open: false, mode: 'create' },
            form: { id: null, version: '', title: '', body: '', published_at: '' },
            errors: {},
            saving: false,
            deleting: { open: false, entry: null, busy: false },
            toast: { message: '', type: 'success' },
            showToast(message, type = 'success') {
                this.toast = { message, type };
                setTimeout(() => { this.toast.message = ''; }, 3000);
            },
            headers() {
                const t = document.querySelector('meta[name="api-token"]')?.content;
                const h = {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                };
                if (t) h['Authorization'] = 'Bearer ' + t;
                return h;
            },
            async load() {
                const r = await fetch('/api/admin/biz/changelog', { headers: this.headers() });
                const j = await r.json();
                if (j.success) this.entries = j.data;
            },
            resetForm() {
                this.form = { id: null, version: '', title: '', body: '', published_at: new Date().toISOString().slice(0, 10) };
                this.errors = {};
            },
            openCreate() {
                this.resetForm();
                this.modal = { open: true, mode: 'create' };
            },
            openEdit(e) {
                this.form = {
                    id: e.id,
                    version: e.version ?? '',
                    title: e.title ?? '',
                    body: e.body ?? '',
                    published_at: e.published_at ? e.published_at.slice(0, 10) : '',
                };
                this.errors = {};
                this.modal = { open: true, mode: 'edit' };
            },
            closeModal() { this.modal.open = false; },
            async save() {
                this.saving = true;
                this.errors = {};
                const isEdit = this.modal.mode === 'edit';
                const url = isEdit ? `/api/admin/biz/changelog/${this.form.id}` : '/api/admin/biz/changelog';
                const method = isEdit ? 'PUT' : 'POST';
                const payload = {
                    version: this.form.version,
                    title: this.form.title,
                    body: this.form.body,
                    published_at: this.form.published_at || null,
                };
                try {
                    const r = await fetch(url, { method, headers: this.headers(), body: JSON.stringify(payload) });
                    const j = await r.json();
                    if (!r.ok) {
                        if (j.errors) for (const k in j.errors) this.errors[k] = j.errors[k][0];
                        this.showToast(j.message || 'Save failed.', 'error');
                        return;
                    }
                    this.showToast(isEdit ? 'Entry updated.' : 'Entry created.');
                    this.modal.open = false;
                    await this.load();
                } finally {
                    this.saving = false;
                }
            },
            confirmDelete(e) { this.deleting = { open: true, entry: e, busy: false }; },
            async doDelete() {
                this.deleting.busy = true;
                try {
                    const r = await fetch(`/api/admin/biz/changelog/${this.deleting.entry.id}`, {
                        method: 'DELETE', headers: this.headers(),
                    });
                    if (!r.ok) { this.showToast('Delete failed.', 'error'); return; }
                    this.showToast('Entry deleted.');
                    this.deleting.open = false;
                    await this.load();
                } finally {
                    this.deleting.busy = false;
                }
            },
        };
    }
    </script>
</x-sidebar-layout>

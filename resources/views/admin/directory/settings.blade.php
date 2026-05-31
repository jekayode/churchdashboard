<x-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Directory Branding & Announcements</h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto space-y-8" x-data="bizSettings()" x-init="loadAll()">
        {{-- Toast --}}
        <div x-show="toast.message" x-transition
             class="fixed top-6 right-6 z-50 px-4 py-3 rounded-lg shadow-lg text-white"
             :class="toast.type === 'error' ? 'bg-rose-600' : 'bg-emerald-600'"
             x-text="toast.message"></div>

        {{-- Branding --}}
        <form @submit.prevent="saveSettings" class="bg-white shadow rounded-lg p-6 space-y-4">
            <h3 class="font-semibold text-slate-900">Branding</h3>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tagline</label>
                <input x-model="form.tagline" type="text" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Primary color</label>
                    <input x-model="form.primary_color" type="color" class="h-10 w-20 rounded">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Secondary color</label>
                    <input x-model="form.secondary_color" type="color" class="h-10 w-20 rounded">
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" x-model="form.reviews_require_approval" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                Reviews require approval
            </label>
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" x-model="form.business_approval_required" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                Businesses require approval
            </label>
            <div>
                <button type="submit" :disabled="savingSettings" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium disabled:opacity-60" x-text="savingSettings ? 'Saving...' : 'Save settings'"></button>
            </div>
        </form>

        {{-- Announcements --}}
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-slate-900">Announcements</h3>
                    <p class="text-sm text-gray-600">Banners shown on the public directory landing page.</p>
                </div>
                <button @click="openCreate()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    + Add Announcement
                </button>
            </div>

            <ul class="divide-y border rounded-lg">
                <template x-for="a in announcements" :key="a.id">
                    <li class="px-4 py-3 flex items-start gap-4">
                        <template x-if="a.image_url">
                            <img :src="a.image_url" alt="" class="w-14 h-14 rounded-lg object-cover flex-shrink-0">
                        </template>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-medium text-slate-900 truncate" x-text="a.title"></p>
                                <span class="text-xs px-2 py-0.5 rounded-full"
                                      :class="a.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'"
                                      x-text="a.is_active ? 'active' : 'inactive'"></span>
                            </div>
                            <p x-show="a.body" class="text-sm text-gray-600 mt-1 line-clamp-2" x-text="a.body"></p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <button @click="openEdit(a)" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Edit</button>
                            <button @click="confirmDelete(a)" class="text-rose-600 hover:text-rose-800 text-sm font-medium">Delete</button>
                        </div>
                    </li>
                </template>
                <template x-if="!announcements.length">
                    <li class="px-4 py-8 text-center text-gray-500 text-sm">No announcements yet.</li>
                </template>
            </ul>
        </div>

        {{-- Announcement modal --}}
        <div x-show="modal.open" x-transition.opacity
             class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50 px-4"
             @keydown.escape.window="closeModal()">
            <div @click.outside="closeModal()" class="bg-white rounded-xl shadow-2xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-slate-900 mb-4" x-text="modal.mode === 'edit' ? 'Edit Announcement' : 'Add Announcement'"></h3>

                <form @submit.prevent="saveAnnouncement" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Title</label>
                        <input x-model="annForm.title" type="text" required maxlength="255"
                               class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <p x-show="annErrors.title" class="text-rose-600 text-xs mt-1" x-text="annErrors.title"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Body</label>
                        <textarea x-model="annForm.body" rows="3" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Link (optional)</label>
                        <input x-model="annForm.link" type="url" maxlength="500" placeholder="https://..."
                               class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <p x-show="annErrors.link" class="text-rose-600 text-xs mt-1" x-text="annErrors.link"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Image (optional)</label>
                        <template x-if="annForm.existing_image_url && !removeExistingImage && !imageFile">
                            <div class="mb-2 flex items-center gap-3">
                                <img :src="annForm.existing_image_url" alt="" class="w-16 h-16 rounded-lg object-cover border">
                                <button type="button" @click="removeExistingImage = true" class="text-rose-600 hover:text-rose-800 text-sm">Remove image</button>
                            </div>
                        </template>
                        <input type="file" accept="image/*" @change="imageFile = $event.target.files[0]; removeExistingImage = false"
                               class="block w-full text-sm text-slate-700 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p x-show="imageFile" class="text-xs text-gray-600 mt-1" x-text="imageFile ? 'Selected: ' + imageFile.name : ''"></p>
                        <p x-show="annErrors.image" class="text-rose-600 text-xs mt-1" x-text="annErrors.image"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Sort order</label>
                            <input x-model.number="annForm.sort_order" type="number" min="0" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="flex flex-col gap-2 pt-6">
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" x-model="annForm.is_active" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                Active
                            </label>
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" x-model="annForm.is_dismissible" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                Dismissible
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="closeModal()" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-gray-100">Cancel</button>
                        <button type="submit" :disabled="savingAnn" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium disabled:opacity-60" x-text="savingAnn ? 'Saving...' : 'Save'"></button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Delete confirm modal --}}
        <div x-show="deleting.open" x-transition.opacity
             class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50 px-4"
             @keydown.escape.window="deleting.open = false">
            <div @click.outside="deleting.open = false" class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Delete announcement?</h3>
                <p class="text-sm text-gray-600 mb-4">This will permanently remove <span class="font-semibold" x-text="deleting.ann?.title"></span>.</p>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="deleting.open = false" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-gray-100">Cancel</button>
                    <button type="button" @click="doDeleteAnnouncement()" :disabled="deleting.busy" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg text-sm font-medium disabled:opacity-60" x-text="deleting.busy ? 'Deleting...' : 'Delete'"></button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function bizSettings() {
        return {
            form: {},
            announcements: [],
            modal: { open: false, mode: 'create' },
            annForm: { id: null, title: '', body: '', link: '', sort_order: 0, is_active: true, is_dismissible: true, existing_image_url: null },
            annErrors: {},
            imageFile: null,
            removeExistingImage: false,
            savingSettings: false,
            savingAnn: false,
            deleting: { open: false, ann: null, busy: false },
            toast: { message: '', type: 'success' },
            showToast(message, type = 'success') {
                this.toast = { message, type };
                setTimeout(() => { this.toast.message = ''; }, 3000);
            },
            headers(json = true) {
                const t = document.querySelector('meta[name="api-token"]')?.content;
                const h = {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                };
                if (json) h['Content-Type'] = 'application/json';
                if (t) h['Authorization'] = 'Bearer ' + t;
                return h;
            },
            async loadAll() {
                await Promise.all([this.loadSettings(), this.loadAnnouncements()]);
            },
            async loadSettings() {
                const r = await fetch('/api/admin/biz/settings', { headers: this.headers() });
                const j = await r.json();
                if (j.success) this.form = j.data;
            },
            async loadAnnouncements() {
                const r = await fetch('/api/admin/biz/announcements', { headers: this.headers() });
                const j = await r.json();
                if (j.success) this.announcements = j.data;
            },
            async saveSettings() {
                this.savingSettings = true;
                try {
                    const r = await fetch('/api/admin/biz/settings', {
                        method: 'PUT', headers: this.headers(), body: JSON.stringify(this.form),
                    });
                    if (!r.ok) { this.showToast('Save failed.', 'error'); return; }
                    this.showToast('Settings saved.');
                } finally {
                    this.savingSettings = false;
                }
            },
            resetAnnForm() {
                this.annForm = { id: null, title: '', body: '', link: '', sort_order: 0, is_active: true, is_dismissible: true, existing_image_url: null };
                this.annErrors = {};
                this.imageFile = null;
                this.removeExistingImage = false;
            },
            openCreate() {
                this.resetAnnForm();
                this.modal = { open: true, mode: 'create' };
            },
            openEdit(a) {
                this.annForm = {
                    id: a.id,
                    title: a.title ?? '',
                    body: a.body ?? '',
                    link: a.link ?? '',
                    sort_order: a.sort_order ?? 0,
                    is_active: !!a.is_active,
                    is_dismissible: !!a.is_dismissible,
                    existing_image_url: a.image_url ?? null,
                };
                this.annErrors = {};
                this.imageFile = null;
                this.removeExistingImage = false;
                this.modal = { open: true, mode: 'edit' };
            },
            closeModal() { this.modal.open = false; },
            async saveAnnouncement() {
                this.savingAnn = true;
                this.annErrors = {};
                const isEdit = this.modal.mode === 'edit';
                const url = isEdit ? `/api/admin/biz/announcements/${this.annForm.id}` : '/api/admin/biz/announcements';

                const fd = new FormData();
                fd.append('title', this.annForm.title);
                if (this.annForm.body) fd.append('body', this.annForm.body);
                if (this.annForm.link) fd.append('link', this.annForm.link);
                fd.append('sort_order', String(this.annForm.sort_order || 0));
                fd.append('is_active', this.annForm.is_active ? '1' : '0');
                fd.append('is_dismissible', this.annForm.is_dismissible ? '1' : '0');
                if (this.imageFile) fd.append('image', this.imageFile);
                if (isEdit && this.removeExistingImage) fd.append('remove_image', '1');

                try {
                    const r = await fetch(url, { method: 'POST', headers: this.headers(false), body: fd });
                    const j = await r.json();
                    if (!r.ok) {
                        if (j.errors) for (const k in j.errors) this.annErrors[k] = j.errors[k][0];
                        this.showToast(j.message || 'Save failed.', 'error');
                        return;
                    }
                    this.showToast(isEdit ? 'Announcement updated.' : 'Announcement created.');
                    this.modal.open = false;
                    await this.loadAnnouncements();
                } finally {
                    this.savingAnn = false;
                }
            },
            confirmDelete(a) { this.deleting = { open: true, ann: a, busy: false }; },
            async doDeleteAnnouncement() {
                this.deleting.busy = true;
                try {
                    const r = await fetch(`/api/admin/biz/announcements/${this.deleting.ann.id}`, {
                        method: 'DELETE', headers: this.headers(),
                    });
                    if (!r.ok) { this.showToast('Delete failed.', 'error'); return; }
                    this.showToast('Announcement deleted.');
                    this.deleting.open = false;
                    await this.loadAnnouncements();
                } finally {
                    this.deleting.busy = false;
                }
            },
        };
    }
    </script>
</x-sidebar-layout>

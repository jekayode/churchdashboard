<x-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Directory Categories</h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto" x-data="adminCats()" x-init="load()">
        {{-- Toast --}}
        <div x-show="toast.message" x-transition
             class="fixed top-6 right-6 z-50 px-4 py-3 rounded-lg shadow-lg text-white"
             :class="toast.type === 'error' ? 'bg-rose-600' : 'bg-emerald-600'"
             x-text="toast.message"></div>

        <div class="flex items-center justify-between mb-4">
            <p class="text-sm text-gray-600"><span x-text="cats.length"></span> categories</p>
            <button @click="openCreate()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                + Add Category
            </button>
        </div>

        <ul class="bg-white shadow rounded-lg divide-y">
            <template x-for="c in cats" :key="c.id">
                <li class="px-4 py-3 flex items-center justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-slate-900 truncate" x-text="c.name"></p>
                        <p class="text-xs text-gray-500 truncate" x-text="c.slug + ' • ' + (c.businesses_count ?? 0) + ' listings'"></p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full"
                          :class="c.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'"
                          x-text="c.is_active ? 'active' : 'inactive'"></span>
                    <div class="flex items-center gap-2">
                        <button @click="openEdit(c)" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Edit</button>
                        <button @click="confirmDelete(c)" class="text-rose-600 hover:text-rose-800 text-sm font-medium">Delete</button>
                    </div>
                </li>
            </template>
            <template x-if="!cats.length">
                <li class="px-4 py-8 text-center text-gray-500 text-sm">No categories yet. Add your first one.</li>
            </template>
        </ul>

        {{-- Create / Edit modal --}}
        <div x-show="modal.open" x-transition.opacity
             class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50 px-4"
             @keydown.escape.window="closeModal()">
            <div @click.outside="closeModal()" class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4" x-text="modal.mode === 'edit' ? 'Edit Category' : 'Add Category'"></h3>

                <form @submit.prevent="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                        <input x-model="form.name" type="text" required maxlength="255"
                               class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="e.g. Wellness">
                        <p x-show="errors.name" class="text-rose-600 text-xs mt-1" x-text="errors.name"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Slug <span class="text-gray-400 font-normal">(optional, auto-generated)</span></label>
                        <input x-model="form.slug" type="text" maxlength="255"
                               class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="wellness">
                        <p x-show="errors.slug" class="text-rose-600 text-xs mt-1" x-text="errors.slug"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                        <textarea x-model="form.description" rows="2" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Icon</label>
                            <input x-model="form.icon" type="text" maxlength="100" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. heart">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Sort order</label>
                            <input x-model.number="form.sort_order" type="number" min="0" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" x-model="form.is_active" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        Active
                    </label>

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
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Delete category?</h3>
                <p class="text-sm text-gray-600 mb-4">This will remove <span class="font-semibold" x-text="deleting.cat?.name"></span> and unassign it from all businesses. This action cannot be undone.</p>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="deleting.open = false" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-gray-100">Cancel</button>
                    <button type="button" @click="doDelete()" :disabled="deleting.busy" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg text-sm font-medium disabled:opacity-60" x-text="deleting.busy ? 'Deleting...' : 'Delete'"></button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function adminCats() {
        return {
            cats: [],
            modal: { open: false, mode: 'create' },
            form: { id: null, name: '', slug: '', description: '', icon: '', sort_order: 0, is_active: true },
            errors: {},
            saving: false,
            deleting: { open: false, cat: null, busy: false },
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
            async load() {
                const r = await fetch('/api/admin/biz/categories', { headers: this.headers() });
                const j = await r.json();
                if (j.success) this.cats = j.data;
            },
            resetForm() {
                this.form = { id: null, name: '', slug: '', description: '', icon: '', sort_order: 0, is_active: true };
                this.errors = {};
            },
            openCreate() {
                this.resetForm();
                this.modal = { open: true, mode: 'create' };
            },
            openEdit(c) {
                this.form = {
                    id: c.id,
                    name: c.name ?? '',
                    slug: c.slug ?? '',
                    description: c.description ?? '',
                    icon: c.icon ?? '',
                    sort_order: c.sort_order ?? 0,
                    is_active: !!c.is_active,
                };
                this.errors = {};
                this.modal = { open: true, mode: 'edit' };
            },
            closeModal() {
                this.modal.open = false;
            },
            async save() {
                this.saving = true;
                this.errors = {};
                const isEdit = this.modal.mode === 'edit';
                const url = isEdit ? `/api/admin/biz/categories/${this.form.id}` : '/api/admin/biz/categories';
                const method = isEdit ? 'PUT' : 'POST';
                const payload = {
                    name: this.form.name,
                    slug: this.form.slug || null,
                    description: this.form.description || null,
                    icon: this.form.icon || null,
                    sort_order: this.form.sort_order || 0,
                    is_active: this.form.is_active,
                };
                try {
                    const r = await fetch(url, { method, headers: this.headers(), body: JSON.stringify(payload) });
                    const j = await r.json();
                    if (!r.ok) {
                        if (j.errors) {
                            for (const k in j.errors) this.errors[k] = j.errors[k][0];
                        }
                        this.showToast(j.message || 'Save failed.', 'error');
                        return;
                    }
                    this.showToast(isEdit ? 'Category updated.' : 'Category created.');
                    this.modal.open = false;
                    await this.load();
                } finally {
                    this.saving = false;
                }
            },
            confirmDelete(c) {
                this.deleting = { open: true, cat: c, busy: false };
            },
            async doDelete() {
                this.deleting.busy = true;
                try {
                    const r = await fetch(`/api/admin/biz/categories/${this.deleting.cat.id}`, {
                        method: 'DELETE', headers: this.headers(),
                    });
                    if (!r.ok) {
                        this.showToast('Delete failed.', 'error');
                        return;
                    }
                    this.showToast('Category deleted.');
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

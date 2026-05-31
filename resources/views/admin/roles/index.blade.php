<x-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Roles & Permissions</h2>
    </x-slot>

    <div class="py-6" x-data="rolesMatrix()" x-init="init()">
        <div class="grid lg:grid-cols-4 gap-6">
            <div class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Roles</h3>
                    <button type="button" @click="showCreate = true" class="text-xs text-indigo-600">+ New</button>
                </div>
                <ul class="space-y-1">
                    <template x-for="role in roles" :key="role.id">
                        <li>
                            <button type="button" @click="selectRole(role)"
                                    :class="selectedRole?.id === role.id ? 'bg-indigo-50 text-indigo-800' : 'hover:bg-gray-50 dark:hover:bg-gray-700'"
                                    class="w-full text-left px-3 py-2 rounded-lg text-sm">
                                <span x-text="role.display_name"></span>
                                <span class="block text-xs text-gray-500" x-text="role.name"></span>
                            </button>
                        </li>
                    </template>
                </ul>
            </div>

            <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-lg shadow p-4" x-show="selectedRole">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100" x-text="selectedRole?.display_name"></h3>
                    <div class="flex gap-2">
                        <button type="button" @click="savePermissions()" :disabled="saving"
                                class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg disabled:opacity-50">Save permissions</button>
                        <button type="button" x-show="selectedRole && !selectedRole.is_system" @click="deleteRole()"
                                class="px-4 py-2 text-sm text-red-600 border border-red-200 rounded-lg">Delete role</button>
                    </div>
                </div>
                <p x-show="message" x-text="message" class="text-sm mb-3" :class="messageOk ? 'text-green-600' : 'text-red-600'"></p>

                <template x-for="group in permissionGroups" :key="group.group">
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-2" x-text="group.group"></h4>
                        <div class="grid sm:grid-cols-2 gap-2">
                            <template x-for="perm in group.permissions" :key="perm.id">
                                <label class="flex items-start gap-2 text-sm p-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                    <input type="checkbox" :value="perm.id" x-model="selectedPermissionIds"
                                           class="mt-0.5 rounded border-gray-300 text-indigo-600">
                                    <span>
                                        <span class="font-medium text-gray-800 dark:text-gray-200" x-text="perm.label"></span>
                                        <span class="block text-xs text-gray-500" x-text="perm.name"></span>
                                    </span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div x-show="showCreate" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-semibold mb-4">New role</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">Slug (name)</label>
                        <input type="text" x-model="newRole.name" placeholder="custom_role" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Display name</label>
                        <input type="text" x-model="newRole.display_name" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea x-model="newRole.description" rows="2" class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" @click="showCreate = false" class="text-sm text-gray-600">Cancel</button>
                    <button type="button" @click="createRole()" class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg">Create</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function apiHeaders() {
            const t = document.querySelector('meta[name="api-token"]')?.content;
            return {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                ...(t ? {'Authorization': 'Bearer ' + t} : {}),
            };
        }

        function rolesMatrix() {
            const systemRoles = @json(\App\Models\Role::systemRoleNames());
            return {
                roles: [],
                permissionGroups: [],
                selectedRole: null,
                selectedPermissionIds: [],
                saving: false,
                message: '',
                messageOk: true,
                showCreate: false,
                newRole: {name: '', display_name: '', description: ''},
                async init() {
                    await Promise.all([this.loadRoles(), this.loadPermissions()]);
                    if (this.roles.length) this.selectRole(this.roles[0]);
                },
                async loadRoles() {
                    const res = await fetch('/api/admin/roles', {headers: apiHeaders()});
                    if (res.ok) {
                        const json = await res.json();
                        this.roles = (json.data ?? []).map(r => ({
                            ...r,
                            is_system: systemRoles.includes(r.name),
                        }));
                    }
                },
                async loadPermissions() {
                    const res = await fetch('/api/admin/permissions', {headers: apiHeaders()});
                    if (res.ok) {
                        const json = await res.json();
                        this.permissionGroups = json.data ?? [];
                    }
                },
                async selectRole(role) {
                    this.selectedRole = role;
                    const res = await fetch(`/api/admin/roles/${role.id}/permissions`, {headers: apiHeaders()});
                    if (res.ok) {
                        const json = await res.json();
                        this.selectedPermissionIds = (json.data?.permission_ids ?? []).map(String);
                    }
                },
                async savePermissions() {
                    if (!this.selectedRole) return;
                    this.saving = true;
                    const res = await fetch(`/api/admin/roles/${this.selectedRole.id}/permissions`, {
                        method: 'PUT',
                        headers: apiHeaders(),
                        body: JSON.stringify({
                            permissions: this.selectedPermissionIds.map(id => parseInt(id, 10)),
                        }),
                    });
                    const json = await res.json();
                    this.saving = false;
                    this.messageOk = res.ok;
                    this.message = json.message || (res.ok ? 'Saved.' : 'Save failed.');
                },
                async createRole() {
                    const res = await fetch('/api/admin/roles', {
                        method: 'POST',
                        headers: apiHeaders(),
                        body: JSON.stringify(this.newRole),
                    });
                    const json = await res.json();
                    if (res.ok) {
                        this.showCreate = false;
                        this.newRole = {name: '', display_name: '', description: ''};
                        await this.loadRoles();
                        this.selectRole(json.data);
                    } else {
                        this.messageOk = false;
                        this.message = json.message || 'Create failed.';
                    }
                },
                async deleteRole() {
                    if (!this.selectedRole || !confirm('Delete this role?')) return;
                    const res = await fetch(`/api/admin/roles/${this.selectedRole.id}`, {
                        method: 'DELETE',
                        headers: apiHeaders(),
                    });
                    const json = await res.json();
                    this.messageOk = res.ok;
                    this.message = json.message || (res.ok ? 'Deleted.' : 'Delete failed.');
                    if (res.ok) {
                        await this.loadRoles();
                        this.selectedRole = this.roles[0] ?? null;
                        if (this.selectedRole) this.selectRole(this.selectedRole);
                    }
                },
            };
        }
    </script>
</x-sidebar-layout>

<x-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $pageTitle }}</h2>
    </x-slot>

    <div class="py-6" x-data="userRolesManager({
        fixedBranchId: @js($fixedBranchId),
        isSuperAdmin: @js($isSuperAdmin),
    })" x-init="init()">
        <div class="mb-4 flex flex-wrap gap-3 items-center">
            <input type="search" x-model="search" @input.debounce.400ms="loadUsers()"
                   placeholder="Search by name or email…"
                   class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm w-full max-w-md">
            <button type="button" @click="loadUsers()" class="text-sm text-indigo-600 hover:text-indigo-800">Refresh</button>
        </div>

        <p x-show="error" x-text="error" class="mb-4 text-sm text-red-600"></p>
        <p x-show="success" x-text="success" class="mb-4 text-sm text-green-600"></p>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">User</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Roles</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="user in users" :key="user.id">
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-gray-100" x-text="user.name"></p>
                                <p class="text-gray-500 text-xs" x-text="user.email"></p>
                            </td>
                            <td class="px-4 py-3">
                                <template x-for="role in user.roles" :key="role.id + '-' + (role.branch_id ?? 'g')">
                                    <span class="inline-flex items-center gap-1 mr-1 mb-1 px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-800 text-xs">
                                        <span x-text="role.display_name"></span>
                                        <span x-show="role.branch_id" class="text-indigo-500" x-text="'#' + role.branch_id"></span>
                                        <button type="button" @click="revokeRole(user, role)" class="text-indigo-400 hover:text-red-600">&times;</button>
                                    </span>
                                </template>
                                <span x-show="!user.roles?.length" class="text-gray-400 text-xs">No roles</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button type="button" @click="openAssign(user)"
                                        class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Assign role</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <p x-show="loading" class="p-4 text-center text-gray-500 text-sm">Loading…</p>
            <p x-show="!loading && users.length === 0" class="p-4 text-center text-gray-500 text-sm">No users found.</p>
        </div>

        <div x-show="assignOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md mx-4" @click.outside="assignOpen = false">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Assign role</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4" x-text="selectedUser?.name"></p>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                        <select x-model="assignRoleId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 text-sm">
                            <option value="">Select role…</option>
                            <template x-for="role in assignableRoles" :key="role.id">
                                <option :value="role.id" x-text="role.display_name"></option>
                            </template>
                        </select>
                    </div>
                    <div x-show="isSuperAdmin">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch (optional)</label>
                        <select x-model="assignBranchId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 text-sm">
                            <option value="">Global (no branch)</option>
                            <template x-for="branch in branches" :key="branch.id">
                                <option :value="branch.id" x-text="branch.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="assignOpen = false" class="px-4 py-2 text-sm text-gray-600">Cancel</button>
                    <button type="button" @click="submitAssign()" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg">Assign</button>
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

        function userRolesManager(config) {
            return {
                users: [],
                roles: [],
                branches: [],
                search: '',
                loading: false,
                error: '',
                success: '',
                assignOpen: false,
                selectedUser: null,
                assignRoleId: '',
                assignBranchId: config.fixedBranchId ?? '',
                fixedBranchId: config.fixedBranchId,
                isSuperAdmin: config.isSuperAdmin,
                async init() {
                    await Promise.all([this.loadRoles(), this.loadUsers()]);
                    if (this.isSuperAdmin) {
                        await this.loadBranches();
                    }
                },
                get assignableRoles() {
                    const blocked = this.isSuperAdmin ? [] : ['super_admin', 'branch_pastor'];
                    return this.roles.filter(r => !blocked.includes(r.name));
                },
                async loadRoles() {
                    const res = await fetch('/api/admin/roles', {headers: apiHeaders()});
                    if (res.ok) {
                        const json = await res.json();
                        this.roles = json.data ?? [];
                    }
                },
                async loadBranches() {
                    const res = await fetch('/api/branches', {headers: apiHeaders()});
                    if (res.ok) {
                        const json = await res.json();
                        this.branches = json.data ?? json ?? [];
                    }
                },
                async loadUsers() {
                    this.loading = true;
                    this.error = '';
                    const params = new URLSearchParams();
                    if (this.search) params.set('search', this.search);
                    const res = await fetch('/api/admin/users?' + params, {headers: apiHeaders()});
                    this.loading = false;
                    if (!res.ok) {
                        this.error = 'Could not load users.';
                        return;
                    }
                    const json = await res.json();
                    this.users = json.data?.data ?? json.data ?? [];
                },
                openAssign(user) {
                    this.selectedUser = user;
                    this.assignRoleId = '';
                    this.assignBranchId = this.fixedBranchId ?? '';
                    this.assignOpen = true;
                },
                async submitAssign() {
                    if (!this.selectedUser || !this.assignRoleId) return;
                    const body = {
                        role_id: parseInt(this.assignRoleId, 10),
                        branch_id: this.assignBranchId ? parseInt(this.assignBranchId, 10) : null,
                    };
                    const res = await fetch(`/api/admin/users/${this.selectedUser.id}/roles`, {
                        method: 'POST',
                        headers: apiHeaders(),
                        body: JSON.stringify(body),
                    });
                    const json = await res.json();
                    if (!res.ok) {
                        this.error = json.message || 'Assign failed.';
                        return;
                    }
                    this.success = json.message || 'Role assigned.';
                    this.assignOpen = false;
                    await this.loadUsers();
                },
                async revokeRole(user, role) {
                    if (!confirm(`Remove ${role.display_name} from ${user.name}?`)) return;
                    const body = {role_id: role.id, branch_id: role.branch_id};
                    const res = await fetch(`/api/admin/users/${user.id}/roles`, {
                        method: 'DELETE',
                        headers: apiHeaders(),
                        body: JSON.stringify(body),
                    });
                    const json = await res.json();
                    if (!res.ok) {
                        this.error = json.message || 'Revoke failed.';
                        return;
                    }
                    this.success = json.message || 'Role removed.';
                    await this.loadUsers();
                },
            };
        }
    </script>
</x-sidebar-layout>

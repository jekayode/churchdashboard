<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>LifeGroups • LifePointe</title>
        @vite(['resources/css/app.css','resources/js/app.js'])
    </head>
    <body class="bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="lifegroupsPage()" x-init="init()">
            <div class="mb-6 flex flex-col md:flex-row md:items-end gap-4">
                <div class="md:w-1/3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expression (Branch)</label>
                    <select x-model="filters.branch_id" @change="load()" class="w-full rounded-lg border-gray-300">
                        <option value="">All Expressions</option>
                        <template x-for="b in branches" :key="b.id">
                            <option :value="b.id" x-text="b.name"></option>
                        </template>
                    </select>
                </div>
                <div class="md:flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search LifeGroups</label>
                    <input x-model.debounce.400ms="filters.q" @input="load()" type="text" placeholder="Search by name or location" class="w-full rounded-lg border-gray-300"/>
                </div>
            </div>

            <div x-show="loading" class="text-center py-8">Loading LifeGroups...</div>
            <div x-show="!loading && groups.length === 0" class="text-center py-8 text-gray-500">No LifeGroups match your filters.</div>

            <div x-show="!loading && groups.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <template x-for="g in groups" :key="g.id">
                    <div class="border rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900" x-text="g.name"></h3>
                        <p class="text-sm text-gray-600" x-text="g.branch?.name"></p>
                        <p class="text-sm text-gray-600" x-text="g.location"></p>
                        <p class="text-xs text-gray-500 mt-1" x-text="g.meeting_day + ' ' + g.meeting_time"></p>
                    </div>
                </template>
            </div>
        </div>

        <script>
            function lifegroupsPage() {
                return {
                    branches: [],
                    groups: [],
                    loading: false,
                    filters: { branch_id: '', q: '' },
                    async init() {
                        this.loading = true;
                        await this.loadBranches();
                        await this.load();
                        this.loading = false;
                    },
                    async loadBranches() {
                        const res = await fetch('/api/welcome/branches');
                        this.branches = await res.json();
                    },
                    async load() {
                        const params = new URLSearchParams(this.filters).toString();
                        const res = await fetch(`/api/welcome/small-groups?${params}`);
                        this.groups = await res.json();
                    }
                }
            }
        </script>
    </body>
</html>



<x-sidebar-layout title="Email Campaigns">
    <div class="space-y-6" x-data="emailCampaigns()">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Email Campaigns</h1>
                    <p class="text-gray-600 mt-1">Create and manage automated email sequences.</p>
                </div>
                <button @click="openCreateModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                    Create Campaign
                </button>
            </div>
        </div>

        <!-- Branch Selection (for Super Admin) -->
        @if($isSuperAdmin ?? false)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
            <select x-model="selectedBranch" @change="loadCampaigns()" class="w-full border-gray-300 rounded-lg">
                <option value="">Select a branch...</option>
                <template x-for="branch in branches" :key="branch.id">
                    <option :value="branch.id" x-text="branch.name"></option>
                </template>
            </select>
        </div>
        @endif

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Trigger Event</label>
                    <select x-model="filters.trigger_event" @change="loadCampaigns()" class="w-full border-gray-300 rounded-lg">
                        <option value="">All Triggers</option>
                        <option value="guest-registration">Guest Registration</option>
                        <option value="member-created">Member Created</option>
                        <option value="custom">Manual Trigger</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select x-model="filters.active" @change="loadCampaigns()" class="w-full border-gray-300 rounded-lg">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" x-model="filters.search" @input.debounce.300ms="loadCampaigns()" 
                           placeholder="Search campaigns..." 
                           class="w-full border-gray-300 rounded-lg">
                </div>
            </div>
        </div>

        <!-- Campaigns List -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Campaigns</h2>
            </div>
            
            <!-- Loading State -->
            <div x-show="loading" class="p-8 text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-gray-600">Loading campaigns...</p>
            </div>

            <!-- Campaigns Table -->
            <div x-show="!loading && campaigns.length > 0" class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trigger</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Steps</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrollments</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="campaign in campaigns" :key="campaign.id">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900" x-text="campaign.name"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800" 
                                          x-text="formatTriggerEvent(campaign.trigger_event)"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="campaign.steps_count || 0"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        Total: <span x-text="campaign.enrollments_count || 0"></span><br>
                                        Active: <span x-text="campaign.active_enrollments_count || 0"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="campaign.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                          class="px-2 py-1 text-xs font-medium rounded-full" 
                                          x-text="campaign.is_active ? 'Active' : 'Inactive'"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex space-x-2">
                                        <button @click="viewCampaign(campaign)" 
                                                class="text-blue-600 hover:text-blue-800">View</button>
                                        <button @click="editCampaign(campaign)" 
                                                class="text-green-600 hover:text-green-800">Edit</button>
                                        <button @click="cloneCampaign(campaign)" 
                                                class="text-purple-600 hover:text-purple-800">Clone</button>
                                        <button @click="deleteCampaign(campaign)" 
                                                class="text-red-600 hover:text-red-800">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div x-show="!loading && campaigns.length === 0" class="p-8 text-center">
                <p class="text-gray-500">No campaigns found. Create your first campaign to get started!</p>
            </div>

            <!-- Pagination -->
            <div x-show="pagination.total > pagination.per_page" class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing <span x-text="((pagination.current_page - 1) * pagination.per_page) + 1"></span>
                        to <span x-text="Math.min(pagination.current_page * pagination.per_page, pagination.total)"></span>
                        of <span x-text="pagination.total"></span> results
                    </div>
                    <div class="flex space-x-2">
                        <button @click="changePage(pagination.current_page - 1)" 
                                :disabled="pagination.current_page <= 1"
                                class="px-3 py-1 text-sm border rounded disabled:opacity-50">Previous</button>
                        <button @click="changePage(pagination.current_page + 1)" 
                                :disabled="pagination.current_page >= pagination.last_page"
                                class="px-3 py-1 text-sm border rounded disabled:opacity-50">Next</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div x-show="showModal" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50" @click="closeModal()"></div>
                <div class="relative bg-white rounded-lg max-w-4xl w-full p-6 max-h-screen overflow-y-auto">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold" x-text="editingCampaign ? 'Edit Campaign' : 'Create Campaign'"></h3>
                        <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="saveCampaign()">
                        <div class="space-y-6">
                            <!-- Basic Information -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                                    <input type="text" x-model="campaignForm.name" required
                                           class="w-full border-gray-300 rounded-lg">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Trigger Event *</label>
                                    <select x-model="campaignForm.trigger_event" required class="w-full border-gray-300 rounded-lg">
                                        <option value="guest-registration">Guest Registration</option>
                                        <option value="member-created">Member Created</option>
                                        <option value="custom">Manual Trigger</option>
                                    </select>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" x-model="campaignForm.is_active" 
                                           id="campaign_active" class="rounded border-gray-300">
                                    <label for="campaign_active" class="ml-2 text-sm text-gray-700">Active</label>
                                </div>
                            </div>

                            <!-- Campaign Steps -->
                            <div>
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-md font-medium text-gray-900">Campaign Steps</h4>
                                    <button type="button" @click="addStep()" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                        Add Step
                                    </button>
                                </div>

                                <div class="space-y-4">
                                    <template x-for="(step, index) in campaignForm.steps" :key="index">
                                        <div class="border border-gray-200 rounded-lg p-4">
                                            <div class="flex justify-between items-center mb-3">
                                                <h5 class="font-medium text-gray-900">Step <span x-text="index + 1"></span></h5>
                                                <button type="button" @click="removeStep(index)" 
                                                        class="text-red-600 hover:text-red-800 text-sm">Remove</button>
                                            </div>
                                            
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Step Order</label>
                                                    <input type="number" x-model.number="step.step_order" min="1" required
                                                           class="w-full border-gray-300 rounded-lg">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Delay (Days)</label>
                                                    <input type="number" x-model.number="step.delay_days" min="0" required
                                                           class="w-full border-gray-300 rounded-lg">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Template</label>
                                                    <select x-model="step.template_id" required class="w-full border-gray-300 rounded-lg">
                                                        <option value="">Select template...</option>
                                                        <template x-for="template in emailTemplates" :key="template.id">
                                                            <option :value="template.id" x-text="template.name"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <div x-show="campaignForm.steps.length === 0" class="text-center p-6 text-gray-500">
                                        No steps added yet. Click "Add Step" to create your first step.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" @click="closeModal()" 
                                    class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                                Cancel
                            </button>
                            <button type="submit" :disabled="saving"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                <span x-show="!saving" x-text="editingCampaign ? 'Update' : 'Create'"></span>
                                <span x-show="saving">Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Campaign Modal -->
        <div x-show="showViewModal" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50" @click="showViewModal = false"></div>
                <div class="relative bg-white rounded-lg max-w-4xl w-full p-6 max-h-screen overflow-y-auto">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Campaign Details</h3>
                        <button @click="showViewModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div x-show="viewingCampaign">
                        <!-- Campaign Info -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="font-medium text-gray-900" x-text="viewingCampaign?.name"></h4>
                                    <p class="text-sm text-gray-600">
                                        Trigger: <span x-text="formatTriggerEvent(viewingCampaign?.trigger_event)"></span>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span :class="viewingCampaign?.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                          class="px-2 py-1 text-xs font-medium rounded-full" 
                                          x-text="viewingCampaign?.is_active ? 'Active' : 'Inactive'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics -->
                        <div x-show="campaignStatistics" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-blue-50 rounded-lg p-4">
                                <div class="text-2xl font-bold text-blue-600" x-text="campaignStatistics?.total_enrollments || 0"></div>
                                <div class="text-sm text-blue-800">Total Enrollments</div>
                            </div>
                            <div class="bg-green-50 rounded-lg p-4">
                                <div class="text-2xl font-bold text-green-600" x-text="campaignStatistics?.active_enrollments || 0"></div>
                                <div class="text-sm text-green-800">Active</div>
                            </div>
                            <div class="bg-purple-50 rounded-lg p-4">
                                <div class="text-2xl font-bold text-purple-600" x-text="campaignStatistics?.completed_enrollments || 0"></div>
                                <div class="text-sm text-purple-800">Completed</div>
                            </div>
                            <div class="bg-orange-50 rounded-lg p-4">
                                <div class="text-2xl font-bold text-orange-600" x-text="(campaignStatistics?.completion_rate || 0) + '%'"></div>
                                <div class="text-sm text-orange-800">Completion Rate</div>
                            </div>
                        </div>

                        <!-- Campaign Steps -->
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Campaign Steps</h4>
                            <div class="space-y-3">
                                <template x-for="step in viewingCampaign?.steps" :key="step.id">
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h5 class="font-medium text-gray-900">Step <span x-text="step.step_order"></span></h5>
                                                <p class="text-sm text-gray-600">
                                                    Template: <span x-text="step.template?.name"></span><br>
                                                    Delay: <span x-text="step.delay_days"></span> days
                                                </p>
                                            </div>
                                            <button @click="previewStep(step.step_order)" 
                                                    class="text-blue-600 hover:text-blue-800 text-sm">Preview</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function emailCampaigns() {
            return {
                campaigns: [],
                branches: [],
                emailTemplates: [],
                selectedBranch: @json($isSuperAdmin ? null : auth()->user()->getActiveBranchId()),
                loading: false,
                saving: false,
                showModal: false,
                showViewModal: false,
                editingCampaign: null,
                viewingCampaign: null,
                campaignStatistics: null,
                campaignForm: {
                    name: '',
                    trigger_event: 'guest-registration',
                    is_active: false,
                    steps: []
                },
                filters: {
                    trigger_event: '',
                    active: '',
                    search: ''
                },
                pagination: {
                    current_page: 1,
                    last_page: 1,
                    per_page: 15,
                    total: 0
                },

                async init() {
                    @if($isSuperAdmin ?? false)
                        await this.loadBranches();
                    @endif
                    if (this.selectedBranch) {
                        await this.loadCampaigns();
                        await this.loadEmailTemplates();
                    }
                },

                async loadBranches() {
                    try {
                        const response = await fetch('/api/branches?per_page=100');
                        const data = await response.json();
                        this.branches = data.data || [];
                    } catch (error) {
                        console.error('Failed to load branches:', error);
                    }
                },

                async loadCampaigns(page = 1) {
                    if (!this.selectedBranch) return;

                    this.loading = true;
                    try {
                        const params = new URLSearchParams({
                            branch_id: this.selectedBranch,
                            page: page,
                            per_page: this.pagination.per_page,
                            ...Object.fromEntries(Object.entries(this.filters).filter(([_, v]) => v))
                        });

                        const response = await fetch(`/api/communication/campaigns?${params}`);
                        const data = await response.json();
                        
                        this.campaigns = data.campaigns || [];
                        this.pagination = data.pagination || this.pagination;
                    } catch (error) {
                        console.error('Failed to load campaigns:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async loadEmailTemplates() {
                    if (!this.selectedBranch) return;

                    try {
                        const response = await fetch(`/api/communication/templates?branch_id=${this.selectedBranch}&type=email&per_page=100`);
                        const data = await response.json();
                        this.emailTemplates = data.templates || [];
                    } catch (error) {
                        console.error('Failed to load templates:', error);
                    }
                },

                formatTriggerEvent(trigger) {
                    return {
                        'guest-registration': 'Guest Registration',
                        'member-created': 'Member Created',
                        'custom': 'Manual Trigger'
                    }[trigger] || trigger;
                },

                openCreateModal() {
                    this.editingCampaign = null;
                    this.campaignForm = {
                        name: '',
                        trigger_event: 'guest-registration',
                        is_active: false,
                        steps: []
                    };
                    this.showModal = true;
                },

                editCampaign(campaign) {
                    this.editingCampaign = campaign;
                    this.campaignForm = {
                        name: campaign.name,
                        trigger_event: campaign.trigger_event,
                        is_active: campaign.is_active,
                        steps: campaign.steps?.map(step => ({
                            step_order: step.step_order,
                            delay_days: step.delay_days,
                            template_id: step.template_id
                        })) || []
                    };
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                    this.editingCampaign = null;
                },

                addStep() {
                    const nextOrder = this.campaignForm.steps.length + 1;
                    this.campaignForm.steps.push({
                        step_order: nextOrder,
                        delay_days: 0,
                        template_id: ''
                    });
                },

                removeStep(index) {
                    this.campaignForm.steps.splice(index, 1);
                    // Reorder steps
                    this.campaignForm.steps.forEach((step, i) => {
                        step.step_order = i + 1;
                    });
                },

                async saveCampaign() {
                    if (!this.selectedBranch) {
                        alert('Please select a branch');
                        return;
                    }

                    this.saving = true;
                    try {
                        const url = this.editingCampaign 
                            ? `/api/communication/campaigns/${this.editingCampaign.id}`
                            : '/api/communication/campaigns';
                        
                        const method = this.editingCampaign ? 'PUT' : 'POST';

                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                branch_id: this.selectedBranch,
                                ...this.campaignForm
                            })
                        });

                        if (response.ok) {
                            this.closeModal();
                            await this.loadCampaigns();
                            alert(this.editingCampaign ? 'Campaign updated successfully!' : 'Campaign created successfully!');
                        } else {
                            const data = await response.json();
                            alert('Error: ' + (data.message || 'Failed to save campaign'));
                        }
                    } catch (error) {
                        alert('Failed to save campaign: ' + error.message);
                    } finally {
                        this.saving = false;
                    }
                },

                async viewCampaign(campaign) {
                    try {
                        const response = await fetch(`/api/communication/campaigns/${campaign.id}`);
                        const data = await response.json();
                        
                        this.viewingCampaign = data.campaign;
                        this.campaignStatistics = data.statistics;
                        this.showViewModal = true;
                    } catch (error) {
                        alert('Failed to load campaign details: ' + error.message);
                    }
                },

                async cloneCampaign(campaign) {
                    const name = prompt('Enter name for cloned campaign:', campaign.name + ' (Copy)');
                    if (!name) return;

                    try {
                        const response = await fetch(`/api/communication/campaigns/${campaign.id}/clone`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ name: name })
                        });

                        if (response.ok) {
                            await this.loadCampaigns();
                            alert('Campaign cloned successfully!');
                        } else {
                            const data = await response.json();
                            alert('Error: ' + (data.message || 'Failed to clone campaign'));
                        }
                    } catch (error) {
                        alert('Failed to clone campaign: ' + error.message);
                    }
                },

                async deleteCampaign(campaign) {
                    if (!confirm(`Are you sure you want to delete "${campaign.name}"?`)) return;

                    try {
                        const response = await fetch(`/api/communication/campaigns/${campaign.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        if (response.ok) {
                            await this.loadCampaigns();
                            alert('Campaign deleted successfully!');
                        } else {
                            const data = await response.json();
                            alert('Error: ' + (data.error || 'Failed to delete campaign'));
                        }
                    } catch (error) {
                        alert('Failed to delete campaign: ' + error.message);
                    }
                },

                async previewStep(stepOrder) {
                    try {
                        const response = await fetch(`/api/communication/campaigns/${this.viewingCampaign.id}/preview-step`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ step_order: stepOrder })
                        });

                        const data = await response.json();
                        
                        if (response.ok) {
                            alert(`Step ${stepOrder} Preview:\n\nSubject: ${data.preview.subject}\n\nContent:\n${data.preview.content}`);
                        } else {
                            alert('Error: ' + (data.error || 'Failed to preview step'));
                        }
                    } catch (error) {
                        alert('Failed to preview step: ' + error.message);
                    }
                },

                changePage(page) {
                    if (page >= 1 && page <= this.pagination.last_page) {
                        this.loadCampaigns(page);
                    }
                }
            }
        }
    </script>
</x-sidebar-layout>

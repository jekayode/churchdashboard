<x-sidebar-layout title="Message Templates">
    <div class="space-y-6" x-data="messageTemplates">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Message Templates</h1>
                    <p class="text-gray-600 mt-1">Create and manage email and SMS templates.</p>
                </div>
                <button @click="openCreateModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                    Create Template
                </button>
            </div>
        </div>

        <!-- Branch Selection (for Super Admin) -->
        @if($isSuperAdmin ?? false)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
            <select x-model="selectedBranch" @change="loadTemplates()" class="w-full border-gray-300 rounded-lg">
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                    <select x-model="filters.type" @change="loadTemplates()" class="w-full border-gray-300 rounded-lg">
                        <option value="">All Types</option>
                        <option value="email">Email</option>
                        <option value="sms">SMS</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select x-model="filters.active" @change="loadTemplates()" class="w-full border-gray-300 rounded-lg">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" x-model="filters.search" @input.debounce.300ms="loadTemplates()" 
                           placeholder="Search templates..." 
                           class="w-full border-gray-300 rounded-lg">
                </div>
                <div class="flex items-end">
                    <button @click="showVariablesModal = true" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        View Variables
                    </button>
                </div>
            </div>
        </div>

        <!-- Templates List -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Templates</h2>
            </div>
            
            <!-- Loading State -->
            <div x-show="loading" class="p-8 text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-gray-600">Loading templates...</p>
            </div>

            <!-- Templates Table -->
            <div x-show="!loading && templates.length > 0" class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="template in templates" :key="template.id">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900" x-text="template.name"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="template.type === 'email' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                                          class="px-2 py-1 text-xs font-medium rounded-full" x-text="template.type.toUpperCase()"></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-gray-900 truncate max-w-xs" x-text="template.subject || 'N/A'"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="template.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                          class="px-2 py-1 text-xs font-medium rounded-full" 
                                          x-text="template.is_active ? 'Active' : 'Inactive'"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" 
                                    x-text="new Date(template.updated_at).toLocaleDateString()"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex space-x-2">
                                        <button @click="previewTemplate(template)" 
                                                class="text-blue-600 hover:text-blue-800">Preview</button>
                                        <button @click="editTemplate(template)" 
                                                class="text-green-600 hover:text-green-800">Edit</button>
                                        <button @click="cloneTemplate(template)" 
                                                class="text-purple-600 hover:text-purple-800">Clone</button>
                                        <button @click="deleteTemplate(template)" 
                                                class="text-red-600 hover:text-red-800">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div x-show="!loading && templates.length === 0" class="p-8 text-center">
                <p class="text-gray-500">No templates found. Create your first template to get started!</p>
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
                <div class="relative bg-white rounded-lg max-w-2xl w-full p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold" x-text="editingTemplate ? 'Edit Template' : 'Create Template'"></h3>
                        <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="saveTemplate()">
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                                    <input type="text" x-model="templateForm.name" required
                                           class="w-full border-gray-300 rounded-lg">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Type *</label>
                                    <select x-model="templateForm.type" required class="w-full border-gray-300 rounded-lg">
                                        <option value="email">Email</option>
                                        <option value="sms">SMS</option>
                                    </select>
                                </div>
                            </div>

                            <div x-show="templateForm.type === 'email'">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                                <input type="text" x-model="templateForm.subject" 
                                       :required="templateForm.type === 'email'"
                                       class="w-full border-gray-300 rounded-lg">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Content *</label>
                                <textarea x-model="templateForm.content" required rows="6"
                                          class="w-full border-gray-300 rounded-lg"
                                          placeholder="Use variables like {member_name}, {branch_name}, etc."></textarea>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" x-model="templateForm.is_active" 
                                       id="is_active" class="rounded border-gray-300">
                                <label for="is_active" class="ml-2 text-sm text-gray-700">Active</label>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" @click="closeModal()" 
                                    class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                                Cancel
                            </button>
                            <button type="submit" :disabled="saving"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                <span x-show="!saving" x-text="editingTemplate ? 'Update' : 'Create'"></span>
                                <span x-show="saving">Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
        <div x-show="showPreviewModal" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50" @click="showPreviewModal = false"></div>
                <div class="relative bg-white rounded-lg max-w-4xl w-full p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Template Preview</h3>
                        <button @click="showPreviewModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div x-show="preview">
                        <div x-show="preview && preview.subject" class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Subject:</label>
                            <div class="p-3 bg-gray-50 rounded border" x-text="preview?.subject || ''"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Content:</label>
                            <div class="p-4 bg-gray-50 rounded border min-h-32 whitespace-pre-wrap" x-text="preview?.content || ''"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Variables Modal -->
        <div x-show="showVariablesModal" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50" @click="showVariablesModal = false"></div>
                <div class="relative bg-white rounded-lg max-w-4xl w-full p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Available Variables</h3>
                        <button @click="showVariablesModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <template x-for="(categoryVars, category) in availableVariables" :key="category">
                            <div>
                                <h4 class="font-medium text-gray-900 mb-3 capitalize" x-text="category.replace('_', ' ')"></h4>
                                <div class="space-y-2">
                                    <template x-for="(description, variable) in categoryVars" :key="variable">
                                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                            <code class="text-sm text-blue-600" x-text="`{${variable}}`"></code>
                                            <span class="text-xs text-gray-600" x-text="description"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('messageTemplates', () => ({
                templates: [],
                branches: [],
                selectedBranch: @json($isSuperAdmin ? null : auth()->user()->getActiveBranchId()),
                loading: false,
                saving: false,
                showModal: false,
                showPreviewModal: false,
                showVariablesModal: false,
                editingTemplate: null,
                preview: {
                    subject: '',
                    content: '',
                    recipient_count: 0
                },
                availableVariables: {},
                templateForm: {
                    name: '',
                    type: 'email',
                    subject: '',
                    content: '',
                    is_active: true
                },
                filters: {
                    type: '',
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
                        await this.loadTemplates();
                    }
                    await this.loadAvailableVariables();
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

                async loadTemplates(page = 1) {
                    if (!this.selectedBranch) return;

                    this.loading = true;
                    try {
                        const params = new URLSearchParams({
                            branch_id: this.selectedBranch,
                            page: page,
                            per_page: this.pagination.per_page,
                            ...Object.fromEntries(Object.entries(this.filters).filter(([_, v]) => v))
                        });

                        const response = await fetch(`/api/communication/templates?${params}`);
                        const data = await response.json();
                        
                        this.templates = data.templates || [];
                        this.pagination = data.pagination || this.pagination;
                    } catch (error) {
                        console.error('Failed to load templates:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async loadAvailableVariables() {
                    try {
                        const response = await fetch('/api/communication/templates/variables');
                        const data = await response.json();
                        this.availableVariables = data.variables || {};
                    } catch (error) {
                        console.error('Failed to load variables:', error);
                    }
                },

                openCreateModal() {
                    this.editingTemplate = null;
                    this.templateForm = {
                        name: '',
                        type: 'email',
                        subject: '',
                        content: '',
                        is_active: true
                    };
                    this.showModal = true;
                },

                editTemplate(template) {
                    this.editingTemplate = template;
                    this.templateForm = {
                        name: template.name,
                        type: template.type,
                        subject: template.subject || '',
                        content: template.content,
                        is_active: template.is_active
                    };
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                    this.editingTemplate = null;
                },

                async saveTemplate() {
                    if (!this.selectedBranch) {
                        alert('Please select a branch');
                        return;
                    }

                    this.saving = true;
                    try {
                        const url = this.editingTemplate 
                            ? `/api/communication/templates/${this.editingTemplate.id}`
                            : '/api/communication/templates';
                        
                        const method = this.editingTemplate ? 'PUT' : 'POST';

                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                branch_id: this.selectedBranch,
                                ...this.templateForm
                            })
                        });

                        if (response.ok) {
                            this.closeModal();
                            await this.loadTemplates();
                            alert(this.editingTemplate ? 'Template updated successfully!' : 'Template created successfully!');
                        } else {
                            const data = await response.json();
                            alert('Error: ' + (data.message || 'Failed to save template'));
                        }
                    } catch (error) {
                        alert('Failed to save template: ' + error.message);
                    } finally {
                        this.saving = false;
                    }
                },

                async previewTemplate(template) {
                    try {
                        const response = await fetch(`/api/communication/templates/${template.id}/preview`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();
                        
                        if (response.ok) {
                            this.preview = data.preview;
                            this.showPreviewModal = true;
                        } else {
                            alert('Error: ' + (data.error || 'Failed to preview template'));
                        }
                    } catch (error) {
                        alert('Failed to preview template: ' + error.message);
                    }
                },

                async cloneTemplate(template) {
                    const name = prompt('Enter name for cloned template:', template.name + ' (Copy)');
                    if (!name) return;

                    try {
                        const response = await fetch(`/api/communication/templates/${template.id}/clone`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ name: name })
                        });

                        if (response.ok) {
                            await this.loadTemplates();
                            alert('Template cloned successfully!');
                        } else {
                            const data = await response.json();
                            alert('Error: ' + (data.message || 'Failed to clone template'));
                        }
                    } catch (error) {
                        alert('Failed to clone template: ' + error.message);
                    }
                },

                async deleteTemplate(template) {
                    if (!confirm(`Are you sure you want to delete "${template.name}"?`)) return;

                    try {
                        const response = await fetch(`/api/communication/templates/${template.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        if (response.ok) {
                            await this.loadTemplates();
                            alert('Template deleted successfully!');
                        } else {
                            const data = await response.json();
                            alert('Error: ' + (data.error || 'Failed to delete template'));
                        }
                    } catch (error) {
                        alert('Failed to delete template: ' + error.message);
                    }
                },

                changePage(page) {
                    if (page >= 1 && page <= this.pagination.last_page) {
                        this.loadTemplates(page);
                    }
                }
            }))
        })
    </script>
</x-sidebar-layout>

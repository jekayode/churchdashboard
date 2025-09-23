<x-sidebar-layout title="Quick Send">
    <div class="space-y-6" x-data="quickSend()">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Quick Send</h1>
                    <p class="text-gray-600 mt-1">Send individual emails or SMS messages to members.</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="previewMessage()" 
                            :disabled="!messageForm.content || previewing"
                            :class="messageForm.content && !previewing ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'"
                            class="px-4 py-2 text-white rounded-lg transition-colors">
                        <span x-show="!previewing">Preview</span>
                        <span x-show="previewing">Previewing...</span>
                    </button>
                    <button @click="sendMessage()" 
                            :disabled="!canSend || sending"
                            :class="canSend && !sending ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed'"
                            class="px-4 py-2 text-white rounded-lg transition-colors">
                        <span x-show="!sending">Send Messages</span>
                        <span x-show="sending">Sending...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Branch Selection (for Super Admin) -->
        @if($isSuperAdmin ?? false)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
            <select x-model="selectedBranch" @change="loadTemplates(); loadRecipients('')" class="w-full border-gray-300 rounded-lg">
                <option value="">Select a branch...</option>
                <template x-for="branch in branches" :key="branch.id">
                    <option :value="branch.id" x-text="branch.name"></option>
                </template>
            </select>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Message Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Message Type & Template -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Message Settings</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Message Type</label>
                            <select x-model="messageForm.type" @change="loadTemplates()" class="w-full border-gray-300 rounded-lg">
                                <option value="email">Email</option>
                                <option value="sms">SMS</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Use Template (Optional)</label>
                            <select x-model="messageForm.template_id" @change="loadTemplate()" class="w-full border-gray-300 rounded-lg">
                                <option value="">Start from scratch</option>
                                <template x-for="template in templates" :key="template.id">
                                    <option :value="template.id" x-text="template.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Message Content -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Message Content</h2>
                    
                    <!-- Subject (for email) -->
                    <div x-show="messageForm.type === 'email'" class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                        <input type="text" x-model="messageForm.subject" 
                               placeholder="Enter email subject..." 
                               class="w-full border-gray-300 rounded-lg">
                    </div>

                    <!-- Content -->
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-medium text-gray-700">Content *</label>
                            <button @click="showVariablesHelp = !showVariablesHelp" 
                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                <span x-text="showVariablesHelp ? 'Hide Variables' : 'Show Variables'"></span>
                            </button>
                        </div>
                        <textarea x-model="messageForm.content" rows="8" 
                                  placeholder="Enter your message content here. Use {variable_name} for dynamic content."
                                  class="w-full border-gray-300 rounded-lg"></textarea>
                    </div>

                    <!-- Variables Help -->
                    <div x-show="showVariablesHelp" x-transition class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-medium text-blue-900 mb-2">Available Variables</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                            <div><code>{recipient_name}</code> - Recipient's name</div>
                            <div><code>{recipient_email}</code> - Recipient's email</div>
                            <div><code>{recipient_phone}</code> - Recipient's phone</div>
                            <div><code>{branch_name}</code> - Branch name</div>
                            <div><code>{current_date}</code> - Current date</div>
                            <div><code>{app_name}</code> - Application name</div>
                        </div>
                    </div>

                    <!-- Custom Variables -->
                    <div class="mt-4">
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-medium text-gray-700">Custom Variables</label>
                            <button @click="addCustomVariable()" class="text-green-600 hover:text-green-800 text-sm">
                                Add Variable
                            </button>
                        </div>
                        <div class="space-y-2">
                            <template x-for="(variable, index) in customVariables" :key="index">
                                <div class="flex space-x-2">
                                    <input type="text" x-model="variable.key" placeholder="variable_name" 
                                           class="flex-1 border-gray-300 rounded-lg text-sm">
                                    <input type="text" x-model="variable.value" placeholder="Variable value" 
                                           class="flex-1 border-gray-300 rounded-lg text-sm">
                                    <button @click="removeCustomVariable(index)" 
                                            class="text-red-600 hover:text-red-800 px-2">×</button>
                                </div>
                            </template>
                            <div x-show="customVariables.length === 0" class="text-sm text-gray-500">
                                No custom variables. Click "Add Variable" to create one.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recipients Panel -->
            <div class="space-y-6">
                <!-- Recipient Search -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Recipients</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search Recipients</label>
                            <input type="text" x-model="recipientSearch" 
                                   @input.debounce.300ms="loadRecipients(recipientSearch)"
                                   placeholder="Search by name, email, or phone..." 
                                   class="w-full border-gray-300 rounded-lg">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Recipient Type</label>
                            <select x-model="recipientType" @change="loadRecipients(recipientSearch)" 
                                    class="w-full border-gray-300 rounded-lg">
                                <option value="both">Users & Members</option>
                                <option value="user">Users Only</option>
                                <option value="member">Members Only</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Available Recipients -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-medium text-gray-900 mb-3">Available Recipients</h3>
                    
                    <div x-show="loadingRecipients" class="text-center py-4">
                        <div class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                        <p class="text-sm text-gray-600 mt-1">Loading...</p>
                    </div>

                    <div x-show="!loadingRecipients" class="space-y-2 max-h-64 overflow-y-auto">
                        <template x-for="recipient in availableRecipients" :key="recipient.id + recipient.type">
                            <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900 truncate" x-text="recipient.name"></div>
                                    <div class="text-xs text-gray-500 truncate">
                                        <span x-text="messageForm.type === 'email' ? recipient.email : recipient.phone"></span>
                                        <span :class="recipient.type === 'user' ? 'text-blue-600' : 'text-green-600'"
                                              class="ml-1" x-text="'(' + recipient.type + ')'"></span>
                                    </div>
                                </div>
                                <button @click="addRecipient(recipient)" 
                                        class="text-green-600 hover:text-green-800 text-sm ml-2">
                                    Add
                                </button>
                            </div>
                        </template>
                        <div x-show="availableRecipients.length === 0" class="text-center py-4 text-gray-500 text-sm">
                            No recipients found
                        </div>
                    </div>
                </div>

                <!-- Selected Recipients -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-medium text-gray-900 mb-3">
                        Selected Recipients (<span x-text="selectedRecipients.length"></span>)
                    </h3>
                    
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        <template x-for="(recipient, index) in selectedRecipients" :key="index">
                            <div class="flex items-center justify-between p-2 bg-blue-50 rounded">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900 truncate" x-text="recipient.name"></div>
                                    <div class="text-xs text-gray-500 truncate">
                                        <span x-text="messageForm.type === 'email' ? recipient.email : recipient.phone"></span>
                                        <span :class="recipient.type === 'user' ? 'text-blue-600' : 'text-green-600'"
                                              class="ml-1" x-text="'(' + recipient.type + ')'"></span>
                                    </div>
                                </div>
                                <button @click="removeRecipient(index)" 
                                        class="text-red-600 hover:text-red-800 text-sm ml-2">
                                    Remove
                                </button>
                            </div>
                        </template>
                        <div x-show="selectedRecipients.length === 0" class="text-center py-4 text-gray-500 text-sm">
                            No recipients selected
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
        <div x-show="showPreviewModal" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50" @click="showPreviewModal = false"></div>
                <div class="relative bg-white rounded-lg max-w-4xl w-full p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Message Preview</h3>
                        <button @click="showPreviewModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div x-show="preview">
                        <div x-show="preview && preview.subject" class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Subject:</label>
                            <div class="p-3 bg-gray-50 rounded border" x-text="preview.subject"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Content:</label>
                            <div class="p-4 bg-gray-50 rounded border min-h-32 whitespace-pre-wrap" x-text="preview.content"></div>
                        </div>
                        <div class="mt-4 text-sm text-gray-600">
                            <strong>Note:</strong> This preview uses sample data. Actual messages will use real recipient information.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Modal -->
        <div x-show="showResultsModal" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50" @click="showResultsModal = false"></div>
                <div class="relative bg-white rounded-lg max-w-4xl w-full p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Send Results</h3>
                        <button @click="showResultsModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div x-show="sendResults">
                        <!-- Summary -->
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="bg-blue-50 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-blue-600" x-text="sendResults.summary?.total || 0"></div>
                                <div class="text-sm text-blue-800">Total</div>
                            </div>
                            <div class="bg-green-50 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-green-600" x-text="sendResults.summary?.success || 0"></div>
                                <div class="text-sm text-green-800">Successful</div>
                            </div>
                            <div class="bg-red-50 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-red-600" x-text="sendResults.summary?.failed || 0"></div>
                                <div class="text-sm text-red-800">Failed</div>
                            </div>
                        </div>

                        <!-- Detailed Results -->
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Detailed Results</h4>
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                <template x-for="result in sendResults.results" :key="result.recipient">
                                    <div class="flex items-center justify-between p-3 border rounded">
                                        <div>
                                            <div class="font-medium" x-text="result.recipient"></div>
                                            <div x-show="result.contact" class="text-sm text-gray-600" x-text="result.contact"></div>
                                        </div>
                                        <div>
                                            <span :class="result.status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                  class="px-2 py-1 text-xs font-medium rounded-full" 
                                                  x-text="result.status"></span>
                                            <div x-show="result.error" class="text-xs text-red-600 mt-1" x-text="result.error"></div>
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
        function quickSend() {
            return {
                branches: [],
                templates: [],
                availableRecipients: [],
                selectedRecipients: [],
                selectedBranch: @json($isSuperAdmin ? null : auth()->user()->getActiveBranchId()),
                messageForm: {
                    type: 'email',
                    template_id: '',
                    subject: '',
                    content: '',
                },
                customVariables: [],
                recipientSearch: '',
                recipientType: 'both',
                loadingRecipients: false,
                sending: false,
                previewing: false,
                showVariablesHelp: false,
                showPreviewModal: false,
                showResultsModal: false,
                preview: null,
                sendResults: null,

                async init() {
                    @if($isSuperAdmin ?? false)
                        await this.loadBranches();
                    @endif
                    if (this.selectedBranch) {
                        await this.loadTemplates();
                        await this.loadRecipients('');
                    }
                },

                get canSend() {
                    return this.selectedBranch && 
                           this.messageForm.content && 
                           (this.messageForm.type === 'sms' || this.messageForm.subject) &&
                           this.selectedRecipients.length > 0;
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

                async loadTemplates() {
                    if (!this.selectedBranch) return;

                    try {
                        const response = await fetch(`/api/communication/templates?branch_id=${this.selectedBranch}&type=${this.messageForm.type}&per_page=100`);
                        const data = await response.json();
                        this.templates = data.templates || [];
                    } catch (error) {
                        console.error('Failed to load templates:', error);
                    }
                },

                async loadTemplate() {
                    if (!this.messageForm.template_id) return;

                    try {
                        const response = await fetch(`/api/communication/templates/${this.messageForm.template_id}`);
                        const data = await response.json();
                        
                        if (data.template) {
                            this.messageForm.subject = data.template.subject || '';
                            this.messageForm.content = data.template.content || '';
                        }
                    } catch (error) {
                        console.error('Failed to load template:', error);
                    }
                },

                async loadRecipients(search) {
                    if (!this.selectedBranch) return;

                    this.loadingRecipients = true;
                    try {
                        const params = new URLSearchParams({
                            branch_id: this.selectedBranch,
                            recipient_type: this.recipientType,
                            search: search
                        });

                        const response = await fetch(`/api/communication/quick-send/recipients?${params}`);
                        const data = await response.json();
                        
                        this.availableRecipients = (data.recipients || []).filter(recipient => {
                            // Filter out already selected recipients
                            return !this.selectedRecipients.some(selected => 
                                selected.id === recipient.id && selected.type === recipient.type
                            );
                        });
                    } catch (error) {
                        console.error('Failed to load recipients:', error);
                    } finally {
                        this.loadingRecipients = false;
                    }
                },

                addRecipient(recipient) {
                    // Check if recipient has the required contact method
                    const requiredContact = this.messageForm.type === 'email' ? recipient.email : recipient.phone;
                    if (!requiredContact) {
                        alert(`This recipient doesn't have a ${this.messageForm.type} address.`);
                        return;
                    }

                    this.selectedRecipients.push(recipient);
                    // Remove from available list
                    this.availableRecipients = this.availableRecipients.filter(r => 
                        !(r.id === recipient.id && r.type === recipient.type)
                    );
                },

                removeRecipient(index) {
                    const recipient = this.selectedRecipients[index];
                    this.selectedRecipients.splice(index, 1);
                    // Add back to available list if it matches current search
                    this.loadRecipients(this.recipientSearch);
                },

                addCustomVariable() {
                    this.customVariables.push({ key: '', value: '' });
                },

                removeCustomVariable(index) {
                    this.customVariables.splice(index, 1);
                },

                getCustomVariablesObject() {
                    const variables = {};
                    this.customVariables.forEach(variable => {
                        if (variable.key && variable.value) {
                            variables[variable.key] = variable.value;
                        }
                    });
                    return variables;
                },

                async previewMessage() {
                    if (!this.selectedBranch) {
                        alert('Please select a branch');
                        return;
                    }

                    this.previewing = true;
                    try {
                        const response = await fetch('/api/communication/quick-send/preview', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                branch_id: this.selectedBranch,
                                type: this.messageForm.type,
                                subject: this.messageForm.subject,
                                content: this.messageForm.content,
                                custom_variables: this.getCustomVariablesObject()
                            })
                        });

                        const data = await response.json();
                        
                        if (response.ok) {
                            this.preview = data.preview;
                            this.showPreviewModal = true;
                        } else {
                            alert('Error: ' + (data.error || 'Failed to preview message'));
                        }
                    } catch (error) {
                        alert('Failed to preview message: ' + error.message);
                    } finally {
                        this.previewing = false;
                    }
                },

                async sendMessage() {
                    if (!this.canSend) return;

                    if (!confirm(`Send ${this.messageForm.type} to ${this.selectedRecipients.length} recipient(s)?`)) {
                        return;
                    }

                    this.sending = true;
                    try {
                        const response = await fetch('/api/communication/quick-send/send', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                branch_id: this.selectedBranch,
                                type: this.messageForm.type,
                                recipients: this.selectedRecipients.map(r => ({ id: r.id, type: r.type })),
                                template_id: this.messageForm.template_id || null,
                                subject: this.messageForm.subject,
                                content: this.messageForm.content,
                                custom_variables: this.getCustomVariablesObject()
                            })
                        });

                        const data = await response.json();
                        
                        if (response.ok) {
                            this.sendResults = data;
                            this.showResultsModal = true;
                            
                            // Clear form if all messages were successful
                            if (data.summary.failed === 0) {
                                this.messageForm.subject = '';
                                this.messageForm.content = '';
                                this.messageForm.template_id = '';
                                this.customVariables = [];
                                this.selectedRecipients = [];
                                this.loadRecipients(this.recipientSearch);
                            }
                        } else {
                            alert('Error: ' + (data.error || 'Failed to send messages'));
                        }
                    } catch (error) {
                        alert('Failed to send messages: ' + error.message);
                    } finally {
                        this.sending = false;
                    }
                }
            }
        }
    </script>
</x-sidebar-layout>

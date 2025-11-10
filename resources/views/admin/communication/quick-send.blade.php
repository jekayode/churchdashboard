<x-sidebar-layout title="Quick Send">
    <div class="space-y-6" x-data="quickSend">
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
                            <select x-model="messageForm.type" @change="loadTemplates(); loadRecipients('')" class="w-full border-gray-300 rounded-lg">
                                <option value="email">Email</option>
                                <option value="sms">SMS</option>
                                <option value="whatsapp">WhatsApp</option>
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
                    
                    <div class="space-y-4">
                        <!-- Subject (for email) -->
                        <div x-show="messageForm.type === 'email'">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                            <input type="text" 
                                   x-model="messageForm.subject" 
                                   placeholder="Enter email subject..."
                                   class="w-full border-gray-300 rounded-lg">
                        </div>

                        <!-- Content -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-sm font-medium text-gray-700">Content *</label>
                                <button @click="showVariablesHelp = !showVariablesHelp" 
                                        class="text-sm text-blue-600 hover:text-blue-800">
                                    <span x-show="!showVariablesHelp">Show Variables</span>
                                    <span x-show="showVariablesHelp">Hide Variables</span>
                                </button>
                            </div>
                            
                            <textarea x-model="messageForm.content" 
                                      :placeholder="messageForm.type === 'email' ? 'Enter your email content here. Use {variable_name} for dynamic content.' : 'Enter your message content here. Use {variable_name} for dynamic content.'"
                                      rows="8"
                                      class="w-full border-gray-300 rounded-lg"></textarea>
                            
                            <!-- Variables Help -->
                            <div x-show="showVariablesHelp" class="mt-3 p-4 bg-gray-50 rounded-lg">
                                <h4 class="font-medium text-gray-900 mb-2">Available Variables:</h4>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                    <div><code class="bg-white px-2 py-1 rounded">{first_name}</code> - Recipient's first name</div>
                                    <div><code class="bg-white px-2 py-1 rounded">{user_name}</code> - Recipient's full name</div>
                                    <div><code class="bg-white px-2 py-1 rounded">{member_name}</code> - Member's full name</div>
                                    <div><code class="bg-white px-2 py-1 rounded">{branch_name}</code> - Church branch name</div>
                                    <div><code class="bg-white px-2 py-1 rounded">{current_date}</code> - Current date</div>
                                    <div><code class="bg-white px-2 py-1 rounded">{app_name}</code> - Application name</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recipients Panel -->
            <div class="space-y-6">
                <!-- Search Recipients -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Recipients</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search Recipients</label>
                            <input type="text" 
                                   x-model="searchQuery" 
                                   @input="loadRecipients($event.target.value)"
                                   placeholder="Search by name, email, or phone..."
                                   class="w-full border-gray-300 rounded-lg">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Recipient Type</label>
                            <select x-model="recipientType" @change="loadRecipients('')" class="w-full border-gray-300 rounded-lg">
                                <option value="all">Users & Members</option>
                                <option value="users">Users Only</option>
                                <option value="members">Members Only</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fetch Limit</label>
                            <select x-model.number="recipientLimit" @change="loadRecipients(searchQuery)" class="w-full border-gray-300 rounded-lg">
                                <option :value="100">100</option>
                                <option :value="500">500</option>
                                <option :value="1000">1000</option>
                                <option :value="2000">2000</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Available Recipients -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Available Recipients</h3>
                        <span class="text-sm text-gray-500" x-text="availableRecipients.length + ' found'"></span>
                    </div>
                    
                    <div class="max-h-96 overflow-y-auto space-y-2">
                        <template x-for="(recipient, index) in availableRecipients" :key="'recipient-' + recipient.id + '-' + recipient.type + '-' + index">
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-900 truncate" x-text="recipient.name"></div>
                                    <div class="text-sm text-gray-500 truncate" x-text="getRecipientContact(recipient)"></div>
                                    <div class="text-xs text-gray-400" x-text="recipient.type"></div>
                                </div>
                                <button @click="addRecipient(recipient)" 
                                        :disabled="selectedRecipients.some(r => r.id === recipient.id)"
                                        :class="selectedRecipients.some(r => r.id === recipient.id) ? 'bg-gray-300 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                                        class="ml-3 px-3 py-1 text-white text-sm rounded transition-colors">
                                    <span x-show="!selectedRecipients.some(r => r.id === recipient.id)">Add</span>
                                    <span x-show="selectedRecipients.some(r => r.id === recipient.id)">Added</span>
                                </button>
                            </div>
                        </template>
                        
                        <div x-show="availableRecipients.length === 0 && !loadingRecipients" class="text-center py-8 text-gray-500">
                            No recipients found
                        </div>
                        
                        <div x-show="loadingRecipients" class="text-center py-8 text-gray-500">
                            Loading recipients...
                        </div>
                    </div>
                </div>

                <!-- Selected Recipients -->
                <div class="bg-white rounded-lg shadow-sm p-6" x-show="selectedRecipients.length > 0">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Selected Recipients</h3>
                        <span class="text-sm text-gray-500" x-text="selectedRecipients.length + ' selected'"></span>
                    </div>
                    
                    <div class="max-h-64 overflow-y-auto space-y-2">
                        <template x-for="(recipient, index) in selectedRecipients" :key="'selected-' + recipient.id + '-' + recipient.type + '-' + index">
                            <div class="flex items-center justify-between p-2 bg-blue-50 border border-blue-200 rounded">
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-900 truncate" x-text="recipient.name"></div>
                                    <div class="text-sm text-gray-500 truncate" x-text="getRecipientContact(recipient)"></div>
                                </div>
                                <button @click="removeRecipient(recipient)" 
                                        class="ml-2 text-red-600 hover:text-red-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div x-show="showPreviewModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showPreviewModal = false"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Message Preview</h3>
                        <button @click="showPreviewModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <div x-show="messageForm && messageForm.subject">
                            <label class="block text-sm font-medium text-gray-700">Subject:</label>
                            <div class="mt-1 p-3 bg-gray-50 rounded-lg" x-text="(messageForm && messageForm.subject) || ''"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Content:</label>
                            <div class="mt-1 p-3 bg-gray-50 rounded-lg whitespace-pre-wrap" x-text="(messageForm && messageForm.content) || ''"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Recipients:</label>
                            <div class="mt-1 text-sm text-gray-600" x-text="(selectedRecipients && selectedRecipients.length ? selectedRecipients.length : 0) + ' recipients selected'"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Modal -->
    <div x-show="showResultsModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showResultsModal = false"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Send Results</h3>
                        <button @click="showResultsModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600" x-text="(sendResults && sendResults.total) || 0"></div>
                                <div class="text-sm text-blue-800">Total</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-green-600" x-text="(sendResults && sendResults.successful) || 0"></div>
                                <div class="text-sm text-green-800">Successful</div>
                            </div>
                            <div class="bg-red-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-red-600" x-text="(sendResults && sendResults.failed) || 0"></div>
                                <div class="text-sm text-red-800">Failed</div>
                            </div>
                        </div>
                        
                        <div x-show="(sendResults && sendResults.failed > 0)" class="mt-4">
                            <h4 class="font-medium text-gray-900 mb-2">Failed Recipients:</h4>
                            <div class="max-h-32 overflow-y-auto space-y-2">
                                <template x-for="failure in (sendResults && sendResults.failures ? sendResults.failures : [])" :key="failure.recipient">
                                    <div class="flex items-center justify-between p-2 bg-red-50 rounded">
                                        <div>
                                            <div class="font-medium" x-text="failure.recipient"></div>
                                            <div class="text-sm text-red-600" x-text="failure.error"></div>
                                        </div>
                                        <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">Failed</span>
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
        // Register the component before Alpine starts
        document.addEventListener('alpine:init', () => {
            console.log('Registering quickSend component...');
            Alpine.data('quickSend', () => ({
                branches: [],
                templates: [],
                availableRecipients: [],
                selectedRecipients: [],
                selectedBranch: @json($isSuperAdmin ? null : (auth()->user() ? auth()->user()->getActiveBranchId() : null)),
                messageForm: {
                    type: 'email',
                    template_id: '',
                    subject: '',
                    content: '',
                },
                searchQuery: '',
                recipientType: 'all',
                recipientLimit: 1000,
                loadingRecipients: false,
                sending: false,
                previewing: false,
                showVariablesHelp: false,
                showPreviewModal: false,
                showResultsModal: false,
                sendResults: {
                    total: 0,
                    successful: 0,
                    failed: 0,
                    failures: []
                },

                async init() {
                    console.log('QuickSend component initializing...');
                    try {
                        @if($isSuperAdmin ?? false)
                            await this.loadBranches();
                        @endif
                        if (this.selectedBranch) {
                            await this.loadTemplates();
                            await this.loadRecipients('');
                        }
                        console.log('QuickSend component initialized successfully');
                    } catch (error) {
                        console.error('Error initializing QuickSend component:', error);
                    }
                },

                get canSend() {
                    return this.messageForm.content && 
                           this.selectedRecipients.length > 0 &&
                           (this.messageForm.type === 'sms' || this.messageForm.type === 'whatsapp' || this.messageForm.subject);
                },

                getRecipientContact(recipient) {
                    if (this.messageForm.type === 'email') {
                        return recipient.email || 'No email';
                    } else {
                        return recipient.phone || 'No phone';
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

                async loadTemplates() {
                    if (!this.selectedBranch) return;

                    try {
                        const response = await fetch(`/api/communication/message-templates?branch_id=${this.selectedBranch}&type=${this.messageForm.type}`);
                        const data = await response.json();
                        this.templates = data.data || [];
                        console.log('Templates loaded:', this.templates);
                    } catch (error) {
                        console.error('Failed to load templates:', error);
                    }
                },

                async loadRecipients(query = '') {
                    if (!this.selectedBranch) return;

                    this.loadingRecipients = true;
                    try {
                                const response = await fetch('/api/communication/quick-send/recipients', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                                    body: JSON.stringify({
                                branch_id: this.selectedBranch,
                                query: query,
                                        type: this.recipientType,
                                        limit: this.recipientLimit
                            })
                        });
                        const data = await response.json();
                        console.log('Quick Send API Response:', data);
                        this.availableRecipients = data.data?.recipients || data.recipients || [];
                        console.log('Available Recipients:', this.availableRecipients);
                    } catch (error) {
                        console.error('Failed to load recipients:', error);
                    } finally {
                        this.loadingRecipients = false;
                    }
                },

                async loadTemplate() {
                    if (!this.messageForm.template_id) {
                        this.messageForm.subject = '';
                        this.messageForm.content = '';
                        return;
                    }

                    try {
                        const response = await fetch(`/api/communication/message-templates/${this.messageForm.template_id}`);
                        const data = await response.json();
                        if (data.template) {
                            this.messageForm.subject = data.template.subject || '';
                            this.messageForm.content = data.template.content || '';
                        }
                    } catch (error) {
                        console.error('Failed to load template:', error);
                    }
                },

                addRecipient(recipient) {
                    if (!this.selectedRecipients.some(r => r.id === recipient.id)) {
                        this.selectedRecipients.push(recipient);
                    }
                },

                removeRecipient(recipient) {
                    this.selectedRecipients = this.selectedRecipients.filter(r => r.id !== recipient.id);
                },

                async previewMessage() {
                    if (!this.messageForm.content) return;

                    this.previewing = true;
                    this.showPreviewModal = true;
                    this.previewing = false;
                },

                async sendMessage() {
                    if (!this.canSend) return;

                    if (!confirm(`Are you sure you want to send this message to ${this.selectedRecipients.length} recipients?`)) {
                        return;
                    }

                    this.sending = true;
                    try {
                        const response = await fetch('/api/communication/quick-send/send', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                        body: JSON.stringify({
                            branch_id: this.selectedBranch,
                            type: this.messageForm.type,
                            template_id: this.messageForm.template_id,
                            subject: this.messageForm.subject,
                            content: this.messageForm.content,
                            recipients: this.selectedRecipients
                        })
                        });

                        if (!response.ok) {
                            const errorData = await response.json().catch(() => ({ error: 'Unknown error occurred' }));
                            const errorMessage = errorData.errors 
                                ? Object.values(errorData.errors).flat().join('\n')
                                : errorData.error || `HTTP ${response.status}: ${response.statusText}`;
                            throw new Error(errorMessage);
                        }

                        const data = await response.json();
                        console.log('Send message response:', data);
                        
                        if (data.success === false) {
                            throw new Error(data.message || 'Failed to send messages');
                        }

                        this.sendResults = data.data?.summary || data.summary || {
                            total: data.data?.results?.length || 0,
                            successful: data.data?.results?.filter(r => r.status === 'success').length || 0,
                            failed: data.data?.results?.filter(r => r.status === 'failed').length || 0,
                            failures: data.data?.results?.filter(r => r.status === 'failed') || []
                        };
                        this.showResultsModal = true;
                        
                        // Reset form only if all messages were successful
                        if (this.sendResults.failed === 0) {
                            this.messageForm.subject = '';
                            this.messageForm.content = '';
                            this.messageForm.template_id = '';
                            this.selectedRecipients = [];
                        }
                    } catch (error) {
                        console.error('Failed to send message:', error);
                        alert(`Failed to send message: ${error.message || error}`);
                    } finally {
                        this.sending = false;
                    }
                }
            }));
        });
    </script>
</x-sidebar-layout>

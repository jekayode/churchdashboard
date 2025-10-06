<x-sidebar-layout title="Mass Communication">
    <div class="space-y-6" x-data="massCommunication()">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Mass Communication</h1>
                    <p class="text-gray-600 mt-1">Send messages to filtered groups of members.</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="previewMessage()" 
                            :disabled="!canPreview || previewing"
                            :class="canPreview && !previewing ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'"
                            class="px-4 py-2 text-white rounded-lg transition-colors">
                        <span x-show="!previewing">Preview</span>
                        <span x-show="previewing">Previewing...</span>
                    </button>
                    <button @click="sendMessage()" 
                            :disabled="!canSend || sending"
                            :class="canSend && !sending ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed'"
                            class="px-4 py-2 text-white rounded-lg transition-colors">
                        <span x-show="!sending">Send to <span x-text="recipients.length"></span> Recipients</span>
                        <span x-show="sending">Sending...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Branch Selection (for Super Admin) -->
        @if($isSuperAdmin ?? false)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
            <select x-model="selectedBranch" @change="loadFilters(); loadTemplates()" class="w-full border-gray-300 rounded-lg">
                <option value="">Select a branch...</option>
                <template x-for="branch in branches" :key="branch.id">
                    <option :value="branch.id" x-text="branch.name"></option>
                </template>
            </select>
        </div>
        @endif

        <!-- Main Content Area with Filters and Message Settings -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <!-- Message Settings and Filters Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Message Settings -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Message Settings</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Message Type</label>
                            <select x-model="messageForm.type" @change="loadTemplates(); loadRecipients()" class="w-full border-gray-300 rounded-lg">
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

                <!-- Recipient Filters -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Recipient Filters</h2>
                    
                    <!-- Horizontal Filter Row 1 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Member Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Member Status</label>
                            <select x-model="filters.member_status" @change="loadRecipients()" class="w-full border-gray-300 rounded-lg">
                                <template x-for="(label, value) in availableFilters.member_status" :key="value">
                                    <option :value="value" x-text="label"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Gender -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                            <select x-model="filters.gender" @change="loadRecipients()" class="w-full border-gray-300 rounded-lg">
                                <template x-for="(label, value) in availableFilters.gender" :key="value">
                                    <option :value="value" x-text="label"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Horizontal Filter Row 2 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- TECI Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">TECI Status</label>
                            <select x-model="filters.teci_status" @change="loadRecipients()" class="w-full border-gray-300 rounded-lg">
                                <template x-for="(label, value) in availableFilters.teci_status" :key="value">
                                    <option :value="value" x-text="label"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Marital Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Marital Status</label>
                            <select x-model="filters.marital_status" @change="loadRecipients()" class="w-full border-gray-300 rounded-lg">
                                <template x-for="(label, value) in availableFilters.marital_status" :key="value">
                                    <option :value="value" x-text="label"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Horizontal Filter Row 3 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Growth Level -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Growth Level</label>
                            <select x-model="filters.growth_level" @change="loadRecipients()" class="w-full border-gray-300 rounded-lg">
                                <template x-for="(label, value) in availableFilters.growth_level" :key="value">
                                    <option :value="value" x-text="label"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Birthday Month -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Birthday Month</label>
                            <select x-model="filters.birthday_month" @change="loadRecipients()" class="w-full border-gray-300 rounded-lg">
                                <option value="">All months</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                    </div>

                    <!-- Multi-select Filters -->
                    <div class="space-y-4">
                        <!-- Departments -->
                        <div x-show="Object.keys(availableFilters.departments || {}).length > 0">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Departments</label>
                            <div class="max-h-32 overflow-y-auto border border-gray-200 rounded-lg p-3">
                                <div class="grid grid-cols-2 gap-2">
                                    <template x-for="(name, id) in availableFilters.departments" :key="id">
                                        <label class="flex items-center">
                                            <input type="checkbox" :value="id" x-model="filters.departments" @change="loadRecipients()" class="rounded">
                                            <span class="ml-2 text-sm" x-text="name"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Ministries -->
                        <div x-show="Object.keys(availableFilters.ministries || {}).length > 0">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ministries</label>
                            <div class="max-h-32 overflow-y-auto border border-gray-200 rounded-lg p-3">
                                <div class="grid grid-cols-2 gap-2">
                                    <template x-for="(name, id) in availableFilters.ministries" :key="id">
                                        <label class="flex items-center">
                                            <input type="checkbox" :value="id" x-model="filters.ministries" @change="loadRecipients()" class="rounded">
                                            <span class="ml-2 text-sm" x-text="name"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Small Groups -->
                        <div x-show="Object.keys(availableFilters.small_groups || {}).length > 0">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Small Groups</label>
                            <div class="max-h-32 overflow-y-auto border border-gray-200 rounded-lg p-3">
                                <div class="grid grid-cols-2 gap-2">
                                    <template x-for="(name, id) in availableFilters.small_groups" :key="id">
                                        <label class="flex items-center">
                                            <input type="checkbox" :value="id" x-model="filters.small_groups" @change="loadRecipients()" class="rounded">
                                            <span class="ml-2 text-sm" x-text="name"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Events -->
                        <div x-show="Object.keys(availableFilters.events || {}).length > 0">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Event Attendees</label>
                            <div class="max-h-32 overflow-y-auto border border-gray-200 rounded-lg p-3">
                                <div class="grid grid-cols-1 gap-2">
                                    <template x-for="(name, id) in availableFilters.events" :key="id">
                                        <label class="flex items-center">
                                            <input type="checkbox" :value="id" x-model="filters.events" @change="loadRecipients()" class="rounded">
                                            <span class="ml-2 text-sm" x-text="name"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Age Groups -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Age Groups</label>
                            <div class="grid grid-cols-2 gap-2">
                                <template x-for="(label, value) in availableFilters.age_groups" :key="value">
                                    <label class="flex items-center">
                                        <input type="checkbox" :value="value" x-model="filters.age_groups" @change="loadRecipients()" class="rounded">
                                        <span class="ml-2 text-sm" x-text="label"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Content -->
            <div class="border-t pt-6">
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
                                  rows="6"
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

        <!-- Send Options -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Send Options</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Timing</label>
                    <select x-model="sendOptions.timing" class="w-full border-gray-300 rounded-lg">
                        <option value="immediate">Send Immediately</option>
                        <option value="scheduled">Schedule for Later</option>
                    </select>
                </div>
                
                <div x-show="sendOptions.timing === 'scheduled'">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Date & Time</label>
                    <input type="datetime-local" 
                           x-model="sendOptions.schedule_date" 
                           class="w-full border-gray-300 rounded-lg">
                </div>
            </div>
        </div>

        <!-- Recipients Summary -->
        <div class="bg-white rounded-lg shadow-sm p-6" x-show="recipients.length > 0">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Recipients (<span x-text="recipients.length"></span>)</h2>
            
            <div class="max-h-64 overflow-y-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                    <template x-for="recipient in recipients" :key="recipient.id">
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                            <div>
                                <div class="font-medium" x-text="recipient.name"></div>
                                <div class="text-sm text-gray-600" x-text="recipient.email || recipient.phone"></div>
                            </div>
                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded" x-text="recipient.type"></span>
                        </div>
                    </template>
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
                    
                    <div x-show="preview" class="space-y-4">
                        <div x-show="preview.subject">
                            <label class="block text-sm font-medium text-gray-700">Subject:</label>
                            <div class="mt-1 p-3 bg-gray-50 rounded-lg" x-text="preview.subject"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Content:</label>
                            <div class="mt-1 p-3 bg-gray-50 rounded-lg whitespace-pre-wrap" x-text="preview.content"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Recipients:</label>
                            <div class="mt-1 text-sm text-gray-600" x-text="preview.recipient_count + ' recipients'"></div>
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
                    
                    <div x-show="sendResults" class="space-y-4">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600" x-text="sendResults.total"></div>
                                <div class="text-sm text-blue-800">Total</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-green-600" x-text="sendResults.successful"></div>
                                <div class="text-sm text-green-800">Successful</div>
                            </div>
                            <div class="bg-red-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-red-600" x-text="sendResults.failed"></div>
                                <div class="text-sm text-red-800">Failed</div>
                            </div>
                        </div>
                        
                        <div x-show="sendResults.failed > 0" class="mt-4">
                            <h4 class="font-medium text-gray-900 mb-2">Failed Recipients:</h4>
                            <div class="max-h-32 overflow-y-auto space-y-2">
                                <template x-for="failure in sendResults.failures" :key="failure.recipient">
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
        function massCommunication() {
            return {
                branches: [],
                templates: [],
                availableFilters: {},
                recipients: [],
                selectedBranch: @json($isSuperAdmin ? null : auth()->user()->getActiveBranchId()),
                messageForm: {
                    type: 'email',
                    template_id: '',
                    subject: '',
                    content: '',
                },
                filters: {
                    member_status: 'all',
                    departments: [],
                    ministries: [],
                    small_groups: [],
                    events: [],
                    gender: 'all',
                    age_groups: [],
                    teci_status: 'all',
                    marital_status: 'all',
                    growth_level: 'all',
                    membership_date_from: '',
                    membership_date_to: '',
                    birthday_month: '',
                    anniversary_month: '',
                },
                sendOptions: {
                    timing: 'immediate',
                    schedule_date: '',
                },
                customVariables: [],
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
                        await this.loadFilters();
                        await this.loadTemplates();
                        await this.loadRecipients();
                    }
                },

                get canPreview() {
                    return this.messageForm.content && 
                           (this.messageForm.type === 'sms' || this.messageForm.type === 'whatsapp' || this.messageForm.subject);
                },

                get canSend() {
                    return this.canPreview && this.recipients.length > 0;
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

                async loadFilters() {
                    if (!this.selectedBranch) return;

                    try {
                        const response = await fetch(`/api/communication/mass-send/filters?branch_id=${this.selectedBranch}`, {
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await response.json();
                        this.availableFilters = data.filters || {};
                    } catch (error) {
                        console.error('Failed to load filters:', error);
                    }
                },

                async loadTemplates() {
                    if (!this.selectedBranch) return;

                    try {
                        const response = await fetch(`/api/message-templates?branch_id=${this.selectedBranch}&type=${this.messageForm.type}`);
                        const data = await response.json();
                        this.templates = data.data || [];
                    } catch (error) {
                        console.error('Failed to load templates:', error);
                    }
                },

                async loadRecipients() {
                    if (!this.selectedBranch) return;

                    this.loadingRecipients = true;
                    try {
                        const response = await fetch('/api/communication/mass-send/recipients', {
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
                                filters: this.filters
                            })
                        });
                        const data = await response.json();
                        this.recipients = data.recipients || [];
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
                        const response = await fetch(`/api/message-templates/${this.messageForm.template_id}`);
                        const data = await response.json();
                        if (data.template) {
                            this.messageForm.subject = data.template.subject || '';
                            this.messageForm.content = data.template.content || '';
                        }
                    } catch (error) {
                        console.error('Failed to load template:', error);
                    }
                },

                async previewMessage() {
                    if (!this.canPreview) return;

                    this.previewing = true;
                    try {
                        const response = await fetch('/api/communication/mass-send/preview', {
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
                                message: this.messageForm,
                                filters: this.filters
                            })
                        });
                        const data = await response.json();
                        this.preview = data.preview;
                        this.showPreviewModal = true;
                    } catch (error) {
                        console.error('Failed to preview message:', error);
                        alert('Failed to preview message');
                    } finally {
                        this.previewing = false;
                    }
                },

                async sendMessage() {
                    if (!this.canSend) return;

                    if (!confirm(`Are you sure you want to send this message to ${this.recipients.length} recipients?`)) {
                        return;
                    }

                    this.sending = true;
                    try {
                        const response = await fetch('/api/communication/mass-send/send', {
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
                                message: this.messageForm,
                                filters: this.filters,
                                send_options: this.sendOptions
                            })
                        });
                        const data = await response.json();
                        this.sendResults = data.results;
                        this.showResultsModal = true;
                        
                        // Reset form
                        this.messageForm.subject = '';
                        this.messageForm.content = '';
                        this.messageForm.template_id = '';
                        this.recipients = [];
                    } catch (error) {
                        console.error('Failed to send message:', error);
                        alert('Failed to send message');
                    } finally {
                        this.sending = false;
                    }
                }
            }
        }
    </script>
</x-sidebar-layout>











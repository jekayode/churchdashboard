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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Filters Panel -->
            <div class="space-y-6">
                <!-- Message Type & Template -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Message Settings</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Message Type</label>
                            <select x-model="messageForm.type" @change="loadTemplates(); loadRecipients()" class="w-full border-gray-300 rounded-lg">
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

                <!-- Recipient Filters -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Recipient Filters</h2>
                    
                    <div class="space-y-4">
                        <!-- Member Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Member Status</label>
                            <select x-model="filters.member_status" @change="loadRecipients()" class="w-full border-gray-300 rounded-lg">
                                <template x-for="(label, value) in availableFilters.member_status" :key="value">
                                    <option :value="value" x-text="label"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Departments -->
                        <div x-show="Object.keys(availableFilters.departments || {}).length > 0">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Departments</label>
                            <div class="space-y-2 max-h-32 overflow-y-auto border border-gray-200 rounded p-2">
                                <template x-for="(name, id) in availableFilters.departments" :key="id">
                                    <label class="flex items-center">
                                        <input type="checkbox" :value="id" x-model="filters.departments" @change="loadRecipients()" class="rounded">
                                        <span class="ml-2 text-sm" x-text="name"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- Ministries -->
                        <div x-show="Object.keys(availableFilters.ministries || {}).length > 0">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ministries</label>
                            <div class="space-y-2 max-h-32 overflow-y-auto border border-gray-200 rounded p-2">
                                <template x-for="(name, id) in availableFilters.ministries" :key="id">
                                    <label class="flex items-center">
                                        <input type="checkbox" :value="id" x-model="filters.ministries" @change="loadRecipients()" class="rounded">
                                        <span class="ml-2 text-sm" x-text="name"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- Small Groups -->
                        <div x-show="Object.keys(availableFilters.small_groups || {}).length > 0">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Small Groups</label>
                            <div class="space-y-2 max-h-32 overflow-y-auto border border-gray-200 rounded p-2">
                                <template x-for="(name, id) in availableFilters.small_groups" :key="id">
                                    <label class="flex items-center">
                                        <input type="checkbox" :value="id" x-model="filters.small_groups" @change="loadRecipients()" class="rounded">
                                        <span class="ml-2 text-sm" x-text="name"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- Events -->
                        <div x-show="Object.keys(availableFilters.events || {}).length > 0">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Event Attendees</label>
                            <div class="space-y-2 max-h-32 overflow-y-auto border border-gray-200 rounded p-2">
                                <template x-for="(name, id) in availableFilters.events" :key="id">
                                    <label class="flex items-center">
                                        <input type="checkbox" :value="id" x-model="filters.events" @change="loadRecipients()" class="rounded">
                                        <span class="ml-2 text-sm" x-text="name"></span>
                                    </label>
                                </template>
                            </div>
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

                        <!-- Age Groups -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Age Groups</label>
                            <div class="space-y-2">
                                <template x-for="(label, value) in availableFilters.age_groups" :key="value">
                                    <label class="flex items-center">
                                        <input type="checkbox" :value="value" x-model="filters.age_groups" @change="loadRecipients()" class="rounded">
                                        <span class="ml-2 text-sm" x-text="label"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

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

                        <!-- Growth Level -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Growth Level</label>
                            <select x-model="filters.growth_level" @change="loadRecipients()" class="w-full border-gray-300 rounded-lg">
                                <template x-for="(label, value) in availableFilters.growth_level" :key="value">
                                    <option :value="value" x-text="label"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Membership Date Range -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Member Since</label>
                                <input type="date" x-model="filters.membership_date_from" @change="loadRecipients()" 
                                       class="w-full border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Member Until</label>
                                <input type="date" x-model="filters.membership_date_to" @change="loadRecipients()" 
                                       class="w-full border-gray-300 rounded-lg">
                            </div>
                        </div>

                        <!-- Special Occasions -->
                        <div class="grid grid-cols-2 gap-3">
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
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Anniversary Month</label>
                                <select x-model="filters.anniversary_month" @change="loadRecipients()" class="w-full border-gray-300 rounded-lg">
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

                        <!-- Clear Filters -->
                        <button @click="clearFilters()" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                            Clear All Filters
                        </button>
                    </div>
                </div>

                <!-- Recipients Preview -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-medium text-gray-900 mb-3">
                        Recipients (<span x-text="recipients.length"></span>)
                    </h3>
                    
                    <div x-show="loadingRecipients" class="text-center py-4">
                        <div class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                        <p class="text-sm text-gray-600 mt-1">Loading...</p>
                    </div>

                    <div x-show="!loadingRecipients" class="space-y-2 max-h-64 overflow-y-auto">
                        <template x-for="recipient in recipients.slice(0, 10)" :key="recipient.id">
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded text-sm">
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-900 truncate" x-text="recipient.name"></div>
                                    <div class="text-gray-500 truncate" x-text="messageForm.type === 'email' ? recipient.email : recipient.phone"></div>
                                </div>
                                <span :class="getStatusColor(recipient.status)" 
                                      class="px-2 py-1 text-xs rounded-full" 
                                      x-text="recipient.status"></span>
                            </div>
                        </template>
                        <div x-show="recipients.length > 10" class="text-center text-sm text-gray-500 py-2">
                            ... and <span x-text="recipients.length - 10"></span> more
                        </div>
                        <div x-show="recipients.length === 0" class="text-center py-4 text-gray-500 text-sm">
                            No recipients found with current filters
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Message Form -->
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
                        <textarea x-model="messageForm.content" rows="12" 
                                  placeholder="Enter your message content here. Use {variable_name} for dynamic content."
                                  class="w-full border-gray-300 rounded-lg"></textarea>
                    </div>

                    <!-- Variables Help -->
                    <div x-show="showVariablesHelp" x-transition class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
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
                    <div>
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

                <!-- Send Options -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Send Options</h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="radio" x-model="sendOptions.timing" value="immediate" 
                                   id="send_immediate" class="rounded">
                            <label for="send_immediate" class="ml-2 text-sm text-gray-700">Send immediately</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" x-model="sendOptions.timing" value="scheduled" 
                                   id="send_scheduled" class="rounded">
                            <label for="send_scheduled" class="ml-2 text-sm text-gray-700">Schedule for later</label>
                        </div>
                        <div x-show="sendOptions.timing === 'scheduled'" class="ml-6">
                            <input type="datetime-local" x-model="sendOptions.schedule_date" 
                                   class="border-gray-300 rounded-lg">
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
                        <h3 class="text-lg font-semibold">Mass Communication Preview</h3>
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
                            <strong>Recipients:</strong> <span x-text="recipients.length"></span> members will receive this message.
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
                        <h3 class="text-lg font-semibold">Mass Communication Results</h3>
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
                    return this.selectedBranch && 
                           this.messageForm.content && 
                           (this.messageForm.type === 'sms' || this.messageForm.subject);
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
                        const response = await fetch(`/api/communication/templates?branch_id=${this.selectedBranch}&type=${this.messageForm.type}&per_page=100`, {
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
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

                async loadRecipients() {
                    if (!this.selectedBranch) return;

                    this.loadingRecipients = true;
                    try {
                        const response = await fetch('/api/communication/mass-send/recipients', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                branch_id: this.selectedBranch,
                                filters: this.filters,
                                message_type: this.messageForm.type
                            })
                        });

                        const data = await response.json();
                        
                        if (response.ok) {
                            this.recipients = data.recipients || [];
                        } else {
                            console.error('Failed to load recipients:', data.error);
                            this.recipients = [];
                        }
                    } catch (error) {
                        console.error('Failed to load recipients:', error);
                        this.recipients = [];
                    } finally {
                        this.loadingRecipients = false;
                    }
                },

                getStatusColor(status) {
                    const colors = {
                        'member': 'bg-blue-100 text-blue-800',
                        'volunteer': 'bg-green-100 text-green-800',
                        'leader': 'bg-purple-100 text-purple-800',
                        'minister': 'bg-orange-100 text-orange-800',
                        'visitor': 'bg-gray-100 text-gray-800',
                    };
                    return colors[status] || 'bg-gray-100 text-gray-800';
                },

                clearFilters() {
                    this.filters = {
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
                    };
                    this.loadRecipients();
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
                    if (!this.canPreview) return;

                    this.previewing = true;
                    try {
                        const response = await fetch('/api/communication/mass-send/preview', {
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

                    if (!confirm(`Send ${this.messageForm.type} to ${this.recipients.length} recipient(s)?`)) {
                        return;
                    }

                    this.sending = true;
                    try {
                        const response = await fetch('/api/communication/mass-send/send', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                branch_id: this.selectedBranch,
                                type: this.messageForm.type,
                                filters: this.filters,
                                template_id: this.messageForm.template_id || null,
                                subject: this.messageForm.subject,
                                content: this.messageForm.content,
                                custom_variables: this.getCustomVariablesObject(),
                                send_immediately: this.sendOptions.timing === 'immediate',
                                schedule_date: this.sendOptions.timing === 'scheduled' ? this.sendOptions.schedule_date : null
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

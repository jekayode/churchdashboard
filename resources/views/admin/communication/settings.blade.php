<x-sidebar-layout title="Communication Settings">
    <div class="space-y-6" x-data="communicationSettings">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Communication Settings</h1>
                    <p class="text-gray-600 mt-1">Configure email and SMS providers for your branch.</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="testSettings()" 
                            :disabled="!hasSettings || testing"
                            :class="hasSettings && !testing ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'"
                            class="px-4 py-2 text-white rounded-lg transition-colors">
                        <span x-show="!testing">Test Settings</span>
                        <span x-show="testing">Testing...</span>
                    </button>
                    <button @click="saveSettings()" 
                            :disabled="saving"
                            :class="saving ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700'"
                            class="px-4 py-2 text-white rounded-lg transition-colors">
                        <span x-show="!saving">Save Settings</span>
                        <span x-show="saving">Saving...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Branch Selection (for Super Admin) -->
        @if($isSuperAdmin ?? false)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
            <select x-model="selectedBranch" @change="loadSettings()" class="w-full border-gray-300 rounded-lg">
                <option value="">Select a branch...</option>
                <template x-for="branch in branches" :key="branch.id">
                    <option :value="branch.id" x-text="branch.name"></option>
                </template>
            </select>
        </div>
        @endif

        <!-- Email Provider Configuration -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Email Configuration</h2>
            
            <!-- Provider Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Email Provider</label>
                <select x-model="settings.email_provider" @change="loadProviderTemplate('email')" 
                        class="w-full border-gray-300 rounded-lg">
                    <option value="smtp">SMTP</option>
                    <option value="resend">Resend</option>
                    <option value="mailgun">Mailgun</option>
                    <option value="ses">Amazon SES</option>
                    <option value="postmark">Postmark</option>
                </select>
            </div>

            <!-- From Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Name</label>
                    <input type="text" x-model="settings.from_name" 
                           placeholder="Church Name" 
                           class="w-full border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Email</label>
                    <input type="email" x-model="settings.from_email" 
                           placeholder="noreply@church.com" 
                           class="w-full border-gray-300 rounded-lg">
                </div>
            </div>

            <!-- Dynamic Provider Configuration -->
            <div x-show="emailTemplate">
                <h3 class="text-md font-medium text-gray-900 mb-3">Provider Settings</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="(field, key) in emailTemplate" :key="key">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" x-text="field.label"></label>
                            <input :type="field.type" 
                                   x-model="settings.email_config[key]"
                                   :placeholder="field.placeholder"
                                   class="w-full border-gray-300 rounded-lg">
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- SMS Provider Configuration -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">SMS Configuration (Optional)</h2>
            
            <!-- Provider Selection -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">SMS Provider</label>
                <select x-model="settings.sms_provider" @change="loadProviderTemplate('sms')" 
                        class="w-full border-gray-300 rounded-lg">
                    <option value="">Select SMS Provider (Optional)</option>
                    <option value="twilio">Twilio</option>
                    <option value="africas-talking">Africa's Talking</option>
                    <option value="jusibe">Jusibe</option>
                    <option value="bulksmsnigeria">Bulksmsnigeria</option>
                </select>
            </div>

            <!-- Dynamic SMS Provider Configuration -->
            <div x-show="smsTemplate && settings.sms_provider">
                <h3 class="text-md font-medium text-gray-900 mb-3">SMS Provider Settings</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="(field, key) in smsTemplate" :key="key">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" x-text="field.label"></label>
                            <input :type="field.type" 
                                   x-model="settings.sms_config[key]"
                                   :placeholder="field.placeholder"
                                   class="w-full border-gray-300 rounded-lg">
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- WhatsApp Provider Configuration -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">WhatsApp Configuration (Optional)</h2>
            
            <!-- Provider Selection -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">WhatsApp Provider</label>
                <select x-model="settings.whatsapp_provider" @change="loadProviderTemplate('whatsapp')" 
                        class="w-full border-gray-300 rounded-lg">
                    <option value="">Select WhatsApp Provider (Optional)</option>
                    <option value="twilio">Twilio WhatsApp</option>
                    <option value="meta">Meta WhatsApp Business</option>
                </select>
            </div>

            <!-- Dynamic WhatsApp Provider Configuration -->
            <div x-show="whatsappTemplate && settings.whatsapp_provider">
                <h3 class="text-md font-medium text-gray-900 mb-3">WhatsApp Provider Settings</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="(field, key) in whatsappTemplate" :key="key">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" x-text="field.label"></label>
                            <input :type="field.type" 
                                   x-model="settings.whatsapp_config[key]"
                                   :placeholder="field.placeholder"
                                   class="w-full border-gray-300 rounded-lg">
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Birthday & Anniversary Configuration -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Birthday & Anniversary Messages</h2>
            
            <!-- Auto Send Toggles -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Birthday Auto Send -->
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div>
                        <h3 class="text-md font-medium text-gray-900">Auto Send Birthday Messages</h3>
                        <p class="text-sm text-gray-600">Automatically send birthday messages to members</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="settings.auto_send_birthdays" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <!-- Anniversary Auto Send -->
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div>
                        <h3 class="text-md font-medium text-gray-900">Auto Send Anniversary Messages</h3>
                        <p class="text-sm text-gray-600">Automatically send anniversary messages to members</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="settings.auto_send_anniversaries" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
            </div>

            <!-- Template Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Birthday Template -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Birthday Message Template</label>
                    <select x-model="settings.birthday_template_id" class="w-full border-gray-300 rounded-lg">
                        <option value="">Select Birthday Template</option>
                        <template x-for="template in emailTemplates" :key="template.id">
                            <option :value="template.id" x-text="template.name"></option>
                        </template>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Template will be used for both email and SMS birthday messages</p>
                </div>

                <!-- Anniversary Template -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Anniversary Message Template</label>
                    <select x-model="settings.anniversary_template_id" class="w-full border-gray-300 rounded-lg">
                        <option value="">Select Anniversary Template</option>
                        <template x-for="template in emailTemplates" :key="template.id">
                            <option :value="template.id" x-text="template.name"></option>
                        </template>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Template will be used for both email and SMS anniversary messages</p>
                </div>
            </div>

            <!-- Template Variables Help -->
            <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                <h4 class="text-sm font-medium text-blue-900 mb-2">Available Template Variables:</h4>
                <div class="text-xs text-blue-800 space-y-1">
                    <div><code>{{member_name}}</code> - Full member name</div>
                    <div><code>@{{member_first_name}}</code> - First name only</div>
                    <div><code>@{{member_birthday}}</code> - Birthday date (e.g., "January 15")</div>
                    <div><code>@{{member_anniversary}}</code> - Anniversary date (e.g., "June 20")</div>
                    <div><code>@{{church_name}}</code> - Church/branch name</div>
                    <div><code>@{{current_date}}</code> - Current date</div>
                    <div><code>@{{current_year}}</code> - Current year</div>
                </div>
            </div>
        </div>

        <!-- Status Toggle -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Enable Communication</h3>
                    <p class="text-sm text-gray-600">Turn on/off communication features for this branch</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" x-model="settings.is_active" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
        </div>

        <!-- Test Results -->
        <div x-show="testResults" class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Test Results</h3>
            <template x-for="(result, type) in testResults" :key="type">
                <div class="mb-3">
                    <div class="flex items-center space-x-2">
                        <span class="font-medium capitalize" x-text="type"></span>
                        <span :class="result.status === 'success' ? 'text-green-600' : result.status === 'error' ? 'text-red-600' : 'text-blue-600'"
                              class="text-sm" x-text="result.status"></span>
                    </div>
                    <p class="text-sm text-gray-600" x-text="result.message"></p>
                </div>
            </template>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('communicationSettings', () => ({
                settings: {
                    email_provider: 'smtp',
                    email_config: {},
                    sms_provider: '',
                    sms_config: {},
                    whatsapp_provider: '',
                    whatsapp_config: {},
                    from_name: '',
                    from_email: '',
                    is_active: true
                },
                branches: [],
                selectedBranch: @json($isSuperAdmin ? null : auth()->user()->getActiveBranchId()),
                emailTemplate: null,
                smsTemplate: null,
                whatsappTemplate: null,
                hasSettings: false,
                saving: false,
                testing: false,
                testResults: null,
                emailTemplates: [],

                async init() {
                    @if($isSuperAdmin ?? false)
                        await this.loadBranches();
                    @endif
                    if (this.selectedBranch) {
                        await this.loadSettings();
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

                async loadSettings() {
                    if (!this.selectedBranch) return;

                    try {
                        const response = await fetch(`/api/communication/settings?branch_id=${this.selectedBranch}`, {
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await response.json();
                        
                        if (data.setting) {
                            this.settings = {
                                email_provider: data.setting.email_provider || 'smtp',
                                email_config: data.setting.email_config || {},
                                sms_provider: data.setting.sms_provider || '',
                                sms_config: data.setting.sms_config || {},
                                whatsapp_provider: data.setting.whatsapp_provider || '',
                                whatsapp_config: data.setting.whatsapp_config || {},
                                birthday_template_id: data.setting.birthday_template_id || '',
                                anniversary_template_id: data.setting.anniversary_template_id || '',
                                auto_send_birthdays: data.setting.auto_send_birthdays ?? false,
                                auto_send_anniversaries: data.setting.auto_send_anniversaries ?? false,
                                from_name: data.setting.from_name || '',
                                from_email: data.setting.from_email || '',
                                is_active: data.setting.is_active ?? true
                            };
                            this.hasSettings = true;
                        }

                        // Load provider templates
                        await this.loadProviderTemplate('email');
                        if (this.settings.sms_provider) {
                            await this.loadProviderTemplate('sms');
                        }
                        if (this.settings.whatsapp_provider) {
                            await this.loadProviderTemplate('whatsapp');
                        }

                        // Load email templates for birthday/anniversary dropdowns
                        await this.loadEmailTemplates();
                    } catch (error) {
                        console.error('Failed to load settings:', error);
                    }
                },

                async loadProviderTemplate(type) {
                    let provider;
                    if (type === 'email') {
                        provider = this.settings.email_provider;
                    } else if (type === 'sms') {
                        provider = this.settings.sms_provider;
                    } else if (type === 'whatsapp') {
                        provider = this.settings.whatsapp_provider;
                    }
                    
                    if (!provider) return;

                    try {
                        const response = await fetch(`/api/communication/settings/provider-template?provider=${provider}&type=${type}`, {
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await response.json();
                        
                        if (type === 'email') {
                            this.emailTemplate = data.template;
                        } else if (type === 'sms') {
                            this.smsTemplate = data.template;
                        } else if (type === 'whatsapp') {
                            this.whatsappTemplate = data.template;
                        }
                    } catch (error) {
                        console.error('Failed to load provider template:', error);
                    }
                },

                async loadEmailTemplates() {
                    if (!this.selectedBranch) return;

                    try {
                        const response = await fetch(`/api/message-templates?branch_id=${this.selectedBranch}&type=email`, {
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await response.json();
                        this.emailTemplates = data.data || [];
                    } catch (error) {
                        console.error('Failed to load email templates:', error);
                        this.emailTemplates = [];
                    }
                },

                async saveSettings() {
                    if (!this.selectedBranch) {
                        alert('Please select a branch');
                        return;
                    }

                    this.saving = true;
                    try {
                        const response = await fetch('/api/communication/settings', {
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
                                ...this.settings
                            })
                        });

                        const data = await response.json();
                        
                        if (response.ok) {
                            this.hasSettings = true;
                            alert('Settings saved successfully!');
                        } else {
                            console.error('Save failed:', data);
                            alert('Error: ' + (data.message || data.error || 'Failed to save settings'));
                        }
                    } catch (error) {
                        alert('Failed to save settings: ' + error.message);
                    } finally {
                        this.saving = false;
                    }
                },

                async testSettings() {
                    if (!this.selectedBranch) {
                        alert('Please select a branch');
                        return;
                    }

                    this.testing = true;
                    this.testResults = null;

                    try {
                        const response = await fetch('/api/communication/settings/test', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                branch_id: this.selectedBranch
                            })
                        });

                        const data = await response.json();
                        
                        if (response.ok) {
                            this.testResults = data.results;
                        } else {
                            alert('Error: ' + (data.error || 'Failed to test settings'));
                        }
                    } catch (error) {
                        alert('Failed to test settings: ' + error.message);
                    } finally {
                        this.testing = false;
                    }
                }
            }))
        })
    </script>
</x-sidebar-layout>

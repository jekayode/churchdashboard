<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Data Import & Export
            </h2>
            <div class="flex space-x-2">
                <button @click="showStatsModal = true" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    View Statistics
                </button>
                <button @click="cleanupOldFiles()" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                    Cleanup Old Files
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="importExportManager()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Import Section -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Import Data</h3>
                
                <!-- Import Tabs -->
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8">
                        <button @click="activeImportTab = 'members'" 
                                :class="activeImportTab === 'members' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="py-2 px-1 border-b-2 font-medium text-sm">
                            Members
                        </button>
                        <button @click="activeImportTab = 'ministries'" 
                                :class="activeImportTab === 'ministries' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="py-2 px-1 border-b-2 font-medium text-sm">
                            Ministries
                        </button>
                        <button @click="activeImportTab = 'departments'" 
                                :class="activeImportTab === 'departments' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="py-2 px-1 border-b-2 font-medium text-sm">
                            Departments
                        </button>
                        <button @click="activeImportTab = 'small-groups'" 
                                :class="activeImportTab === 'small-groups' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="py-2 px-1 border-b-2 font-medium text-sm">
                            Small Groups
                        </button>
                    </nav>
                </div>

                <!-- Import Forms -->
                <div class="mt-6">
                    <!-- Members Import -->
                    <div x-show="activeImportTab === 'members'" class="space-y-4">
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <h4 class="font-medium text-gray-900 mb-2">📋 Required Data Format</h4>
                            <div class="text-sm text-gray-600 space-y-1">
                                <p><strong>Required fields:</strong> name, email</p>
                                <p><strong>TECI Status:</strong> not_started, 100_level, 200_level, 300_level, 400_level, 500_level, graduated, paused</p>
                                <p><strong>Growth Level:</strong> core, pastor, growing, new_believer</p>
                                <p><strong>Member Status:</strong> visitor, member, volunteer, leader, minister</p>
                                <p><strong>Gender:</strong> male, female</p>
                                <p><strong>Marital Status:</strong> single, married, divorced, separated, widowed, in_a_relationship, engaged</p>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-medium text-blue-900 mb-2">Import Members</h4>
                            <p class="text-sm text-blue-700 mb-4">Upload a CSV file with member information. <a href="#" @click="downloadTemplate('members')" class="underline">Download template</a></p>
                            
                            <!-- Branch Selection for Super Admin -->
                            <div x-show="isSuperAdmin" class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Target Branch</label>
                                <select x-model="importForms.members.branch_id" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                                    <option value="">Select Branch</option>
                                    <template x-for="branch in branches" :key="branch.id">
                                        <option :value="branch.id" x-text="branch.name"></option>
                                    </template>
                                    <option x-show="branches.length === 0" disabled>No branches available</option>
                                </select>
                                <p x-show="branches.length === 0" class="text-sm text-gray-500 mt-1">
                                    Please ensure you're logged in as a Super Admin to see all branches.
                                </p>
                            </div>

                            <div class="flex items-center space-x-4">
                                <div class="flex-1">
                                    <input type="file" 
                                           @change="handleFileSelection('members', $event)"
                                           accept=".csv,.xlsx,.xls"
                                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                                </div>
                                <button type="button" 
                                        @click="validateFile('members')"
                                        :disabled="!importForms.members.file || validating"
                                        class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 disabled:opacity-50 disabled:cursor-not-allowed mr-2">
                                    <span x-show="!validating">🔍 Validate File</span>
                                    <span x-show="validating">Validating...</span>
                                </button>
                                <button type="button" 
                                        @click="importData('members')"
                                        :disabled="!importForms.members.file || importing"
                                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span x-show="!importing">Import</span>
                                    <span x-show="importing">Importing...</span>
                                </button>
                            </div>
                        </div>

                        <!-- Export Members -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h4 class="font-medium text-green-900 mb-2">Export Members</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div x-show="isSuperAdmin">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                                    <select x-model="exportForms.members.branch_id" class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                                        <option value="">All Branches</option>
                                        <template x-for="branch in branches" :key="branch.id">
                                            <option :value="branch.id" x-text="branch.name"></option>
                                        </template>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select x-model="exportForms.members.member_status" class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                                        <option value="">All Statuses</option>
                                        <option value="visitor">Visitor</option>
                                        <option value="member">Member</option>
                                        <option value="volunteer">Volunteer</option>
                                        <option value="leader">Leader</option>
                                        <option value="minister">Minister</option>
                                    </select>
                                </div>
                                
                                <div class="flex items-end">
                                    <button @click="exportData('members')" 
                                            :disabled="exporting"
                                            class="w-full bg-green-500 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg">
                                        <span x-show="!exporting">Export Members</span>
                                        <span x-show="exporting">Exporting...</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ministries Import -->
                    <div x-show="activeImportTab === 'ministries'" class="space-y-4">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h4 class="font-medium text-green-900 mb-2">Import Ministries</h4>
                            <p class="text-sm text-green-700 mb-4">Upload a CSV file with ministry information. <a href="#" @click="downloadTemplate('ministries')" class="underline">Download template</a></p>
                            
                            <!-- Branch Selection for Super Admin -->
                            <div x-show="isSuperAdmin" class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Target Branch</label>
                                <select x-model="importForms.ministries.branch_id" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                                    <option value="">Select Branch</option>
                                    <template x-for="branch in branches" :key="branch.id">
                                        <option :value="branch.id" x-text="branch.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="flex items-center space-x-4">
                                <div class="flex-1">
                                    <input type="file" 
                                           @change="handleFileSelection('ministries', $event)"
                                           accept=".csv,.xlsx,.xls"
                                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                                </div>
                                <button @click="importData('ministries')" 
                                        :disabled="!importForms.ministries.file || importing"
                                        :class="!importForms.ministries.file || importing ? 'bg-gray-300' : 'bg-green-500 hover:bg-green-700'"
                                        class="px-4 py-2 text-white font-medium rounded-lg">
                                    <span x-show="!importing">Import</span>
                                    <span x-show="importing">Importing...</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Departments Import -->
                    <div x-show="activeImportTab === 'departments'" class="space-y-4">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <h4 class="font-medium text-yellow-900 mb-2">Import Departments</h4>
                            <p class="text-sm text-yellow-700 mb-4">Upload a CSV file with department information. <a href="#" @click="downloadTemplate('departments')" class="underline">Download template</a></p>
                            
                            <!-- Ministry Selection -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Target Ministry</label>
                                <select x-model="importForms.departments.ministry_id" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                                    <option value="">Select Ministry</option>
                                    <template x-for="ministry in ministries" :key="ministry.id">
                                        <option :value="ministry.id" x-text="ministry.name + ' (' + ministry.branch?.name + ')'"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="flex items-center space-x-4">
                                <div class="flex-1">
                                    <input type="file" 
                                           @change="handleFileSelection('departments', $event)"
                                           accept=".csv,.xlsx,.xls"
                                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100">
                                </div>
                                <button @click="importData('departments')" 
                                        :disabled="!importForms.departments.file || importing"
                                        :class="!importForms.departments.file || importing ? 'bg-gray-300' : 'bg-yellow-500 hover:bg-yellow-700'"
                                        class="px-4 py-2 text-white font-medium rounded-lg">
                                    <span x-show="!importing">Import</span>
                                    <span x-show="importing">Importing...</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Small Groups Import -->
                    <div x-show="activeImportTab === 'small-groups'" class="space-y-4">
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <h4 class="font-medium text-red-900 mb-2">Import Small Groups</h4>
                            <p class="text-sm text-red-700 mb-4">Upload a CSV file with small group information. <a href="#" @click="downloadTemplate('small-groups')" class="underline">Download template</a></p>
                            
                            <!-- Branch Selection for Super Admin -->
                            <div x-show="isSuperAdmin" class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Target Branch</label>
                                <select x-model="importForms.smallGroups.branch_id" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                                    <option value="">Select Branch</option>
                                    <template x-for="branch in branches" :key="branch.id">
                                        <option :value="branch.id" x-text="branch.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="flex items-center space-x-4">
                                <div class="flex-1">
                                    <input type="file" 
                                           @change="handleFileSelection('small-groups', $event)"
                                           accept=".csv,.xlsx,.xls"
                                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                                </div>
                                <button @click="importData('small-groups')" 
                                        :disabled="!importForms.smallGroups.file || importing"
                                        :class="!importForms.smallGroups.file || importing ? 'bg-gray-300' : 'bg-red-500 hover:bg-red-700'"
                                        class="px-4 py-2 text-white font-medium rounded-lg">
                                    <span x-show="!importing">Import</span>
                                    <span x-show="importing">Importing...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Import Progress -->
                <div x-show="importing" class="mt-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center mb-2">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mr-3"></div>
                            <span class="text-blue-800">Processing import... Please wait.</span>
                        </div>
                        <div class="bg-blue-200 rounded-full h-2 mb-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-1000" style="width: 50%"></div>
                        </div>
                        <div class="text-xs text-blue-700">
                            <p><strong>Large files may take several minutes to process.</strong></p>
                            <p>If you see a timeout error, try reducing your file size or splitting it into smaller files.</p>
                        </div>
                    </div>
                </div>

                <!-- Import Results -->
                <div x-show="lastImportResult || lastExportResult" class="mt-4">
                    <!-- Import Results -->
                    <div x-show="lastImportResult" class="mb-4">
                        <div :class="lastImportResult?.success ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'" 
                             class="border rounded-lg p-4">
                            <h4 class="font-medium mb-2">Import Result</h4>
                            <p x-text="lastImportResult?.message"></p>
                            
                            <!-- Show detailed errors if available -->
                            <div x-show="!lastImportResult?.success && lastImportResult?.details" class="mt-3">
                                <details class="cursor-pointer">
                                    <summary class="font-medium text-sm mb-2">Show Error Details</summary>
                                    <div class="bg-white bg-opacity-50 rounded p-3 mt-2">
                                        <pre class="text-xs whitespace-pre-wrap font-mono" x-text="lastImportResult?.details"></pre>
                                    </div>
                                </details>
                            </div>
                            
                            <!-- Show success details if available -->
                            <div x-show="lastImportResult?.success && lastImportResult?.details" class="mt-3">
                                <details class="cursor-pointer">
                                    <summary class="font-medium text-sm mb-2">Show Preview</summary>
                                    <div class="bg-white bg-opacity-50 rounded p-3 mt-2">
                                        <pre class="text-xs whitespace-pre-wrap font-mono" x-text="lastImportResult?.details"></pre>
                                    </div>
                                </details>
                            </div>
                        </div>
                    </div>

                    <!-- Export Results -->
                    <div x-show="lastExportResult" class="mb-4">
                        <div :class="lastExportResult?.success ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'" 
                             class="border rounded-lg p-4">
                            <h4 class="font-medium mb-2">Export Result</h4>
                            <p x-text="lastExportResult?.message"></p>
                            <div x-show="lastExportResult?.success && lastExportResult?.download_url" class="mt-3">
                                <a :href="lastExportResult.download_url" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-lg hover:bg-blue-600">
                                    Download File
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Section -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Export Data</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Members Export -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-medium text-blue-900 mb-2">Members</h4>
                        <p class="text-sm text-blue-700 mb-4">Export member database with filtering options</p>
                        
                        <div class="space-y-3">
                            <div x-show="isSuperAdmin">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Branch</label>
                                <select x-model="exportForms.members.branch_id" class="w-full text-sm rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">All Branches</option>
                                    <template x-for="branch in branches" :key="branch.id">
                                        <option :value="branch.id" x-text="branch.name"></option>
                                    </template>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                                <select x-model="exportForms.members.member_status" class="w-full text-sm rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">All Statuses</option>
                                    <option value="visitor">Visitor</option>
                                    <option value="member">Member</option>
                                    <option value="volunteer">Volunteer</option>
                                    <option value="leader">Leader</option>
                                    <option value="minister">Minister</option>
                                </select>
                            </div>
                            
                            <button @click="exportData('members')" 
                                    :disabled="exporting"
                                    class="w-full bg-blue-500 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded text-sm">
                                <span x-show="!exporting">Export Members</span>
                                <span x-show="exporting">Exporting...</span>
                            </button>
                        </div>
                    </div>

                    <!-- Ministries Export -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h4 class="font-medium text-green-900 mb-2">Ministries</h4>
                        <p class="text-sm text-green-700 mb-4">Export all ministries with their details</p>
                        
                        <div class="space-y-3">
                            <div x-show="isSuperAdmin">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Branch</label>
                                <select x-model="exportForms.ministries.branch_id" class="w-full text-sm rounded border-gray-300 focus:border-green-500 focus:ring-green-500">
                                    <option value="">All Branches</option>
                                    <template x-for="branch in branches" :key="branch.id">
                                        <option :value="branch.id" x-text="branch.name"></option>
                                    </template>
                                </select>
                            </div>
                            
                            <button @click="exportData('ministries')" 
                                    :disabled="exporting"
                                    class="w-full bg-green-500 hover:bg-green-700 text-white font-medium py-2 px-4 rounded text-sm">
                                <span x-show="!exporting">Export Ministries</span>
                                <span x-show="exporting">Exporting...</span>
                            </button>
                        </div>
                    </div>

                    <!-- Departments Export -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <h4 class="font-medium text-yellow-900 mb-2">Departments</h4>
                        <p class="text-sm text-yellow-700 mb-4">Export all departments with ministry info</p>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Ministry</label>
                                <select x-model="exportForms.departments.ministry_id" class="w-full text-sm rounded border-gray-300 focus:border-yellow-500 focus:ring-yellow-500">
                                    <option value="">All Ministries</option>
                                    <template x-for="ministry in ministries" :key="ministry.id">
                                        <option :value="ministry.id" x-text="ministry.name"></option>
                                    </template>
                                </select>
                            </div>
                            
                            <button @click="exportData('departments')" 
                                    :disabled="exporting"
                                    class="w-full bg-yellow-500 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded text-sm">
                                <span x-show="!exporting">Export Departments</span>
                                <span x-show="exporting">Exporting...</span>
                            </button>
                        </div>
                    </div>

                    <!-- Small Groups Export -->
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <h4 class="font-medium text-red-900 mb-2">Small Groups</h4>
                        <p class="text-sm text-red-700 mb-4">Export small groups with member lists</p>
                        
                        <div class="space-y-3">
                            <div x-show="isSuperAdmin">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Branch</label>
                                <select x-model="exportForms.smallGroups.branch_id" class="w-full text-sm rounded border-gray-300 focus:border-red-500 focus:ring-red-500">
                                    <option value="">All Branches</option>
                                    <template x-for="branch in branches" :key="branch.id">
                                        <option :value="branch.id" x-text="branch.name"></option>
                                    </template>
                                </select>
                            </div>
                            
                            <button @click="exportData('small-groups')" 
                                    :disabled="exporting"
                                    class="w-full bg-red-500 hover:bg-red-700 text-white font-medium py-2 px-4 rounded text-sm">
                                <span x-show="!exporting">Export Small Groups</span>
                                <span x-show="exporting">Exporting...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Operations -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Operations</h3>
                
                <div x-show="recentOperations.length === 0" class="text-gray-500 text-center py-8">
                    No recent operations found.
                </div>
                
                <div x-show="recentOperations.length > 0" class="space-y-3">
                    <template x-for="operation in recentOperations" :key="operation.id">
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div :class="operation.type === 'import' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'" 
                                     class="px-2 py-1 rounded text-xs font-medium">
                                    <span x-text="operation.type.toUpperCase()"></span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900" x-text="operation.entity + ' - ' + operation.status"></p>
                                    <p class="text-xs text-gray-500" x-text="operation.timestamp"></p>
                                </div>
                            </div>
                            <div class="text-sm text-gray-600">
                                <span x-text="operation.record_count + ' records'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Statistics Modal -->
        <div x-show="showStatsModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Import/Export Statistics</h3>
                            
                            <div x-show="stats" class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-blue-50 p-3 rounded-lg">
                                        <p class="text-sm text-blue-600">Total Imports</p>
                                        <p class="text-2xl font-bold text-blue-900" x-text="stats?.total_imports || 0"></p>
                                    </div>
                                    <div class="bg-green-50 p-3 rounded-lg">
                                        <p class="text-sm text-green-600">Total Exports</p>
                                        <p class="text-2xl font-bold text-green-900" x-text="stats?.total_exports || 0"></p>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <p class="text-sm text-gray-600">Storage Used</p>
                                    <p class="text-lg font-medium text-gray-900" x-text="stats?.storage_used || '0 MB'"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button @click="showStatsModal = false" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function importExportManager() {
            return {
                activeImportTab: 'members',
                importing: false,
                exporting: false,
                validating: false,
                currentUser: @json(auth()->user()),
                showStatsModal: false,
                isSuperAdmin: {{ auth()->user()->isSuperAdmin() ? 'true' : 'false' }},
                
                branches: [],
                ministries: [],
                stats: null,
                recentOperations: [],
                
                lastImportResult: null,
                lastExportResult: null,
                importProgress: 0,
                
                importForms: {
                    members: { file: null, branch_id: '' },
                    ministries: { file: null, branch_id: '' },
                    departments: { file: null, ministry_id: '' },
                    smallGroups: { file: null, branch_id: '' }
                },
                
                exportForms: {
                    members: { branch_id: '', member_status: '' },
                    ministries: { branch_id: '' },
                    departments: { ministry_id: '' },
                    smallGroups: { branch_id: '' }
                },

                async init() {
                    await Promise.all([
                        this.loadBranches(),
                        this.loadMinistries(),
                        this.loadStats(),
                        this.loadRecentOperations()
                    ]);
                },

                async loadBranches() {
                    try {
                        const response = await fetch('/api/branches', {
                            credentials: 'include',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                                'Accept': 'application/json'
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            // Handle paginated response - branches are in data.data.data
                            this.branches = data.data?.data || data.data || [];
                        } else {
                            console.error('Failed to load branches:', response.status, response.statusText);
                        }
                    } catch (error) {
                        console.error('Error loading branches:', error);
                    }
                },

                async loadMinistries() {
                    try {
                        const response = await fetch('/api/ministries', {
                            credentials: 'include',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                                'Accept': 'application/json'
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            // Handle paginated response - ministries are in data.data.data
                            this.ministries = data.data?.data || data.data || [];
                        }
                    } catch (error) {
                        console.error('Error loading ministries:', error);
                    }
                },

                async loadStats() {
                    try {
                        const response = await fetch('/api/import-export/stats', {
                            credentials: 'include',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                                'Accept': 'application/json'
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            this.stats = data.data || {};
                        }
                    } catch (error) {
                        console.error('Error loading stats:', error);
                    }
                },

                async loadRecentOperations() {
                    // This would need to be implemented on the backend
                    // For now, just showing empty state
                    this.recentOperations = [];
                },

                handleFileSelection(type, event) {
                    const file = event.target.files[0];
                    if (file) {
                        // Convert type from kebab-case to camelCase for object access
                        const formType = type === 'small-groups' ? 'smallGroups' : type;
                        this.importForms[formType].file = file;
                    }
                },

                async downloadTemplate(type) {
                    try {
                        let endpoint = '';
                        switch(type) {
                            case 'members':
                                endpoint = '/api/import-export/members/import-template';
                                break;
                            case 'ministries':
                                endpoint = '/api/import-export/ministries/import-template';
                                break;
                            case 'departments':
                                endpoint = '/api/import-export/departments/import-template';
                                break;
                            case 'small-groups':
                                endpoint = '/api/import-export/small-groups/import-template';
                                break;
                            default:
                                this.showNotification('Template not available for ' + type, 'error');
                                return;
                        }

                        const response = await fetch(endpoint, {
                            credentials: 'include',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                            }
                        });

                        if (response.ok) {
                            const blob = await response.blob();
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            
                            // Extract filename from response headers or create default
                            const contentDisposition = response.headers.get('content-disposition');
                            let filename = `${type}_import_template.xlsx`;
                            
                            if (contentDisposition) {
                                const filenameMatch = contentDisposition.match(/filename="(.+)"/);
                                if (filenameMatch) {
                                    filename = filenameMatch[1];
                                }
                            }
                            
                            a.download = filename;
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            window.URL.revokeObjectURL(url);
                            
                            this.showNotification('Template downloaded successfully!', 'success');
                        } else {
                            // Try to get JSON error message
                            try {
                                const errorData = await response.json();
                                this.showNotification(errorData.message || 'Failed to download template', 'error');
                            } catch {
                                this.showNotification('Failed to download template', 'error');
                            }
                        }
                    } catch (error) {
                        console.error('Error downloading template:', error);
                        this.showNotification('Error downloading template', 'error');
                    }
                },

                async importData(type) {
                                            this.importing = true;
                        this.lastImportResult = null;
                        this.importProgress = 0;

                    try {
                        const formType = type === 'small-groups' ? 'smallGroups' : type;
                        const formData = new FormData();
                        
                        formData.append('file', this.importForms[formType].file);
                        
                        // Add branch/ministry ID based on type
                        if (type === 'members' || type === 'ministries' || type === 'small-groups') {
                            if (this.importForms[formType].branch_id) {
                                formData.append('branch_id', this.importForms[formType].branch_id);
                            }
                        } else if (type === 'departments') {
                            if (this.importForms[formType].ministry_id) {
                                formData.append('ministry_id', this.importForms[formType].ministry_id);
                            }
                        }

                        const response = await fetch(`/api/import-export/${type}/import`, {
                            method: 'POST',
                            credentials: 'include',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const data = await response.json();
                        
                        this.lastImportResult = data;
                        
                        if (data.success) {
                            this.showNotification(data.message || 'Import completed successfully!', 'success');
                            // Reset form
                            this.importForms[formType].file = null;
                            // Reset file input
                            const fileInput = document.querySelector(`input[type="file"][onchange*="${type}"]`);
                            if (fileInput) fileInput.value = '';
                        } else {
                            this.showNotification(data.message || 'Import failed. Please check your file and try again.', 'error');
                        }

                    } catch (error) {
                        console.error('Error importing data:', error);
                        this.lastImportResult = {
                            success: false,
                            message: 'Network error. Please check your connection and try again.'
                        };
                        this.showNotification('Network error. Please try again.', 'error');
                    } finally {
                        this.importing = false;
                    }
                },

                async validateFile(type) {
                    this.validating = true;
                    this.lastImportResult = null;

                    try {
                        const formType = type === 'small-groups' ? 'smallGroups' : type;
                        const form = this.importForms[formType];
                        
                        if (!form.file) {
                            this.showNotification('Please select a file first', 'error');
                            return;
                        }

                        const formData = new FormData();
                        formData.append('file', form.file);

                        const response = await fetch('/api/import-export/validate-file', {
                            method: 'POST',
                            credentials: 'include',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const result = await response.json();

                        if (result.success) {
                            this.showNotification(`✅ File validation passed! Found ${result.preview_count || 0} valid rows`, 'success');
                            
                            // Show preview if available
                            if (result.preview && result.preview.length > 0) {
                                console.log('File preview:', result.preview);
                                this.lastImportResult = {
                                    success: true,
                                    message: `File is valid and ready for import. Preview of first few rows:`,
                                    details: result.preview.map(row => `• ${row.name} (${row.email})`).join('\n')
                                };
                            }
                        } else {
                            this.showNotification(`❌ ${result.message}`, 'error');
                            
                            // Show detailed errors if available
                            if (result.errors && result.errors.length > 0) {
                                this.lastImportResult = {
                                    success: false,
                                    message: result.message,
                                    details: result.errors.map(error => `Row ${error.row}: ${error.message}`).join('\n')
                                };
                            }
                        }
                    } catch (error) {
                        console.error('Validation error:', error);
                        this.showNotification('Failed to validate file', 'error');
                        this.lastImportResult = {
                            success: false,
                            message: 'Failed to validate file: ' + error.message
                        };
                    } finally {
                        this.validating = false;
                    }
                },

                async exportData(type) {
                    this.exporting = true;
                    this.lastExportResult = null;

                    try {
                        const formType = type === 'small-groups' ? 'smallGroups' : type;
                        const exportPayload = {};
                        
                        // Add filters based on export form
                        Object.entries(this.exportForms[formType]).forEach(([key, value]) => {
                            if (value) {
                                exportPayload[key] = value;
                            }
                        });

                        const response = await fetch(`/api/import-export/${type}/export`, {
                            method: 'POST',
                            credentials: 'include',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(exportPayload)
                        });

                        // Check if response is successful
                        if (response.ok) {
                            // Check if response is a file (binary) or JSON
                            const contentType = response.headers.get('content-type');
                            
                            if (contentType && contentType.includes('application/json')) {
                                // Handle JSON error response
                                const data = await response.json();
                                this.lastExportResult = data;
                                this.showNotification(data.message || 'Export failed. Please try again.', 'error');
                            } else {
                                // Handle file download
                                const blob = await response.blob();
                                const url = window.URL.createObjectURL(blob);
                                const a = document.createElement('a');
                                a.href = url;
                                
                                // Extract filename from response headers or create default
                                const contentDisposition = response.headers.get('content-disposition');
                                let filename = `${type}_export_${new Date().toISOString().split('T')[0]}.xlsx`;
                                
                                if (contentDisposition) {
                                    const filenameMatch = contentDisposition.match(/filename="(.+)"/);
                                    if (filenameMatch) {
                                        filename = filenameMatch[1];
                                    }
                                }
                                
                                a.download = filename;
                                document.body.appendChild(a);
                                a.click();
                                document.body.removeChild(a);
                                window.URL.revokeObjectURL(url);
                                
                                this.lastExportResult = {
                                    success: true,
                                    message: 'Export completed successfully!'
                                };
                                this.showNotification('Export completed successfully!', 'success');
                            }
                        } else {
                            // Handle HTTP error responses
                            try {
                                const data = await response.json();
                                this.lastExportResult = data;
                                this.showNotification(data.message || 'Export failed. Please try again.', 'error');
                            } catch (jsonError) {
                                this.lastExportResult = {
                                    success: false,
                                    message: `Export failed with status ${response.status}`
                                };
                                this.showNotification(`Export failed with status ${response.status}`, 'error');
                            }
                        }

                    } catch (error) {
                        console.error('Error exporting data:', error);
                        this.lastExportResult = {
                            success: false,
                            message: 'Network error. Please check your connection and try again.'
                        };
                        this.showNotification('Network error. Please try again.', 'error');
                    } finally {
                        this.exporting = false;
                    }
                },

                async cleanupOldFiles() {
                    try {
                        const response = await fetch('/api/import-export/cleanup-exports', {
                            method: 'DELETE',
                            credentials: 'include',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.showNotification(data.message || 'Old files cleaned up successfully!', 'success');
                            await this.loadStats(); // Reload stats
                        } else {
                            this.showNotification(data.message || 'Cleanup failed.', 'error');
                        }

                    } catch (error) {
                        console.error('Error cleaning up files:', error);
                        this.showNotification('Error cleaning up files.', 'error');
                    }
                },

                showNotification(message, type) {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: { message, type }
                    }));
                }
            }
        }
    </script>
</x-app-layout> 
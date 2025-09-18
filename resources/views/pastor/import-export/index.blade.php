<x-sidebar-layout title="Data Import & Export">
    <div class="flex justify-between items-center mb-6">
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

    <div x-data="importExportManager()">
        <!-- Import Section -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Import Data</h3>
            
            <!-- Import Tabs -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button @click="activeImportTab = 'members'" 
                            :class="activeImportTab === 'members' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-2 px-1 border-b-2 font-medium text-sm">
                        Members
                    </button>
                    <button @click="activeImportTab = 'small-groups'" 
                            :class="activeImportTab === 'small-groups' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-2 px-1 border-b-2 font-medium text-sm">
                        Small Groups
                    </button>
                    <button @click="activeImportTab = 'event-reports'" 
                            :class="activeImportTab === 'event-reports' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-2 px-1 border-b-2 font-medium text-sm">
                        Event Reports
                    </button>
                </nav>
            </div>

            <!-- Import Forms -->
            <div class="mt-6">
                <!-- Members Import -->
                <div x-show="activeImportTab === 'members'" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-md font-medium text-gray-900">Import Members</h4>
                        <button @click="downloadMembersTemplate()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                            Download Template
                        </button>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <p class="text-sm text-yellow-700">
                            Upload a CSV file with member data. All members will be automatically assigned to your branch.
                        </p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <input type="file" @change="handleFileSelect($event, 'members')" accept=".csv" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                        <button @click="importData('members')" :disabled="!files.members" class="bg-blue-500 hover:bg-blue-700 disabled:bg-gray-300 text-white font-bold py-2 px-4 rounded">
                            Import
                        </button>
                    </div>
                </div>

                <!-- Small Groups Import -->
                <div x-show="activeImportTab === 'small-groups'" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-md font-medium text-gray-900">Import Small Groups</h4>
                        <button @click="downloadSmallGroupsTemplate()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                            Download Template
                        </button>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <p class="text-sm text-yellow-700">
                            Upload a CSV file with small group data. All groups will be automatically assigned to your branch.
                        </p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <input type="file" @change="handleFileSelect($event, 'small-groups')" accept=".csv" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                        <button @click="importData('small-groups')" :disabled="!files['small-groups']" class="bg-blue-500 hover:bg-blue-700 disabled:bg-gray-300 text-white font-bold py-2 px-4 rounded">
                            Import
                        </button>
                    </div>
                </div>

                <!-- Event Reports Import -->
                <div x-show="activeImportTab === 'event-reports'" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-md font-medium text-gray-900">Import Event Reports</h4>
                        <button @click="downloadEventReportsTemplate()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                            Download Template
                        </button>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <p class="text-sm text-yellow-700">
                            Upload a CSV file with event report data. All reports will be automatically assigned to your branch.
                        </p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <input type="file" @change="handleFileSelect($event, 'event-reports')" accept=".csv" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                        <button @click="importData('event-reports')" :disabled="!files['event-reports']" class="bg-blue-500 hover:bg-blue-700 disabled:bg-gray-300 text-white font-bold py-2 px-4 rounded">
                            Import
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Section -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Export Data</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Members Export -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="text-md font-medium text-gray-900 mb-2">Members</h4>
                    <p class="text-sm text-gray-600 mb-4">Export all members from your branch</p>
                    <button @click="exportData('members')" class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Export Members
                    </button>
                </div>

                <!-- Small Groups Export -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="text-md font-medium text-gray-900 mb-2">Small Groups</h4>
                    <p class="text-sm text-gray-600 mb-4">Export all small groups from your branch</p>
                    <button @click="exportData('small-groups')" class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Export Small Groups
                    </button>
                </div>

                <!-- Event Reports Export -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="text-md font-medium text-gray-900 mb-2">Event Reports</h4>
                    <p class="text-sm text-gray-600 mb-4">Export all event reports from your branch</p>
                    <button @click="exportData('event-reports')" class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Export Reports
                    </button>
                </div>
            </div>
        </div>

        <!-- Progress Modal -->
        <div x-show="showProgressModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" x-transition>
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg font-medium text-gray-900" x-text="progressTitle"></h3>
                    <div class="mt-4">
                        <div class="bg-gray-200 rounded-full h-3">
                            <div class="bg-green-600 h-3 rounded-full transition-all duration-300" :style="`width: ${progressPercentage}%`"></div>
                        </div>
                        <p class="text-sm text-gray-500 mt-2" x-text="progressMessage"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Modal -->
        <div x-show="showStatsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" x-transition>
            <div class="relative top-20 mx-auto p-5 border w-3/4 max-w-4xl shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Import/Export Statistics</h3>
                    <button @click="showStatsModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4" x-html="statisticsContent">
                    <!-- Statistics will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function importExportManager() {
            return {
                activeImportTab: 'members',
                files: {},
                showProgressModal: false,
                showStatsModal: false,
                progressTitle: '',
                progressMessage: '',
                progressPercentage: 0,
                statisticsContent: '',

                handleFileSelect(event, type) {
                    this.files[type] = event.target.files[0];
                },

                async importData(type) {
                    if (!this.files[type]) return;

                    const formData = new FormData();
                    formData.append('file', this.files[type]);

                    this.showProgressModal = true;
                    this.progressTitle = `Importing ${type.replace('-', ' ')}...`;
                    this.progressMessage = 'Uploading file...';
                    this.progressPercentage = 0;

                    try {
                        const response = await fetch(`/api/import-export/${type}/import`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin',
                            body: formData
                        });

                        this.progressPercentage = 100;
                        this.progressMessage = 'Processing...';

                        const result = await response.json();
                        
                        setTimeout(() => {
                            this.showProgressModal = false;
                            if (result.success) {
                                this.showNotification(result.message || 'Import completed successfully!', 'success');
                                // Reset file input
                                this.files[type] = null;
                                document.querySelector(`input[type="file"]`).value = '';
                            } else {
                                this.showNotification(result.message || 'Import failed. Please try again.', 'error');
                            }
                        }, 1000);

                    } catch (error) {
                        this.showProgressModal = false;
                        this.showNotification('Import failed. Please try again.', 'error');
                        console.error('Import error:', error);
                    }
                },

                async exportData(type) {
                    this.showProgressModal = true;
                    this.progressTitle = `Exporting ${type.replace('-', ' ')}...`;
                    this.progressMessage = 'Preparing export...';
                    this.progressPercentage = 50;

                    try {
                        const response = await fetch(`/api/import-export/${type}/export`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'application/json'
                            },
                            credentials: 'same-origin'
                        });

                        if (response.ok) {
                            this.progressPercentage = 100;
                            this.progressMessage = 'Download starting...';
                            
                            const blob = await response.blob();
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.style.display = 'none';
                            a.href = url;
                            a.download = `${type}-export-${new Date().toISOString().split('T')[0]}.csv`;
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(url);

                            setTimeout(() => {
                                this.showProgressModal = false;
                                this.showNotification('Export completed successfully!', 'success');
                            }, 1000);
                        } else {
                            throw new Error('Export failed');
                        }
                    } catch (error) {
                        this.showProgressModal = false;
                        this.showNotification('Export failed. Please try again.', 'error');
                        console.error('Export error:', error);
                    }
                },

                async downloadMembersTemplate() {
                    await this.downloadTemplate('members');
                },

                async downloadSmallGroupsTemplate() {
                    await this.downloadTemplate('small-groups');
                },

                async downloadEventReportsTemplate() {
                    await this.downloadTemplate('event-reports');
                },

                async downloadTemplate(type) {
                    try {
                        const response = await fetch(`/api/import-export/${type}/import-template`, {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });

                        if (response.ok) {
                            const blob = await response.blob();
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.style.display = 'none';
                            a.href = url;
                            a.download = `${type}-import-template.csv`;
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(url);
                            
                            this.showNotification('Template downloaded successfully!', 'success');
                        } else {
                            throw new Error('Template download failed');
                        }
                    } catch (error) {
                        this.showNotification('Failed to download template. Please try again.', 'error');
                        console.error('Template download error:', error);
                    }
                },

                async cleanupOldFiles() {
                    try {
                        const response = await fetch('/api/import-export/cleanup', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });

                        const result = await response.json();
                        this.showNotification(result.message || 'Cleanup completed!', result.success ? 'success' : 'error');
                    } catch (error) {
                        this.showNotification('Cleanup failed. Please try again.', 'error');
                        console.error('Cleanup error:', error);
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
    @endpush
</x-sidebar-layout>

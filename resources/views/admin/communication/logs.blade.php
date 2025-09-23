<x-sidebar-layout title="Communication Logs">
    <div class="space-y-6" x-data="communicationLogs()">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Communication Logs</h1>
                    <p class="text-gray-600 mt-1">Track all sent messages and their delivery status.</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="showStatistics = !showStatistics" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <span x-text="showStatistics ? 'Hide Stats' : 'Show Stats'"></span>
                    </button>
                    <button @click="exportLogs()" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Export Logs
                    </button>
                </div>
            </div>
        </div>

        <!-- Branch Selection (for Super Admin) -->
        @if($isSuperAdmin ?? false)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
            <select x-model="selectedBranch" @change="loadLogs(); loadStatistics()" class="w-full border-gray-300 rounded-lg">
                <option value="">Select a branch...</option>
                <template x-for="branch in branches" :key="branch.id">
                    <option :value="branch.id" x-text="branch.name"></option>
                </template>
            </select>
        </div>
        @endif

        <!-- Statistics -->
        <div x-show="showStatistics" x-transition class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="text-2xl font-bold text-blue-600" x-text="statistics.total_messages || 0"></div>
                <div class="text-sm text-gray-600">Total Messages</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="text-2xl font-bold text-green-600" x-text="statistics.sent_messages || 0"></div>
                <div class="text-sm text-gray-600">Sent Successfully</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="text-2xl font-bold text-red-600" x-text="statistics.failed_messages || 0"></div>
                <div class="text-sm text-gray-600">Failed</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="text-2xl font-bold text-purple-600" x-text="(statistics.success_rate || 0) + '%'"></div>
                <div class="text-sm text-gray-600">Success Rate</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                    <select x-model="filters.type" @change="loadLogs()" class="w-full border-gray-300 rounded-lg">
                        <option value="">All Types</option>
                        <option value="email">Email</option>
                        <option value="sms">SMS</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select x-model="filters.status" @change="loadLogs()" class="w-full border-gray-300 rounded-lg">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="sent">Sent</option>
                        <option value="failed">Failed</option>
                        <option value="delivered">Delivered</option>
                        <option value="bounced">Bounced</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                    <input type="date" x-model="filters.date_from" @change="loadLogs()" 
                           class="w-full border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                    <input type="date" x-model="filters.date_to" @change="loadLogs()" 
                           class="w-full border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" x-model="filters.search" @input.debounce.300ms="loadLogs()" 
                           placeholder="Search recipient..." 
                           class="w-full border-gray-300 rounded-lg">
                </div>
                <div class="flex items-end">
                    <button @click="clearFilters()" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Logs List -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Communication Logs</h2>
            </div>
            
            <!-- Loading State -->
            <div x-show="loading" class="p-8 text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-gray-600">Loading logs...</p>
            </div>

            <!-- Logs Table -->
            <div x-show="!loading && logs.length > 0" class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Template</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sender</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="log in logs" :key="log.id">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" 
                                    x-text="new Date(log.created_at).toLocaleString()"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="log.type === 'email' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                                          class="px-2 py-1 text-xs font-medium rounded-full" x-text="log.type.toUpperCase()"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="log.recipient"></td>
                                <td class="px-6 py-4 max-w-xs">
                                    <div class="text-sm text-gray-900 truncate" x-text="log.subject || 'N/A'"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="log.template?.name || 'N/A'"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getStatusColor(log.status)"
                                          class="px-2 py-1 text-xs font-medium rounded-full capitalize" 
                                          x-text="log.status"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="log.user?.name || 'System'"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button @click="viewLog(log)" 
                                            class="text-blue-600 hover:text-blue-800">View</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div x-show="!loading && logs.length === 0" class="p-8 text-center">
                <p class="text-gray-500">No communication logs found.</p>
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

        <!-- View Log Modal -->
        <div x-show="showViewModal" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50" @click="showViewModal = false"></div>
                <div class="relative bg-white rounded-lg max-w-4xl w-full p-6 max-h-screen overflow-y-auto">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Message Details</h3>
                        <button @click="showViewModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div x-show="viewingLog">
                        <!-- Message Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Type</label>
                                    <span class="text-sm text-gray-900 capitalize" x-text="viewingLog?.type"></span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Recipient</label>
                                    <span class="text-sm text-gray-900" x-text="viewingLog?.recipient"></span>
                                </div>
                                <div x-show="viewingLog?.subject">
                                    <label class="block text-sm font-medium text-gray-700">Subject</label>
                                    <span class="text-sm text-gray-900" x-text="viewingLog?.subject"></span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Template</label>
                                    <span class="text-sm text-gray-900" x-text="viewingLog?.template?.name || 'N/A'"></span>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status</label>
                                    <span :class="getStatusColor(viewingLog?.status)"
                                          class="px-2 py-1 text-xs font-medium rounded-full capitalize" 
                                          x-text="viewingLog?.status"></span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Sender</label>
                                    <span class="text-sm text-gray-900" x-text="viewingLog?.user?.name || 'System'"></span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Created</label>
                                    <span class="text-sm text-gray-900" x-text="viewingLog?.created_at ? new Date(viewingLog.created_at).toLocaleString() : ''"></span>
                                </div>
                                <div x-show="viewingLog?.sent_at">
                                    <label class="block text-sm font-medium text-gray-700">Sent</label>
                                    <span class="text-sm text-gray-900" x-text="viewingLog?.sent_at ? new Date(viewingLog.sent_at).toLocaleString() : ''"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Error Message -->
                        <div x-show="viewingLog?.error_message" class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Error Message</label>
                            <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                                <span class="text-sm text-red-800" x-text="viewingLog?.error_message"></span>
                            </div>
                        </div>

                        <!-- Message Content -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                <pre class="text-sm text-gray-900 whitespace-pre-wrap" x-text="viewingLog?.content"></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function communicationLogs() {
            return {
                logs: [],
                branches: [],
                statistics: {},
                selectedBranch: @json($isSuperAdmin ? null : auth()->user()->getActiveBranchId()),
                loading: false,
                showStatistics: false,
                showViewModal: false,
                viewingLog: null,
                filters: {
                    type: '',
                    status: '',
                    date_from: '',
                    date_to: '',
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
                        await this.loadLogs();
                        await this.loadStatistics();
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

                async loadLogs(page = 1) {
                    if (!this.selectedBranch) return;

                    this.loading = true;
                    try {
                        const params = new URLSearchParams({
                            branch_id: this.selectedBranch,
                            page: page,
                            per_page: this.pagination.per_page,
                            ...Object.fromEntries(Object.entries(this.filters).filter(([_, v]) => v))
                        });

                        const response = await fetch(`/api/communication/logs?${params}`);
                        const data = await response.json();
                        
                        this.logs = data.logs || [];
                        this.pagination = data.pagination || this.pagination;
                    } catch (error) {
                        console.error('Failed to load logs:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async loadStatistics() {
                    if (!this.selectedBranch) return;

                    try {
                        const params = new URLSearchParams({
                            branch_id: this.selectedBranch,
                            ...Object.fromEntries(Object.entries(this.filters).filter(([_, v]) => v && ['date_from', 'date_to'].includes(_)))
                        });

                        const response = await fetch(`/api/communication/logs/statistics?${params}`);
                        const data = await response.json();
                        
                        this.statistics = data.statistics || {};
                    } catch (error) {
                        console.error('Failed to load statistics:', error);
                    }
                },

                getStatusColor(status) {
                    const colors = {
                        'pending': 'bg-yellow-100 text-yellow-800',
                        'sent': 'bg-green-100 text-green-800',
                        'failed': 'bg-red-100 text-red-800',
                        'delivered': 'bg-blue-100 text-blue-800',
                        'bounced': 'bg-orange-100 text-orange-800'
                    };
                    return colors[status] || 'bg-gray-100 text-gray-800';
                },

                clearFilters() {
                    this.filters = {
                        type: '',
                        status: '',
                        date_from: '',
                        date_to: '',
                        search: ''
                    };
                    this.loadLogs();
                    this.loadStatistics();
                },

                async viewLog(log) {
                    try {
                        const response = await fetch(`/api/communication/logs/${log.id}`);
                        const data = await response.json();
                        
                        this.viewingLog = data.log;
                        this.showViewModal = true;
                    } catch (error) {
                        alert('Failed to load log details: ' + error.message);
                    }
                },

                async exportLogs() {
                    if (!this.selectedBranch) {
                        alert('Please select a branch');
                        return;
                    }

                    try {
                        const params = new URLSearchParams({
                            branch_id: this.selectedBranch,
                            ...Object.fromEntries(Object.entries(this.filters).filter(([_, v]) => v))
                        });

                        const response = await fetch(`/api/communication/logs/export?${params}`);
                        const data = await response.json();
                        
                        if (response.ok) {
                            // Convert data to CSV
                            const csvContent = this.convertToCSV(data.data);
                            this.downloadCSV(csvContent, data.filename || 'communication_logs.csv');
                        } else {
                            alert('Error: ' + (data.error || 'Failed to export logs'));
                        }
                    } catch (error) {
                        alert('Failed to export logs: ' + error.message);
                    }
                },

                convertToCSV(data) {
                    if (!data.length) return '';
                    
                    const headers = Object.keys(data[0]);
                    const csvArray = [headers.join(',')];
                    
                    data.forEach(row => {
                        const values = headers.map(header => {
                            const value = row[header] || '';
                            return `"${value.toString().replace(/"/g, '""')}"`;
                        });
                        csvArray.push(values.join(','));
                    });
                    
                    return csvArray.join('\n');
                },

                downloadCSV(csvContent, filename) {
                    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement('a');
                    const url = URL.createObjectURL(blob);
                    link.setAttribute('href', url);
                    link.setAttribute('download', filename);
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },

                changePage(page) {
                    if (page >= 1 && page <= this.pagination.last_page) {
                        this.loadLogs(page);
                    }
                }
            }
        }
    </script>
</x-sidebar-layout>

<x-sidebar-layout title="Guest Management">
    <div class="space-y-6" x-data="guestManagement()" x-init="showImportModal = false; importFile = null; importing = false; importResult = null; showEmailModal = false; sendingEmails = false; emailResult = null; selectedGuests = []; sendToAll = false">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Guest Management</h1>
                    <p class="text-gray-600 mt-1">View and manage guests registered via the public registration form</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('guests.attempts') }}"
                       class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg transition-colors">
                        View Registration Attempts
                    </a>
                    <button @click="showImportModal = true" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Import Guests
                    </button>
                    <button @click="showEmailModal = true" 
                            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Send Account Setup Emails
                    </button>
                    <a :href="`{{ route('guests.export') }}?format=csv&${getFilterParams()}`" 
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Export CSV
                    </a>
                    <a :href="`{{ route('guests.export') }}?format=xlsx&${getFilterParams()}`" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Export Excel
                    </a>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <form @submit.prevent="applyFilters()" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" 
                               x-model="filters.search" 
                               placeholder="Name, email, or phone..."
                               class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Date From -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Registration From</label>
                        <input type="date" 
                               x-model="filters.date_from" 
                               class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Registration To</label>
                        <input type="date" 
                               x-model="filters.date_to" 
                               class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Staying Intention -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Staying Intention</label>
                        <select x-model="filters.staying_intention" 
                                class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All</option>
                            <option value="yes-for-sure">Yes, For Sure</option>
                            <option value="visit-when-in-town">Visit When In Town</option>
                            <option value="just-visiting">Just Visiting</option>
                            <option value="weighing-options">Weighing Options</option>
                        </select>
                    </div>

                    <!-- Discovery Source -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Discovery Source</label>
                        <select x-model="filters.discovery_source" 
                                class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All</option>
                            <option value="social-media">Social Media</option>
                            <option value="word-of-mouth">Word of Mouth</option>
                            <option value="billboard">Billboard</option>
                            <option value="email">Email</option>
                            <option value="website">Website</option>
                            <option value="promotional-material">Promotional Material</option>
                            <option value="radio-tv">Radio/TV</option>
                            <option value="outreach">Outreach</option>
                        </select>
                    </div>

                    <!-- Gender -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                        <select x-model="filters.gender" 
                                class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="prefer-not-to-say">Prefer Not to Say</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <button type="button" 
                            @click="clearFilters()" 
                            class="text-gray-600 hover:text-gray-800 text-sm">
                        Clear Filters
                    </button>
                    <button type="submit" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Guests Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staying Intention</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($guests as $guest)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $guest->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $guest->email ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $guest->phone ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $guest->branch->name ?? 'N/A' }}</div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $guest->created_at->format('M d, Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        @if($guest->staying_intention)
                                            {{ ucfirst(str_replace('-', ' ', $guest->staying_intention)) }}
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('guests.show', $guest) }}" 
                                       class="text-indigo-600 hover:text-indigo-900">View Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No guests found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($guests->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $guests->links() }}
                </div>
            @endif
        </div>

        <!-- Import Modal -->
        <div x-show="showImportModal" 
             x-cloak
             class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
             @click.self="showImportModal = false">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Import Guests</h3>
                    
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700">Select File</label>
                            <a href="{{ route('guests.template') }}" 
                               class="text-xs text-indigo-600 hover:text-indigo-800 underline">
                                Download Template
                            </a>
                        </div>
                        <input type="file" 
                               @change="importFile = $event.target.files[0]"
                               accept=".xlsx,.xls,.csv"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="mt-2 text-xs text-gray-500">Supported formats: Excel (.xlsx, .xls) or CSV. Max size: 10MB</p>
                    </div>

                    @if($isSuperAdmin)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                        <select x-model="importBranchId" 
                                class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Branch</option>
                            @foreach(\App\Models\Branch::all() as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Import Result Messages -->
                    <div x-show="importResult" class="mb-4">
                        <div x-show="importResult && importResult.success" 
                             class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                            <p class="font-medium">Success!</p>
                            <p x-text="importResult && importResult.message ? importResult.message : ''" class="text-sm mt-1"></p>
                            <div x-show="importResult && importResult.summary" class="mt-2 text-xs space-y-1">
                                <p>Processed: <span x-text="importResult && importResult.summary ? importResult.summary.total_processed : 0"></span></p>
                                <p>Successful: <span x-text="importResult && importResult.summary ? importResult.summary.successful_imports : 0"></span></p>
                                <p>Failed: <span x-text="importResult && importResult.summary ? importResult.summary.failed_imports : 0"></span></p>
                                <p x-show="importResult && importResult.summary && importResult.summary.account_setup_emails_scheduled">
                                    Account Setup Emails Scheduled: <span x-text="importResult && importResult.summary ? importResult.summary.account_setup_emails_scheduled : 0"></span>
                                </p>
                            </div>
                            <div x-show="importResult && importResult.summary && importResult.summary.account_setup_emails_scheduled > 0" class="mt-3">
                                <button @click="showEmailModal = true; showImportModal = false" 
                                        class="text-xs bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded">
                                    Send Account Setup Emails Now
                                </button>
                            </div>
                        </div>
                        <div x-show="importResult && !importResult.success" 
                             class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                            <p class="font-medium">Error!</p>
                            <p x-text="importResult && importResult.message ? importResult.message : 'An unknown error occurred'" class="text-sm mt-1"></p>
                        </div>
                        
                        <!-- Duplicate Comparison Details -->
                        <div x-show="importResult && importResult.summary && importResult.summary.errors && importResult.summary.errors.length > 0" class="mt-4 max-h-60 overflow-y-auto">
                            <template x-for="(error, index) in (importResult && importResult.summary && importResult.summary.errors ? importResult.summary.errors : [])" :key="index">
                                <div x-show="error.type === 'duplicate' && error.comparison" 
                                     class="bg-yellow-50 border border-yellow-200 rounded p-3 mb-2 text-xs">
                                    <p class="font-medium text-yellow-800 mb-2">Row <span x-text="error.row"></span>: Duplicate Found</p>
                                    <p class="text-yellow-700 mb-2" x-text="error.message"></p>
                                    <div x-show="error.comparison.differences && error.comparison.differences.length > 0" class="mt-2">
                                        <p class="font-medium text-yellow-800 mb-1">Differences:</p>
                                        <template x-for="diff in error.comparison.differences" :key="diff.field">
                                            <div class="ml-2 mb-1">
                                                <span class="font-medium" x-text="diff.field"></span>:
                                                <span class="text-red-600">Existing: <span x-text="diff.existing || 'N/A'"></span></span> →
                                                <span class="text-green-600">Imported: <span x-text="diff.imported || 'N/A'"></span></span>
                                            </div>
                                        </template>
                                    </div>
                                    <div x-show="error.comparison.matches && error.comparison.matches.length > 0" class="mt-2">
                                        <p class="font-medium text-yellow-800 mb-1">Matching Fields:</p>
                                        <span x-for="match in error.comparison.matches" 
                                              class="inline-block bg-green-100 text-green-700 px-2 py-1 rounded mr-1 mb-1 text-xs">
                                            <span x-text="match.field"></span>
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button @click="showImportModal = false; importFile = null; importResult = null" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                            Close
                        </button>
                        <button @click="importGuests()" 
                                :disabled="!canImport()"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors">
                            <span x-show="!importing">Import</span>
                            <span x-show="importing">Importing...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Send Account Setup Emails Modal -->
        <div x-show="showEmailModal" 
             x-cloak
             class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
             @click.self="showEmailModal = false">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Send Account Setup Emails</h3>
                    
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   x-model="sendToAll"
                                   class="mr-2">
                            <span class="text-sm text-gray-700">Send to all guests (with email addresses)</span>
                        </label>
                        <p class="mt-2 text-xs text-gray-500">Note: Guests with temporary email addresses (@church.local) will be skipped.</p>
                    </div>

                    <!-- Email Result Messages -->
                    <div x-show="emailResult" class="mb-4">
                        <div x-show="emailResult && emailResult.success" 
                             class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                            <p class="font-medium">Success!</p>
                            <p x-text="emailResult && emailResult.message ? emailResult.message : ''" class="text-sm mt-1"></p>
                            <div x-show="emailResult && emailResult.data" class="mt-2 text-xs">
                                <p>Total: <span x-text="emailResult && emailResult.data ? emailResult.data.total : 0"></span></p>
                                <p>Sent: <span x-text="emailResult && emailResult.data ? emailResult.data.sent : 0"></span></p>
                                <p>Skipped: <span x-text="emailResult && emailResult.data ? emailResult.data.skipped : 0"></span></p>
                                <p>Failed: <span x-text="emailResult && emailResult.data ? emailResult.data.failed : 0"></span></p>
                            </div>
                        </div>
                        <div x-show="emailResult && !emailResult.success" 
                             class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                            <p class="font-medium">Error!</p>
                            <p x-text="emailResult && emailResult.message ? emailResult.message : 'An unknown error occurred'" class="text-sm mt-1"></p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button @click="showEmailModal = false; emailResult = null; sendToAll = false" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                            Close
                        </button>
                        <button @click="sendAccountSetupEmails()" 
                                :disabled="sendingEmails"
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors">
                            <span x-show="!sendingEmails">Send Emails</span>
                            <span x-show="sendingEmails">Sending...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function guestManagement() {
            return {
                showImportModal: false,
                importFile: null,
                importing: false,
                importResult: null,
                importBranchId: @js($isSuperAdmin ? '' : ($branchId ?? '')),
                filters: {
                    search: @js($filters['search'] ?? ''),
                    date_from: @js($filters['date_from'] ?? ''),
                    date_to: @js($filters['date_to'] ?? ''),
                    staying_intention: @js($filters['staying_intention'] ?? ''),
                    discovery_source: @js($filters['discovery_source'] ?? ''),
                    gender: @js($filters['gender'] ?? ''),
                },
                canImport() {
                    if (!this.importFile || this.importing) {
                        return false;
                    }
                    @if($isSuperAdmin)
                    if (!this.importBranchId || this.importBranchId === '') {
                        return false;
                    }
                    @endif
                    return true;
                },
                async importGuests() {
                    if (!this.importFile) return;
                    @if($isSuperAdmin)
                    if (!this.importBranchId) {
                        alert('Please select a branch');
                        return;
                    }
                    @endif

                    this.importing = true;
                    this.importResult = null;

                    const formData = new FormData();
                    formData.append('file', this.importFile);
                    @if($isSuperAdmin)
                    formData.append('branch_id', this.importBranchId);
                    @endif

                    try {
                        const response = await fetch('{{ route('guests.import') }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                        });

                        let data;
                        const contentType = response.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) {
                            data = await response.json();
                        } else {
                            // If response is not JSON, get text and try to parse
                            const text = await response.text();
                            try {
                                data = JSON.parse(text);
                            } catch (e) {
                                throw new Error('Server returned an invalid response');
                            }
                        }
                        
                        // Handle both success and error responses
                        if (!response.ok) {
                            // Handle validation errors or other error responses
                            this.importResult = {
                                success: false,
                                message: data.message || 'Import failed. Please check the file and try again.',
                                errors: data.errors || {},
                                summary: data.summary || null
                            };
                        } else {
                            this.importResult = data;
                            
                            if (data.success) {
                                // Reload page after 2 seconds to show imported guests
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            }
                        }
                    } catch (error) {
                        this.importResult = {
                            success: false,
                            message: 'An error occurred during import: ' + error.message
                        };
                    } finally {
                        this.importing = false;
                    }
                },
                async sendAccountSetupEmails() {
                    this.sendingEmails = true;
                    this.emailResult = null;

                    const payload = {
                        send_to_all: this.sendToAll,
                        @if($isSuperAdmin)
                        branch_id: this.importBranchId || null,
                        @endif
                    };

                    if (!this.sendToAll && this.selectedGuests.length === 0) {
                        this.emailResult = {
                            success: false,
                            message: 'Please select guests or choose "Send to all"'
                        };
                        this.sendingEmails = false;
                        return;
                    }

                    if (!this.sendToAll) {
                        payload.member_ids = this.selectedGuests;
                    }

                    try {
                        const response = await fetch('{{ route('guests.send-setup-emails') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify(payload),
                        });

                        const data = await response.json();
                        this.emailResult = data;
                    } catch (error) {
                        this.emailResult = {
                            success: false,
                            message: 'An error occurred while sending emails: ' + error.message
                        };
                    } finally {
                        this.sendingEmails = false;
                    }
                },
                applyFilters() {
                    const params = new URLSearchParams();
                    Object.keys(this.filters).forEach(key => {
                        if (this.filters[key]) {
                            params.append(key, this.filters[key]);
                        }
                    });
                    window.location.href = '{{ route('guests.index') }}?' + params.toString();
                },
                clearFilters() {
                    this.filters = {
                        search: '',
                        date_from: '',
                        date_to: '',
                        staying_intention: '',
                        discovery_source: '',
                        gender: '',
                    };
                    window.location.href = '{{ route('guests.index') }}';
                },
                getFilterParams() {
                    const params = new URLSearchParams();
                    Object.keys(this.filters).forEach(key => {
                        if (this.filters[key]) {
                            params.append(key, this.filters[key]);
                        }
                    });
                    return params.toString();
                }
            }
        }
    </script>
</x-sidebar-layout>



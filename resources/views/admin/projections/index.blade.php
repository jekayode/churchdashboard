<x-sidebar-layout title="Branch Projections Management">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h1 class="text-3xl font-bold leading-7 text-gray-900 sm:text-4xl sm:truncate">
                    Branch Projections Management
                </h1>
                <p class="mt-2 text-lg text-gray-600">
                    Manage yearly projections and track performance across all branches
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <button type="button" onclick="openCreateModal()" 
                        class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create Projection
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Projections</dt>
                                <dd class="text-lg font-medium text-gray-900" id="stat-total">Loading...</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Approved</dt>
                                <dd class="text-lg font-medium text-gray-900" id="stat-approved">Loading...</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Pending Review</dt>
                                <dd class="text-lg font-medium text-gray-900" id="stat-pending">Loading...</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Branches Covered</dt>
                                <dd class="text-lg font-medium text-gray-900" id="stat-branches">Loading...</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Filters</h3>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label for="filter-year" class="block text-sm font-medium text-gray-700">Year</label>
                        <select id="filter-year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">All Years</option>
                            <!-- Years will be populated by JavaScript -->
                        </select>
                    </div>

                    <div>
                        <label for="filter-status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="filter-status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="in_review">In Review</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    @if(Auth::user()->isSuperAdmin())
                    <div>
                        <label for="filter-branch" class="block text-sm font-medium text-gray-700">Branch</label>
                        <select id="filter-branch" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">All Branches</option>
                            <!-- Branches will be populated by JavaScript -->
                        </select>
                    </div>
                    @endif

                    <div>
                        <label for="filter-current-year" class="block text-sm font-medium text-gray-700">Current Year Status</label>
                        <select id="filter-current-year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">All</option>
                            <option value="1">Current Year</option>
                            <option value="0">Not Current Year</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex justify-end space-x-3">
                    <button type="button" onclick="clearFilters()" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Clear Filters
                    </button>
                    <button type="button" onclick="loadProjections()" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Projections Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Branch Projections</h3>
            </div>
            
            <div id="loading-spinner" class="flex items-center justify-center py-12">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                <span class="ml-2 text-sm text-gray-500">Loading projections...</span>
            </div>

            <div id="projections-content" class="hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Branch & Year
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Targets
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Creator
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Updated
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="projections-table-body" class="bg-white divide-y divide-gray-200">
                            <!-- Projections will be populated here -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div id="pagination-container" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <!-- Pagination will be populated here -->
                </div>
            </div>

            <div id="empty-state" class="hidden text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No projections found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new projection.</p>
                <div class="mt-6">
                    <button type="button" onclick="openCreateModal()" 
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Projection
                    </button>
                </div>
            </div>
        </div>
    </div>

<!-- Create/Edit Projection Modal -->
<div id="projection-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900" id="modal-title">Create New Projection</h3>
                <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="projection-form" class="mt-6">
                <input type="hidden" id="projection-id" name="projection_id">
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch *</label>
                        <select id="branch_id" name="branch_id" required 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Select a branch</option>
                        </select>
                        @if(!Auth::user()->isSuperAdmin())
                        <p class="mt-1 text-sm text-gray-500">Your assigned branch will be automatically selected</p>
                        @endif
                        <div id="branch_id-error" class="mt-1 text-sm text-red-600 hidden"></div>
                    </div>

                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700">Year *</label>
                        <input type="number" id="year" name="year" required min="2020" max="2035"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <div id="year-error" class="mt-1 text-sm text-red-600 hidden"></div>
                    </div>
                </div>

                <div class="mt-6">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Yearly Targets</h4>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label for="attendance_target" class="block text-sm font-medium text-gray-700">Attendance Target *</label>
                            <input type="number" id="attendance_target" name="attendance_target" required min="1" max="10000"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <div id="attendance_target-error" class="mt-1 text-sm text-red-600 hidden"></div>
                        </div>

                        <div>
                            <label for="converts_target" class="block text-sm font-medium text-gray-700">Converts Target *</label>
                            <input type="number" id="converts_target" name="converts_target" required min="0" max="1000"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <div id="converts_target-error" class="mt-1 text-sm text-red-600 hidden"></div>
                        </div>

                        <div>
                            <label for="leaders_target" class="block text-sm font-medium text-gray-700">Leaders Target *</label>
                            <input type="number" id="leaders_target" name="leaders_target" required min="0" max="500"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <div id="leaders_target-error" class="mt-1 text-sm text-red-600 hidden"></div>
                        </div>

                        <div>
                            <label for="volunteers_target" class="block text-sm font-medium text-gray-700">Volunteers Target *</label>
                            <input type="number" id="volunteers_target" name="volunteers_target" required min="0" max="1000"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <div id="volunteers_target-error" class="mt-1 text-sm text-red-600 hidden"></div>
                        </div>
                    </div>
                </div>

                <!-- Quarterly Breakdown -->
                <div class="mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-md font-medium text-gray-900">Quarterly Breakdown</h4>
                        <button type="button" onclick="autoDistributeTargets()" 
                                class="text-sm text-indigo-600 hover:text-indigo-500">
                            Auto Distribute Targets
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-4" id="quarterly-breakdown">
                        <!-- Quarterly inputs will be generated by JavaScript -->
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </button>
                    <button type="submit" id="save-button"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span id="save-button-text">Create Projection</span>
                        <div id="save-spinner" class="hidden ml-2">
                            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                        </div>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Action Modals -->
<!-- Approval Modal -->
<div id="approval-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Approve Projection</h3>
            <form id="approval-form">
                <input type="hidden" id="approve-projection-id">
                <div class="mb-4">
                    <label for="approval_notes" class="block text-sm font-medium text-gray-700">Approval Notes (Optional)</label>
                    <textarea id="approval_notes" name="approval_notes" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                              placeholder="Add any notes about this approval..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeApprovalModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                        Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejection-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Projection</h3>
            <form id="rejection-form">
                <input type="hidden" id="reject-projection-id">
                <div class="mb-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Rejection Reason *</label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="3" required
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                              placeholder="Please provide a reason for rejecting this projection..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeRejectionModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                        Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Notification System -->
<div id="notification" class="hidden fixed top-4 right-4 z-50">
    <div class="max-w-md w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div id="notification-icon"></div>
                </div>
                <div class="ml-3 flex-1 pt-0.5">
                    <p class="text-sm font-medium text-gray-900" id="notification-title"></p>
                    <p class="mt-1 text-sm text-gray-500 break-words" id="notification-message"></p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button onclick="hideNotification()" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let currentProjections = [];
let currentPage = 1;
let editingProjectionId = null;
const isSuperAdmin = {{ Auth::user()->isSuperAdmin() ? 'true' : 'false' }};
const userBranchId = {{ Auth::user()->getActiveBranchId() ?? 'null' }};
const userBranchName = @json(Auth::user()->getPrimaryBranch()?->name ?? null);

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    initializePage();
});

async function initializePage() {
    try {
        await Promise.all([
            loadBranches(),
            loadProjections(),
            loadStatistics(),
            populateYearFilters()
        ]);
    } catch (error) {
        console.error('Error initializing page:', error);
        showNotification('Error', 'Failed to load initial data', 'error');
    }
}

// Load functions
async function loadBranches() {
    try {
        if (isSuperAdmin) {
            // Super admin can see all branches
            const response = await fetch('/api/projections/branches/available', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            if (!response.ok) throw new Error('Failed to load branches');
            
            const data = await response.json();
            
            if (data.success) {
                populateBranchSelects(data.data);
            }
        } else if (userBranchId && userBranchName) {
            // Branch pastor can only see their own branch
            const branchData = [{ id: userBranchId, name: userBranchName }];
            populateBranchSelects(branchData);
        }
    } catch (error) {
        console.error('Error loading branches:', error);
    }
}

async function loadProjections() {
    showLoadingSpinner();
    
    try {
        const params = new URLSearchParams({
            page: currentPage,
            year: document.getElementById('filter-year').value,
            status: document.getElementById('filter-status').value,
            is_current_year: document.getElementById('filter-current-year').value,
        });
        
        if (isSuperAdmin && document.getElementById('filter-branch').value) {
            params.append('branch_id', document.getElementById('filter-branch').value);
        }
        
        console.log('Loading projections with params:', params.toString());
        
        const response = await fetch(`/api/projections?${params}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Response error:', errorText);
            throw new Error(`HTTP ${response.status}: ${response.statusText}. ${errorText}`);
        }
        
        const data = await response.json();
        console.log('Response data:', data);
        
        if (data.success) {
            currentProjections = data.data.data || [];
            hideLoadingSpinner();
            renderProjectionsTable();
            renderPagination(data.data);
        } else {
            throw new Error(data.message || 'Failed to load projections');
        }
    } catch (error) {
        console.error('Error loading projections:', error);
        hideLoadingSpinner();
        showEmptyState();
        showNotification('Error', `Failed to load projections: ${error.message}`, 'error');
    }
}

async function loadStatistics() {
    try {
        const response = await fetch('/api/projections/statistics', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        if (!response.ok) throw new Error('Failed to load statistics');
        
        const data = await response.json();
        
        if (data.success) {
            const stats = data.data;
            document.getElementById('stat-total').textContent = stats.total || 0;
            document.getElementById('stat-approved').textContent = stats.approved || 0;
            document.getElementById('stat-pending').textContent = stats.pending || 0;
            document.getElementById('stat-branches').textContent = stats.branches_covered || 0;
        }
    } catch (error) {
        console.error('Error loading statistics:', error);
    }
}

function populateYearFilters() {
    const currentYear = new Date().getFullYear();
    const yearSelect = document.getElementById('filter-year');
    
    for (let year = currentYear - 5; year <= currentYear + 5; year++) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        if (year === currentYear) option.selected = true;
        yearSelect.appendChild(option);
    }
}

function populateBranchSelects(branches) {
    const selects = ['filter-branch', 'branch_id'];
    
    selects.forEach(selectId => {
        const select = document.getElementById(selectId);
        if (!select) return;
        
        // Clear existing options except the first one
        while (select.children.length > 1) {
            select.removeChild(select.lastChild);
        }
        
        branches.forEach(branch => {
            const option = document.createElement('option');
            option.value = branch.id;
            option.textContent = branch.name;
            select.appendChild(option);
        });
    });
}

function showLoadingSpinner() {
    document.getElementById('loading-spinner').classList.remove('hidden');
    document.getElementById('projections-content').classList.add('hidden');
    document.getElementById('empty-state').classList.add('hidden');
}

function hideLoadingSpinner() {
    document.getElementById('loading-spinner').classList.add('hidden');
    
    if (currentProjections.length > 0) {
        document.getElementById('projections-content').classList.remove('hidden');
        document.getElementById('empty-state').classList.add('hidden');
    } else {
        showEmptyState();
    }
}

function showEmptyState() {
    document.getElementById('loading-spinner').classList.add('hidden');
    document.getElementById('projections-content').classList.add('hidden');
    document.getElementById('empty-state').classList.remove('hidden');
}

function renderProjectionsTable() {
    const tbody = document.getElementById('projections-table-body');
    tbody.innerHTML = '';
    
    currentProjections.forEach(projection => {
        const row = createProjectionRow(projection);
        tbody.appendChild(row);
    });
}

function createProjectionRow(projection) {
    const row = document.createElement('tr');
    row.className = 'hover:bg-gray-50';
    
    row.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-gray-900">${projection.branch.name}</div>
            <div class="text-sm text-gray-500">${projection.year}</div>
            ${projection.is_current_year ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 mt-1">Current Year</span>' : ''}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>Attendance: <span class="font-medium">${formatNumber(projection.attendance_target)}</span></div>
                    <div>Converts: <span class="font-medium">${formatNumber(projection.converts_target)}</span></div>
                    <div>Leaders: <span class="font-medium">${formatNumber(projection.leaders_target)}</span></div>
                    <div>Volunteers: <span class="font-medium">${formatNumber(projection.volunteers_target)}</span></div>
                </div>
            </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            ${getStatusBadge(projection.status)}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            ${projection.creator ? projection.creator.name : 'System'}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            ${formatDate(projection.updated_at)}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            ${createActionDropdown(projection)}
        </td>
    `;
    
    return row;
}

function getStatusBadge(status) {
    const badges = {
        'draft': 'bg-gray-100 text-gray-800',
        'in_review': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800'
    };
    
    const displayText = status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
    const badgeClass = badges[status] || 'bg-gray-100 text-gray-800';
    
    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass}">${displayText}</span>`;
}

function createActionDropdown(projection) {
    // Super admins can edit ALL projections, branch pastors can only edit draft/rejected
    const canEdit = isSuperAdmin || (projection.status === 'draft' || projection.status === 'rejected');
    const canApprove = isSuperAdmin && projection.status === 'in_review';
    const canSubmit = projection.status === 'draft' || projection.status === 'rejected';
    const canSetCurrent = isSuperAdmin && projection.status === 'approved' && !projection.is_current_year;
    
    return `
        <div class="relative inline-block text-left">
            <button type="button" onclick="toggleDropdown(event)" 
                    class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none">
                Actions
                <svg class="-mr-1 ml-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
            <div class="dropdown-menu hidden origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                <div class="py-1">
                    <button onclick="openEditModal(${projection.id})" 
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                        View Details
                    </button>
                    ${canEdit ? `
                    <button onclick="openEditModal(${projection.id})" 
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                        Edit Projection
                    </button>` : ''}
                    ${canSubmit ? `
                    <button onclick="submitForReview(${projection.id})" 
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                        Submit for Review
                    </button>` : ''}
                    ${canApprove ? `
                    <div class="border-t border-gray-100"></div>
                    <button onclick="openApprovalModal(${projection.id})" 
                            class="block px-4 py-2 text-sm text-green-700 hover:bg-green-50 w-full text-left">
                        Approve
                    </button>
                    <button onclick="openRejectionModal(${projection.id})" 
                            class="block px-4 py-2 text-sm text-red-700 hover:bg-red-50 w-full text-left">
                        Reject
                    </button>` : ''}
                    ${canSetCurrent ? `
                    <div class="border-t border-gray-100"></div>
                    <button onclick="setCurrentYear(${projection.id})" 
                            class="block px-4 py-2 text-sm text-blue-700 hover:bg-blue-50 w-full text-left">
                        Set as Current Year
                    </button>` : ''}
                </div>
            </div>
        </div>
    `;
}

function renderPagination(data) {
    const container = document.getElementById('pagination-container');
    
    if (!data.links || data.last_page <= 1) {
        container.classList.add('hidden');
        return;
    }
    
    container.classList.remove('hidden');
    container.innerHTML = `
        <div class="flex-1 flex justify-between sm:hidden">
            <button onclick="changePage(${data.current_page - 1})" 
                    ${data.current_page === 1 ? 'disabled' : ''}
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                Previous
            </button>
            <button onclick="changePage(${data.current_page + 1})" 
                    ${data.current_page === data.last_page ? 'disabled' : ''}
                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                Next
            </button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium">${data.from || 0}</span> to <span class="font-medium">${data.to || 0}</span> of <span class="font-medium">${data.total}</span> results
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    ${data.links.map(link => createPaginationLink(link)).join('')}
                </nav>
            </div>
        </div>
    `;
}

function createPaginationLink(link) {
    let page = 1;
    
    if (link.url) {
        try {
            const url = new URL(link.url, window.location.origin);
            page = url.searchParams.get('page') || 1;
        } catch (e) {
            console.warn('Invalid pagination URL:', link.url);
        }
    }
    
    if (link.label.includes('Previous')) {
        return `
            <button onclick="changePage(${parseInt(page)})" 
                    ${!link.url ? 'disabled' : ''}
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
            </button>
        `;
    }
    
    if (link.label.includes('Next')) {
        return `
            <button onclick="changePage(${parseInt(page)})" 
                    ${!link.url ? 'disabled' : ''}
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
            </button>
        `;
    }
    
    const isActive = link.active;
    const activeClasses = isActive 
        ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' 
        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50';
    
    return `
        <button onclick="changePage(${parseInt(page)})" 
                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium ${activeClasses}">
            ${link.label}
        </button>
    `;
}

// Modal functions
function openCreateModal() {
    editingProjectionId = null;
    document.getElementById('modal-title').textContent = 'Create New Projection';
    document.getElementById('save-button-text').textContent = 'Create Projection';
    document.getElementById('projection-form').reset();
    
    // Pre-select branch for branch pastors
    if (!isSuperAdmin && userBranchId) {
        document.getElementById('branch_id').value = userBranchId;
        // Disable branch selection for branch pastors
        document.getElementById('branch_id').disabled = true;
    } else {
        // Enable branch selection for super admins
        document.getElementById('branch_id').disabled = false;
    }
    
    generateQuarterlyInputs();
    document.getElementById('projection-modal').classList.remove('hidden');
}

async function openEditModal(projectionId) {
    try {
        const response = await fetch(`/api/projections/${projectionId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        if (!response.ok) throw new Error('Failed to load projection');
        
        const data = await response.json();
        
        if (data.success) {
            const projection = data.data;
            editingProjectionId = projectionId;
            
            document.getElementById('modal-title').textContent = 'Edit Projection';
            document.getElementById('save-button-text').textContent = 'Update Projection';
            
            // Populate form fields
            document.getElementById('projection-id').value = projection.id;
            document.getElementById('branch_id').value = projection.branch_id;
            document.getElementById('year').value = projection.year;
            document.getElementById('attendance_target').value = projection.attendance_target;
            document.getElementById('converts_target').value = projection.converts_target;
            document.getElementById('leaders_target').value = projection.leaders_target;
            document.getElementById('volunteers_target').value = projection.volunteers_target;
            
            // Handle branch selection for edit mode
            if (!isSuperAdmin && userBranchId) {
                // Disable branch selection for branch pastors in edit mode
                document.getElementById('branch_id').disabled = true;
            } else {
                // Enable branch selection for super admins in edit mode
                document.getElementById('branch_id').disabled = false;
            }
            
            generateQuarterlyInputs(projection);
            document.getElementById('projection-modal').classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error loading projection:', error);
        showNotification('Error', 'Failed to load projection details', 'error');
    }
}

function closeModal() {
    document.getElementById('projection-modal').classList.add('hidden');
    clearFormErrors();
}

function generateQuarterlyInputs(projection = null) {
    const container = document.getElementById('quarterly-breakdown');
    const quarters = ['Q1', 'Q2', 'Q3', 'Q4'];
    const targets = ['attendance', 'converts', 'leaders', 'volunteers'];
    
    container.innerHTML = '';
    
    quarters.forEach((quarter, qIndex) => {
        const quarterDiv = document.createElement('div');
        quarterDiv.className = 'border border-gray-200 rounded-lg p-4';
        
        let quarterHtml = `<h5 class="text-sm font-medium text-gray-900 mb-3">${quarter}</h5>`;
        
        targets.forEach(target => {
            const fieldName = `quarterly_${target}[${qIndex}]`;
            const value = projection && projection[`quarterly_${target}`] 
                ? (projection[`quarterly_${target}`][qIndex] || '') 
                : '';
            
            quarterHtml += `
                <div class="mb-2">
                    <label class="block text-xs font-medium text-gray-700 capitalize">${target}</label>
                    <input type="number" name="${fieldName}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" 
                           value="${value}" min="0">
                </div>
            `;
        });
        
        quarterDiv.innerHTML = quarterHtml;
        container.appendChild(quarterDiv);
    });
}

function autoDistributeTargets() {
    const attendance = parseInt(document.getElementById('attendance_target').value) || 0;
    const converts = parseInt(document.getElementById('converts_target').value) || 0;
    const leaders = parseInt(document.getElementById('leaders_target').value) || 0;
    const volunteers = parseInt(document.getElementById('volunteers_target').value) || 0;
    
    if (!attendance && !converts && !leaders && !volunteers) {
        showNotification('Warning', 'Please enter yearly targets first', 'warning');
        return;
    }
    
    // Simple distribution: 20%, 25%, 30%, 25%
    const distribution = [0.20, 0.25, 0.30, 0.25];
    const targets = { attendance, converts, leaders, volunteers };
    
    Object.keys(targets).forEach(target => {
        distribution.forEach((percentage, index) => {
            const input = document.querySelector(`input[name="quarterly_${target}[${index}]"]`);
            if (input) {
                input.value = Math.round(targets[target] * percentage);
            }
        });
    });
    
    showNotification('Success', 'Targets distributed across quarters', 'success');
}

// Form submission
document.getElementById('projection-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const saveButton = document.getElementById('save-button');
    const saveSpinner = document.getElementById('save-spinner');
    const saveButtonText = document.getElementById('save-button-text');
    
    // Show loading
    saveButton.disabled = true;
    saveSpinner.classList.remove('hidden');
    saveButtonText.textContent = editingProjectionId ? 'Updating...' : 'Creating...';
    
    try {
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        
        // Ensure branch_id is included even if disabled for branch pastors
        if (!isSuperAdmin && userBranchId && !data.branch_id) {
            data.branch_id = userBranchId;
        }
        
        // Process quarterly data
        const quarterlyData = { attendance: [], converts: [], leaders: [], volunteers: [] };
        Object.keys(data).forEach(key => {
            if (key.startsWith('quarterly_')) {
                const match = key.match(/quarterly_(\w+)\[(\d+)\]/);
                if (match) {
                    const [, target, index] = match;
                    quarterlyData[target][parseInt(index)] = parseInt(data[key]) || 0;
                    delete data[key];
                }
            }
        });
        
        // Add quarterly data to main data object
        data.quarterly_attendance = quarterlyData.attendance;
        data.quarterly_converts = quarterlyData.converts;
        data.quarterly_leaders = quarterlyData.leaders;
        data.quarterly_volunteers = quarterlyData.volunteers;
        
        const url = editingProjectionId 
            ? `/api/projections/${editingProjectionId}` 
            : '/api/projections';
        const method = editingProjectionId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Success', result.message, 'success');
            closeModal();
            await loadProjections();
            await loadStatistics();
        } else {
            if (result.errors) {
                displayFormErrors(result.errors);
            } else {
                showNotification('Error', result.message || 'Operation failed', 'error');
            }
        }
    } catch (error) {
        console.error('Error saving projection:', error);
        showNotification('Error', 'Failed to save projection', 'error');
    } finally {
        // Hide loading
        saveButton.disabled = false;
        saveSpinner.classList.add('hidden');
        saveButtonText.textContent = editingProjectionId ? 'Update Projection' : 'Create Projection';
    }
});

// Utility functions
function toggleDropdown(event) {
    event.stopPropagation();
    const button = event.currentTarget;
    const dropdown = button.nextElementSibling;
    
    // Close all other dropdowns
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        if (menu !== dropdown) {
            menu.classList.add('hidden');
        }
    });
    
    dropdown.classList.toggle('hidden');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function() {
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        menu.classList.add('hidden');
    });
});

function clearFilters() {
    document.getElementById('filter-year').value = '';
    document.getElementById('filter-status').value = '';
    document.getElementById('filter-current-year').value = '';
    if (document.getElementById('filter-branch')) {
        document.getElementById('filter-branch').value = '';
    }
    loadProjections();
}

function changePage(page) {
    if (page < 1) return;
    currentPage = page;
    loadProjections();
}

function formatNumber(num) {
    return new Intl.NumberFormat().format(num);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString();
}

function displayFormErrors(errors) {
    clearFormErrors();
    
    Object.keys(errors).forEach(field => {
        const errorDiv = document.getElementById(`${field}-error`);
        if (errorDiv) {
            errorDiv.textContent = errors[field][0];
            errorDiv.classList.remove('hidden');
        }
    });
}

function clearFormErrors() {
    document.querySelectorAll('[id$="-error"]').forEach(errorDiv => {
        errorDiv.classList.add('hidden');
        errorDiv.textContent = '';
    });
}

// Approval workflow functions
function openApprovalModal(projectionId) {
    document.getElementById('approve-projection-id').value = projectionId;
    document.getElementById('approval-modal').classList.remove('hidden');
}

function closeApprovalModal() {
    document.getElementById('approval-modal').classList.add('hidden');
    document.getElementById('approval-form').reset();
}

function openRejectionModal(projectionId) {
    document.getElementById('reject-projection-id').value = projectionId;
    document.getElementById('rejection-modal').classList.remove('hidden');
}

function closeRejectionModal() {
    document.getElementById('rejection-modal').classList.add('hidden');
    document.getElementById('rejection-form').reset();
}

// Approval form submission
document.getElementById('approval-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const projectionId = document.getElementById('approve-projection-id').value;
    const notes = document.getElementById('approval_notes').value;
    
    try {
        const response = await fetch(`/api/projections/${projectionId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ approval_notes: notes })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Success', result.message, 'success');
            closeApprovalModal();
            await loadProjections();
            await loadStatistics();
        } else {
            showNotification('Error', result.message || 'Approval failed', 'error');
        }
    } catch (error) {
        console.error('Error approving projection:', error);
        showNotification('Error', 'Failed to approve projection', 'error');
    }
});

// Rejection form submission
document.getElementById('rejection-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const projectionId = document.getElementById('reject-projection-id').value;
    const reason = document.getElementById('rejection_reason').value;
    
    try {
        const response = await fetch(`/api/projections/${projectionId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ rejection_reason: reason })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Success', result.message, 'success');
            closeRejectionModal();
            await loadProjections();
            await loadStatistics();
        } else {
            showNotification('Error', result.message || 'Rejection failed', 'error');
        }
    } catch (error) {
        console.error('Error rejecting projection:', error);
        showNotification('Error', 'Failed to reject projection', 'error');
    }
});

// Other workflow functions
async function submitForReview(projectionId) {
    if (!confirm('Submit this projection for review? You will not be able to edit it until it is approved or rejected.')) {
        return;
    }
    
    try {
        const response = await fetch(`/api/projections/${projectionId}/submit-for-review`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Success', result.message, 'success');
            await loadProjections();
            await loadStatistics();
        } else {
            showNotification('Error', result.message || 'Submit failed', 'error');
        }
    } catch (error) {
        console.error('Error submitting projection:', error);
        showNotification('Error', 'Failed to submit projection', 'error');
    }
}

async function setCurrentYear(projectionId) {
    if (!confirm('Set this projection as the current year? This will remove the current year designation from other projections for this branch.')) {
        return;
    }
    
    try {
        const response = await fetch(`/api/projections/${projectionId}/set-current-year`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Success', result.message, 'success');
            await loadProjections();
            await loadStatistics();
        } else {
            showNotification('Error', result.message || 'Operation failed', 'error');
        }
    } catch (error) {
        console.error('Error setting current year:', error);
        showNotification('Error', 'Failed to set current year', 'error');
    }
}

// Notification system
function showNotification(title, message, type = 'info') {
    const notification = document.getElementById('notification');
    const icon = document.getElementById('notification-icon');
    const titleElement = document.getElementById('notification-title');
    const messageElement = document.getElementById('notification-message');
    
    titleElement.textContent = title;
    messageElement.textContent = message;
    
    // Set icon based on type
    const icons = {
        'success': '<svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        'error': '<svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>',
        'warning': '<svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>',
        'info': '<svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
    };
    
    icon.innerHTML = icons[type] || icons['info'];
    
    notification.classList.remove('hidden');
    
    setTimeout(() => {
        hideNotification();
    }, 5000);
}

function hideNotification() {
    document.getElementById('notification').classList.add('hidden');
}
</script>
</x-sidebar-layout> 
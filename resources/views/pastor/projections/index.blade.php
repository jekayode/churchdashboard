<x-sidebar-layout title="Branch Projections">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h1 class="text-3xl font-bold leading-7 text-gray-900 sm:text-4xl sm:truncate">
                    Branch Projections
                </h1>
                <p class="mt-2 text-lg text-gray-600">
                    Create and manage yearly projections and track performance for your branch
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Current Year</dt>
                                <dd class="text-lg font-medium text-gray-900" id="stat-current">Loading...</dd>
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
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label for="filter-year" class="block text-sm font-medium text-gray-700">Year</label>
                        <select id="filter-year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">All Years</option>
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
                <h3 class="text-lg font-medium text-gray-900">Your Branch Projections</h3>
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
                                    Year
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Targets
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
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
                        </tbody>
                    </table>
                </div>

                <div id="pagination-container" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                </div>
            </div>

            <div id="empty-state" class="hidden text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No projections found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new projection for your branch.</p>
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

<!-- Projection Modal and JavaScript -->
<div id="projection-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900" id="modal-title">Create New Projection</h3>
                <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="projection-form" class="mt-6">
                <input type="hidden" id="projection-id" name="projection_id">
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch</label>
                        <input type="text" id="branch_display" readonly 
                               class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm"
                               value="{{ Auth::user()->getPrimaryBranch()?->name ?? 'Your Branch' }}">
                        <input type="hidden" id="branch_id" name="branch_id" value="{{ Auth::user()->getActiveBranchId() }}">
                        <p class="mt-1 text-sm text-gray-500">Creating projection for your assigned branch</p>
                    </div>

                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700">Year *</label>
                        <input type="number" id="year" name="year" required min="2020" max="2035"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>

                <div class="mt-6">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Yearly Targets</h4>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label for="attendance_target" class="block text-sm font-medium text-gray-700">Attendance Target *</label>
                            <input type="number" id="attendance_target" name="attendance_target" required min="1" max="10000"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="converts_target" class="block text-sm font-medium text-gray-700">Converts Target *</label>
                            <input type="number" id="converts_target" name="converts_target" required min="0" max="1000"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="leaders_target" class="block text-sm font-medium text-gray-700">Leaders Target *</label>
                            <input type="number" id="leaders_target" name="leaders_target" required min="0" max="500"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="volunteers_target" class="block text-sm font-medium text-gray-700">Volunteers Target *</label>
                            <input type="number" id="volunteers_target" name="volunteers_target" required min="0" max="1000"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" id="save-button"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        <span id="save-button-text">Create Projection</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Global variables
let currentProjections = [];
let currentPage = 1;
let editingProjectionId = null;
const isSuperAdmin = false;
const userBranchId = {{ Auth::user()->getActiveBranchId() ?? 'null' }};

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    initializePage();
});

async function initializePage() {
    try {
        await Promise.all([
            loadProjections(),
            loadStatistics(),
            populateYearFilters()
        ]);
    } catch (error) {
        console.error('Error initializing page:', error);
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
        
        const response = await fetch(`/api/projections?${params}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        
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
        
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                const stats = data.data;
                document.getElementById('stat-total').textContent = stats.total || 0;
                document.getElementById('stat-approved').textContent = stats.approved || 0;
                document.getElementById('stat-pending').textContent = stats.pending || 0;
                document.getElementById('stat-current').textContent = stats.current_year_projections || 0;
            }
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
            <div class="text-sm font-medium text-gray-900">${projection.year}</div>
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
            ${formatDate(projection.updated_at)}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <button onclick="viewProjection(${projection.id})" class="text-indigo-600 hover:text-indigo-900">View</button>
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

function renderPagination(data) {
    const container = document.getElementById('pagination-container');
    if (!data.links || data.last_page <= 1) {
        container.classList.add('hidden');
        return;
    }
    container.classList.remove('hidden');
    // Add pagination logic here
}

// Modal functions
function openCreateModal() {
    editingProjectionId = null;
    document.getElementById('modal-title').textContent = 'Create New Projection';
    document.getElementById('save-button-text').textContent = 'Create Projection';
    document.getElementById('projection-form').reset();
    document.getElementById('branch_id').value = userBranchId;
    document.getElementById('projection-modal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('projection-modal').classList.add('hidden');
}

function viewProjection(projectionId) {
    // Add view projection logic
    console.log('Viewing projection:', projectionId);
}

// Utility functions
function clearFilters() {
    document.getElementById('filter-year').value = '';
    document.getElementById('filter-status').value = '';
    document.getElementById('filter-current-year').value = '';
    loadProjections();
}

function formatNumber(num) {
    return new Intl.NumberFormat().format(num);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString();
}

// Form submission
document.getElementById('projection-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const saveButton = document.getElementById('save-button');
    saveButton.disabled = true;
    document.getElementById('save-button-text').textContent = 'Creating...';
    
    try {
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        data.branch_id = userBranchId;
        
        const response = await fetch('/api/projections', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeModal();
            await loadProjections();
            await loadStatistics();
        } else {
            alert(result.message || 'Operation failed');
        }
    } catch (error) {
        console.error('Error saving projection:', error);
        alert('Failed to save projection');
    } finally {
        saveButton.disabled = false;
        document.getElementById('save-button-text').textContent = 'Create Projection';
    }
});
</script>
</x-sidebar-layout>

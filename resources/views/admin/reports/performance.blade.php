<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Network Performance Analysis</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

    <!-- Controls -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Year Selection -->
            <div>
                <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                <select id="year" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    @for($year = now()->year - 3; $year <= now()->year + 1; $year++)
                        <option value="{{ $year }}" {{ $year === now()->year ? 'selected' : '' }}>{{ $year }}</option>
                    @endfor
                </select>
            </div>

            <!-- Range Selection -->
            <div>
                <label for="range" class="block text-sm font-medium text-gray-700 mb-2">Time Range</label>
                <select id="range" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="YTD">Year to Date</option>
                    <option value="QTD">Quarter to Date</option>
                    <option value="MTD">Month to Date</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>

            <!-- Comparison Type -->
            <div>
                <label for="compare" class="block text-sm font-medium text-gray-700 mb-2">Compare With</label>
                <select id="compare" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">No Comparison</option>
                    <option value="YoY">Year over Year</option>
                    <option value="QoQ">Quarter over Quarter</option>
                    <option value="MoM">Month over Month</option>
                </select>
            </div>

            <!-- Branch Filter -->
            <div>
                <label for="branchFilter" class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                <select id="branchFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">All Branches</option>
                    <!-- Branches will be loaded dynamically -->
                </select>
            </div>

            <!-- Custom Date Range -->
            <div id="customDateRange" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">Custom Range</label>
                <div class="flex space-x-2">
                    <input type="date" id="startDate" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <input type="date" id="endDate" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
            </div>
        </div>

        <div class="mt-4">
            <button onclick="loadPerformanceData()" class="bg-purple-600 text-white px-6 py-2 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                Load Analysis
            </button>
        </div>
    </div>

    <!-- Global Projection Cards -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Global Projections</h2>
        <div id="globalProjectionCards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Cards will be loaded here -->
        </div>
    </div>

    <!-- Network Performance Cards -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Network Performance</h2>
        <div id="networkPerformanceCards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Cards will be loaded here -->
        </div>
    </div>

    <!-- Branch Performance Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Branch Performance Comparison</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guests</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Converts</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weekly Avg</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Growth</th>
                    </tr>
                </thead>
                <tbody id="branchPerformanceTable" class="bg-white divide-y divide-gray-200">
                    <!-- Data will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Monthly Analysis Chart -->
    <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Monthly Trend Analysis</h2>
        <div id="monthlyChart" class="h-64">
            <!-- Chart will be rendered here -->
        </div>
    </div>

    <!-- Quarterly Analysis -->
    <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Quarterly Analysis</h2>
        <div id="quarterlyAnalysis" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Quarterly data will be loaded here -->
        </div>
    </div>

    <!-- Yearly Trend Analysis -->
    <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Yearly Trend Analysis</h2>
        <div id="yearlyTrend" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Yearly data will be loaded here -->
        </div>
    </div>

    <!-- Projection vs Actual Analysis -->
    <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Projection vs Actual Analysis</h2>
        <div id="projectionComparison" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Projection comparison will be loaded here -->
        </div>
    </div>

    <!-- Insights -->
    <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Network Insights</h2>
        <div id="networkInsights" class="space-y-2">
            <!-- Insights will be loaded here -->
        </div>
    </div>
</div>

<!-- Loading Spinner -->
<div id="loadingSpinner" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-purple-100">
                <svg class="animate-spin h-6 w-6 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-2">Loading Performance Data</h3>
            <p class="text-sm text-gray-500 mt-1">Please wait while we analyze the network performance...</p>
        </div>
    </div>
</div>

<script>
let currentData = null;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadBranches();
    loadPerformanceData();
    
    // Handle range change
    document.getElementById('range').addEventListener('change', function() {
        const customRange = document.getElementById('customDateRange');
        if (this.value === 'custom') {
            customRange.classList.remove('hidden');
        } else {
            customRange.classList.add('hidden');
        }
    });
    
    // Handle branch filter change
    document.getElementById('branchFilter').addEventListener('change', function() {
        loadPerformanceData();
    });
});

function showLoadingSpinner() {
    document.getElementById('loadingSpinner').classList.remove('hidden');
}

function hideLoadingSpinner() {
    document.getElementById('loadingSpinner').classList.add('hidden');
}

// Load branches for the filter dropdown
async function loadBranches() {
    try {
        const res = await fetch('/api/branches', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        if (res.ok) {
            const data = await res.json();
            if (data.success) {
                const branchSelect = document.getElementById('branchFilter');
                // Clear existing options except "All Branches"
                branchSelect.innerHTML = '<option value="">All Branches</option>';
                
                // Add branch options
                data.data.forEach(branch => {
                    const option = document.createElement('option');
                    option.value = branch.id;
                    option.textContent = branch.name;
                    branchSelect.appendChild(option);
                });
            }
        }
    } catch (error) {
        console.error('Error loading branches:', error);
    }
}

async function loadPerformanceData() {
    showLoadingSpinner();
    
    try {
        const year = document.getElementById('year').value;
        const range = document.getElementById('range').value;
        const compare = document.getElementById('compare').value;
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        const branchFilter = document.getElementById('branchFilter').value;
        
        const params = new URLSearchParams({
            year: year,
            range: range,
            compare: compare
        });
        
        if (range === 'custom' && startDate && endDate) {
            params.append('start_date', startDate);
            params.append('end_date', endDate);
        }
        
        // Determine which API endpoint to call based on branch filter
        const apiEndpoint = branchFilter ? `/api/performance/branch/${branchFilter}` : '/api/performance/network';
        
        const response = await fetch(`${apiEndpoint}?${params}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        if (!response.ok) {
            throw new Error('Failed to load performance data');
        }
        
        const data = await response.json();
        currentData = data.data;
        
        // Load all sections
        loadGlobalProjectionCards();
        loadNetworkPerformanceCards();
        loadBranchPerformanceTable();
        loadMonthlyChart();
        loadQuarterlyAnalysis();
        loadYearlyTrend();
        loadProjectionComparison();
        loadNetworkInsights();
        
    } catch (error) {
        console.error('Error loading performance data:', error);
        alert('Error loading performance data. Please try again.');
    } finally {
        hideLoadingSpinner();
    }
}

function loadGlobalProjectionCards() {
    if (!currentData?.projections) return;
    
    const container = document.getElementById('globalProjectionCards');
    const projection = currentData.projections;
    
    container.innerHTML = `
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">Total Attendance Target</p>
                    <p class="text-2xl font-bold">${projection.attendance_target?.toLocaleString() || 0}</p>
                </div>
                <div class="bg-purple-400 rounded-full p-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Converts Target</p>
                    <p class="text-2xl font-bold">${projection.converts_target?.toLocaleString() || 0}</p>
                </div>
                <div class="bg-blue-400 rounded-full p-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Guests Target</p>
                    <p class="text-2xl font-bold">${projection.guests_target?.toLocaleString() || 0}</p>
                </div>
                <div class="bg-green-400 rounded-full p-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm">LifeGroups Target</p>
                    <p class="text-2xl font-bold">${projection.lifegroups_target?.toLocaleString() || 0}</p>
                </div>
                <div class="bg-orange-400 rounded-full p-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>
        </div>
    `;
}

function loadNetworkPerformanceCards() {
    if (!currentData?.actuals) return;
    
    const container = document.getElementById('networkPerformanceCards');
    const actuals = currentData.actuals;
    const comparison = currentData.comparison;
    
    container.innerHTML = `
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Attendance</p>
                    <p class="text-2xl font-bold text-gray-900">${actuals.attendance?.toLocaleString() || 0}</p>
                    ${comparison ? `<p class="text-sm ${comparison.attendance_delta >= 0 ? 'text-green-600' : 'text-red-600'}">${comparison.attendance_delta >= 0 ? '+' : ''}${comparison.attendance_delta}%</p>` : ''}
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Guests</p>
                    <p class="text-2xl font-bold text-gray-900">${actuals.guests?.toLocaleString() || 0}</p>
                    ${comparison ? `<p class="text-sm ${comparison.guests_delta >= 0 ? 'text-green-600' : 'text-red-600'}">${comparison.guests_delta >= 0 ? '+' : ''}${comparison.guests_delta}%</p>` : ''}
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Converts</p>
                    <p class="text-2xl font-bold text-gray-900">${actuals.converts?.toLocaleString() || 0}</p>
                    ${comparison ? `<p class="text-sm ${comparison.converts_delta >= 0 ? 'text-green-600' : 'text-red-600'}">${comparison.converts_delta >= 0 ? '+' : ''}${comparison.converts_delta}%</p>` : ''}
                </div>
                <div class="bg-purple-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Weekly Average</p>
                    <p class="text-2xl font-bold text-gray-900">${actuals.weekly_avg_attendance?.toLocaleString() || 0}</p>
                    ${comparison ? `<p class="text-sm ${comparison.weekly_avg_delta >= 0 ? 'text-green-600' : 'text-red-600'}">${comparison.weekly_avg_delta >= 0 ? '+' : ''}${comparison.weekly_avg_delta}%</p>` : ''}
                </div>
                <div class="bg-orange-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    `;
}

function loadBranchPerformanceTable() {
    if (!currentData?.branches) return;
    
    const container = document.getElementById('branchPerformanceTable');
    const branches = currentData.branches;
    
    container.innerHTML = branches.map(branch => `
        <tr>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${branch.name}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${branch.attendance?.toLocaleString() || 0}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${branch.guests?.toLocaleString() || 0}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${branch.converts?.toLocaleString() || 0}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${branch.weekly_avg?.toLocaleString() || 0}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm ${branch.growth >= 0 ? 'text-green-600' : 'text-red-600'}">
                ${branch.growth >= 0 ? '+' : ''}${branch.growth}%
            </td>
        </tr>
    `).join('');
}

function loadMonthlyChart() {
    if (!currentData?.monthly) return;
    
    const container = document.getElementById('monthlyChart');
    const monthlyData = currentData.monthly;
    
    // Simple chart implementation (you can replace with Chart.js or similar)
    container.innerHTML = `
        <div class="flex items-end justify-between h-full space-x-2">
            ${monthlyData.map(month => {
                const maxValue = Math.max(...monthlyData.map(m => m.attendance));
                const height = (month.attendance / maxValue) * 100;
                return `
                    <div class="flex flex-col items-center flex-1">
                        <div class="bg-blue-500 rounded-t w-full" style="height: ${height}%"></div>
                        <div class="text-xs text-gray-500 mt-2">${month.month}</div>
                        <div class="text-xs text-gray-700 font-medium">${month.attendance}</div>
                    </div>
                `;
            }).join('')}
        </div>
    `;
}

function loadQuarterlyAnalysis() {
    if (!currentData?.quarterly_progress) return;
    
    const container = document.getElementById('quarterlyAnalysis');
    const quarterlyData = currentData.quarterly_progress;
    
    container.innerHTML = quarterlyData.map(quarter => `
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <h3 class="font-semibold text-gray-900 mb-2">${quarter.quarter}: ${quarter.actual || 0}/${quarter.projected || 0}</h3>
            <div class="text-2xl font-bold text-gray-900">${quarter.progress}%</div>
            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                <div class="bg-blue-600 h-2 rounded-full" style="width: ${Math.min(quarter.progress, 100)}%"></div>
            </div>
        </div>
    `).join('');
}

function loadYearlyTrend() {
    if (!currentData?.yearly) return;
    
    const container = document.getElementById('yearlyTrend');
    const yearlyData = currentData.yearly;
    
    container.innerHTML = yearlyData.map(year => `
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <h3 class="font-semibold text-gray-900 mb-2">${year.year}</h3>
            <div class="text-lg font-bold text-gray-900">${year.attendance?.toLocaleString() || 0}</div>
            <div class="text-sm ${year.growth >= 0 ? 'text-green-600' : 'text-red-600'}">
                ${year.growth >= 0 ? '+' : ''}${year.growth}%
            </div>
        </div>
    `).join('');
}

function loadProjectionComparison() {
    if (!currentData?.projections) return;
    
    const container = document.getElementById('projectionComparison');
    const projection = currentData.projections;
    const actuals = currentData.actuals;
    
    const attendanceProgress = projection.attendance_target > 0 ? 
        Math.round((actuals.attendance / projection.attendance_target) * 100) : 0;
    const convertsProgress = projection.converts_target > 0 ? 
        Math.round((actuals.converts / projection.converts_target) * 100) : 0;
    const guestsProgress = projection.guests_target > 0 ? 
        Math.round((actuals.guests / projection.guests_target) * 100) : 0;
    const lifegroupsProgress = projection.lifegroups_target > 0 ? 
        Math.round((actuals.lifegroups / projection.lifegroups_target) * 100) : 0;
    
    container.innerHTML = `
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <h3 class="font-semibold text-gray-900 mb-2">Attendance</h3>
            <div class="text-lg font-bold text-gray-900">${attendanceProgress}%</div>
            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                <div class="bg-blue-600 h-2 rounded-full" style="width: ${Math.min(attendanceProgress, 100)}%"></div>
            </div>
            <div class="text-sm text-gray-500 mt-1">${actuals.attendance?.toLocaleString() || 0} / ${projection.attendance_target?.toLocaleString() || 0}</div>
        </div>
        
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <h3 class="font-semibold text-gray-900 mb-2">Converts</h3>
            <div class="text-lg font-bold text-gray-900">${convertsProgress}%</div>
            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                <div class="bg-purple-600 h-2 rounded-full" style="width: ${Math.min(convertsProgress, 100)}%"></div>
            </div>
            <div class="text-sm text-gray-500 mt-1">${actuals.converts?.toLocaleString() || 0} / ${projection.converts_target?.toLocaleString() || 0}</div>
        </div>
        
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <h3 class="font-semibold text-gray-900 mb-2">Guests</h3>
            <div class="text-lg font-bold text-gray-900">${guestsProgress}%</div>
            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                <div class="bg-green-600 h-2 rounded-full" style="width: ${Math.min(guestsProgress, 100)}%"></div>
            </div>
            <div class="text-sm text-gray-500 mt-1">${actuals.guests?.toLocaleString() || 0} / ${projection.guests_target?.toLocaleString() || 0}</div>
        </div>
        
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <h3 class="font-semibold text-gray-900 mb-2">LifeGroups</h3>
            <div class="text-lg font-bold text-gray-900">${lifegroupsProgress}%</div>
            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                <div class="bg-orange-600 h-2 rounded-full" style="width: ${Math.min(lifegroupsProgress, 100)}%"></div>
            </div>
            <div class="text-sm text-gray-500 mt-1">${actuals.lifegroups?.toLocaleString() || 0} / ${projection.lifegroups_target?.toLocaleString() || 0}</div>
        </div>
    `;
}

function loadNetworkInsights() {
    if (!currentData?.insights) return;
    
    const container = document.getElementById('networkInsights');
    const insights = currentData.insights;
    
    container.innerHTML = insights.map(insight => `
        <div class="flex items-start space-x-3 p-3 bg-blue-50 rounded-lg">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-sm text-blue-800">${insight}</p>
        </div>
    `).join('');
}
</script>
</div>
</div>
</x-app-layout>

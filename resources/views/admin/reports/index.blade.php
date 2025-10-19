<x-sidebar-layout title="Reports & Analytics">
    <div class="mb-6">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reports & Analytics') }}
        </h2>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Filters Section -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Event Reports</h3>
                    <div class="flex gap-3">
                        <button id="manageTokensBtn" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                            Manage Submission Links
                        </button>
                        <button id="exportPdfBtn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export PDF
                        </button>
                        <button id="importReportsBtn" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                            </svg>
                            Import Reports
                        </button>
                        <button id="createReportBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                            Create New Report
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                    <!-- Period Selection Dropdown -->
                    <select id="periodSelect" class="border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="week">This Week</option>
                        <option value="month" selected>This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="quarter">This Quarter</option>
                        <option value="year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                    <select id="eventTypeFilter" class="border-gray-300 rounded-md shadow-sm">
                        <option value="">All Event Types</option>
                        <!-- Event types will be loaded dynamically -->
                    </select>
                    <input type="date" id="dateFromFilter" class="border-gray-300 rounded-md shadow-sm" placeholder="From Date">
                    <input type="date" id="dateToFilter" class="border-gray-300 rounded-md shadow-sm" placeholder="To Date">
                    <button id="applyFiltersBtn" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                        Apply Filters
                    </button>
                </div>

                <!-- Custom Date Range (initially hidden) -->
                <div id="customDateRange" class="hidden mb-4 p-4 bg-gray-50 rounded-lg">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="customDateFrom" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                            <input type="date" id="customDateFrom" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="customDateTo" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                            <input type="date" id="customDateTo" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button onclick="applyCustomDateRange()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Apply Date Range
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dashboard Overview -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Dashboard Overview</h3>
                    @if(auth()->user()->isSuperAdmin())
                        <!-- Branch Selection for Super Admin -->
                        <div class="relative">
                            <select id="branchSelect" class="bg-white border border-gray-300 rounded-md px-4 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Branches</option>
                            </select>
                        </div>
                    @endif
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-blue-50 rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-600">Total Attendance</p>
                                <p class="text-2xl font-bold text-blue-900" id="totalAttendance">0</p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-full">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.196-2.121M9 20H4v-2a3 3 0 00-5.196-2.121m4-18a4 4 0 00-8 0 4 4 0 008 0zM8 14a3 3 0 106 0 3 3 0 00-6 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 p-6 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-green-600">Average Attendance</p>
                                <p class="text-2xl font-bold text-green-900" id="avgAttendance">-</p>
                            </div>
                            <div class="ml-4">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 p-6 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-yellow-600">New Converts</p>
                                <p class="text-2xl font-bold text-yellow-900" id="totalConverts">-</p>
                            </div>
                            <div class="ml-4">
                                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-purple-50 p-6 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-purple-600">First Time Guests</p>
                                <p class="text-2xl font-bold text-purple-900" id="totalGuests">-</p>
                            </div>
                            <div class="ml-4">
                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Demographic Breakdown Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-indigo-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-xs font-medium text-indigo-600">Male Attendance</p>
                                <p class="text-lg font-bold text-indigo-900" id="malePercentage">0%</p>
                                <p class="text-xs text-indigo-500" id="maleTotal">(0)</p>
                            </div>
                            <div class="ml-2">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-pink-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-xs font-medium text-pink-600">Female Attendance</p>
                                <p class="text-lg font-bold text-pink-900" id="femalePercentage">0%</p>
                                <p class="text-xs text-pink-500" id="femaleTotal">(0)</p>
                            </div>
                            <div class="ml-2">
                                <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-orange-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-xs font-medium text-orange-600">Children Attendance</p>
                                <p class="text-lg font-bold text-orange-900" id="childrenPercentage">0%</p>
                                <p class="text-xs text-orange-500" id="childrenTotal">(0)</p>
                            </div>
                            <div class="ml-2">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h4a1 1 0 110 2h-1v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6H3a1 1 0 110-2h4zM9 6v10h6V6H9z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-teal-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-xs font-medium text-teal-600">Online Attendance</p>
                                <p class="text-lg font-bold text-teal-900" id="onlinePercentage">0%</p>
                                <p class="text-xs text-teal-500" id="onlineTotal">(0)</p>
                            </div>
                            <div class="ml-2">
                                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Small Groups Statistics -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Small Groups Statistics</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-indigo-50 rounded-lg p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-indigo-600">Total Attendance</p>
                                    <p class="text-2xl font-bold text-indigo-900" id="smallGroupsTotalAttendance">0</p>
                                </div>
                                <div class="p-3 bg-indigo-100 rounded-full">
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.196-2.121M9 20H4v-2a3 3 0 00-5.196-2.121m4-18a4 4 0 00-8 0 4 4 0 008 0zM8 14a3 3 0 106 0 3 3 0 00-6 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-emerald-50 rounded-lg p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-emerald-600">Average Attendance</p>
                                    <p class="text-2xl font-bold text-emerald-900" id="smallGroupsAvgAttendance">0</p>
                                </div>
                                <div class="p-3 bg-emerald-100 rounded-full">
                                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-amber-50 rounded-lg p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-amber-600">Total Active Groups</p>
                                    <p class="text-2xl font-bold text-amber-900" id="smallGroupsActiveGroups">0</p>
                                </div>
                                <div class="p-3 bg-amber-100 rounded-full">
                                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-rose-50 rounded-lg p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-rose-600">Total Guests</p>
                                    <p class="text-2xl font-bold text-rose-900" id="smallGroupsTotalGuests">0</p>
                                </div>
                                <div class="p-3 bg-rose-100 rounded-full">
                                    <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Attendance by Event Type</h4>
                        <div class="relative h-64">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Weekly Sunday Service Breakdown</h4>
                        <div class="relative h-64">
                            <canvas id="eventTypeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event Reports Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Attendance</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">First Time Guests</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Converts</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Services</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="reportsTableBody" class="bg-white divide-y divide-gray-200">
                                <!-- Reports will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination Controls -->
                    <div class="bg-white px-6 py-3 border-t border-gray-200 flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-700">
                                Showing <span id="paginationStart">1</span> to <span id="paginationEnd">20</span> of <span id="paginationTotal">0</span> results
                            </span>
                            <div class="flex items-center space-x-2">
                                <label for="perPageSelect" class="text-sm text-gray-700">Show:</label>
                                <select id="perPageSelect" class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="10">10</option>
                                    <option value="20" selected>20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <span class="text-sm text-gray-700">per page</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <button id="paginationPrev" class="px-3 py-1 text-sm text-gray-500 border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                Previous
                            </button>
                            
                            <div id="paginationNumbers" class="flex space-x-1">
                                <!-- Page numbers will be inserted here -->
                            </div>
                            
                            <button id="paginationNext" class="px-3 py-1 text-sm text-gray-500 border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Global Ministry Monthly Report Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Global Ministry Monthly Report</h3>
                        <div class="flex gap-3">
                            <button id="generateGlobalReportBtn" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Generate Report
                            </button>
                            @if(auth()->user()->isSuperAdmin())
                            <button id="generateAllBranchesReportBtn" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                All Branches Report
                            </button>
                            @endif
                        </div>
                    </div>

                    <!-- Report Parameters -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="reportYear" class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                            <select id="reportYear" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <!-- Years will be populated dynamically -->
                            </select>
                        </div>
                        <div>
                            <label for="reportMonth" class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                            <select id="reportMonth" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
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
                        @if(auth()->user()->isSuperAdmin())
                        <div>
                            <label for="reportBranchSelect" class="block text-sm font-medium text-gray-700 mb-1">Branch (Optional)</label>
                            <select id="reportBranchSelect" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Branches</option>
                                <!-- Branches will be populated dynamically -->
                            </select>
                        </div>
                        @endif
                    </div>

                    <!-- Report Display Area -->
                    <div id="globalReportDisplay" class="hidden">
                        <div class="bg-gray-50 rounded-lg p-6">
                            <div id="globalReportContent">
                                <!-- Report content will be displayed here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comparative Analytics Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Comparative Analytics</h3>
                    
                    <!-- Preset and Branch Filter Options -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="comparisonPreset" class="block text-sm font-medium text-gray-700 mb-1">Preset Comparisons</label>
                            <select id="comparisonPreset" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Custom Range</option>
                                <option value="this_month_vs_last">This Month vs Last Month</option>
                                <option value="this_quarter_vs_last">This Quarter vs Last Quarter</option>
                                <option value="last_6_months_vs_previous">Last 6 Months vs Previous 6 Months</option>
                                <option value="this_year_vs_last">This Year vs Last Year</option>
                                <option value="last_30_days_vs_previous">Last 30 Days vs Previous 30 Days</option>
                            </select>
                        </div>
                        @if(auth()->user()->isSuperAdmin())
                        <div>
                            <label for="comparisonBranch" class="block text-sm font-medium text-gray-700 mb-1">Branch (Optional)</label>
                            <select id="comparisonBranch" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">All Branches</option>
                                <!-- Branches will be populated dynamically -->
                            </select>
                        </div>
                        @endif
                        <div class="flex items-end">
                            <button id="applyPresetBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                                Apply Preset
                            </button>
                        </div>
                    </div>

                    <!-- Custom Date Range Selection -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">Period 1</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <input type="date" id="period1Start" class="border-gray-300 rounded-md shadow-sm" placeholder="Start Date">
                                <input type="date" id="period1End" class="border-gray-300 rounded-md shadow-sm" placeholder="End Date">
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">Period 2</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <input type="date" id="period2Start" class="border-gray-300 rounded-md shadow-sm" placeholder="Start Date">
                                <input type="date" id="period2End" class="border-gray-300 rounded-md shadow-sm" placeholder="End Date">
                            </div>
                        </div>
                    </div>
                    
                    <button id="comparePeriodsBtn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md mb-6">
                        Compare Periods
                    </button>

                    <div id="comparisonResults" class="hidden">
                        <!-- Comparison results will be displayed here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Report Modal -->
    <div id="createReportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Create Event Report</h3>
                <form id="createReportForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Event</label>
                            <select id="eventSelect" name="event_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">Select Event</option>
                                <!-- Events will be loaded dynamically -->
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Event Date</label>
                            <input type="date" name="event_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Event Type</label>
                            <select id="createEventType" name="event_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">Select Event Type</option>
                                @foreach($eventTypes as $eventType)
                                    <option value="{{ $eventType }}">{{ $eventType }}</option>
                                @endforeach
                            </select>
                        </div>
                        

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Time</label>
                            <input type="time" name="start_time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Time</label>
                            <input type="time" name="end_time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                    </div>

                    <!-- First Service -->
                    <div class="border-t pt-4">
                        <h4 class="font-medium text-gray-900 mb-3">First Service Attendance</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Male</label>
                                <input type="number" name="male_attendance" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Female</label>
                                <input type="number" name="female_attendance" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Children</label>
                                <input type="number" name="children_attendance" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Online</label>
                                <input type="number" name="online_attendance" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="0">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">First Time Guests</label>
                                <input type="number" name="first_time_guests" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Converts</label>
                                <input type="number" name="converts" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cars</label>
                                <input type="number" name="cars" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>
                        </div>
                    </div>

                    <!-- Second Service Toggle -->
                    <div class="border-t pt-4">
                        <div class="flex items-center mb-3">
                            <input type="checkbox" id="hasSecondService" name="has_second_service" class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <label for="hasSecondService" class="ml-2 block text-sm font-medium text-gray-900">
                                Has Second Service
                            </label>
                        </div>

                        <div id="secondServiceFields" class="hidden space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Second Service Start Time</label>
                                    <input type="time" name="second_service_start_time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Second Service End Time</label>
                                    <input type="time" name="second_service_end_time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                            </div>
                            
                            <h5 class="font-medium text-gray-900">Second Service Attendance</h5>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Male</label>
                                    <input type="number" name="second_male_attendance" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Female</label>
                                    <input type="number" name="second_female_attendance" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Children</label>
                                    <input type="number" name="second_children_attendance" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Online</label>
                                    <input type="number" name="second_online_attendance" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="0">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">First Time Guests</label>
                                    <input type="number" name="second_first_time_guests" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Converts</label>
                                    <input type="number" name="second_converts" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Cars</label>
                                    <input type="number" name="second_cars" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" id="cancelReportBtn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md">
                            Cancel
                        </button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                            Create Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Report Modal -->
    <div id="editReportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Event Report</h3>
                <form id="editReportForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Event</label>
                            <select id="editEventSelect" name="event_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">Select Event</option>
                                <!-- Events will be loaded dynamically -->
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Event Date</label>
                            <input type="date" id="editEventDate" name="event_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Event Type</label>
                            <select id="editEventType" name="event_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">Select Event Type</option>
                                @foreach($eventTypes as $eventType)
                                    <option value="{{ $eventType }}">{{ $eventType }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Time</label>
                            <input type="time" id="editStartTime" name="start_time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Time</label>
                            <input type="time" id="editEndTime" name="end_time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                    </div>

                    <!-- First Service -->
                    <div class="border-t pt-4">
                        <h4 class="font-medium text-gray-900 mb-3">First Service Attendance</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Male</label>
                                <input type="number" id="editMaleAttendance" name="male_attendance" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Female</label>
                                <input type="number" id="editFemaleAttendance" name="female_attendance" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Children</label>
                                <input type="number" id="editChildrenAttendance" name="children_attendance" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Online</label>
                                <input type="number" id="editOnlineAttendance" name="online_attendance" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="0">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">First Time Guests</label>
                                <input type="number" id="editFirstTimeGuests" name="first_time_guests" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Converts</label>
                                <input type="number" id="editConverts" name="converts" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cars</label>
                                <input type="number" id="editCars" name="cars" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>
                        </div>
                    </div>

                    <!-- Second Service Toggle -->
                    <div class="border-t pt-4">
                        <div class="flex items-center mb-3">
                            <input type="checkbox" id="editHasSecondService" name="has_second_service" class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <label for="editHasSecondService" class="ml-2 block text-sm font-medium text-gray-900">
                                Has Second Service
                            </label>
                        </div>

                        <div id="editSecondServiceFields" class="hidden space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Second Service Start Time</label>
                                    <input type="time" id="editSecondServiceStartTime" name="second_service_start_time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Second Service End Time</label>
                                    <input type="time" id="editSecondServiceEndTime" name="second_service_end_time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                            </div>
                            
                            <h5 class="font-medium text-gray-900">Second Service Attendance</h5>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Male</label>
                                    <input type="number" id="editSecondMaleAttendance" name="second_male_attendance" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Female</label>
                                    <input type="number" id="editSecondFemaleAttendance" name="second_female_attendance" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Children</label>
                                    <input type="number" id="editSecondChildrenAttendance" name="second_children_attendance" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Online</label>
                                    <input type="number" id="editSecondOnlineAttendance" name="second_online_attendance" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="0">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">First Time Guests</label>
                                    <input type="number" id="editSecondFirstTimeGuests" name="second_first_time_guests" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Converts</label>
                                    <input type="number" id="editSecondConverts" name="second_converts" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Cars</label>
                                    <input type="number" id="editSecondCars" name="second_cars" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea id="editNotes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" id="cancelEditBtn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md">
                            Cancel
                        </button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                            Update Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Import Reports Modal -->
    <div id="importReportsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Import Event Reports</h3>
                
                <!-- Template Download Section -->
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <h4 class="text-sm font-medium text-blue-900 mb-2">Need a template?</h4>
                    <p class="text-sm text-blue-700 mb-3">Download our Excel template with sample data to get started.</p>
                    <button id="downloadTemplateBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download Template
                    </button>
                </div>

                <!-- File Upload Section -->
                <form id="importReportsForm" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label for="importFile" class="block text-sm font-medium text-gray-700 mb-2">
                            Choose Excel/CSV File
                        </label>
                        <input type="file" id="importFile" name="file" accept=".xlsx,.xls,.csv" 
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" 
                               required>
                        <p class="text-xs text-gray-500 mt-1">Supported formats: Excel (.xlsx, .xls) and CSV files. Max size: 10MB</p>
                    </div>

                    <!-- Progress Bar (initially hidden) -->
                    <div id="importProgress" class="hidden mb-4">
                        <div class="bg-gray-200 rounded-full h-2">
                            <div id="importProgressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <p id="importProgressText" class="text-sm text-gray-600 mt-1">Uploading...</p>
                    </div>

                    <!-- Import Results (initially hidden) -->
                    <div id="importResults" class="hidden mb-4 p-4 rounded-lg">
                        <h4 class="font-medium mb-2">Import Results</h4>
                        <div id="importResultsContent"></div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" id="cancelImportBtn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md">
                            Cancel
                        </button>
                        <button type="submit" id="importSubmitBtn" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md">
                            Import Reports
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        // Initialize the reporting dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize charts
            initCharts();
            
            // Set initial date filters for default period (This Month)
            setDateFiltersForPeriod('month');
            
            // Load branches for super admin
            @if(auth()->user()->isSuperAdmin())
            loadBranches();
            @endif
            
            // Load initial data
            loadDashboardData();
            loadEventReports();
            loadEventTypes();
            loadEvents();
            loadTrendData();
            
            // Event listeners
            setupEventListeners();
            setupComparisonEventListeners();
        });

        function initCharts() {
            // Event Type Attendance Chart (Bar Chart)
            const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
            window.attendanceChart = new Chart(attendanceCtx, {
                type: 'bar',
                data: {
                    labels: ['Sunday Service', 'Mid-Week Service'],
                    datasets: [{
                        label: 'Total Attendance',
                        data: [0, 0],
                        backgroundColor: ['#3B82F6', '#10B981'],
                        borderColor: ['#1D4ED8', '#059669'],
                        borderWidth: 2,
                        borderRadius: 4,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            display: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    }
                }
            });

            // Weekly Sunday Service Chart (Line Chart)
            const eventTypeCtx = document.getElementById('eventTypeChart').getContext('2d');
            window.eventTypeChart = new Chart(eventTypeCtx, {
                type: 'line',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'],
                    datasets: [{
                        label: 'Sunday Service Attendance',
                        data: [0, 0, 0, 0, 0],
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            display: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    },
                    elements: {
                        point: {
                            radius: 4,
                            hoverRadius: 6
                        }
                    }
                }
            });
        }

        function setupEventListeners() {
            // Period and branch selectors
            document.getElementById('periodSelect').addEventListener('change', handlePeriodChange);
            
            const branchSelect = document.getElementById('branchSelect');
            if (branchSelect) {
                branchSelect.addEventListener('change', function() {
                    loadDashboardData();
                    loadTrendData();
                    loadEvents(); // Reload events when branch changes
                    loadEventReports(); // Also reload reports when branch changes
                });
            }

            // Modal controls
            document.getElementById('createReportBtn').addEventListener('click', showCreateModal);
            document.getElementById('cancelReportBtn').addEventListener('click', hideCreateModal);
            
            // Export PDF button
            document.getElementById('exportPdfBtn').addEventListener('click', exportToPDF);
            
            // Import Reports modal controls
            document.getElementById('importReportsBtn').addEventListener('click', showImportModal);
            document.getElementById('cancelImportBtn').addEventListener('click', hideImportModal);
            document.getElementById('downloadTemplateBtn').addEventListener('click', downloadImportTemplate);
            document.getElementById('importReportsForm').addEventListener('submit', handleImportReports);
            
            // Edit modal controls
            document.getElementById('cancelEditBtn').addEventListener('click', hideEditModal);
            
            // Second service toggle
            document.getElementById('hasSecondService').addEventListener('change', function() {
                const fields = document.getElementById('secondServiceFields');
                if (this.checked) {
                    fields.classList.remove('hidden');
                } else {
                    fields.classList.add('hidden');
                }
            });

            // Edit second service toggle
            document.getElementById('editHasSecondService').addEventListener('change', function() {
                const fields = document.getElementById('editSecondServiceFields');
                if (this.checked) {
                    fields.classList.remove('hidden');
                } else {
                    fields.classList.add('hidden');
                }
            });

            // Form submission
            document.getElementById('createReportForm').addEventListener('submit', handleCreateReport);
            document.getElementById('editReportForm').addEventListener('submit', handleEditReport);
            
            // Filter controls
                    document.getElementById('applyFiltersBtn').addEventListener('click', () => loadEventReports(1));
            
            // Date filter auto-update
        document.getElementById('dateFromFilter').addEventListener('change', () => loadEventReports(1));
        document.getElementById('dateToFilter').addEventListener('change', () => loadEventReports(1));
        document.getElementById('eventTypeFilter').addEventListener('change', () => loadEventReports(1));
        
        // Pagination event listeners
        document.getElementById('paginationPrev').addEventListener('click', function() {
            if (currentPage > 1) {
                loadEventReports(currentPage - 1);
            }
        });
        
        document.getElementById('paginationNext').addEventListener('click', function() {
            if (currentPage < totalPages) {
                loadEventReports(currentPage + 1);
            }
        });
        
        document.getElementById('perPageSelect').addEventListener('change', function() {
            const newPerPage = parseInt(this.value);
            loadEventReports(1, newPerPage);
        });
            
            // Comparison event listeners are handled in setupComparisonEventListeners()
        }

        // Handle period selection changes
        function handlePeriodChange() {
            const periodSelect = document.getElementById('periodSelect');
            const customDateRange = document.getElementById('customDateRange');
            
            if (periodSelect.value === 'custom') {
                customDateRange.classList.remove('hidden');
            } else {
                customDateRange.classList.add('hidden');
                
                // Set date filters based on selected period
                setDateFiltersForPeriod(periodSelect.value);
                
                // Load data for the selected period
                loadDashboardData();
                loadTrendData();
                loadEventReports();
            }
        }

        // Helper function to set date filters based on period
        function setDateFiltersForPeriod(period) {
            const now = new Date();
            let startDate, endDate;
            
            switch (period) {
                case 'week':
                    // This week (Monday to Sunday)
                    const dayOfWeek = now.getDay();
                    const daysFromMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1; // Sunday = 0, so adjust
                    startDate = new Date(now);
                    startDate.setDate(now.getDate() - daysFromMonday);
                    endDate = new Date(startDate);
                    endDate.setDate(startDate.getDate() + 6);
                    break;
                    
                case 'month':
                    // This month
                    startDate = new Date(now.getFullYear(), now.getMonth(), 1);
                    endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                    break;
                    
                case 'last_month':
                    // Last month
                    startDate = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    endDate = new Date(now.getFullYear(), now.getMonth(), 0);
                    break;
                    
                case 'quarter':
                    // This quarter
                    const quarter = Math.floor(now.getMonth() / 3);
                    startDate = new Date(now.getFullYear(), quarter * 3, 1);
                    endDate = new Date(now.getFullYear(), (quarter + 1) * 3, 0);
                    break;
                    
                case 'year':
                    // This year
                    startDate = new Date(now.getFullYear(), 0, 1);
                    endDate = new Date(now.getFullYear(), 11, 31);
                    break;
                    
                default:
                    // Clear filters for unknown periods
                    document.getElementById('dateFromFilter').value = '';
                    document.getElementById('dateToFilter').value = '';
                    return;
            }
            
            // Set the date filter inputs
            document.getElementById('dateFromFilter').value = startDate.toISOString().split('T')[0];
            document.getElementById('dateToFilter').value = endDate.toISOString().split('T')[0];
        }

        // Apply custom date range
        function applyCustomDateRange() {
            const dateFrom = document.getElementById('customDateFrom').value;
            const dateTo = document.getElementById('customDateTo').value;
            
            if (!dateFrom || !dateTo) {
                alert('Please select both start and end dates.');
                return;
            }
            
            if (new Date(dateFrom) > new Date(dateTo)) {
                alert('Start date cannot be after end date.');
                return;
            }
            
            // Load dashboard data for custom range
            loadDashboardDataForCustomRange(dateFrom, dateTo);
            
            // Set the date filters in the reports section
            document.getElementById('dateFromFilter').value = dateFrom;
            document.getElementById('dateToFilter').value = dateTo;
            
            // Load filtered reports
            loadEventReports();
        }

        // Load dashboard data for custom date range
        async function loadDashboardDataForCustomRange(dateFrom, dateTo) {
            try {
                const branchSelect = document.getElementById('branchSelect');
                const branchId = branchSelect ? branchSelect.value : '';
                
                const params = new URLSearchParams({
                    period: 'custom',
                    date_from: dateFrom,
                    date_to: dateTo
                });
                if (branchId) params.append('branch_id', branchId);
                
                // Load dashboard statistics
                const response = await fetch(`/api/reports/dashboard?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    updateDashboardStats(data.data);
                }
                
                // Load trend data for charts
                const trendResponse = await fetch(`/api/reports/trends?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const trendData = await trendResponse.json();
                
                if (trendData.success) {
                    updateCharts(trendData.data);
                }
                
                // Load Small Groups statistics
                await loadSmallGroupsStats(params);
            } catch (error) {
                console.error('Error loading dashboard data for custom range:', error);
            }
        }

        async function loadDashboardData() {
            try {
                const period = document.getElementById('periodSelect').value;
                const branchSelect = document.getElementById('branchSelect');
                const branchId = branchSelect ? branchSelect.value : '';
                
                console.log('Loading dashboard data for period:', period, 'branch:', branchId);
                
                const params = new URLSearchParams({ period });
                if (branchId) params.append('branch_id', branchId);
                
                console.log('API URL:', `/api/reports/dashboard?${params}`);
                
                // Load dashboard statistics
                const response = await fetch(`/api/reports/dashboard?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                console.log('Dashboard API response:', data);
                
                if (data.success) {
                    updateDashboardStats(data.data);
                } else {
                    console.error('Dashboard API returned error:', data.message);
                }
                
                // Load trend data for charts
                const trendResponse = await fetch(`/api/reports/trends?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const trendData = await trendResponse.json();
                
                console.log('Trend API response:', trendData);
                
                if (trendData.success) {
                    updateCharts(trendData.data);
                } else {
                    console.error('Trend API returned error:', trendData.message);
                }
                
                // Load Small Groups statistics
                await loadSmallGroupsStats(params);
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        }

        // Load Small Groups statistics
        async function loadSmallGroupsStats(params) {
            try {
                console.log('Loading Small Groups statistics with params:', params.toString());
                
                // Get API token from meta tag
                const apiToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
                const headers = {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                };
                
                // Use API token if available, otherwise use CSRF token
                if (apiToken) {
                    headers['Authorization'] = `Bearer ${apiToken}`;
                } else {
                    headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                }
                
                const response = await fetch(`/api/small-group-reports/statistics?${params}`, {
                    headers: headers,
                    credentials: 'same-origin'
                });
                
                if (!response.ok) {
                    console.error('Small Groups API error:', response.status, response.statusText);
                    return;
                }
                
                const data = await response.json();
                console.log('Small Groups API response:', data);
                
                if (data.success) {
                    updateSmallGroupsStats(data.data);
                } else {
                    console.error('Small Groups API returned error:', data.message);
                }
            } catch (error) {
                console.error('Error loading Small Groups statistics:', error);
            }
        }

        // Update Small Groups statistics display
        function updateSmallGroupsStats(data) {
            console.log('updateSmallGroupsStats called with data:', data);
            
            // Update Total Attendance
            const totalAttendanceElement = document.getElementById('smallGroupsTotalAttendance');
            if (totalAttendanceElement) {
                totalAttendanceElement.textContent = data.total_attendance || '0';
            }
            
            // Update Average Attendance
            const avgAttendanceElement = document.getElementById('smallGroupsAvgAttendance');
            if (avgAttendanceElement) {
                const avgAttendance = data.total_reports > 0 ? Math.round(data.total_attendance / data.total_reports) : 0;
                avgAttendanceElement.textContent = avgAttendance;
            }
            
            // Update Active Groups
            const activeGroupsElement = document.getElementById('smallGroupsActiveGroups');
            if (activeGroupsElement) {
                activeGroupsElement.textContent = data.active_groups || '0';
            }
            
            // Update Total Guests
            const totalGuestsElement = document.getElementById('smallGroupsTotalGuests');
            if (totalGuestsElement) {
                totalGuestsElement.textContent = data.total_guests || '0';
            }
        }

        function updateDashboardStats(data) {
            // Update dashboard statistics with new data
            console.log('updateDashboardStats called with data:', data);
            
            // Update Total Attendance
            if (data.attendance && typeof data.attendance.total_attendance !== 'undefined') {
                const totalAttendanceElement = document.getElementById('totalAttendance');
                if (totalAttendanceElement) {
                    console.log('Updating totalAttendance from', totalAttendanceElement.textContent, 'to', data.attendance.total_attendance);
                    totalAttendanceElement.textContent = data.attendance.total_attendance || '0';
                }
            }
            
            // Update Average Attendance with gender breakdown
            if (data.attendance && typeof data.attendance.average_attendance !== 'undefined') {
                const avgAttendanceElement = document.getElementById('avgAttendance');
                if (avgAttendanceElement) {
                    const avg = Math.round(data.attendance.average_attendance || 0);
                    
                    // Include gender breakdown if available
                    const genderStats = data.attendance.average_by_gender || {};
                    const avgMale = Math.round(genderStats.male || 0);
                    const avgFemale = Math.round(genderStats.female || 0);
                    const avgChildren = Math.round(genderStats.children || 0);
                    
                    console.log('Updating avgAttendance from', avgAttendanceElement.textContent, 'to', avg);
                    avgAttendanceElement.textContent = avg;
                }
            }
            
            // Update Total Converts
            if (data.attendance && typeof data.attendance.total_converts !== 'undefined') {
                const totalConvertsElement = document.getElementById('totalConverts');
                if (totalConvertsElement) {
                    console.log('Updating totalConverts from', totalConvertsElement.textContent, 'to', data.attendance.total_converts);
                    totalConvertsElement.textContent = data.attendance.total_converts || '0';
                }
            }
            
            // Update First Time Guests
            if (data.attendance && typeof data.attendance.total_first_time_guests !== 'undefined') {
                const totalGuestsElement = document.getElementById('totalGuests');
                if (totalGuestsElement) {
                    console.log('Updating totalGuests from', totalGuestsElement.textContent, 'to', data.attendance.total_first_time_guests);
                    totalGuestsElement.textContent = data.attendance.total_first_time_guests || '0';
                }
            }

            // Update Demographic Breakdown Widgets
            if (data.attendance && data.attendance.percentages_by_gender && data.attendance.totals_by_gender) {
                const percentages = data.attendance.percentages_by_gender;
                const totals = data.attendance.totals_by_gender;

                // Update Male Percentage
                const malePercentageElement = document.getElementById('malePercentage');
                const maleTotalElement = document.getElementById('maleTotal');
                if (malePercentageElement && maleTotalElement) {
                    malePercentageElement.textContent = `${percentages.male || 0}%`;
                    maleTotalElement.textContent = `(${totals.male || 0})`;
                }

                // Update Female Percentage
                const femalePercentageElement = document.getElementById('femalePercentage');
                const femaleTotalElement = document.getElementById('femaleTotal');
                if (femalePercentageElement && femaleTotalElement) {
                    femalePercentageElement.textContent = `${percentages.female || 0}%`;
                    femaleTotalElement.textContent = `(${totals.female || 0})`;
                }

                // Update Children Percentage
                const childrenPercentageElement = document.getElementById('childrenPercentage');
                const childrenTotalElement = document.getElementById('childrenTotal');
                if (childrenPercentageElement && childrenTotalElement) {
                    childrenPercentageElement.textContent = `${percentages.children || 0}%`;
                    childrenTotalElement.textContent = `(${totals.children || 0})`;
                }

                // Update Online Percentage
                const onlinePercentageElement = document.getElementById('onlinePercentage');
                const onlineTotalElement = document.getElementById('onlineTotal');
                if (onlinePercentageElement && onlineTotalElement) {
                    onlinePercentageElement.textContent = `${percentages.online || 0}%`;
                    onlineTotalElement.textContent = `(${totals.online || 0})`;
                }
            }
        }

        function updateDashboardStatsFromReports(summary) {
            // Update dashboard stats based on filtered event reports
            if (summary.total_attendance !== undefined) {
                // Update Total Attendance
                const totalAttendanceElement = document.getElementById('totalAttendance');
                if (totalAttendanceElement) {
                    totalAttendanceElement.textContent = summary.total_attendance || 0;
                }
                
                // Update Average Attendance
                const avgAttendance = Math.round(summary.average_attendance || 0);
                const avgMale = Math.round(summary.average_male || 0);
                const avgFemale = Math.round(summary.average_female || 0);
                const avgChildren = Math.round(summary.average_children || 0);
                
                const avgAttendanceElement = document.getElementById('avgAttendance');
                avgAttendanceElement.textContent = avgAttendance;
            }
            
            if (summary.total_converts !== undefined) {
                document.getElementById('totalConverts').textContent = summary.total_converts || 0;
            }
            
            if (summary.total_first_time_guests !== undefined) {
                document.getElementById('totalGuests').textContent = summary.total_first_time_guests || 0;
            }

            // Update demographic breakdown widgets if summary has the required data
            if (summary.total_male !== undefined && summary.total_female !== undefined && 
                summary.total_children !== undefined && summary.total_online !== undefined) {
                
                const totalAttendance = summary.total_attendance || 0;
                const totalMale = summary.total_male || 0;
                const totalFemale = summary.total_female || 0;
                const totalChildren = summary.total_children || 0;
                const totalOnline = summary.total_online || 0;

                // Calculate percentages
                const malePercentage = totalAttendance > 0 ? Math.round((totalMale / totalAttendance) * 100 * 10) / 10 : 0;
                const femalePercentage = totalAttendance > 0 ? Math.round((totalFemale / totalAttendance) * 100 * 10) / 10 : 0;
                const childrenPercentage = totalAttendance > 0 ? Math.round((totalChildren / totalAttendance) * 100 * 10) / 10 : 0;
                const onlinePercentage = totalAttendance > 0 ? Math.round((totalOnline / totalAttendance) * 100 * 10) / 10 : 0;

                // Update Male Percentage
                const malePercentageElement = document.getElementById('malePercentage');
                const maleTotalElement = document.getElementById('maleTotal');
                if (malePercentageElement && maleTotalElement) {
                    malePercentageElement.textContent = `${malePercentage}%`;
                    maleTotalElement.textContent = `(${totalMale})`;
                }

                // Update Female Percentage
                const femalePercentageElement = document.getElementById('femalePercentage');
                const femaleTotalElement = document.getElementById('femaleTotal');
                if (femalePercentageElement && femaleTotalElement) {
                    femalePercentageElement.textContent = `${femalePercentage}%`;
                    femaleTotalElement.textContent = `(${totalFemale})`;
                }

                // Update Children Percentage
                const childrenPercentageElement = document.getElementById('childrenPercentage');
                const childrenTotalElement = document.getElementById('childrenTotal');
                if (childrenPercentageElement && childrenTotalElement) {
                    childrenPercentageElement.textContent = `${childrenPercentage}%`;
                    childrenTotalElement.textContent = `(${totalChildren})`;
                }

                // Update Online Percentage
                const onlinePercentageElement = document.getElementById('onlinePercentage');
                const onlineTotalElement = document.getElementById('onlineTotal');
                if (onlinePercentageElement && onlineTotalElement) {
                    onlinePercentageElement.textContent = `${onlinePercentage}%`;
                    onlineTotalElement.textContent = `(${totalOnline})`;
                }
            }
        }

        function updateCharts(trendData) {
            // Update attendance chart with event type data (Bar Chart)
            if (trendData.attendance_by_event_type && Array.isArray(trendData.attendance_by_event_type)) {
                const labels = trendData.attendance_by_event_type.map(item => item.event_type);
                const data = trendData.attendance_by_event_type.map(item => item.total_attendance || 0);
                
                window.attendanceChart.data.labels = labels;
                window.attendanceChart.data.datasets[0].data = data;
                window.attendanceChart.update();
            }

            // Update weekly Sunday service chart if breakdown data is available (Line Chart)
            if (trendData.weekly_sunday_breakdown && Array.isArray(trendData.weekly_sunday_breakdown)) {
                const labels = trendData.weekly_sunday_breakdown.map(item => item.week || item.label);
                const data = trendData.weekly_sunday_breakdown.map(item => item.attendance || 0);
                
                window.eventTypeChart.data.labels = labels;
                window.eventTypeChart.data.datasets[0].data = data;
                window.eventTypeChart.update();
            }
        }

        async function loadFilteredChartData(dateFrom, dateTo, branchId) {
            try {
                const params = new URLSearchParams({
                    period: 'custom',
                    date_from: dateFrom,
                    date_to: dateTo
                });
                if (branchId) params.append('branch_id', branchId);
                
                const response = await fetch(`/api/reports/trends?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    updateCharts(data.data);
                }
            } catch (error) {
                console.error('Error loading filtered chart data:', error);
            }
        }

        // Pagination state variables
        let currentPage = 1;
        let currentPerPage = 20;
        let totalPages = 1;
        let totalRecords = 0;

        async function loadEventReports(page = 1, perPage = null) {
            try {
                // Update pagination state
                currentPage = page;
                if (perPage) currentPerPage = perPage;
                
                const params = new URLSearchParams();
                
                const eventType = document.getElementById('eventTypeFilter').value;
                const dateFrom = document.getElementById('dateFromFilter').value;
                const dateTo = document.getElementById('dateToFilter').value;
                
                // Add branch filter
                const branchSelect = document.getElementById('branchSelect');
                const branchId = branchSelect ? branchSelect.value : '';
                
                if (eventType) params.append('event_type', eventType);
                if (dateFrom) params.append('date_from', dateFrom);
                if (dateTo) params.append('date_to', dateTo);
                if (branchId) params.append('branch_id', branchId);
                
                // Add pagination parameters
                params.append('page', currentPage);
                params.append('per_page', currentPerPage);
                
                const response = await fetch(`/api/reports/event-reports?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    updateReportsTable(data.data);
                    updatePaginationControls(data.data.reports);
                    
                    // Also update dashboard stats based on filtered data
                    if (data.data.summary && typeof updateDashboardStatsFromReports === 'function') {
                        updateDashboardStatsFromReports(data.data.summary);
                    }
                    
                    // Update charts with filtered data if date range is specified
                    if (dateFrom && dateTo) {
                        loadFilteredChartData(dateFrom, dateTo, branchId);
                    }
                }
            } catch (error) {
                console.error('Error loading event reports:', error);
            }
        }

        function updateReportsTable(data) {
            const tbody = document.getElementById('reportsTableBody');
            tbody.innerHTML = '';
            
            // Handle the data structure returned by getEventReports
            const reports = data.reports?.data || data.reports || [];
            
            if (!Array.isArray(reports)) {
                console.error('Reports data is not an array:', reports);
                return;
            }
            
            reports.forEach(report => {
                const row = document.createElement('tr');
                // Format the date properly
                const reportDate = report.report_date ? new Date(report.report_date).toLocaleDateString() : 'Invalid Date';
                
                // Create gender breakdown display
                const genderBreakdown = report.combined_totals_by_gender || {};
                const male = genderBreakdown.male || 0;
                const female = genderBreakdown.female || 0;
                const children = genderBreakdown.children || 0;
                const online = genderBreakdown.online || 0;
                const attendanceDisplay = `${report.combined_total_attendance || 0} <span class="text-red-600 text-xs">(M:${male} F:${female} C:${children} O:${online})</span>`;
                
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${reportDate}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${report.event_type || 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${attendanceDisplay}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${report.combined_first_time_guests || 0}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${report.combined_converts || 0}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${report.is_multi_service ? '2 Services' : '1 Service'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button class="text-blue-600 hover:text-blue-900 mr-2" onclick="editReport(${report.id})">Edit</button>
                        <button class="text-red-600 hover:text-red-900" onclick="deleteReport(${report.id})">Delete</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function updatePaginationControls(paginationData) {
            // Update pagination state
            currentPage = paginationData.current_page || 1;
            totalPages = paginationData.last_page || 1;
            totalRecords = paginationData.total || 0;
            
            // Update pagination display text
            const start = ((currentPage - 1) * currentPerPage) + 1;
            const end = Math.min(currentPage * currentPerPage, totalRecords);
            
            document.getElementById('paginationStart').textContent = totalRecords === 0 ? 0 : start;
            document.getElementById('paginationEnd').textContent = end;
            document.getElementById('paginationTotal').textContent = totalRecords;
            
            // Update pagination buttons
            const prevBtn = document.getElementById('paginationPrev');
            const nextBtn = document.getElementById('paginationNext');
            
            prevBtn.disabled = currentPage <= 1;
            nextBtn.disabled = currentPage >= totalPages;
            
            // Update page numbers
            updatePageNumbers();
        }

        function updatePageNumbers() {
            const numbersContainer = document.getElementById('paginationNumbers');
            numbersContainer.innerHTML = '';
            
            // Calculate page range to show (show 5 pages at a time)
            const maxPagesToShow = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
            let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
            
            // Adjust start if we're near the end
            if (endPage - startPage < maxPagesToShow - 1) {
                startPage = Math.max(1, endPage - maxPagesToShow + 1);
            }
            
            for (let page = startPage; page <= endPage; page++) {
                const pageBtn = document.createElement('button');
                pageBtn.textContent = page;
                pageBtn.className = `px-3 py-1 text-sm border border-gray-300 hover:bg-gray-50 ${
                    page === currentPage ? 'bg-blue-500 text-white border-blue-500' : 'text-gray-700'
                }`;
                pageBtn.onclick = () => loadEventReports(page);
                numbersContainer.appendChild(pageBtn);
            }
        }

        async function loadEventTypes() {
            try {
                const response = await fetch('/api/reports/event-types', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    const selects = document.querySelectorAll('select[name="event_type"], #eventTypeFilter, #editEventType');
                    selects.forEach(select => {
                        // Skip if it already has options (to avoid duplication)
                        if (select.children.length > 1) return;
                        
                        data.data.forEach(type => {
                            const option = document.createElement('option');
                            option.value = type;
                            option.textContent = type;
                            select.appendChild(option);
                        });
                    });
                }
            } catch (error) {
                console.error('Error loading event types:', error);
            }
        }

        async function loadEvents() {
            try {
                // Get branch filter if available (for Super Admin)
                const branchSelect = document.getElementById('branchSelect');
                const branchId = branchSelect ? branchSelect.value : '';
                
                // Build URL with branch filter parameter
                const params = new URLSearchParams();
                if (branchId) {
                    params.append('branch_id', branchId);
                }
                
                const url = `/api/events${params.toString() ? '?' + params.toString() : ''}`;
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.success && data.data) {
                    const eventSelects = document.querySelectorAll('#eventSelect, #editEventSelect');
                    // Handle both paginated response (data.data.data) and direct array (data.data)
                    const events = data.data.data || data.data;
                    
                    // Clear existing options except the first one for each select
                    eventSelects.forEach(eventSelect => {
                        eventSelect.innerHTML = '<option value="">Select Event</option>';
                        
                        if (Array.isArray(events)) {
                            events.forEach(event => {
                                const option = document.createElement('option');
                                option.value = event.id;
                                // Use 'name' field if 'title' doesn't exist
                                const title = event.title || event.name;
                                const date = event.event_date || event.start_date;
                                option.textContent = `${title} - ${new Date(date).toLocaleDateString()}`;
                                eventSelect.appendChild(option);
                            });
                        } else {
                            console.warn('Events is not an array:', events);
                        }
                    });
                } else {
                    console.warn('Events data not in expected format:', data);
                }
            } catch (error) {
                console.error('Error loading events:', error);
            }
        }

        function showCreateModal() {
            document.getElementById('createReportModal').classList.remove('hidden');
            loadEvents(); // Reload events when modal opens
        }

        function hideCreateModal() {
            document.getElementById('createReportModal').classList.add('hidden');
            document.getElementById('createReportForm').reset();
            document.getElementById('secondServiceFields').classList.add('hidden');
        }

        async function handleCreateReport(e) {
            e.preventDefault();
            
            try {
                const formData = new FormData(e.target);
                const data = Object.fromEntries(formData);
                
                // Ensure has_second_service is properly set
                data.has_second_service = document.getElementById('hasSecondService').checked;
                
                // If second service is not enabled, remove second service fields to avoid validation errors
                if (!data.has_second_service) {
                    delete data.second_service_start_time;
                    delete data.second_service_end_time;
                    delete data.second_male_attendance;
                    delete data.second_female_attendance;
                    delete data.second_children_attendance;
                    delete data.second_online_attendance;
                    delete data.second_first_time_guests;
                    delete data.second_converts;
                    delete data.second_cars;
                }
                
                console.log('Submitting data:', data); // Debug log
                
                const response = await fetch('/api/reports/event-reports', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Response error:', errorText);
                    throw new Error(`HTTP ${response.status}: ${errorText}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    hideCreateModal();
                    loadEventReports();
                    loadDashboardData(); // Refresh dashboard stats
                    alert('Event report created successfully!');
                } else {
                    console.error('API Error:', result);
                    alert('Error creating report: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error creating report:', error);
                alert('Error creating report: ' + error.message);
            }
        }

        // Edit and delete functionality
        async function editReport(id) {
            try {
                console.log('Loading report for edit:', id);
                
                // First show the modal and load the dropdowns
                showEditModal();
                
                // Wait for events and event types to load
                await Promise.all([loadEvents(), loadEventTypes()]);
                
                // Then fetch the report data
                const response = await fetch(`/api/reports/event-reports/${id}`);
                const result = await response.json();
                
                console.log('Report data received:', result.data);
                
                if (result.success) {
                    populateEditForm(result.data);
                } else {
                    alert('Error loading report: ' + result.message);
                }
            } catch (error) {
                console.error('Error loading report for edit:', error);
                alert('Error loading report for edit: ' + error.message);
            }
        }

        function populateEditForm(report) {
            console.log('Populating edit form with:', report);
            
            // Store the report ID for updating
            document.getElementById('editReportForm').dataset.reportId = report.id;
            
            // Populate basic fields
            document.getElementById('editEventSelect').value = report.event_id || '';
            
            // Format date properly for input field (YYYY-MM-DD)
            if (report.report_date) {
                const date = new Date(report.report_date);
                const formattedDate = date.toISOString().split('T')[0];
                document.getElementById('editEventDate').value = formattedDate;
            }
            
            document.getElementById('editEventType').value = report.event_type || '';
            
            // Format time properly (HH:MM) - avoid timezone conversion
            if (report.start_time) {
                let startTime;
                if (report.start_time.includes('T')) {
                    // Extract time part directly without timezone conversion
                    startTime = report.start_time.split('T')[1].slice(0, 5);
                } else {
                    startTime = report.start_time.slice(0, 5);
                }
                document.getElementById('editStartTime').value = startTime;
            }
            
            if (report.end_time) {
                let endTime;
                if (report.end_time.includes('T')) {
                    // Extract time part directly without timezone conversion
                    endTime = report.end_time.split('T')[1].slice(0, 5);
                } else {
                    endTime = report.end_time.slice(0, 5);
                }
                document.getElementById('editEndTime').value = endTime;
            }
            
            // Populate first service attendance
            document.getElementById('editMaleAttendance').value = report.attendance_male || 0;
            document.getElementById('editFemaleAttendance').value = report.attendance_female || 0;
            document.getElementById('editChildrenAttendance').value = report.attendance_children || 0;
            document.getElementById('editOnlineAttendance').value = report.online_attendance || 0;
            document.getElementById('editFirstTimeGuests').value = report.first_time_guests || 0;
            document.getElementById('editConverts').value = report.converts || 0;
            document.getElementById('editCars').value = report.number_of_cars || 0;
            
            // Handle second service
            const hasSecondService = report.is_multi_service;
            document.getElementById('editHasSecondService').checked = hasSecondService;
            
            if (hasSecondService) {
                document.getElementById('editSecondServiceFields').classList.remove('hidden');
                
                // Format second service times properly - avoid timezone conversion
                if (report.second_service_start_time) {
                    let secondStartTime;
                    if (report.second_service_start_time.includes('T')) {
                        // Extract time part directly without timezone conversion
                        secondStartTime = report.second_service_start_time.split('T')[1].slice(0, 5);
                    } else {
                        secondStartTime = report.second_service_start_time.slice(0, 5);
                    }
                    document.getElementById('editSecondServiceStartTime').value = secondStartTime;
                }
                
                if (report.second_service_end_time) {
                    let secondEndTime;
                    if (report.second_service_end_time.includes('T')) {
                        // Extract time part directly without timezone conversion
                        secondEndTime = report.second_service_end_time.split('T')[1].slice(0, 5);
                    } else {
                        secondEndTime = report.second_service_end_time.slice(0, 5);
                    }
                    document.getElementById('editSecondServiceEndTime').value = secondEndTime;
                }
                document.getElementById('editSecondMaleAttendance').value = report.second_service_attendance_male || 0;
                document.getElementById('editSecondFemaleAttendance').value = report.second_service_attendance_female || 0;
                document.getElementById('editSecondChildrenAttendance').value = report.second_service_attendance_children || 0;
                document.getElementById('editSecondOnlineAttendance').value = report.second_service_attendance_online || 0;
                document.getElementById('editSecondFirstTimeGuests').value = report.second_service_first_time_guests || 0;
                document.getElementById('editSecondConverts').value = report.second_service_converts || 0;
                document.getElementById('editSecondCars').value = report.second_service_number_of_cars || 0;
            } else {
                document.getElementById('editSecondServiceFields').classList.add('hidden');
            }
            
            // Populate notes
            document.getElementById('editNotes').value = report.notes || '';
        }

        function showEditModal() {
            document.getElementById('editReportModal').classList.remove('hidden');
        }

        function hideEditModal() {
            document.getElementById('editReportModal').classList.add('hidden');
            document.getElementById('editReportForm').reset();
            document.getElementById('editSecondServiceFields').classList.add('hidden');
        }

        async function handleEditReport(e) {
            e.preventDefault();
            
            try {
                const reportId = e.target.dataset.reportId;
                const formData = new FormData(e.target);
                const data = Object.fromEntries(formData);
                
                // Ensure has_second_service is properly set
                data.has_second_service = document.getElementById('editHasSecondService').checked;
                
                // If second service is not enabled, remove second service fields
                if (!data.has_second_service) {
                    delete data.second_service_start_time;
                    delete data.second_service_end_time;
                    delete data.second_male_attendance;
                    delete data.second_female_attendance;
                    delete data.second_children_attendance;
                    delete data.second_online_attendance;
                    delete data.second_first_time_guests;
                    delete data.second_converts;
                    delete data.second_cars;
                }
                
                console.log('Updating report:', reportId, data);
                
                const response = await fetch(`/api/reports/event-reports/${reportId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Response error:', errorText);
                    throw new Error(`HTTP ${response.status}: ${errorText}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    hideEditModal();
                    loadEventReports();
                    loadDashboardData(); // Refresh dashboard stats
                    alert('Event report updated successfully!');
                } else {
                    console.error('API Error:', result);
                    alert('Error updating report: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error updating report:', error);
                alert('Error updating report: ' + error.message);
            }
        }

        async function deleteReport(id) {
            if (confirm('Are you sure you want to delete this report? This action cannot be undone.')) {
                try {
                    const response = await fetch(`/api/reports/event-reports/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        loadEventReports();
                        loadDashboardData(); // Refresh dashboard stats
                        alert('Report deleted successfully!');
                    } else {
                        alert('Error deleting report: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error deleting report:', error);
                    alert('Error deleting report: ' + error.message);
                }
            }
        }

        function setupComparisonEventListeners() {
            // Preset comparison selector
            document.getElementById('comparisonPreset').addEventListener('change', function() {
                const preset = this.value;
                if (preset) {
                    applyComparisonPreset(preset);
                }
            });

            // Apply preset button
            document.getElementById('applyPresetBtn').addEventListener('click', function() {
                const preset = document.getElementById('comparisonPreset').value;
                if (preset) {
                    applyComparisonPreset(preset);
                    comparePeriods();
                }
            });

            // Compare periods button
            document.getElementById('comparePeriodsBtn').addEventListener('click', comparePeriods);
        }

        function applyComparisonPreset(preset) {
            const today = new Date();
            let period1Start, period1End, period2Start, period2End;

            switch (preset) {
                case 'this_month_vs_last':
                    // This month vs last month
                    period1Start = new Date(today.getFullYear(), today.getMonth(), 1);
                    period1End = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    period2Start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    period2End = new Date(today.getFullYear(), today.getMonth(), 0);
                    break;

                case 'this_quarter_vs_last':
                    // This quarter vs last quarter
                    const currentQuarter = Math.floor(today.getMonth() / 3);
                    period1Start = new Date(today.getFullYear(), currentQuarter * 3, 1);
                    period1End = new Date(today.getFullYear(), (currentQuarter + 1) * 3, 0);
                    period2Start = new Date(today.getFullYear(), (currentQuarter - 1) * 3, 1);
                    period2End = new Date(today.getFullYear(), currentQuarter * 3, 0);
                    break;

                case 'last_6_months_vs_previous':
                    // Last 6 months vs previous 6 months
                    period1End = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    period1Start = new Date(today.getFullYear(), today.getMonth() - 5, 1);
                    period2End = new Date(today.getFullYear(), today.getMonth() - 5, 0);
                    period2Start = new Date(today.getFullYear(), today.getMonth() - 11, 1);
                    break;

                case 'this_year_vs_last':
                    // This year vs last year
                    period1Start = new Date(today.getFullYear(), 0, 1);
                    period1End = new Date(today.getFullYear(), 11, 31);
                    period2Start = new Date(today.getFullYear() - 1, 0, 1);
                    period2End = new Date(today.getFullYear() - 1, 11, 31);
                    break;

                case 'last_30_days_vs_previous':
                    // Last 30 days vs previous 30 days
                    period1End = new Date(today);
                    period1Start = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
                    period2End = new Date(period1Start.getTime() - (24 * 60 * 60 * 1000));
                    period2Start = new Date(period2End.getTime() - (30 * 24 * 60 * 60 * 1000));
                    break;

                default:
                    return;
            }

            // Format dates as YYYY-MM-DD for input fields
            document.getElementById('period1Start').value = period1Start.toISOString().split('T')[0];
            document.getElementById('period1End').value = period1End.toISOString().split('T')[0];
            document.getElementById('period2Start').value = period2Start.toISOString().split('T')[0];
            document.getElementById('period2End').value = period2End.toISOString().split('T')[0];
        }

        async function comparePeriods() {
            try {
                const period1Start = document.getElementById('period1Start').value;
                const period1End = document.getElementById('period1End').value;
                const period2Start = document.getElementById('period2Start').value;
                const period2End = document.getElementById('period2End').value;
                
                if (!period1Start || !period1End || !period2Start || !period2End) {
                    alert('Please select all dates for comparison.');
                    return;
                }

                const params = new URLSearchParams({
                    period1_start: period1Start,
                    period1_end: period1End,
                    period2_start: period2Start,
                    period2_end: period2End
                });

                // Add branch filter if selected
                const branchSelect = document.getElementById('comparisonBranch');
                const branchId = branchSelect ? branchSelect.value : '';
                if (branchId) {
                    params.append('branch_id', branchId);
                }
                
                const response = await fetch(`/api/reports/comparative?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    displayComparisonResults(data.data);
                } else {
                    console.error('Comparison API returned error:', data.message);
                    alert('Error loading comparison data: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error comparing periods:', error);
            }
        }

        function displayComparisonResults(comparison) {
            const resultsDiv = document.getElementById('comparisonResults');
            resultsDiv.classList.remove('hidden');
            
            const formatPercentage = (value) => {
                const num = parseFloat(value) || 0;
                const sign = num >= 0 ? '+' : '';
                return `${sign}${num.toFixed(1)}%`;
            };
            
            const getChangeColor = (value) => {
                const num = parseFloat(value) || 0;
                return num >= 0 ? 'text-green-700' : 'text-red-700';
            };
            
            const highestP1 = comparison.highest_attendance_period1;
            const highestP2 = comparison.highest_attendance_period2;
            
            resultsDiv.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h5 class="font-medium text-blue-900">Attendance</h5>
                        <p class="text-2xl font-bold ${getChangeColor(comparison.attendance?.change)}">${formatPercentage(comparison.attendance?.change)}</p>
                        <p class="text-sm text-blue-600">${comparison.attendance?.period1 || 0} → ${comparison.attendance?.period2 || 0}</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h5 class="font-medium text-green-900">Converts</h5>
                        <p class="text-2xl font-bold ${getChangeColor(comparison.converts?.change)}">${formatPercentage(comparison.converts?.change)}</p>
                        <p class="text-sm text-green-600">${comparison.converts?.period1 || 0} → ${comparison.converts?.period2 || 0}</p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <h5 class="font-medium text-purple-900">First Time Guests</h5>
                        <p class="text-2xl font-bold ${getChangeColor(comparison.guests?.change)}">${formatPercentage(comparison.guests?.change)}</p>
                        <p class="text-sm text-purple-600">${comparison.guests?.period1 || 0} → ${comparison.guests?.period2 || 0}</p>
                    </div>
                </div>
                
                ${highestP1 || highestP2 ? `
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h5 class="font-medium text-gray-900 mb-3">Highest Attendance Events</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            ${highestP1 ? `
                                <div class="bg-white p-3 rounded border">
                                    <h6 class="font-medium text-sm text-gray-700">Period 1</h6>
                                    <p class="text-lg font-bold text-blue-600">${highestP1.attendance} attendees</p>
                                    <p class="text-sm text-gray-600">${highestP1.event_name}</p>
                                    <p class="text-xs text-gray-500">${new Date(highestP1.date).toLocaleDateString()} • ${highestP1.event_type}</p>
                                </div>
                            ` : ''}
                            ${highestP2 ? `
                                <div class="bg-white p-3 rounded border">
                                    <h6 class="font-medium text-sm text-gray-700">Period 2</h6>
                                    <p class="text-lg font-bold text-blue-600">${highestP2.attendance} attendees</p>
                                    <p class="text-sm text-gray-600">${highestP2.event_name}</p>
                                    <p class="text-xs text-gray-500">${new Date(highestP2.date).toLocaleDateString()} • ${highestP2.event_type}</p>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                ` : ''}
            `;
        }

        async function loadTrendData() {
            try {
                const period = document.getElementById('periodSelect').value;
                const branchSelect = document.getElementById('branchSelect');
                const branchId = branchSelect ? branchSelect.value : '';
                
                const params = new URLSearchParams({ period });
                if (branchId) params.append('branch_id', branchId);
                
                const response = await fetch(`/api/reports/trends?${params}`);
                const data = await response.json();
                
                if (data.success && data.data) {
                    updateCharts(data.data);
                }
            } catch (error) {
                console.error('Error loading trend data:', error);
                // Charts will display with default empty data
            }
        }

        // Export dashboard to PDF
        async function exportToPDF() {
            try {
                // Show loading state
                const exportBtn = document.getElementById('exportPdfBtn');
                const originalText = exportBtn.innerHTML;
                exportBtn.innerHTML = `
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Generating...
                `;
                exportBtn.disabled = true;

                // Get current period and date range for filename
                const period = document.getElementById('periodSelect').value;
                const dateFrom = document.getElementById('dateFromFilter').value;
                const dateTo = document.getElementById('dateToFilter').value;
                
                // Get branch information
                const branchSelect = document.getElementById('branchSelect');
                const branchName = branchSelect && branchSelect.value ? 
                    branchSelect.options[branchSelect.selectedIndex].text : 'All Branches';
                
                // Create filename based on period and dates
                let filename = 'church-dashboard-report';
                if (dateFrom && dateTo) {
                    filename += `-${dateFrom}-to-${dateTo}`;
                } else {
                    const periodNames = {
                        'week': 'this-week',
                        'month': 'this-month',
                        'last_month': 'last-month',
                        'quarter': 'this-quarter',
                        'year': 'this-year'
                    };
                    filename += `-${periodNames[period] || period}`;
                }
                
                // Add branch to filename if specific branch is selected
                if (branchSelect && branchSelect.value) {
                    const branchSlug = branchName.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
                    filename += `-${branchSlug}`;
                }
                
                filename += '.pdf';

                // Create a temporary container for the content to export
                const exportContainer = document.createElement('div');
                exportContainer.style.cssText = `
                    position: absolute;
                    top: -9999px;
                    left: -9999px;
                    width: 1200px;
                    background: white;
                    padding: 20px;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                `;

                // Get current period info for header
                // Get actual date range for the period
                let actualDateRange = '';
                if (dateFrom && dateTo) {
                    actualDateRange = `${dateFrom} to ${dateTo}`;
                } else {
                    // Calculate actual date range based on period
                    const now = new Date();
                    let startDate, endDate;
                    
                    switch(period) {
                        case 'week':
                            startDate = new Date(now);
                            startDate.setDate(now.getDate() - now.getDay()); // Start of week (Sunday)
                            endDate = new Date(startDate);
                            endDate.setDate(startDate.getDate() + 6); // End of week (Saturday)
                            break;
                        case 'month':
                            startDate = new Date(now.getFullYear(), now.getMonth(), 1);
                            endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                            break;
                        case 'last_month':
                            startDate = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                            endDate = new Date(now.getFullYear(), now.getMonth(), 0);
                            break;
                        case 'quarter':
                            const quarterStart = Math.floor(now.getMonth() / 3) * 3;
                            startDate = new Date(now.getFullYear(), quarterStart, 1);
                            endDate = new Date(now.getFullYear(), quarterStart + 3, 0);
                            break;
                        case 'year':
                            startDate = new Date(now.getFullYear(), 0, 1);
                            endDate = new Date(now.getFullYear(), 11, 31);
                            break;
                        default:
                            startDate = endDate = now;
                    }
                    
                    actualDateRange = `${startDate.toLocaleDateString()} to ${endDate.toLocaleDateString()}`;
                }

                // Build the export content
                exportContainer.innerHTML = `
                    <div style="margin-bottom: 30px; text-align: center; border-bottom: 2px solid #e5e7eb; padding-bottom: 20px;">
                        <h1 style="font-size: 24px; font-weight: bold; color: #1f2937; margin: 0;">${branchName === 'All Branches' ? 'Church Dashboard Report' : `${branchName} Event Report`}</h1>
                        <p style="font-size: 16px; color: #6b7280; margin: 15px 0 5px 0;">Period: ${actualDateRange}</p>
                        <p style="font-size: 14px; color: #9ca3af; margin: 5px 0 0 0;">Generated on ${new Date().toLocaleDateString()}</p>
                    </div>

                    <div style="margin-bottom: 30px;">
                        <h2 style="font-size: 18px; font-weight: 600; color: #1f2937; margin-bottom: 15px;">Summary Statistics</h2>
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
                            <div style="background: #dbeafe; padding: 20px; border-radius: 8px; text-align: center;">
                                <p style="font-size: 14px; color: #2563eb; margin: 0; font-weight: 500;">Total Attendance</p>
                                <p style="font-size: 24px; color: #1e3a8a; margin: 5px 0 0 0; font-weight: bold;">${document.getElementById('totalAttendance').textContent}</p>
                            </div>
                            <div style="background: #dcfce7; padding: 20px; border-radius: 8px; text-align: center;">
                                <p style="font-size: 14px; color: #16a34a; margin: 0; font-weight: 500;">Average Attendance</p>
                                <p style="font-size: 24px; color: #15803d; margin: 5px 0 0 0; font-weight: bold;">${document.getElementById('avgAttendance').textContent}</p>
                            </div>
                            <div style="background: #fef3c7; padding: 20px; border-radius: 8px; text-align: center;">
                                <p style="font-size: 14px; color: #d97706; margin: 0; font-weight: 500;">New Converts</p>
                                <p style="font-size: 24px; color: #92400e; margin: 5px 0 0 0; font-weight: bold;">${document.getElementById('totalConverts').textContent}</p>
                            </div>
                            <div style="background: #e9d5ff; padding: 20px; border-radius: 8px; text-align: center;">
                                <p style="font-size: 14px; color: #7c3aed; margin: 0; font-weight: 500;">First Time Guests</p>
                                <p style="font-size: 24px; color: #5b21b6; margin: 5px 0 0 0; font-weight: bold;">${document.getElementById('totalGuests').textContent}</p>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 30px;">
                        <h2 style="font-size: 18px; font-weight: 600; color: #1f2937; margin-bottom: 15px;">Demographic Breakdown</h2>
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
                            <div style="background: #e0e7ff; padding: 15px; border-radius: 8px; text-align: center;">
                                <p style="font-size: 12px; color: #4338ca; margin: 0; font-weight: 500;">Male Attendance</p>
                                <p style="font-size: 18px; color: #3730a3; margin: 5px 0 0 0; font-weight: bold;">${document.getElementById('malePercentage').textContent}</p>
                                <p style="font-size: 11px; color: #6366f1; margin: 2px 0 0 0;">(${document.getElementById('maleTotal').textContent})</p>
                            </div>
                            <div style="background: #fce7f3; padding: 15px; border-radius: 8px; text-align: center;">
                                <p style="font-size: 12px; color: #be185d; margin: 0; font-weight: 500;">Female Attendance</p>
                                <p style="font-size: 18px; color: #9d174d; margin: 5px 0 0 0; font-weight: bold;">${document.getElementById('femalePercentage').textContent}</p>
                                <p style="font-size: 11px; color: #ec4899; margin: 2px 0 0 0;">(${document.getElementById('femaleTotal').textContent})</p>
                            </div>
                            <div style="background: #fed7aa; padding: 15px; border-radius: 8px; text-align: center;">
                                <p style="font-size: 12px; color: #c2410c; margin: 0; font-weight: 500;">Children Attendance</p>
                                <p style="font-size: 18px; color: #9a3412; margin: 5px 0 0 0; font-weight: bold;">${document.getElementById('childrenPercentage').textContent}</p>
                                <p style="font-size: 11px; color: #ea580c; margin: 2px 0 0 0;">(${document.getElementById('childrenTotal').textContent})</p>
                            </div>
                            <div style="background: #ccfbf1; padding: 15px; border-radius: 8px; text-align: center;">
                                <p style="font-size: 12px; color: #0f766e; margin: 0; font-weight: 500;">Online Attendance</p>
                                <p style="font-size: 18px; color: #115e59; margin: 5px 0 0 0; font-weight: bold;">${document.getElementById('onlinePercentage').textContent}</p>
                                <p style="font-size: 11px; color: #14b8a6; margin: 2px 0 0 0;">(${document.getElementById('onlineTotal').textContent})</p>
                            </div>
                        </div>
                    </div>

                    <div id="chartsSection" style="margin-bottom: 30px;">
                        <h2 style="font-size: 18px; font-weight: 600; color: #1f2937; margin-bottom: 15px;">Charts</h2>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div style="background: #f9fafb; padding: 20px; border-radius: 8px;">
                                <canvas id="exportAttendanceChart" width="400" height="200"></canvas>
                            </div>
                            <div style="background: #f9fafb; padding: 20px; border-radius: 8px;">
                                <canvas id="exportEventTypeChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <h2 style="font-size: 18px; font-weight: 600; color: #1f2937; margin-bottom: 15px;">Event Reports</h2>
                        <div id="reportsTable"></div>
                    </div>
                `;

                document.body.appendChild(exportContainer);

                // Clone and recreate charts for export
                await recreateChartsForExport(exportContainer);

                // Create the reports table
                createReportsTableForExport(exportContainer);

                // Use html2canvas to capture the content
                const canvas = await html2canvas(exportContainer, {
                    scale: 2,
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#ffffff'
                });

                // Create PDF using jsPDF
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');
                
                const imgData = canvas.toDataURL('image/png');
                const imgWidth = 210; // A4 width in mm
                const pageHeight = 295; // A4 height in mm
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                let heightLeft = imgHeight;
                let position = 0;

                // Add first page
                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;

                // Add additional pages if needed
                while (heightLeft >= 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }

                // Save the PDF
                pdf.save(filename);

                // Cleanup
                document.body.removeChild(exportContainer);

                // Reset button
                exportBtn.innerHTML = originalText;
                exportBtn.disabled = false;

            } catch (error) {
                console.error('Error exporting PDF:', error);
                alert('Error generating PDF. Please try again.');
                
                // Reset button
                const exportBtn = document.getElementById('exportPdfBtn');
                exportBtn.innerHTML = `
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export PDF
                `;
                exportBtn.disabled = false;
            }
        }

        // Recreate charts for export
        async function recreateChartsForExport(container) {
            const attendanceChart = container.querySelector('#exportAttendanceChart');
            const eventTypeChart = container.querySelector('#exportEventTypeChart');

            // Get chart data from existing charts
            const originalAttendanceChart = window.attendanceChart;
            const originalEventTypeChart = window.eventTypeChart;

            if (originalAttendanceChart && originalAttendanceChart.data) {
                const ctx1 = attendanceChart.getContext('2d');
                new Chart(ctx1, {
                    type: 'line',
                    data: originalAttendanceChart.data,
                    options: {
                        responsive: false,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Attendance by Event Type'
                            },
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            if (originalEventTypeChart && originalEventTypeChart.data) {
                const ctx2 = eventTypeChart.getContext('2d');
                new Chart(ctx2, {
                    type: 'bar',
                    data: originalEventTypeChart.data,
                    options: {
                        responsive: false,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Weekly Sunday Service Breakdown'
                            },
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Wait a bit for charts to render
            await new Promise(resolve => setTimeout(resolve, 1000));
        }

        // Create reports table for export
        function createReportsTableForExport(container) {
            const reportsTableDiv = container.querySelector('#reportsTable');
            const originalTableBody = document.getElementById('reportsTableBody');
            
            if (!originalTableBody || originalTableBody.children.length === 0) {
                reportsTableDiv.innerHTML = '<p style="color: #6b7280; font-style: italic;">No event reports found for the selected period.</p>';
                return;
            }

            let tableHTML = `
                <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                    <thead>
                        <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #374151;">Date</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #374151;">Event Type</th>
                            <th style="padding: 8px; text-align: center; font-weight: 600; color: #374151;">Total Attendance</th>
                            <th style="padding: 8px; text-align: center; font-weight: 600; color: #374151;">First Time Guests</th>
                            <th style="padding: 8px; text-align: center; font-weight: 600; color: #374151;">Converts</th>
                            <th style="padding: 8px; text-align: center; font-weight: 600; color: #374151;">Services</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            // Extract data from existing table rows
            Array.from(originalTableBody.children).forEach((row, index) => {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 6) {
                    const bgColor = index % 2 === 0 ? '#ffffff' : '#f9fafb';
                    tableHTML += `
                        <tr style="background: ${bgColor}; border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 8px; color: #374151;">${cells[0].textContent.trim()}</td>
                            <td style="padding: 8px; color: #374151;">${cells[1].textContent.trim()}</td>
                            <td style="padding: 8px; text-align: center; color: #374151;">${cells[2].textContent.trim()}</td>
                            <td style="padding: 8px; text-align: center; color: #374151;">${cells[3].textContent.trim()}</td>
                            <td style="padding: 8px; text-align: center; color: #374151;">${cells[4].textContent.trim()}</td>
                            <td style="padding: 8px; text-align: center; color: #374151;">${cells[5].textContent.trim()}</td>
                        </tr>
                    `;
                }
            });

            tableHTML += '</tbody></table>';
            reportsTableDiv.innerHTML = tableHTML;
        }

        // Import Modal Functions
        function showImportModal() {
            document.getElementById('importReportsModal').classList.remove('hidden');
            resetImportForm();
        }

        function hideImportModal() {
            document.getElementById('importReportsModal').classList.add('hidden');
            resetImportForm();
        }

        function resetImportForm() {
            document.getElementById('importReportsForm').reset();
            document.getElementById('importProgress').classList.add('hidden');
            document.getElementById('importResults').classList.add('hidden');
            document.getElementById('importProgressBar').style.width = '0%';
            document.getElementById('importSubmitBtn').disabled = false;
            document.getElementById('importSubmitBtn').textContent = 'Import Reports';
        }

        async function downloadImportTemplate() {
            try {
                const downloadBtn = document.getElementById('downloadTemplateBtn');
                const originalText = downloadBtn.innerHTML;
                
                downloadBtn.innerHTML = `
                    <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Generating...
                `;
                downloadBtn.disabled = true;

                const response = await fetch('/api/import-export/event-reports/import-template', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'include'
                });

                if (response.ok) {
                    // Create blob and download
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = `event_reports_template_${new Date().toISOString().split('T')[0]}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                } else {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to download template');
                }

                downloadBtn.innerHTML = originalText;
                downloadBtn.disabled = false;

            } catch (error) {
                console.error('Error downloading template:', error);
                alert('Error downloading template: ' + error.message);
                
                const downloadBtn = document.getElementById('downloadTemplateBtn');
                downloadBtn.innerHTML = `
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download Template
                `;
                downloadBtn.disabled = false;
            }
        }

        async function handleImportReports(event) {
            event.preventDefault();
            
            const fileInput = document.getElementById('importFile');
            const file = fileInput.files[0];
            
            if (!file) {
                alert('Please select a file to import.');
                return;
            }

            // Validate file type
            const allowedTypes = [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                'application/vnd.ms-excel', // .xls
                'text/csv' // .csv
            ];
            
            if (!allowedTypes.includes(file.type)) {
                alert('Please select a valid Excel (.xlsx, .xls) or CSV file.');
                return;
            }

            // Validate file size (10MB)
            if (file.size > 10 * 1024 * 1024) {
                alert('File size must be less than 10MB.');
                return;
            }

            const submitBtn = document.getElementById('importSubmitBtn');
            const progressDiv = document.getElementById('importProgress');
            const progressBar = document.getElementById('importProgressBar');
            const progressText = document.getElementById('importProgressText');
            const resultsDiv = document.getElementById('importResults');

            try {
                // Disable submit button and show progress
                submitBtn.disabled = true;
                submitBtn.textContent = 'Importing...';
                progressDiv.classList.remove('hidden');
                resultsDiv.classList.add('hidden');
                
                // Simulate progress
                let progress = 0;
                const progressInterval = setInterval(() => {
                    progress += 10;
                    if (progress <= 90) {
                        progressBar.style.width = progress + '%';
                        progressText.textContent = `Uploading... ${progress}%`;
                    }
                }, 200);

                // Prepare form data
                const formData = new FormData();
                formData.append('file', file);
                
                // Get branch ID if user is super admin
                const branchSelect = document.getElementById('branchSelect');
                if (branchSelect && branchSelect.value) {
                    formData.append('branch_id', branchSelect.value);
                }

                // Make API call
                const response = await fetch('/api/import-export/event-reports/import', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'include',
                    body: formData
                });

                clearInterval(progressInterval);
                progressBar.style.width = '100%';
                progressText.textContent = 'Processing...';

                const result = await response.json();

                if (response.ok && result.success) {
                    // Success
                    progressText.textContent = 'Import completed successfully!';
                    
                    // Show results
                    const resultsContent = document.getElementById('importResultsContent');
                    resultsContent.innerHTML = `
                        <div class="bg-green-50 border border-green-200 rounded-md p-3">
                            <div class="flex">
                                <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">Import Successful</h3>
                                    <div class="mt-2 text-sm text-green-700">
                                        <p>Total processed: <strong>${result.summary?.total_processed || 0}</strong></p>
                                        <p>Successfully imported: <strong>${result.summary?.successful_imports || 0}</strong></p>
                                        <p>Failed: <strong>${result.summary?.failed_imports || 0}</strong></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    resultsDiv.classList.remove('hidden');
                    
                    // Reload reports data
                    setTimeout(() => {
                        loadEventReports();
                        loadDashboardData();
                        loadTrendData();
                    }, 1000);

                } else {
                    // Error
                    throw new Error(result.message || 'Import failed');
                }

            } catch (error) {
                console.error('Import error:', error);
                
                // Show error results
                const resultsContent = document.getElementById('importResultsContent');
                resultsContent.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-md p-3">
                        <div class="flex">
                            <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Import Failed</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p>${error.message}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                resultsDiv.classList.remove('hidden');
                progressText.textContent = 'Import failed';

            } finally {
                // Reset form state
                submitBtn.disabled = false;
                submitBtn.textContent = 'Import Reports';
            }
        }

        // Global Ministry Monthly Report Functions
        function initializeGlobalReport() {
            // Initialize year dropdown
            const yearSelect = document.getElementById('reportYear');
            const currentYear = new Date().getFullYear();
            for (let year = currentYear; year >= currentYear - 5; year--) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                if (year === currentYear) option.selected = true;
                yearSelect.appendChild(option);
            }

            // Set current month
            const monthSelect = document.getElementById('reportMonth');
            monthSelect.value = new Date().getMonth() + 1;

            // Branches are already loaded by the main loadBranches() function

            // Event listeners
            document.getElementById('generateGlobalReportBtn').addEventListener('click', generateGlobalReport);
            @if(auth()->user()->isSuperAdmin())
            document.getElementById('generateAllBranchesReportBtn').addEventListener('click', generateAllBranchesReport);
            @endif
        }

        @if(auth()->user()->isSuperAdmin())
        async function loadBranches() {
            try {
                // Try the main branches API first
                let response = await fetch('/api/branches?per_page=1000', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });

                // If main API fails, try the projections available branches endpoint
                if (!response.ok) {
                    response = await fetch('/api/projections/branches/available', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        credentials: 'same-origin'
                    });
                }

                if (response.ok) {
                    const data = await response.json();
                    
                    // Handle Laravel paginated response: {success: true, data: {data: [...], current_page: 1, ...}}
                    let branches = [];
                    if (data.success && data.data && data.data.data) {
                        // Paginated response
                        branches = data.data.data;
                    } else if (data.success && Array.isArray(data.data)) {
                        // Direct array response
                        branches = data.data;
                    } else if (Array.isArray(data)) {
                        // Plain array response
                        branches = data;
                    }
                    
                    if (Array.isArray(branches) && branches.length > 0) {
                        // Populate dashboard branch selector
                        const branchSelect = document.getElementById('branchSelect');
                        if (branchSelect) {
                            // Clear existing options except "All Branches"
                            branchSelect.innerHTML = '<option value="">All Branches</option>';
                            
                            branches.forEach(branch => {
                                const option = document.createElement('option');
                                option.value = branch.id;
                                option.textContent = `${branch.name}${branch.venue ? ' - ' + branch.venue : ''}`;
                                branchSelect.appendChild(option);
                            });
                        }

                        // Populate Global Ministry Report branch selector
                        const reportBranchSelect = document.getElementById('reportBranchSelect');
                        if (reportBranchSelect) {
                            // Clear existing options except "All Branches"
                            reportBranchSelect.innerHTML = '<option value="">All Branches</option>';
                            
                            branches.forEach(branch => {
                                const option = document.createElement('option');
                                option.value = branch.id;
                                option.textContent = `${branch.name}${branch.venue ? ' - ' + branch.venue : ''}`;
                                reportBranchSelect.appendChild(option);
                            });
                        }

                        // Populate Comparative Analytics branch selector
                        const comparisonBranchSelect = document.getElementById('comparisonBranch');
                        if (comparisonBranchSelect) {
                            // Clear existing options except "All Branches"
                            comparisonBranchSelect.innerHTML = '<option value="">All Branches</option>';
                            
                            branches.forEach(branch => {
                                const option = document.createElement('option');
                                option.value = branch.id;
                                option.textContent = `${branch.name}${branch.venue ? ' - ' + branch.venue : ''}`;
                                comparisonBranchSelect.appendChild(option);
                            });
                        }
                    }
                } else {
                    console.error('Failed to load branches. Response status:', response.status);
                    const errorText = await response.text();
                    console.error('Error response:', errorText);
                }
            } catch (error) {
                console.error('Error loading branches:', error);
            }
        }

        @endif

        async function generateGlobalReport() {
            const year = document.getElementById('reportYear').value;
            const month = document.getElementById('reportMonth').value;
            const branchId = document.getElementById('reportBranchSelect')?.value || null;

            const button = document.getElementById('generateGlobalReportBtn');
            const originalText = button.innerHTML;

            try {
                // Show loading state
                button.innerHTML = `
                    <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Generating...
                `;
                button.disabled = true;

                const params = new URLSearchParams({ year, month });
                if (branchId) params.append('branch_id', branchId);

                const response = await fetch(`/api/reports/global-ministry-monthly?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'include'
                });

                if (response.ok) {
                    const data = await response.json();
                    console.log('API response:', data);
                    if (data.success && data.data) {
                        displayGlobalReport(data.data);
                    } else {
                        throw new Error(data.message || 'Invalid response format');
                    }
                } else {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to generate report');
                }

            } catch (error) {
                console.error('Error generating report:', error);
                alert('Error generating report: ' + error.message);
            } finally {
                button.innerHTML = originalText;
                button.disabled = false;
            }
        }

        @if(auth()->user()->isSuperAdmin())
        async function generateAllBranchesReport() {
            const year = document.getElementById('reportYear').value;
            const month = document.getElementById('reportMonth').value;

            const button = document.getElementById('generateAllBranchesReportBtn');
            const originalText = button.innerHTML;

            try {
                // Show loading state
                button.innerHTML = `
                    <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Generating...
                `;
                button.disabled = true;

                const params = new URLSearchParams({ year, month });

                const response = await fetch(`/api/reports/global-ministry-monthly/all-branches?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'include'
                });

                if (response.ok) {
                    const data = await response.json();
                    displayAllBranchesReport(data.data);
                } else {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to generate report');
                }

            } catch (error) {
                console.error('Error generating all branches report:', error);
                alert('Error generating report: ' + error.message);
            } finally {
                button.innerHTML = originalText;
                button.disabled = false;
            }
        }
        @endif

        function displayGlobalReport(reportData) {
            console.log('Report data received:', reportData);
            console.log('Highest attendance event:', reportData.highest_attendance_event);
            console.log('Highest attendance event type:', typeof reportData.highest_attendance_event);
            const reportDisplay = document.getElementById('globalReportDisplay');
            const reportContent = document.getElementById('globalReportContent');

            const html = `
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h4 class="text-lg font-semibold text-gray-900">
                            Global Ministry Monthly Report - ${reportData.report_info?.month_name || 'Unknown'} ${reportData.report_info?.year || 'Unknown'}
                        </h4>
                        <p class="text-sm text-gray-600">Generated on ${reportData.report_info?.generated_at ? new Date(reportData.report_info.generated_at).toLocaleDateString() : 'Unknown'}</p>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Sunday Service -->
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h5 class="font-medium text-blue-900 mb-2">Sunday Service</h5>
                                <p class="text-2xl font-bold text-blue-700">${reportData.sunday_service_attendance?.monthly_average || 0}</p>
                                <p class="text-sm text-blue-600">Monthly Average</p>
                                <p class="text-xs text-blue-500">Total: ${reportData.sunday_service_attendance?.total_attendance || 0}</p>
                            </div>

                            <!-- Guest Attraction -->
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h5 class="font-medium text-green-900 mb-2">Guest Attraction</h5>
                                <p class="text-2xl font-bold text-green-700">${reportData.guest_attraction?.total_guests || 0}</p>
                                <p class="text-sm text-green-600">Total Guests</p>
                            </div>

                            <!-- Converts -->
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <h5 class="font-medium text-yellow-900 mb-2">Converts</h5>
                                <p class="text-2xl font-bold text-yellow-700">${reportData.converts?.total_converts || 0}</p>
                                <p class="text-sm text-yellow-600">Total Converts</p>
                            </div>

                            <!-- FDC Graduates -->
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <h5 class="font-medium text-purple-900 mb-2">FDC Graduates</h5>
                                <p class="text-2xl font-bold text-purple-700">${reportData.converts_assimilated_fdc_graduates?.count || 0}</p>
                                <p class="text-sm text-purple-600">Assimilated</p>
                            </div>

                            <!-- Membership Class -->
                            <div class="bg-indigo-50 p-4 rounded-lg">
                                <h5 class="font-medium text-indigo-900 mb-2">Membership Class</h5>
                                <p class="text-2xl font-bold text-indigo-700">${reportData.membership_class_graduates?.count || 0}</p>
                                <p class="text-sm text-indigo-600">Graduates</p>
                            </div>

                            <!-- TECi Graduates -->
                            <div class="bg-pink-50 p-4 rounded-lg">
                                <h5 class="font-medium text-pink-900 mb-2">TECi Graduates</h5>
                                <p class="text-2xl font-bold text-pink-700">${reportData.teci_graduates?.count || 0}</p>
                                <p class="text-sm text-pink-600">Graduates</p>
                            </div>

                            <!-- Small Groups -->
                            <div class="bg-emerald-50 p-4 rounded-lg">
                                <h5 class="font-medium text-emerald-900 mb-2">Small Groups</h5>
                                <p class="text-2xl font-bold text-emerald-700">${reportData.small_groups?.total_groups || 0}</p>
                                <p class="text-sm text-emerald-600">Total Groups</p>
                                <p class="text-xs text-emerald-500">Members: ${reportData.small_groups?.total_membership || 0}</p>
                                <p class="text-xs text-emerald-500">Avg Attendance: ${reportData.small_groups?.monthly_average_attendance || 0}</p>
                            </div>

                            <!-- G-Squad Volunteers -->
                            <div class="bg-orange-50 p-4 rounded-lg">
                                <h5 class="font-medium text-orange-900 mb-2">G-Squad Volunteers</h5>
                                <p class="text-2xl font-bold text-orange-700">${reportData.g_squad_volunteers?.count || 0}</p>
                                <p class="text-sm text-orange-600">Total Volunteers</p>
                            </div>

                            <!-- Leadership -->
                            <div class="bg-red-50 p-4 rounded-lg">
                                <h5 class="font-medium text-red-900 mb-2">Leadership</h5>
                                <p class="text-2xl font-bold text-red-700">${reportData.leadership?.total_leaders || 0}</p>
                                <p class="text-sm text-red-600">Total Leaders</p>
                                <div class="text-xs text-red-500 mt-1">
                                    <p>Leaders: ${reportData.leadership?.leaders || 0}</p>
                                    <p>Ministers: ${reportData.leadership?.ministers || 0}</p>
                                    <p>Volunteers: ${reportData.leadership?.volunteers || 0}</p>
                                </div>
                            </div>

                            <!-- Baptisms -->
                            <div class="bg-cyan-50 p-4 rounded-lg">
                                <h5 class="font-medium text-cyan-900 mb-2">Baptisms</h5>
                                <div class="space-y-1">
                                    <p class="text-sm text-cyan-700">Baby: <span class="font-semibold">${reportData.baptisms?.baby_dedication || 0}</span></p>
                                    <p class="text-sm text-cyan-700">Water: <span class="font-semibold">${reportData.baptisms?.water_baptism || 0}</span></p>
                                    <p class="text-sm text-cyan-700">Holy Ghost: <span class="font-semibold">${reportData.baptisms?.holy_ghost_baptism || 0}</span></p>
                                </div>
                            </div>

                            <!-- TECi Enrollment -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h5 class="font-medium text-gray-900 mb-2">TECi Enrollment</h5>
                                <p class="text-lg font-bold text-gray-700">${reportData.teci_enrollment?.current_enrollment || 0}</p>
                                <p class="text-sm text-gray-600">Current Students</p>
                                <p class="text-xs text-gray-500">Pending Graduation: ${reportData.teci_enrollment?.pending_graduation || 0}</p>
                            </div>

                            <!-- Highest Attendance Event -->
                            <div class="bg-violet-50 p-4 rounded-lg">
                                <h5 class="font-medium text-violet-900 mb-2">Highest Attendance Event</h5>
                                ${reportData.highest_attendance_event ? `
                                    <p class="text-lg font-bold text-violet-700">${reportData.highest_attendance_event.event_name || 'Unknown Event'}</p>
                                    <p class="text-sm text-violet-600">${reportData.highest_attendance_event.attendance || 0} attendees</p>
                                    <p class="text-xs text-violet-500">${reportData.highest_attendance_event.date ? new Date(reportData.highest_attendance_event.date).toLocaleDateString() : 'Unknown date'}</p>
                                ` : `
                                    <p class="text-sm text-violet-600">No events recorded</p>
                                `}
                            </div>
                        </div>
                    </div>
                </div>
            `;

            reportContent.innerHTML = html;
            reportDisplay.classList.remove('hidden');
        }

        @if(auth()->user()->isSuperAdmin())
        function displayAllBranchesReport(reportData) {
            const reportDisplay = document.getElementById('globalReportDisplay');
            const reportContent = document.getElementById('globalReportContent');

            let branchesHtml = '';
            reportData.branches.forEach(branch => {
                branchesHtml += `
                    <div class="bg-white border rounded-lg p-4 mb-4">
                        <h5 class="font-semibold text-gray-900 mb-2">${branch.branch_name} - ${branch.branch_location}</h5>
                        <p class="text-sm text-gray-600 mb-3">Pastor: ${branch.pastor_name}</p>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                            <div>
                                <span class="text-gray-600">Sunday Service:</span>
                                <span class="font-semibold ml-1">${branch.report_data.sunday_service.monthly_average}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Guests:</span>
                                <span class="font-semibold ml-1">${branch.report_data.guest_attraction.total_guests}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Converts:</span>
                                <span class="font-semibold ml-1">${branch.report_data.converts.total_converts}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Small Groups:</span>
                                <span class="font-semibold ml-1">${branch.report_data.small_groups.total_groups}</span>
                            </div>
                        </div>
                    </div>
                `;
            });

            const html = `
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h4 class="text-lg font-semibold text-gray-900">
                            Organization-wide Global Ministry Report - ${reportData.month_name} ${reportData.year}
                        </h4>
                        <p class="text-sm text-gray-600">Generated on ${new Date(reportData.generated_at).toLocaleDateString()}</p>
                    </div>
                    
                    <!-- Organization Totals -->
                    <div class="p-6 border-b border-gray-200">
                        <h5 class="font-medium text-gray-900 mb-4">Organization Totals</h5>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-blue-600">${reportData.organization_totals.total_branches}</p>
                                <p class="text-sm text-gray-600">Branches</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-green-600">${reportData.organization_totals.sunday_service_attendance}</p>
                                <p class="text-sm text-gray-600">Sunday Attendance</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-yellow-600">${reportData.organization_totals.guest_attraction}</p>
                                <p class="text-sm text-gray-600">Total Guests</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-purple-600">${reportData.organization_totals.converts}</p>
                                <p class="text-sm text-gray-600">Total Converts</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-indigo-600">${reportData.organization_totals.small_groups_count}</p>
                                <p class="text-sm text-gray-600">Small Groups</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-pink-600">${reportData.organization_totals.total_leaders}</p>
                                <p class="text-sm text-gray-600">Total Leaders</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Individual Branch Reports -->
                    <div class="p-6">
                        <h5 class="font-medium text-gray-900 mb-4">Branch Breakdown</h5>
                        ${branchesHtml}
                    </div>
                </div>
            `;

            reportContent.innerHTML = html;
            reportDisplay.classList.remove('hidden');
        }
        @endif

        // Initialize global report when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeGlobalReport();
            setupCascadingDropdowns();
        });

        // Setup cascading dropdown functionality
        function setupCascadingDropdowns() {
            // Create modal event type dropdown
            const createEventTypeSelect = document.getElementById('createEventType');

            
            // Edit modal event type dropdown
            const editEventTypeSelect = document.getElementById('editEventType');

            
            // Event selection dropdowns
            const createEventSelect = document.getElementById('eventSelect');
            const editEventSelect = document.getElementById('editEventSelect');
            
            // Function to populate event type fields based on selected event
            function populateEventTypeFields(eventId, eventTypeSelect) {
                if (!eventId) {
                    // Clear fields if no event selected
                    eventTypeSelect.value = '';
                    eventTypeSelect.removeAttribute('readonly');
                    eventTypeSelect.style.backgroundColor = '';
                    return;
                }
                
                // Fetch event details
                fetch(`/api/events/${eventId}/details`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const eventDetails = data.data;
                            
                            // Populate event type - try to match with the service_type from the event
                            if (eventDetails.service_type) {
                                eventTypeSelect.value = eventDetails.service_type;
                            } else if (eventDetails.type) {
                                // Fallback to type field if available
                                eventTypeSelect.value = eventDetails.type;
                            }
                            
                            // Make field read-only since it's auto-populated
                            eventTypeSelect.setAttribute('readonly', 'readonly');
                            
                            // Add visual indication that field is auto-populated
                            eventTypeSelect.style.backgroundColor = '#f9f9f9';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching event details:', error);
                        // Remove read-only if there's an error
                        eventTypeSelect.removeAttribute('readonly');
                        eventTypeSelect.style.backgroundColor = '';
                    });
            }
            

            
            // Set up event listeners for event selection (auto-populate event type)
            if (createEventSelect) {
                createEventSelect.addEventListener('change', function() {
                    populateEventTypeFields(createEventSelect.value, createEventTypeSelect);
                });
            }
            
            if (editEventSelect) {
                editEventSelect.addEventListener('change', function() {
                    populateEventTypeFields(editEventSelect.value, editEventTypeSelect);
                });
            }
        }

        // Token Management Functionality
        let tokens = [];
        let availableEvents = [];

        function toggleTokenTypeFields() {
            const tokenType = document.querySelector('select[name="token_type"]').value;
            const individualFields = document.getElementById('individualFields');
            const teamFields = document.getElementById('teamFields');
            
            if (tokenType === 'individual') {
                individualFields.style.display = 'block';
                teamFields.style.display = 'none';
                
                // Make individual fields required
                document.querySelector('input[name="name"]').required = true;
                document.querySelector('input[name="email"]').required = false;
                
                // Make team fields not required
                document.querySelector('input[name="team_name"]').required = false;
            } else {
                individualFields.style.display = 'none';
                teamFields.style.display = 'block';
                
                // Make individual fields not required
                document.querySelector('input[name="name"]').required = false;
                document.querySelector('input[name="email"]').required = false;
                
                // Make team fields required
                document.querySelector('input[name="team_name"]').required = true;
            }
        }

        function addTeamMember() {
            const container = document.getElementById('teamMembersContainer');
            const newRow = document.createElement('div');
            newRow.className = 'team-member-row flex gap-2 mb-2';
            newRow.innerHTML = `
                <input type="email" name="team_emails[]" placeholder="Email" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="text" name="team_roles[]" placeholder="Role (e.g., Second Service Chief)" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="button" onclick="removeTeamMember(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">Remove</button>
            `;
            container.appendChild(newRow);
        }

        function removeTeamMember(button) {
            button.parentElement.remove();
        }

        async function populateCreateTokenBranchSelector() {
            try {
                const response = await fetch('/api/branches', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.success && data.data) {
                    const branchSelect = document.querySelector('#createTokenForm select[name="branch_id"]');
                    if (branchSelect) {
                        // Clear existing options except the first one
                        branchSelect.innerHTML = '<option value="">Select a branch...</option>';
                        
                        data.data.forEach(branch => {
                            const option = document.createElement('option');
                            option.value = branch.id;
                            option.textContent = `${branch.name}${branch.venue ? ' - ' + branch.venue : ''}`;
                            branchSelect.appendChild(option);
                        });
                    }
                }
            } catch (error) {
                console.error('Error loading branches for token creation:', error);
            }
        }

        // Load tokens and events
        async function loadTokens() {
            try {
                const response = await fetch('/api/reports/tokens', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    tokens = data.data;
                    renderTokensTable();
                }
            } catch (error) {
                console.error('Error loading tokens:', error);
            }
        }

        async function loadAvailableEvents() {
            try {
                const response = await fetch('/api/reports/tokens/available-events', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    availableEvents = data.data;
                }
            } catch (error) {
                console.error('Error loading events:', error);
            }
        }

        function renderTokensTable() {
            const tbody = document.getElementById('tokensTableBody');
            if (!tbody) return;

            tbody.innerHTML = '';
            
            tokens.forEach(token => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        ${token.name}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${token.branch.name}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${token.email || 'N/A'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${token.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${token.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${token.usage_count}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${token.last_used_at ? new Date(token.last_used_at).toLocaleDateString() : 'Never'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${token.expires_at ? new Date(token.expires_at).toLocaleDateString() : 'Never'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button onclick="copyTokenUrl('${token.token}')" class="text-indigo-600 hover:text-indigo-900 mr-3">
                            Copy Link
                        </button>
                        <button onclick="editToken(${token.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                            Edit
                        </button>
                        <button onclick="regenerateToken(${token.id})" class="text-yellow-600 hover:text-yellow-900 mr-3">
                            Regenerate
                        </button>
                        <button onclick="deleteToken(${token.id})" class="text-red-600 hover:text-red-900">
                            Delete
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function copyTokenUrl(token) {
            const url = `${window.location.origin}/public/reports/submit/${token}`;
            navigator.clipboard.writeText(url).then(() => {
                alert('Link copied to clipboard!');
            });
        }

        async function createToken() {
            const form = document.getElementById('createTokenForm');
            const formData = new FormData(form);
            
            // For super admin, branch_id comes from the form
            // For regular users, use their active branch
            const branchId = formData.get('branch_id') || @json(Auth::user()->getActiveBranchId());
            if (branchId) {
                formData.set('branch_id', branchId);
            }
            
            // Clean up form data based on token type
            const tokenType = formData.get('token_type');
            if (tokenType === 'team') {
                // Remove individual token fields for team tokens
                formData.delete('name');
                formData.delete('email');
            } else {
                // Remove team token fields for individual tokens
                formData.delete('team_name');
                formData.delete('team_emails');
                formData.delete('team_roles');
            }
            
            try {
                const response = await fetch('/api/reports/tokens', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Token created successfully!');
                    document.getElementById('createTokenModal').classList.add('hidden');
                    document.getElementById('tokenManagementModal').classList.add('hidden');
                    loadTokens();
                } else {
                    alert('Error creating token: ' + data.message);
                }
            } catch (error) {
                console.error('Error creating token:', error);
                alert('Error creating token');
            }
        }

        async function editToken(tokenId) {
            const token = tokens.find(t => t.id === tokenId);
            if (!token) return;

            // Populate edit form
            document.getElementById('editTokenId').value = token.id;
            document.getElementById('editTokenName').value = token.name;
            document.getElementById('editTokenEmail').value = token.email || '';
            document.getElementById('editTokenActive').checked = token.is_active;
            document.getElementById('editTokenExpiresAt').value = token.expires_at ? token.expires_at.split('T')[0] : '';
            
            // Show edit modal
            document.getElementById('editTokenModal').classList.remove('hidden');
        }

        async function updateToken() {
            const form = document.getElementById('editTokenForm');
            const formData = new FormData(form);
            const tokenId = formData.get('token_id');
            
            try {
                const response = await fetch(`/api/reports/tokens/${tokenId}`, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Token updated successfully!');
                    document.getElementById('editTokenModal').classList.add('hidden');
                    loadTokens();
                } else {
                    alert('Error updating token: ' + data.message);
                }
            } catch (error) {
                console.error('Error updating token:', error);
                alert('Error updating token');
            }
        }

        async function regenerateToken(tokenId) {
            if (!confirm('Are you sure you want to regenerate this token? The old link will stop working.')) {
                return;
            }
            
            try {
                const response = await fetch(`/api/reports/tokens/${tokenId}/regenerate`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Token regenerated successfully!');
                    loadTokens();
                } else {
                    alert('Error regenerating token: ' + data.message);
                }
            } catch (error) {
                console.error('Error regenerating token:', error);
                alert('Error regenerating token');
            }
        }

        async function deleteToken(tokenId) {
            if (!confirm('Are you sure you want to delete this token? This action cannot be undone.')) {
                return;
            }
            
            try {
                const response = await fetch(`/api/reports/tokens/${tokenId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Token deleted successfully!');
                    loadTokens();
                } else {
                    alert('Error deleting token: ' + data.message);
                }
            } catch (error) {
                console.error('Error deleting token:', error);
                alert('Error deleting token');
            }
        }

        // Token management event listeners - wrapped in DOMContentLoaded to ensure DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const manageTokensBtn = document.getElementById('manageTokensBtn');
            if (manageTokensBtn) {
                manageTokensBtn.addEventListener('click', function() {
                    document.getElementById('tokenManagementModal').classList.remove('hidden');
                    loadTokens();
                    loadAvailableEvents();
                });
            }

            const createNewTokenBtn = document.getElementById('createNewTokenBtn');
            if (createNewTokenBtn) {
                createNewTokenBtn.addEventListener('click', function() {
                    document.getElementById('createTokenModal').classList.remove('hidden');
                    populateCreateTokenBranchSelector();
                });
            }

            const createTokenBtn = document.getElementById('createTokenBtn');
            if (createTokenBtn) {
                createTokenBtn.addEventListener('click', createToken);
            }

            const updateTokenBtn = document.getElementById('updateTokenBtn');
            if (updateTokenBtn) {
                updateTokenBtn.addEventListener('click', updateToken);
            }

            // Close modals
            document.querySelectorAll('[data-modal-close]').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.fixed').classList.add('hidden');
                });
            });
        });
    </script>

    <!-- Token Management Modal -->
    <div id="tokenManagementModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Manage Report Submission Links</h3>
                    <button data-modal-close class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Create New Token Button -->
                <div class="mb-4">
                    <button id="createNewTokenBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                        Create New Submission Link
                    </button>
                </div>

                <!-- Tokens Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Used</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tokensTableBody" class="bg-white divide-y divide-gray-200">
                            <!-- Tokens will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Token Modal -->
    <div id="createTokenModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Submission Link</h3>
                
                <form id="createTokenForm">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Branch *</label>
                        <select name="branch_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select a branch...</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Token Type *</label>
                        <select name="token_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="toggleTokenTypeFields()">
                            <option value="individual">Individual Token</option>
                            <option value="team">Team Token</option>
                        </select>
                    </div>
                    
                    <!-- Individual Token Fields -->
                    <div id="individualFields">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                            <input type="text" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <!-- Team Token Fields -->
                    <div id="teamFields" style="display: none;">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Team Name *</label>
                            <input type="text" name="team_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Team Members *</label>
                            <div id="teamMembersContainer">
                                <div class="team-member-row flex gap-2 mb-2">
                                    <input type="email" name="team_emails[]" placeholder="Email" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <input type="text" name="team_roles[]" placeholder="Role (e.g., First Service Chief)" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <button type="button" onclick="removeTeamMember(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">Remove</button>
                                </div>
                            </div>
                            <button type="button" onclick="addTeamMember()" class="mt-2 px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">Add Team Member</button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expires At</label>
                        <input type="date" name="expires_at" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button type="button" data-modal-close class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="button" id="createTokenBtn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Token Modal -->
    <div id="editTokenModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Submission Link</h3>
                
                <form id="editTokenForm">
                    <input type="hidden" id="editTokenId" name="token_id">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                        <input type="text" id="editTokenName" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" id="editTokenEmail" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Active</label>
                        <input type="checkbox" id="editTokenActive" name="is_active" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expires At</label>
                        <input type="date" id="editTokenExpiresAt" name="expires_at" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button type="button" data-modal-close class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="button" id="updateTokenBtn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endpush
</x-sidebar-layout> 
@php($year = (int) request()->get('year', now()->year))
<x-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Branch Performance Dashboard</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Year</label>
                        <select id="yearSelect" class="mt-1 block w-32 rounded border-gray-300">
                            @for($y = now()->year + 1; $y >= now()->year - 5; $y--)
                                <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Analysis Type</label>
                        <select id="analysisType" class="mt-1 block w-40 rounded border-gray-300">
                            <option value="YTD">Year to Date</option>
                            <option value="QTD">Quarter to Date</option>
                            <option value="MTD">Month to Date</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div id="customDateRange" class="hidden">
                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input id="startDate" type="date" class="mt-1 block w-44 rounded border-gray-300" />
                    </div>
                    <div id="customDateRangeEnd" class="hidden">
                        <label class="block text-sm font-medium text-gray-700">End Date</label>
                        <input id="endDate" type="date" class="mt-1 block w-44 rounded border-gray-300" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Comparison</label>
                        <select id="comparisonType" class="mt-1 block w-40 rounded border-gray-300">
                            <option value="yoy">Year over Year</option>
                            <option value="qoq">Quarter over Quarter</option>
                            <option value="mom">Month over Month</option>
                            <option value="none">No Comparison</option>
                        </select>
                    </div>
                    <button id="applyBtn" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Apply Filters
                    </button>
                </div>
            </div>

            <!-- Performance Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">Total Attendance</div>
                            <div id="attendanceTotal" class="text-2xl font-bold text-gray-900">-</div>
                            <div id="attendanceDelta" class="text-sm text-gray-500"></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">Total Guests</div>
                            <div id="guestsTotal" class="text-2xl font-bold text-gray-900">-</div>
                            <div id="guestsDelta" class="text-sm text-gray-500"></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">Total Converts</div>
                            <div id="convertsTotal" class="text-2xl font-bold text-gray-900">-</div>
                            <div id="convertsDelta" class="text-sm text-gray-500"></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">Weekly Average</div>
                            <div id="weeklyAvgTotal" class="text-2xl font-bold text-gray-900">-</div>
                            <div id="weeklyAvgDelta" class="text-sm text-gray-500"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analysis Tabs -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button id="quarterlyTab" class="tab-button active py-2 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600">
                            Quarterly Analysis
                        </button>
                        <button id="monthlyTab" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            Monthly Analysis
                        </button>
                        <button id="yearlyTab" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            Yearly Analysis
                        </button>
                        <button id="projectionTab" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            vs Projections
                        </button>
                    </nav>
                </div>

                <!-- Quarterly Analysis Tab -->
                <div id="quarterlyContent" class="tab-content p-6">
                    <div class="text-lg font-semibold mb-4">Quarterly Performance Comparison</div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                            <tr class="text-left text-gray-600 border-b">
                                <th class="py-3 pr-4 font-medium">Quarter</th>
                                <th class="py-3 pr-4 font-medium">Attendance {{ $year }}</th>
                                <th class="py-3 pr-4 font-medium">Attendance {{ $year-1 }}</th>
                                <th class="py-3 pr-4 font-medium">Δ%</th>
                                <th class="py-3 pr-4 font-medium">Guests {{ $year }}</th>
                                <th class="py-3 pr-4 font-medium">Guests {{ $year-1 }}</th>
                                <th class="py-3 pr-4 font-medium">Δ%</th>
                                <th class="py-3 pr-4 font-medium">Converts {{ $year }}</th>
                                <th class="py-3 pr-4 font-medium">Converts {{ $year-1 }}</th>
                                <th class="py-3 pr-4 font-medium">Δ%</th>
                            </tr>
                            </thead>
                            <tbody id="quartersBody" class="divide-y divide-gray-200"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Monthly Analysis Tab -->
                <div id="monthlyContent" class="tab-content p-6 hidden">
                    <div class="text-lg font-semibold mb-4">Monthly Performance Analysis</div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                            <tr class="text-left text-gray-600 border-b">
                                <th class="py-3 pr-4 font-medium">Month</th>
                                <th class="py-3 pr-4 font-medium">Attendance</th>
                                <th class="py-3 pr-4 font-medium">Guests</th>
                                <th class="py-3 pr-4 font-medium">Converts</th>
                                <th class="py-3 pr-4 font-medium">Weekly Avg</th>
                                <th class="py-3 pr-4 font-medium">vs Previous</th>
                            </tr>
                            </thead>
                            <tbody id="monthlyBody" class="divide-y divide-gray-200"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Yearly Analysis Tab -->
                <div id="yearlyContent" class="tab-content p-6 hidden">
                    <div class="text-lg font-semibold mb-4">Yearly Performance Trends</div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                <tr class="text-left text-gray-600 border-b">
                                    <th class="py-3 pr-4 font-medium">Year</th>
                                    <th class="py-3 pr-4 font-medium">Attendance</th>
                                    <th class="py-3 pr-4 font-medium">Guests</th>
                                    <th class="py-3 pr-4 font-medium">Converts</th>
                                    <th class="py-3 pr-4 font-medium">Growth %</th>
                                </tr>
                                </thead>
                                <tbody id="yearlyBody" class="divide-y divide-gray-200"></tbody>
                            </table>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-2">Key Insights</h4>
                            <div id="yearlyInsights" class="text-sm text-gray-600 space-y-1"></div>
                        </div>
                    </div>
                </div>

                <!-- Projection Comparison Tab -->
                <div id="projectionContent" class="tab-content p-6 hidden">
                    <div class="text-lg font-semibold mb-4">Performance vs Projections</div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                <tr class="text-left text-gray-600 border-b">
                                    <th class="py-3 pr-4 font-medium">Metric</th>
                                    <th class="py-3 pr-4 font-medium">Target</th>
                                    <th class="py-3 pr-4 font-medium">Actual</th>
                                    <th class="py-3 pr-4 font-medium">Progress</th>
                                    <th class="py-3 pr-4 font-medium">Status</th>
                                </tr>
                                </thead>
                                <tbody id="projectionBody" class="divide-y divide-gray-200"></tbody>
                            </table>
                        </div>
                        <div class="space-y-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h4 class="font-medium text-blue-900 mb-2">Quarterly Progress</h4>
                                <div id="quarterlyProgress" class="space-y-2"></div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h4 class="font-medium text-green-900 mb-2">Achievement Summary</h4>
                                <div id="achievementSummary" class="text-sm text-green-800"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentData = null;
        let currentTab = 'quarterly';

        // Tab functionality
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.tab-button').forEach(btn => {
                    btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
                    btn.classList.add('border-transparent', 'text-gray-500');
                });
                
                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                });
                
                // Add active class to clicked button
                this.classList.add('active', 'border-blue-500', 'text-blue-600');
                this.classList.remove('border-transparent', 'text-gray-500');
                
                // Show corresponding content
                const tabId = this.id.replace('Tab', '');
                currentTab = tabId;
                document.getElementById(tabId + 'Content').classList.remove('hidden');
                
                // Load data for the tab
                if (currentData) {
                    loadTabData(tabId);
                }
            });
        });

        // Show/hide custom date range
        document.getElementById('analysisType').addEventListener('change', function() {
            const isCustom = this.value === 'custom';
            document.getElementById('customDateRange').classList.toggle('hidden', !isCustom);
            document.getElementById('customDateRangeEnd').classList.toggle('hidden', !isCustom);
        });

        async function loadBranchPerformance(params) {
            const url = new URL('/api/performance/branch', window.location.origin);
            Object.entries(params).forEach(([k,v]) => { 
                if (v) url.searchParams.set(k, v) 
            });
            
            const res = await fetch(url, { 
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                } 
            });
            
            if (!res.ok) {
                throw new Error('Failed to load performance data');
            }
            
            return res.json();
        }

        function formatNumber(n) { 
            return Number(n).toLocaleString(); 
        }

        function formatPercentage(n) {
            return n >= 0 ? `+${n}%` : `${n}%`;
        }

        function getDeltaClass(delta) {
            return delta >= 0 ? 'text-green-600' : 'text-red-600';
        }

        function getStatusBadge(progress) {
            if (progress >= 100) return '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Achieved</span>';
            if (progress >= 75) return '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">On Track</span>';
            if (progress >= 50) return '<span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Behind</span>';
            return '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">At Risk</span>';
        }

        async function render() {
            try {
                const year = document.getElementById('yearSelect').value;
                const analysisType = document.getElementById('analysisType').value;
                const comparisonType = document.getElementById('comparisonType').value;
                const start = document.getElementById('startDate').value;
                const end = document.getElementById('endDate').value;
                
                const params = { 
                    year, 
                    range: analysisType,
                    compare: comparisonType !== 'none' ? comparisonType : null
                };
                
                if (start && end) {
                    params.start_date = start;
                    params.end_date = end;
                }
                
                const response = await loadBranchPerformance(params);
                currentData = response.data;
                
                // Update performance cards
                updatePerformanceCards();
                
                // Load current tab data
                loadTabData(currentTab);
                
            } catch (error) {
                console.error('Error loading performance data:', error);
                alert('Failed to load performance data. Please try again.');
            }
        }

        function updatePerformanceCards() {
            const data = currentData;
            
            // Update main metrics
            document.getElementById('attendanceTotal').textContent = formatNumber(data.actuals?.attendance || 0);
            document.getElementById('guestsTotal').textContent = formatNumber(data.actuals?.guests || 0);
            document.getElementById('convertsTotal').textContent = formatNumber(data.actuals?.converts || 0);
            document.getElementById('weeklyAvgTotal').textContent = formatNumber(data.actuals?.weekly_avg_attendance || 0);
            
            // Update deltas if comparison data exists
            if (data.comparison?.deltas) {
                document.getElementById('attendanceDelta').innerHTML = 
                    `<span class="${getDeltaClass(data.comparison.deltas.attendance || 0)}">${formatPercentage(data.comparison.deltas.attendance || 0)}</span>`;
                document.getElementById('guestsDelta').innerHTML = 
                    `<span class="${getDeltaClass(data.comparison.deltas.guests || 0)}">${formatPercentage(data.comparison.deltas.guests || 0)}</span>`;
                document.getElementById('convertsDelta').innerHTML = 
                    `<span class="${getDeltaClass(data.comparison.deltas.converts || 0)}">${formatPercentage(data.comparison.deltas.converts || 0)}</span>`;
                document.getElementById('weeklyAvgDelta').innerHTML = 
                    `<span class="${getDeltaClass(data.comparison.deltas.weekly_avg_attendance || 0)}">${formatPercentage(data.comparison.deltas.weekly_avg_attendance || 0)}</span>`;
            } else {
                document.getElementById('attendanceDelta').textContent = '';
                document.getElementById('guestsDelta').textContent = '';
                document.getElementById('convertsDelta').textContent = '';
                document.getElementById('weeklyAvgDelta').textContent = '';
            }
        }

        function loadTabData(tab) {
            if (!currentData) return;
            
            switch(tab) {
                case 'quarterly':
                    loadQuarterlyData();
                    break;
                case 'monthly':
                    loadMonthlyData();
                    break;
                case 'yearly':
                    loadYearlyData();
                    break;
                case 'projection':
                    loadProjectionData();
                    break;
            }
        }

        function loadQuarterlyData() {
            const tbody = document.getElementById('quartersBody');
            tbody.innerHTML = '';
            
            if (currentData.quarters) {
                currentData.quarters.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="py-3 pr-4 font-medium">${row.quarter}</td>
                        <td class="py-3 pr-4">${formatNumber(row.current.attendance)}</td>
                        <td class="py-3 pr-4 text-gray-500">${formatNumber(row.previous.attendance)}</td>
                        <td class="py-3 pr-4 ${getDeltaClass(row.delta.attendance)}">${formatPercentage(row.delta.attendance)}</td>
                        <td class="py-3 pr-4">${formatNumber(row.current.guests)}</td>
                        <td class="py-3 pr-4 text-gray-500">${formatNumber(row.previous.guests)}</td>
                        <td class="py-3 pr-4 ${getDeltaClass(row.delta.guests)}">${formatPercentage(row.delta.guests)}</td>
                        <td class="py-3 pr-4">${formatNumber(row.current.converts)}</td>
                        <td class="py-3 pr-4 text-gray-500">${formatNumber(row.previous.converts)}</td>
                        <td class="py-3 pr-4 ${getDeltaClass(row.delta.converts)}">${formatPercentage(row.delta.converts)}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        }

        function loadMonthlyData() {
            const tbody = document.getElementById('monthlyBody');
            tbody.innerHTML = '';
            
            if (currentData.monthly) {
                currentData.monthly.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="py-3 pr-4 font-medium">${row.month}</td>
                        <td class="py-3 pr-4">${formatNumber(row.attendance)}</td>
                        <td class="py-3 pr-4">${formatNumber(row.guests)}</td>
                        <td class="py-3 pr-4">${formatNumber(row.converts)}</td>
                        <td class="py-3 pr-4">${formatNumber(row.weekly_avg)}</td>
                        <td class="py-3 pr-4 ${getDeltaClass(row.delta)}">${formatPercentage(row.delta)}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        }

        function loadYearlyData() {
            const tbody = document.getElementById('yearlyBody');
            tbody.innerHTML = '';
            
            if (currentData.yearly) {
                currentData.yearly.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="py-3 pr-4 font-medium">${row.year}</td>
                        <td class="py-3 pr-4">${formatNumber(row.attendance)}</td>
                        <td class="py-3 pr-4">${formatNumber(row.guests)}</td>
                        <td class="py-3 pr-4">${formatNumber(row.converts)}</td>
                        <td class="py-3 pr-4 ${getDeltaClass(row.growth)}">${formatPercentage(row.growth)}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }
            
            // Load insights
            const insightsDiv = document.getElementById('yearlyInsights');
            if (currentData.insights) {
                insightsDiv.innerHTML = currentData.insights.map(insight => 
                    `<div>• ${insight}</div>`
                ).join('');
            }
        }

        function loadProjectionData() {
            const tbody = document.getElementById('projectionBody');
            tbody.innerHTML = '';
            
            if (currentData.projections) {
                currentData.projections.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="py-3 pr-4 font-medium">${row.metric}</td>
                        <td class="py-3 pr-4">${formatNumber(row.target)}</td>
                        <td class="py-3 pr-4">${formatNumber(row.actual)}</td>
                        <td class="py-3 pr-4">${row.progress}%</td>
                        <td class="py-3 pr-4">${getStatusBadge(row.progress)}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }
            
            // Load quarterly progress
            const quarterlyProgressDiv = document.getElementById('quarterlyProgress');
            if (currentData.quarterly_progress) {
                quarterlyProgressDiv.innerHTML = currentData.quarterly_progress.map(q => 
                    `<div class="flex justify-between items-center">
                        <span class="text-sm">${q.quarter}: ${q.actual || 0}/${q.projected || 0}</span>
                        <div class="flex items-center space-x-2">
                            <div class="w-20 bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: ${Math.min(q.progress, 100)}%"></div>
                            </div>
                            <span class="text-sm font-medium">${q.progress}%</span>
                        </div>
                    </div>`
                ).join('');
            }
            
            // Load achievement summary
            const achievementDiv = document.getElementById('achievementSummary');
            if (currentData.achievement_summary) {
                achievementDiv.innerHTML = currentData.achievement_summary;
            }
        }

        // Event listeners
        document.getElementById('applyBtn').addEventListener('click', render);
        
        // Load data on page load
        document.addEventListener('DOMContentLoaded', function() {
            render();
        });
    </script>
</x-sidebar-layout>
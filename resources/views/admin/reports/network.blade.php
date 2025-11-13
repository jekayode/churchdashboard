<x-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Network Performance Overview</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Controls Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Year</label>
                        <select id="yearSelect" class="mt-1 block w-32 rounded border-gray-300">
                            @for($y = now()->year + 1; $y >= now()->year - 5; $y--)
                                <option value="{{ $y }}" @selected($y === now()->year)>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Compare With</label>
                        <select id="compareSelect" class="mt-1 block w-32 rounded border-gray-300">
                            <option value="yoy">Year over Year</option>
                            <option value="qoq">Quarter over Quarter</option>
                            <option value="mom">Month over Month</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Custom Start</label>
                        <input id="startDate" type="date" class="mt-1 block w-44 rounded border-gray-300" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Custom End</label>
                        <input id="endDate" type="date" class="mt-1 block w-44 rounded border-gray-300" />
                    </div>
                    <button id="applyBtn" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Apply Filters
                    </button>
                </div>
            </div>

            <!-- Global Projection Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Global Projection</h3>
                    <button id="createGlobalProjectionBtn" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Set Global Projection
                    </button>
                </div>
                
                <div id="globalProjectionCards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Global projection cards will be loaded here -->
                </div>
                
                <div id="noGlobalProjection" class="hidden text-center py-8 bg-gray-50 rounded-lg">
                    <div class="text-gray-500 mb-2">
                        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No Global Projection Set</h3>
                    <p class="text-gray-600">Set a global projection to track network-wide performance against targets.</p>
                </div>
            </div>

            <!-- Network Performance Cards -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Network Performance</h3>
                <div id="cards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="p-4 rounded border border-blue-200 bg-blue-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-blue-600 text-sm font-medium">Total Attendance</div>
                                <div id="attendanceTotal" class="text-2xl font-bold text-blue-900">-</div>
                                <div id="attendanceDelta" class="text-sm text-blue-600">-</div>
                            </div>
                            <div class="text-blue-500">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.196-2.121M9 20H4v-2a3 3 0 00-5.196-2.121m4-18a4 4 0 00-8 0 4 4 0 008 0zM8 14a3 3 0 106 0 3 3 0 00-6 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 rounded border border-green-200 bg-green-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-green-600 text-sm font-medium">Total Guests</div>
                                <div id="guestsTotal" class="text-2xl font-bold text-green-900">-</div>
                                <div id="guestsDelta" class="text-sm text-green-600">-</div>
                            </div>
                            <div class="text-green-500">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 rounded border border-purple-200 bg-purple-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-purple-600 text-sm font-medium">Total Converts</div>
                                <div id="convertsTotal" class="text-2xl font-bold text-purple-900">-</div>
                                <div id="convertsDelta" class="text-sm text-purple-600">-</div>
                            </div>
                            <div class="text-purple-500">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Branch Performance Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Branch Performance</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guests</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Converts</th>
                                <th colspan="3" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-l border-gray-300">Average Attendance Progress vs Target</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-l border-gray-300">Target</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actual</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">%</th>
                            </tr>
                        </thead>
                        <tbody id="branchPerformanceBody" class="bg-white divide-y divide-gray-200">
                            <!-- Branch data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Projection Modal -->
    <div id="globalProjectionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
                <div class="p-6 max-h-[80vh] overflow-y-auto">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Set Global Projection</h3>
                    <form id="globalProjectionForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Year</label>
                                <input type="number" id="projectionYear" class="mt-1 block w-full rounded border-gray-300" min="2020" max="2030" value="{{ now()->year }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Attendance Target</label>
                                <input type="number" id="attendanceTarget" class="mt-1 block w-full rounded border-gray-300" min="1" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Converts Target</label>
                                <input type="number" id="convertsTarget" class="mt-1 block w-full rounded border-gray-300" min="0" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Leaders Target</label>
                                <input type="number" id="leadersTarget" class="mt-1 block w-full rounded border-gray-300" min="0" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Volunteers Target</label>
                                <input type="number" id="volunteersTarget" class="mt-1 block w-full rounded border-gray-300" min="0" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Weekly Avg Attendance</label>
                                <input type="number" id="weeklyAvgAttendanceTarget" class="mt-1 block w-full rounded border-gray-300" min="0">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Guests Target</label>
                                <input type="number" id="guestsTarget" class="mt-1 block w-full rounded border-gray-300" min="0">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">LifeGroups Target</label>
                                <input type="number" id="lifegroupsTarget" class="mt-1 block w-full rounded border-gray-300" min="0">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">LifeGroups Memberships</label>
                                <input type="number" id="lifegroupsMembershipsTarget" class="mt-1 block w-full rounded border-gray-300" min="0">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">LifeGroups Weekly Avg</label>
                                <input type="number" id="lifegroupsWeeklyAvgTarget" class="mt-1 block w-full rounded border-gray-300" min="0">
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" id="cancelGlobalProjection" class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                Save Projection
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let globalProjection = null;
        let branches = [];

        async function loadNetworkPerformance(params) {
            const url = new URL('/api/performance/network', window.location.origin);
            Object.entries(params).forEach(([k,v]) => { if (v) url.searchParams.set(k, v) });
            const res = await fetch(url, { 
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                } 
            });
            if (!res.ok) throw new Error('Failed to load performance');
            return res.json();
        }

        async function loadGlobalProjection(year) {
            try {
                const res = await fetch(`/api/projections?year=${year}&is_global=true`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.success && data.data.data.length > 0) {
                        globalProjection = data.data.data[0];
                        return globalProjection;
                    }
                }
                return null;
            } catch (error) {
                console.error('Error loading global projection:', error);
                return null;
            }
        }

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
                        branches = data.data;
                        return branches;
                    }
                }
                return [];
            } catch (error) {
                console.error('Error loading branches:', error);
                return [];
            }
        }

        function fmt(n){ return Number(n).toLocaleString(); }

        function updateGlobalProjectionCards(projection) {
            const cardsContainer = document.getElementById('globalProjectionCards');
            const noProjectionDiv = document.getElementById('noGlobalProjection');
            
            if (!projection) {
                cardsContainer.innerHTML = '';
                noProjectionDiv.classList.remove('hidden');
                return;
            }

            noProjectionDiv.classList.add('hidden');
            
            const metrics = [
                { key: 'attendance_target', label: 'Attendance Target', color: 'blue', icon: '👥' },
                { key: 'converts_target', label: 'Converts Target', color: 'green', icon: '✝️' },
                { key: 'leaders_target', label: 'Leaders Target', color: 'purple', icon: '👑' },
                { key: 'volunteers_target', label: 'Volunteers Target', color: 'orange', icon: '🤝' }
            ];
            
            cardsContainer.innerHTML = '';
            
            metrics.forEach(metric => {
                const value = projection[metric.key] || 0;
                const card = document.createElement('div');
                card.className = `bg-${metric.color}-50 rounded-lg p-4 border border-${metric.color}-200`;
                card.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-${metric.color}-600">${metric.label}</p>
                            <p class="text-xl font-bold text-${metric.color}-900">${value.toLocaleString()}</p>
                        </div>
                        <div class="text-2xl">${metric.icon}</div>
                    </div>
                `;
                cardsContainer.appendChild(card);
            });
        }

        function updateBranchPerformanceTable(performanceData, projection) {
            const tbody = document.getElementById('branchPerformanceBody');
            tbody.innerHTML = '';
            
            if (!Array.isArray(performanceData)) {
                console.error('performanceData is not an array:', performanceData);
                return;
            }
            
            performanceData.forEach(branchData => {
                const row = document.createElement('tr');
                
                // Get average attendance (always show this)
                const averageAttendance = branchData.actuals.weekly_avg_attendance || 0;
                
                // Get target and calculate percentage if projection exists
                let targetHtml = '-';
                let percentageHtml = '-';
                let percentageColor = 'text-gray-900';
                
                if (branchData.projection && branchData.projection.weekly_avg_attendance_target) {
                    const weeklyAvgTarget = branchData.projection.weekly_avg_attendance_target;
                    const progressPercentage = weeklyAvgTarget > 0 ? 
                        Math.round((averageAttendance / weeklyAvgTarget) * 100) : 0;
                    
                    targetHtml = fmt(weeklyAvgTarget);
                    
                    // Color coding based on performance
                    if (progressPercentage >= 100) {
                        percentageColor = 'text-green-600';
                    } else if (progressPercentage >= 90) {
                        percentageColor = 'text-yellow-600';
                    } else {
                        percentageColor = 'text-red-600';
                    }
                    
                    percentageHtml = `<span class="${percentageColor} font-semibold">${progressPercentage}%</span>`;
                }
                
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${branchData.branch_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${fmt(branchData.actuals.attendance)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${fmt(branchData.actuals.guests)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${fmt(branchData.actuals.converts)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-l border-gray-300">${targetHtml}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${fmt(averageAttendance)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${percentageHtml}</td>
                `;
                tbody.appendChild(row);
            });
        }

        async function render(){
            const year = document.getElementById('yearSelect').value;
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;
            const compare = document.getElementById('compareSelect').value;
            
            try {
                // Load global projection
                globalProjection = await loadGlobalProjection(year);
                updateGlobalProjectionCards(globalProjection);
                
                // Load network performance
                const data = (await loadNetworkPerformance({ year, start_date: start, end_date: end, compare })).data;
                
                document.getElementById('attendanceTotal').textContent = fmt(data.actuals.attendance);
                document.getElementById('guestsTotal').textContent = fmt(data.actuals.guests);
                document.getElementById('convertsTotal').textContent = fmt(data.actuals.converts);
                
                // Update delta indicators (placeholder for now)
                document.getElementById('attendanceDelta').textContent = '+5.2% vs last year';
                document.getElementById('guestsDelta').textContent = '+12.1% vs last year';
                document.getElementById('convertsDelta').textContent = '+8.7% vs last year';
                
                // Load branches if not already loaded
                if (branches.length === 0) {
                    await loadBranches();
                }
                
                // Update branch performance table
                updateBranchPerformanceTable(data.branches || [], globalProjection);
                
            } catch (error) {
                console.error('Error rendering data:', error);
            }
        }

        // Global projection modal handlers
        document.getElementById('createGlobalProjectionBtn').addEventListener('click', function() {
            document.getElementById('globalProjectionModal').classList.remove('hidden');
        });

        document.getElementById('cancelGlobalProjection').addEventListener('click', function() {
            document.getElementById('globalProjectionModal').classList.add('hidden');
        });

        document.getElementById('globalProjectionForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                year: document.getElementById('projectionYear').value,
                attendance_target: document.getElementById('attendanceTarget').value,
                converts_target: document.getElementById('convertsTarget').value,
                leaders_target: document.getElementById('leadersTarget').value,
                volunteers_target: document.getElementById('volunteersTarget').value,
                weekly_avg_attendance_target: document.getElementById('weeklyAvgAttendanceTarget').value,
                guests_target: document.getElementById('guestsTarget').value,
                lifegroups_target: document.getElementById('lifegroupsTarget').value,
                lifegroups_memberships_target: document.getElementById('lifegroupsMembershipsTarget').value,
                lifegroups_weekly_avg_attendance_target: document.getElementById('lifegroupsWeeklyAvgTarget').value,
                is_global: true
            };
            
            try {
                const res = await fetch('/api/projections/global', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                });
                
                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        document.getElementById('globalProjectionModal').classList.add('hidden');
                        await render(); // Refresh the data
                        alert('Global projection saved successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                } else {
                    alert('Error saving global projection');
                }
            } catch (error) {
                console.error('Error saving global projection:', error);
                alert('Error saving global projection');
            }
        });

        // Close modal when clicking outside
        document.getElementById('globalProjectionModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });

        document.getElementById('applyBtn').addEventListener('click', render);
        
        // Initial load
        render();
    </script>
</x-sidebar-layout>




<x-sidebar-layout title="Super Admin Report Dashboard">
    <div class="space-y-6" x-data="superAdminDashboard()">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Super Admin Report Dashboard</h1>
                    <p class="text-gray-600 mt-1">View event attendance and small groups across all branches</p>
                </div>
            </div>
        </div>

        <!-- Time Period Selector -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex flex-wrap gap-3 items-center">
                <label class="text-sm font-medium text-gray-700">Time Period:</label>
                <button @click="selectPeriod('this_week')" 
                        :class="period === 'this_week' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                        class="px-4 py-2 rounded-lg transition-colors">
                    This Week
                </button>
                <button @click="selectPeriod('this_month')" 
                        :class="period === 'this_month' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                        class="px-4 py-2 rounded-lg transition-colors">
                    This Month
                </button>
                <button @click="selectPeriod('last_quarter')" 
                        :class="period === 'last_quarter' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                        class="px-4 py-2 rounded-lg transition-colors">
                    Last Quarter
                </button>
                <button @click="selectPeriod('this_year')" 
                        :class="period === 'this_year' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                        class="px-4 py-2 rounded-lg transition-colors">
                    This Year
                </button>
                <button @click="selectPeriod('custom')" 
                        :class="period === 'custom' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                        class="px-4 py-2 rounded-lg transition-colors">
                    Custom Range
                </button>
            </div>

            <!-- Custom Date Range Picker -->
            <div x-show="period === 'custom'" 
                 x-transition
                 class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" 
                           x-model="startDate" 
                           @change="loadData()"
                           class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" 
                           x-model="endDate" 
                           @change="loadData()"
                           class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div class="mt-4 text-sm text-gray-600" x-show="periodLabel">
                <span x-text="periodLabel"></span>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="bg-white rounded-lg shadow-sm p-6 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="mt-2 text-gray-600">Loading dashboard data...</p>
        </div>

        <!-- Event Attendance Section -->
        <div x-show="!loading && dashboardData" class="space-y-6">
            <!-- Event Attendance Totals -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Event Attendance - Overall Totals</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-blue-50 rounded-lg p-6">
                        <div class="text-sm font-medium text-blue-600 mb-1">Total Attendance</div>
                        <div class="text-3xl font-bold text-blue-900" x-text="dashboardData?.event_attendance?.totals?.total_attendance || 0"></div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-6">
                        <div class="text-sm font-medium text-green-600 mb-1">First Time Guests</div>
                        <div class="text-3xl font-bold text-green-900" x-text="dashboardData?.event_attendance?.totals?.first_time_guests || 0"></div>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-6">
                        <div class="text-sm font-medium text-purple-600 mb-1">New Converts</div>
                        <div class="text-3xl font-bold text-purple-900" x-text="dashboardData?.event_attendance?.totals?.new_converts || 0"></div>
                    </div>
                </div>
            </div>

            <!-- Branch Breakdown - Event Attendance -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Event Attendance by Branch</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <template x-for="branch in dashboardData?.event_attendance?.branches || []" :key="branch.id">
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3" x-text="branch.name"></h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Total Attendance:</span>
                                    <span class="text-sm font-medium text-gray-900" x-text="branch.total_attendance || 0"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Average Attendance:</span>
                                    <span class="text-sm font-medium text-gray-900" x-text="branch.average_attendance || 0"></span>
                                </div>
                                <template x-if="branch.weekly_avg_target !== null && branch.weekly_avg_target !== undefined">
                                    <div class="pt-2 border-t border-gray-200 space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Weekly Avg Target:</span>
                                            <span class="text-sm font-medium text-gray-900" x-text="branch.weekly_avg_target || 0"></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">vs Target:</span>
                                            <span 
                                                :class="{
                                                    'text-green-600': branch.target_percentage >= 100,
                                                    'text-yellow-600': branch.target_percentage >= 90 && branch.target_percentage < 100,
                                                    'text-red-600': branch.target_percentage < 90
                                                }"
                                                class="text-sm font-semibold"
                                                x-text="branch.target_percentage ? branch.target_percentage + '%' : 'N/A'">
                                            </span>
                                        </div>
                                    </div>
                                </template>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">First Time Guests:</span>
                                    <span class="text-sm font-medium text-gray-900" x-text="branch.first_time_guests || 0"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">New Converts:</span>
                                    <span class="text-sm font-medium text-gray-900" x-text="branch.new_converts || 0"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Small Groups Section - Overall Totals -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Small Groups - Overall</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Small Group Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="group in dashboardData?.small_groups?.totals || []" :key="group.id">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="group.name"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="group.branch_name"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="group.attendance || 0"></td>
                                </tr>
                            </template>
                            <tr x-show="!dashboardData?.small_groups?.totals?.length">
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No small groups found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Small Groups by Branch -->
            <div class="space-y-6">
                <template x-for="branchGroup in dashboardData?.small_groups?.by_branch || []" :key="branchGroup.branch_id">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4" x-text="branchGroup.branch_name + ' - Small Groups'"></h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Small Group Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-for="group in branchGroup.groups || []" :key="group.id">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="group.name"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="group.attendance || 0"></td>
                                        </tr>
                                    </template>
                                    <tr x-show="!branchGroup.groups?.length">
                                        <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">No small groups found</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        function superAdminDashboard() {
            return {
                period: @js($currentPeriod ?? 'this_month'),
                startDate: @js($currentStartDate ?? ''),
                endDate: @js($currentEndDate ?? ''),
                dashboardData: @js($dashboardData ?? null),
                loading: false,
                periodLabel: @js(isset($dashboardData) && isset($dashboardData['period']) && isset($dashboardData['period']['label']) ? $dashboardData['period']['label'] : ''),

                init() {
                    // Set initial period label if data exists
                    if (this.dashboardData?.period?.label) {
                        this.periodLabel = this.dashboardData.period.label;
                    }
                },

                selectPeriod(newPeriod) {
                    this.period = newPeriod;
                    if (newPeriod !== 'custom') {
                        this.startDate = '';
                        this.endDate = '';
                    }
                    this.loadData();
                },

                async loadData() {
                    if (this.period === 'custom' && (!this.startDate || !this.endDate)) {
                        return;
                    }

                    this.loading = true;

                    try {
                        const params = new URLSearchParams({
                            period: this.period,
                        });

                        if (this.period === 'custom') {
                            params.append('start_date', this.startDate);
                            params.append('end_date', this.endDate);
                        }

                        const response = await fetch(`{{ route('admin.api.reports.dashboard') }}?${params.toString()}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin',
                        });

                        const result = await response.json();

                        if (result.success) {
                            this.dashboardData = result.data;
                            this.periodLabel = result.data.period.label;
                        } else {
                            alert('Error loading dashboard data: ' + (result.message || 'Unknown error'));
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error loading dashboard data. Please try again.');
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>
</x-sidebar-layout>


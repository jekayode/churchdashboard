<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Church Dashboard') }} - LifeGroups</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-20">
                    <div class="flex items-center">
                        <a href="/" class="flex-shrink-0 flex items-center">
                            <img src="https://lifepointeng.org/wp-content/uploads/2023/10/Lifepointe-Logo-White.png" alt="LifePointe" class="h-12 w-auto"/>
                        </a>
                    </div>
                    <div class="flex items-center space-x-8">
                        <a href="{{ route('public.events') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                            Events
                        </a>
                        <a href="{{ route('public.lifegroups') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                            LifeGroups
                        </a>
                        <a href="{{ route('login') }}" class="text-gray-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Login</a>
                        <a href="{{ route('register') }}" class="bg-secondary-600 hover:bg-secondary-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">Register</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">LifeGroups</h1>
                    <p class="text-gray-600">Find friends, family, and focus. Join a LifeGroup near you!</p>
                </div>

                <!-- Search and Filters -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <input 
                                type="text" 
                                id="searchInput" 
                                placeholder="Search LifeGroups..." 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>
                        <div>
                            <select id="branchFilter" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Expressions</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- LifeGroups Grid -->
                <div id="lifegroupsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- LifeGroups will be loaded here -->
                </div>

                <!-- Loading State -->
                <div id="loadingState" class="text-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
                    <p class="mt-2 text-gray-600">Loading LifeGroups...</p>
                </div>

                <!-- No LifeGroups State -->
                <div id="noLifeGroupsState" class="text-center py-12 hidden">
                    <div class="text-gray-400 text-6xl mb-4">👥</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No LifeGroups found</h3>
                    <p class="text-gray-600">There are no LifeGroups matching your criteria.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadBranches();
            loadLifeGroups();
            
            // Add event listeners for filters
            document.getElementById('searchInput').addEventListener('input', debounce(loadLifeGroups, 300));
            document.getElementById('branchFilter').addEventListener('change', loadLifeGroups);
        });

        async function loadBranches() {
            try {
                const response = await fetch('/api/welcome/branches');
                const branches = await response.json();
                
                const branchFilter = document.getElementById('branchFilter');
                branchFilter.innerHTML = '<option value="">All Expressions</option>';
                
                branches.forEach(branch => {
                    const option = document.createElement('option');
                    option.value = branch.id;
                    option.textContent = branch.name;
                    branchFilter.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading branches:', error);
            }
        }

        async function loadLifeGroups() {
            try {
                document.getElementById('loadingState').classList.remove('hidden');
                document.getElementById('noLifeGroupsState').classList.add('hidden');
                
                const params = new URLSearchParams();

                // Add filters
                const search = document.getElementById('searchInput').value;
                if (search) params.append('q', search);

                const branchId = document.getElementById('branchFilter').value;
                if (branchId) params.append('branch_id', branchId);

                const response = await fetch(`/api/welcome/small-groups?${params}`);
                const lifegroups = await response.json();

                renderLifeGroupsGrid(lifegroups);
            } catch (error) {
                console.error('Error loading LifeGroups:', error);
                showNotification('Error loading LifeGroups', 'error');
            } finally {
                document.getElementById('loadingState').classList.add('hidden');
            }
        }

        function renderLifeGroupsGrid(lifegroups) {
            const grid = document.getElementById('lifegroupsGrid');
            
            if (lifegroups.length === 0) {
                grid.innerHTML = '';
                document.getElementById('noLifeGroupsState').classList.remove('hidden');
                return;
            }

            document.getElementById('noLifeGroupsState').classList.add('hidden');
            
            grid.innerHTML = lifegroups.map(group => {
                return `
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="text-lg font-semibold text-gray-900">${group.name}</h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    LifeGroup
                                </span>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4">${group.branch?.name || 'Join this LifeGroup!'}</p>
                            
                            <div class="space-y-2 text-sm text-gray-500 mb-4">
                                ${group.location ? `
                                    <div class="flex items-center">
                                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        ${group.location}
                                    </div>
                                ` : ''}
                                ${group.meeting_day && group.meeting_time ? `
                                    <div class="flex items-center">
                                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        ${group.meeting_day} ${group.meeting_time}
                                    </div>
                                ` : ''}
                            </div>
                            
                            <button 
                                onclick="showNotification('Contact the church office to join this LifeGroup!', 'info')"
                                class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md text-sm font-medium transition-colors"
                            >
                                Learn More
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white' :
                type === 'error' ? 'bg-red-500 text-white' :
                'bg-blue-500 text-white'
            }`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 5000);
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    </script>
</body>
</html>



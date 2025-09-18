<x-sidebar-layout title="My Departments">
    <div class="space-y-6">
        @php
            $user = Auth::user();
            $member = $user->member;
        @endphp

        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">My Departments</h1>
                    <p class="text-gray-600 mt-1">View the departments where you serve and contribute.</p>
                </div>
                @if($member && $member->departments()->exists())
                    <span class="px-4 py-2 bg-indigo-100 text-indigo-800 text-sm font-medium rounded-full">
                        Serving in {{ $member->departments()->count() }} Department{{ $member->departments()->count() > 1 ? 's' : '' }}
                    </span>
                @endif
            </div>
        </div>

        @if($member && $member->departments()->exists())
            <!-- Departments Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($member->departments()->with(['ministry:id,name,branch_id', 'ministry.branch:id,name', 'leader:id,name,email', 'members:id,name'])->get() as $department)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                        <!-- Department Header -->
                        <div class="p-6 pb-4">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $department->name }}</h3>
                                    <p class="text-sm text-gray-500">{{ $department->ministry?->name }}</p>
                                    @if($department->ministry?->branch)
                                        <p class="text-xs text-gray-400">{{ $department->ministry->branch->name }}</p>
                                    @endif
                                </div>
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $department->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($department->status) }}
                                </span>
                            </div>

                            @if($department->description)
                                <p class="text-sm text-gray-600 mb-4">{{ $department->description }}</p>
                            @endif
                        </div>

                        <!-- Department Details -->
                        <div class="px-6 pb-4 space-y-3">
                            <!-- Leader -->
                            @if($department->leader)
                                <div class="flex items-center text-sm">
                                    <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span class="text-gray-600">Led by</span>
                                    <span class="font-medium text-gray-900 ml-1">{{ $department->leader->name }}</span>
                                </div>
                            @endif

                            <!-- Team Size -->
                            <div class="flex items-center text-sm">
                                <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                                <span class="text-gray-600">{{ $department->members()->count() }} team member{{ $department->members()->count() > 1 ? 's' : '' }}</span>
                            </div>

                            <!-- Your Role -->
                            <div class="flex items-center text-sm">
                                <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-gray-600">Your role:</span>
                                <span class="font-medium ml-1 {{ $department->leader_id === $member->id ? 'text-indigo-700' : 'text-green-700' }}">
                                    {{ $department->leader_id === $member->id ? 'Department Leader' : 'Team Member' }}
                                </span>
                            </div>
                        </div>

                        <!-- Contact Leader Button -->
                        @if($department->leader && $department->leader->id !== $member->id && $department->leader->email)
                            <div class="px-6 pb-6">
                                <a href="mailto:{{ $department->leader->email }}" 
                                   class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    Contact Leader
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Additional Information -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Serving with Purpose</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>
                                Thank you for serving in {{ $member->departments()->count() > 1 ? 'these departments' : 'this department' }}! 
                                Your contribution makes a significant impact in our church community. 
                                If you have questions about your role or would like to discuss additional ways to serve, 
                                please reach out to your department leader or pastor.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- No Departments Assigned -->
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Department Assignments</h3>
                <p class="text-gray-600 mb-6 max-w-md mx-auto">
                    You're not currently assigned to any departments. There are many ways to serve and contribute to our church community.
                </p>

                <div class="space-y-3">
                    <p class="text-sm text-gray-500">
                        Interested in serving? Contact your pastor or ministry leader to explore opportunities.
                    </p>
                    
                    @if($user->getPrimaryBranch() && $user->getPrimaryBranch()->email)
                        <a href="mailto:{{ $user->getPrimaryBranch()->email }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Contact Church Leadership
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-sidebar-layout>

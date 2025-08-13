@props([
    'event' => null,
    'members' => [],
    'readonly' => false
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-church border border-church-100 overflow-hidden']) }}>
    <!-- Header -->
    <div class="bg-gradient-brand px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white">Attendance Tracker</h3>
                    <p class="text-white/80 text-sm">
                        {{ $event ? $event->name : 'Event Attendance' }} - {{ now()->format('M j, Y') }}
                    </p>
                </div>
            </div>
            @if(!$readonly)
                <div class="flex items-center space-x-2">
                    <button 
                        type="button" 
                        @click="markAllPresent()" 
                        class="px-3 py-1.5 bg-white/20 text-white text-sm rounded-lg hover:bg-white/30 transition-colors">
                        Mark All Present
                    </button>
                    <button 
                        type="button" 
                        @click="clearAll()" 
                        class="px-3 py-1.5 bg-white/20 text-white text-sm rounded-lg hover:bg-white/30 transition-colors">
                        Clear All
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Attendance Form -->
    <div class="p-6" x-data="attendanceTracker()">
        <!-- Quick Stats -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-church-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-church-600" x-text="presentCount"></div>
                <div class="text-sm text-church-700">Present</div>
            </div>
            <div class="bg-secondary-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-secondary-600" x-text="absentCount"></div>
                <div class="text-sm text-secondary-700">Absent</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-gray-600" x-text="totalMembers"></div>
                <div class="text-sm text-gray-700">Total</div>
            </div>
        </div>

        <!-- Search and Filter -->
        @if(!$readonly)
            <div class="mb-6">
                <div class="flex items-center space-x-4">
                    <div class="flex-1">
                        <input 
                            type="text" 
                            x-model="searchTerm"
                            placeholder="Search members..."
                            class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                    </div>
                    <select x-model="filterStatus" class="rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                        <option value="all">All Members</option>
                        <option value="present">Present Only</option>
                        <option value="absent">Absent Only</option>
                        <option value="unmarked">Unmarked Only</option>
                    </select>
                </div>
            </div>
        @endif

        <!-- Member List -->
        <div class="space-y-2 max-h-96 overflow-y-auto">
            <template x-for="member in filteredMembers" :key="member.id">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="flex items-center space-x-3">
                        <!-- Avatar -->
                        <div class="w-10 h-10 bg-church-500 rounded-full flex items-center justify-center text-white font-medium">
                            <span x-text="member.name.charAt(0).toUpperCase()"></span>
                        </div>
                        
                        <!-- Member Info -->
                        <div>
                            <div class="font-medium text-gray-900" x-text="member.name"></div>
                            <div class="text-sm text-gray-500" x-text="member.email"></div>
                        </div>
                    </div>

                    <!-- Attendance Status -->
                    <div class="flex items-center space-x-2">
                        @if(!$readonly)
                            <!-- Present Button -->
                            <button 
                                type="button"
                                @click="markAttendance(member.id, 'present')"
                                :class="{
                                    'bg-church-500 text-white': member.status === 'present',
                                    'bg-gray-200 text-gray-700 hover:bg-church-100': member.status !== 'present'
                                }"
                                class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Present
                            </button>

                            <!-- Absent Button -->
                            <button 
                                type="button"
                                @click="markAttendance(member.id, 'absent')"
                                :class="{
                                    'bg-secondary-500 text-white': member.status === 'absent',
                                    'bg-gray-200 text-gray-700 hover:bg-secondary-100': member.status !== 'absent'
                                }"
                                class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                                Absent
                            </button>
                        @else
                            <!-- Read-only Status Display -->
                            <div class="flex items-center space-x-2">
                                <div 
                                    :class="{
                                        'bg-church-100 text-church-800': member.status === 'present',
                                        'bg-secondary-100 text-secondary-800': member.status === 'absent',
                                        'bg-gray-100 text-gray-600': !member.status
                                    }"
                                    class="px-3 py-1 rounded-full text-sm font-medium">
                                    <span x-text="member.status === 'present' ? 'Present' : member.status === 'absent' ? 'Absent' : 'Not Marked'"></span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </template>
        </div>

        @if(!$readonly)
            <!-- Save Button -->
            <div class="mt-6 flex items-center justify-between pt-4 border-t border-gray-200">
                <div class="text-sm text-gray-500">
                    <span x-text="`${presentCount} of ${totalMembers} members marked present`"></span>
                </div>
                <button 
                    type="button"
                    @click="saveAttendance()"
                    class="px-6 py-2 bg-gradient-church text-white rounded-lg hover:opacity-90 transition-opacity font-medium">
                    Save Attendance
                </button>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function attendanceTracker() {
    return {
        members: @json($members),
        searchTerm: '',
        filterStatus: 'all',
        
        init() {
            // Initialize member status if not set
            this.members.forEach(member => {
                if (!member.status) {
                    member.status = null;
                }
            });
        },

        get filteredMembers() {
            let filtered = this.members;

            // Apply search filter
            if (this.searchTerm) {
                filtered = filtered.filter(member => 
                    member.name.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                    member.email.toLowerCase().includes(this.searchTerm.toLowerCase())
                );
            }

            // Apply status filter
            if (this.filterStatus !== 'all') {
                filtered = filtered.filter(member => {
                    switch (this.filterStatus) {
                        case 'present':
                            return member.status === 'present';
                        case 'absent':
                            return member.status === 'absent';
                        case 'unmarked':
                            return !member.status;
                        default:
                            return true;
                    }
                });
            }

            return filtered;
        },

        get presentCount() {
            return this.members.filter(member => member.status === 'present').length;
        },

        get absentCount() {
            return this.members.filter(member => member.status === 'absent').length;
        },

        get totalMembers() {
            return this.members.length;
        },

        markAttendance(memberId, status) {
            const member = this.members.find(m => m.id === memberId);
            if (member) {
                member.status = status;
            }
        },

        markAllPresent() {
            this.members.forEach(member => {
                member.status = 'present';
            });
        },

        clearAll() {
            this.members.forEach(member => {
                member.status = null;
            });
        },

        saveAttendance() {
            // Prepare attendance data
            const attendanceData = this.members.map(member => ({
                member_id: member.id,
                status: member.status || 'absent',
                event_id: {{ $event ? $event->id : 'null' }}
            }));

            // Send to server
            fetch('/api/attendance', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    attendance: attendanceData
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.$dispatch('notify', {
                        type: 'success',
                        message: 'Attendance saved successfully!'
                    });
                } else {
                    throw new Error(data.message || 'Failed to save attendance');
                }
            })
            .catch(error => {
                this.$dispatch('notify', {
                    type: 'error',
                    message: 'Error saving attendance: ' + error.message
                });
            });
        }
    }
}
</script>
@endpush 
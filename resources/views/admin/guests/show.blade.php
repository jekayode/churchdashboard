<x-sidebar-layout title="Guest Details">
    <div class="space-y-6" x-data="guestDetail()">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $guest->name }}</h1>
                    <p class="text-gray-600 mt-1">Guest Registration Details</p>
                </div>
                <a href="{{ route('guests.index') }}" 
                   class="text-gray-600 hover:text-gray-900">
                    ← Back to Guest List
                </a>
            </div>
        </div>

        <!-- Guest Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Guest Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Full Name</label>
                        <p class="text-gray-900">{{ $guest->name }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">First Name</label>
                        <p class="text-gray-900">{{ $guest->first_name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Surname</label>
                        <p class="text-gray-900">{{ $guest->surname ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Email</label>
                        <p class="text-gray-900">{{ $guest->email ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Phone</label>
                        <p class="text-gray-900">{{ $guest->phone ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Gender</label>
                        <p class="text-gray-900">{{ $guest->gender ? ucfirst($guest->gender) : 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Date of Birth</label>
                        <p class="text-gray-900">{{ $guest->date_of_birth ? $guest->date_of_birth->format('M d, Y') : 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Age Group</label>
                        <p class="text-gray-900">{{ $guest->age_group ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Marital Status</label>
                        <p class="text-gray-900">{{ $guest->marital_status ? ucfirst(str_replace('_', ' ', $guest->marital_status)) : 'N/A' }}</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Home Address</label>
                        <p class="text-gray-900">{{ $guest->home_address ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Preferred Call Time</label>
                        <p class="text-gray-900">{{ $guest->preferred_call_time ? ucfirst(str_replace('-', ' ', $guest->preferred_call_time)) : 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Closest Location</label>
                        <p class="text-gray-900">{{ $guest->closest_location ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Branch</label>
                        <p class="text-gray-900">{{ $guest->branch->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Current Status</label>
                        <p class="text-gray-900">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $guest->member_status === 'visitor' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                {{ ucfirst($guest->member_status) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registration Details -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Registration Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Registration Date</label>
                        <p class="text-gray-900">{{ $guest->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Discovery Source</label>
                        <p class="text-gray-900">{{ $guest->discovery_source ? ucfirst(str_replace('-', ' ', $guest->discovery_source)) : 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Staying Intention</label>
                        <p class="text-gray-900">{{ $guest->staying_intention ? ucfirst(str_replace('-', ' ', $guest->staying_intention)) : 'N/A' }}</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Prayer Request</label>
                        <p class="text-gray-900">{{ $guest->prayer_request ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Additional Info</label>
                        <p class="text-gray-900">{{ $guest->additional_info ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Follow-ups Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Follow-ups</h2>
            
            <!-- Add Follow-up Form -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg" x-show="showFollowUpForm" x-transition>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Follow-up</h3>
                <form @submit.prevent="addFollowUp()" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Follow-up Type *</label>
                            <input type="text" 
                                   x-model="followUpForm.follow_up_type" 
                                   required
                                   class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Date *</label>
                            <input type="date" 
                                   x-model="followUpForm.contact_date" 
                                   required
                                   class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Status *</label>
                            <select x-model="followUpForm.contact_status" 
                                    required
                                    class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select Status</option>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="rescheduled">Rescheduled</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Next Follow-up Date</label>
                            <input type="date" 
                                   x-model="followUpForm.next_follow_up_date" 
                                   class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes *</label>
                        <textarea x-model="followUpForm.notes" 
                                  required
                                  rows="3"
                                  class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Outcome</label>
                        <input type="text" 
                               x-model="followUpForm.outcome" 
                               class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" 
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors">
                            Add Follow-up
                        </button>
                        <button type="button" 
                                @click="showFollowUpForm = false" 
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Follow-ups List -->
            <div class="mb-4">
                <button @click="showFollowUpForm = !showFollowUpForm" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <span x-text="showFollowUpForm ? 'Cancel' : 'Add Follow-up'"></span>
                </button>
            </div>

            <div class="space-y-4">
                @forelse($guest->followUps as $followUp)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="font-medium text-gray-900">{{ $followUp->follow_up_type }}</h4>
                                <p class="text-sm text-gray-500">
                                    {{ $followUp->contact_date->format('M d, Y') }} • 
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        {{ $followUp->contact_status === 'completed' ? 'bg-green-100 text-green-800' : 
                                           ($followUp->contact_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ ucfirst($followUp->contact_status) }}
                                    </span>
                                </p>
                            </div>
                            <div class="text-sm text-gray-500">
                                By {{ $followUp->createdBy->name ?? 'Unknown' }}
                            </div>
                        </div>
                        <p class="text-gray-700 mb-2">{{ $followUp->notes }}</p>
                        @if($followUp->next_follow_up_date)
                            <p class="text-sm text-gray-500">
                                Next follow-up: {{ $followUp->next_follow_up_date->format('M d, Y') }}
                            </p>
                        @endif
                        @if($followUp->outcome)
                            <p class="text-sm text-gray-600 mt-2">
                                <strong>Outcome:</strong> {{ $followUp->outcome }}
                            </p>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-8">No follow-ups recorded yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Status History -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Status History</h2>
            <div class="space-y-4">
                @forelse($guest->statusHistory as $history)
                    <div class="border-l-4 border-indigo-500 pl-4 py-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium text-gray-900">
                                    Changed from <span class="text-gray-600">{{ ucfirst($history->previous_status ?? 'N/A') }}</span> 
                                    to <span class="text-indigo-600">{{ ucfirst($history->new_status) }}</span>
                                </p>
                                @if($history->reason)
                                    <p class="text-sm text-gray-600 mt-1"><strong>Reason:</strong> {{ $history->reason }}</p>
                                @endif
                                @if($history->notes)
                                    <p class="text-sm text-gray-600 mt-1">{{ $history->notes }}</p>
                                @endif
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $history->changed_at->format('M d, Y h:i A') }}<br>
                                by {{ $history->changedBy->name ?? 'Unknown' }}
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-8">No status changes recorded yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Change Status Form -->
        @if($guest->member_status === 'visitor')
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Change Status</h2>
                <form @submit.prevent="updateStatus()" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Status *</label>
                            <select x-model="statusForm.new_status" 
                                    required
                                    class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select Status</option>
                                <option value="member">Member</option>
                                <option value="volunteer">Volunteer</option>
                                <option value="leader">Leader</option>
                                <option value="minister">Minister</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                            <input type="text" 
                                   x-model="statusForm.reason" 
                                   class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea x-model="statusForm.notes" 
                                  rows="3"
                                  class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    <button type="submit" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Update Status
                    </button>
                </form>
            </div>
        @endif

        <!-- Prayer Requests -->
        @if($guest->prayerRequests->isNotEmpty())
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Prayer Requests</h2>
                <div class="space-y-4">
                    @foreach($guest->prayerRequests as $prayerRequest)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <p class="text-gray-700">{{ $prayerRequest->request ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-500 mt-2">
                                {{ $prayerRequest->created_at->format('M d, Y') }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <script>
        function guestDetail() {
            return {
                showFollowUpForm: false,
                followUpForm: {
                    follow_up_type: '',
                    contact_date: '',
                    contact_status: '',
                    notes: '',
                    next_follow_up_date: '',
                    outcome: '',
                },
                statusForm: {
                    new_status: '',
                    reason: '',
                    notes: '',
                },
                async addFollowUp() {
                    try {
                        const response = await fetch('{{ route('guests.add-follow-up', $guest) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(this.followUpForm),
                        });

                        const data = await response.json();

                        if (data.success) {
                            alert('Follow-up added successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Failed to add follow-up'));
                        }
                    } catch (error) {
                        alert('Error: ' + error.message);
                    }
                },
                async updateStatus() {
                    if (!this.statusForm.new_status) {
                        alert('Please select a new status');
                        return;
                    }

                    try {
                        const response = await fetch('{{ route('guests.update-status', $guest) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(this.statusForm),
                        });

                        const data = await response.json();

                        if (data.success) {
                            alert('Status updated successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Failed to update status'));
                        }
                    } catch (error) {
                        alert('Error: ' + error.message);
                    }
                }
            }
        }
    </script>
</x-sidebar-layout>


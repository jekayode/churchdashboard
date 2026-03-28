<x-sidebar-layout title="Edit Event">
    <div class="max-w-5xl mx-auto space-y-6">
        @if(session('status'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.events.update', $event) }}" class="bg-white shadow-sm rounded p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" value="{{ old('name', $event->name) }}" class="w-full border-gray-300 rounded"/>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border-gray-300 rounded">
                        @foreach(['draft','active','cancelled'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $event->status) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <input type="text" name="location" value="{{ old('location', $event->location) }}" class="w-full border-gray-300 rounded"/>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Capacity</label>
                    <input type="number" name="max_capacity" value="{{ old('max_capacity', $event->max_capacity) }}" class="w-full border-gray-300 rounded"/>
                </div>
            </div>

            @php($event->loadMissing('branch'))
            <div class="rounded border border-amber-100 bg-amber-50/80 p-4 space-y-3">
                <h3 class="text-amber-900 font-semibold text-sm">Public page</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Public URL slug *</label>
                        <input type="text" name="public_slug" value="{{ old('public_slug', $event->public_slug) }}" required
                               pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                               class="w-full border-gray-300 rounded font-mono text-sm"/>
                        <p class="text-xs text-gray-600 mt-1">Path: <span class="font-mono">/event/{{ $event->branch?->public_code ?? 'code' }}/{{ old('public_slug', $event->public_slug) }}</span>. Changing the slug breaks old links.</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <input type="hidden" name="is_public" value="0">
                        <input type="checkbox" name="is_public" value="1" id="adminIsPublic" class="mt-1 rounded border-gray-300" @checked(old('is_public', $event->is_public))>
                        <label for="adminIsPublic" class="text-sm text-gray-700">Show on public events calendar</label>
                    </div>
                </div>
                @if($event->public_detail_url)
                    <div class="flex flex-wrap items-start gap-4 pt-2 border-t border-amber-200/60">
                        <div class="flex flex-col items-start gap-2">
                            <p class="text-xs font-medium text-gray-700 mb-1">QR (public page)</p>
                            <img src="{{ url('/api/events/'.$event->id.'/public-page-qr') }}" width="96" height="96" alt="QR code" class="h-24 w-24 border border-gray-200 rounded bg-white">
                            <a href="{{ url('/api/events/'.$event->id.'/public-page-qr/download?pixels=2048') }}" class="text-xs text-indigo-600 hover:underline">Download high-res PNG</a>
                        </div>
                        <div class="text-sm text-gray-700 break-all max-w-xl">
                            <span class="font-medium">Link:</span>
                            <a href="{{ $event->public_detail_url }}" target="_blank" rel="noopener" class="text-indigo-600 hover:underline">{{ $event->public_detail_url }}</a>
                        </div>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Event Type</label>
                    <select name="type" id="eventType" class="w-full border-gray-300 rounded">
                        @php($types = ['service','conference','workshop','outreach','social','other'])
                        @foreach($types as $t)
                            <option value="{{ $t }}" @selected(old('type', $event->type ?? 'service') === $t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date & Time</label>
                    <input type="datetime-local" name="start_date_time" value="{{ old('start_date_time', $event->start_date_time ?? $event->start_date) }}" class="w-full border-gray-300 rounded"/>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Service Time</label>
                    <input type="time" name="service_time" value="{{ old('service_time', $event->service_time) }}" class="w-full border-gray-300 rounded"/>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Day of Week (for recurring)</label>
                    <select name="day_of_week" class="w-full border-gray-300 rounded">
                        @foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $i => $d)
                            <option value="{{ $i }}" @selected(old('day_of_week', $event->day_of_week) == $i)>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="rounded border border-gray-200 bg-gray-50 p-4">
                <h3 class="text-gray-800 font-semibold mb-3">Registration</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Registration Type</label>
                        @php($regTypes = ['none' => 'None', 'simple' => 'Simple', 'form' => 'Custom Form', 'link' => 'External Link'])
                        <select name="registration_type" id="registrationType" class="w-full border-gray-300 rounded" onchange="toggleRegistrationFields(this.value)">
                            @foreach($regTypes as $key => $label)
                                <option value="{{ $key }}" @selected(old('registration_type', $event->registration_type ?? 'simple') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="registrationLinkWrap">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Registration Link</label>
                        <input type="url" name="registration_link" value="{{ old('registration_link', $event->registration_link) }}" class="w-full border-gray-300 rounded" placeholder="https://..."/>
                    </div>
                    <div id="customFormWrap" class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Custom Form Fields (JSON)</label>
                        <textarea name="custom_form_fields" rows="3" class="w-full border-gray-300 rounded" placeholder='[{"label":"Phone","type":"text","required":true}]'>{{ old('custom_form_fields', is_array($event->custom_form_fields) ? json_encode($event->custom_form_fields) : $event->custom_form_fields) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="rounded border border-blue-200 bg-blue-50 p-4">
                <h3 class="text-blue-800 font-semibold mb-3">Recurring Event Configuration</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                        <select name="frequency" class="w-full border-gray-300 rounded">
                            @foreach(['weekly','monthly','quarterly','annually'] as $f)
                                <option value="{{ $f }}" @selected(old('frequency', $event->frequency) === $f)>{{ ucfirst($f) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" name="recurrence_end_date" value="{{ old('recurrence_end_date', optional($event->recurrence_end_date)->toDateString()) }}" class="w-full border-gray-300 rounded"/>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Occurrences (optional)</label>
                        <input type="number" name="max_occurrences" value="{{ old('max_occurrences') }}" class="w-full border-gray-300 rounded"/>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save Changes</button>
                <a href="{{ url()->previous() }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200">Cancel</a>
                <div class="ml-auto flex items-center gap-2">
                    <form method="POST" action="{{ route('admin.events.generate-instances', $event) }}">
                        @csrf
                        <select name="weeks" class="border-gray-300 rounded">
                            <option value="8">8</option>
                            <option value="12" selected>12</option>
                            <option value="24">24</option>
                        </select>
                        <button class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700" onclick="return confirm('Generate future instances?')">Regenerate Future Instances</button>
                    </form>
                </div>
            </div>
        </form>

        <div class="bg-white shadow-sm rounded p-6">
            <h3 class="font-semibold mb-3">Upcoming Instances (preview next 8 weeks)</h3>
            @php($instances = $event->generateRecurringInstances(8))
            @if(empty($instances))
                <p class="text-gray-500">No upcoming instances generated by current settings.</p>
            @else
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($instances as $i)
                        <li>{{ \Carbon\Carbon::parse($i['start_date'])->format('D, M j, Y g:i A') }} — {{ $i['location'] }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <script>
        function toggleRegistrationFields(type) {
            const link = document.getElementById('registrationLinkWrap');
            const form = document.getElementById('customFormWrap');
            if (type === 'link') {
                link.style.display = '';
                form.style.display = 'none';
            } else if (type === 'form') {
                link.style.display = 'none';
                form.style.display = '';
            } else {
                link.style.display = 'none';
                form.style.display = 'none';
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            toggleRegistrationFields(document.getElementById('registrationType').value);
        });
    </script>
</x-sidebar-layout>


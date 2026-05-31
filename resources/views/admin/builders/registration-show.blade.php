@php
    use App\Enums\BuilderIndustry;
    $registration->load(['user', 'contactedBy']);
    $industryLabel = $registration->industry === 'other' && $registration->industry_other
        ? $registration->industry_other
        : (BuilderIndustry::tryFrom($registration->industry)?->label() ?? $registration->industry);
@endphp
<x-sidebar-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $registration->full_name }}</h2>
            <a href="{{ route('admin.builders.registrations') }}" class="text-sm text-orange-600 hover:underline">Back to list</a>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl" x-data="registrationDetail({{ $registration->id }})" x-init="load()">
        <div class="flex gap-3 mb-6">
            <span class="px-3 py-1 rounded-full text-sm {{ $registration->status->value === 'contacted' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                {{ ucfirst($registration->status->value) }}
            </span>
            <button type="button" @click="markContacted()" x-show="detail?.status === 'new'"
                class="text-sm bg-orange-500 text-white px-3 py-1 rounded-lg hover:bg-orange-600">Mark as contacted</button>
        </div>

        <div class="bg-white rounded-lg shadow p-6 space-y-6 text-sm">
            <section>
                <h3 class="font-semibold text-slate-900 mb-3">Your details</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div><dt class="text-gray-500">Full name</dt><dd class="font-medium">{{ $registration->full_name }}</dd></div>
                    <div><dt class="text-gray-500">Phone</dt><dd>{{ $registration->phone }}</dd></div>
                    <div><dt class="text-gray-500">Email</dt><dd>{{ $registration->email }}</dd></div>
                    <div><dt class="text-gray-500">Registered</dt><dd>{{ $registration->created_at?->format('M j, Y g:i A') }}</dd></div>
                </dl>
            </section>
            <section class="border-t pt-6">
                <h3 class="font-semibold text-slate-900 mb-3">Your business</h3>
                <dl class="space-y-3">
                    <div><dt class="text-gray-500">Business name</dt><dd>{{ $registration->business_name }}</dd></div>
                    <div><dt class="text-gray-500">Description</dt><dd>{{ $registration->business_description }}</dd></div>
                    <div><dt class="text-gray-500">Stage</dt><dd>{{ $registration->business_stage?->label() }}</dd></div>
                    <div><dt class="text-gray-500">Industry</dt><dd>{{ $industryLabel }}</dd></div>
                    <div><dt class="text-gray-500">Biggest challenge</dt><dd>{{ $registration->biggest_challenge?->label() }}</dd></div>
                    <div><dt class="text-gray-500">12-month success vision</dt><dd>{{ $registration->success_vision }}</dd></div>
                </dl>
            </section>
            <section class="border-t pt-6">
                <h3 class="font-semibold text-slate-900 mb-3">CAC registration</h3>
                <p>{{ $registration->cac_status?->label() }}</p>
            </section>
            @if($registration->contacted_at)
                <section class="border-t pt-6 text-gray-600">
                    Contacted {{ $registration->contacted_at->format('M j, Y') }}
                    @if($registration->contactedBy) by {{ $registration->contactedBy->name }} @endif
                </section>
            @endif
        </div>
    </div>
    <script>
    function apiHeaders(){const t=document.querySelector('meta[name="api-token"]')?.content;return{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,...(t?{'Authorization':'Bearer '+t}:{})}}
    function registrationDetail(id){return{detail:null,async load(){const r=await fetch('/api/admin/builders/registrations/'+id,{headers:apiHeaders()});const j=await r.json();this.detail=j.data;},async markContacted(){await fetch('/api/admin/builders/registrations/'+id+'/contacted',{method:'POST',headers:apiHeaders()});location.reload();}}}
    </script>
</x-sidebar-layout>

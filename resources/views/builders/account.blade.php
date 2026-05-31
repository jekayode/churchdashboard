@extends('builders.layout')

@section('title', 'My Business Starter Pack')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h1 class="text-2xl font-bold text-slate-900">Welcome, {{ $registration->full_name }}</h1>
        <p class="mt-1 text-sm text-gray-600">Your Builders Community registration for <strong>{{ $registration->business_name }}</strong></p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Download your Business Starter Pack</h2>
        @if($resources->isEmpty() && ! $settings->google_drive_link)
            <p class="text-sm text-gray-600">Pack files will appear here once uploaded by the admin team.</p>
        @else
            <ul class="space-y-3">
                @foreach($resources as $resource)
                    <li class="flex items-center justify-between gap-4 rounded-lg border border-gray-200 p-4">
                        <span class="font-medium text-slate-800">{{ $resource->title }}</span>
                        <a href="{{ route('builders.pack.download', $resource) }}"
                            class="builders-primary-btn shrink-0 rounded-lg px-4 py-2 text-sm font-semibold">
                            Download
                        </a>
                    </li>
                @endforeach
            </ul>
            @if($settings->google_drive_link)
                <a href="{{ $settings->google_drive_link }}" target="_blank" rel="noopener"
                    class="mt-4 inline-block text-orange-600 font-medium hover:underline text-sm">
                    Alternative: Google Drive folder
                </a>
            @endif
        @endif
    </div>

    @if($settings->whatsapp_group_link)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-2">Join the community</h2>
            <p class="text-sm text-gray-600 mb-4">Connect with other builders in the Lifepointe GLK Builders WhatsApp group.</p>
            <a href="{{ $settings->whatsapp_group_link }}" target="_blank" rel="noopener"
                class="inline-flex items-center gap-2 rounded-lg bg-green-600 text-white px-5 py-2.5 text-sm font-semibold hover:bg-green-700">
                Join WhatsApp group
            </a>
        </div>
    @endif

    <div class="bg-gray-50 rounded-xl border border-gray-200 p-6 text-sm text-gray-600">
        <h3 class="font-semibold text-slate-800 mb-2">Your registration summary</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <div><dt class="text-gray-500">Email</dt><dd>{{ $registration->email }}</dd></div>
            <div><dt class="text-gray-500">Phone</dt><dd>{{ $registration->phone }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-gray-500">Business</dt><dd>{{ $registration->business_description }}</dd></div>
        </dl>
    </div>
</div>
@endsection

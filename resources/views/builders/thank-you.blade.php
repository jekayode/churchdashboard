@extends('builders.layout')

@section('title', 'Your Business Starter Pack')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
    <h1 class="text-2xl font-bold text-slate-900">Your Business Starter Pack is ready. Download it now.</h1>

    @if($settings->confirmation_body)
        <p class="mt-4 text-gray-700">{{ $settings->confirmation_body }}</p>
    @else
        <p class="mt-4 text-gray-700">Thank you for taking this step.</p>
    @endif

    @if($isNewUser)
        <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <p class="font-semibold">Check your email</p>
            <p class="mt-1">We sent you a link to verify your account and set a password. Once activated, you can download your pack and join the community.</p>
        </div>
    @else
        <p class="mt-4 text-gray-700">We also sent a link to your email. You can download your pack below:</p>
        <a href="{{ route('builders.account') }}" class="builders-primary-btn mt-4 inline-flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold">
            Go to my pack
        </a>
    @endif

    @if($resources->isNotEmpty() && ! $isNewUser && auth()->check() && auth()->user()->hasVerifiedEmail())
        <div class="mt-8">
            <h2 class="font-semibold text-slate-900 mb-3">Your pack includes:</h2>
            <ul class="space-y-2">
                @foreach($resources as $resource)
                    <li>
                        <a href="{{ route('builders.pack.download', $resource) }}" class="text-orange-600 hover:underline font-medium">{{ $resource->title }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @elseif($resources->isNotEmpty())
        <div class="mt-8">
            <h2 class="font-semibold text-slate-900 mb-3">Your pack includes:</h2>
            <ul class="list-disc list-inside text-gray-700 space-y-1">
                @foreach($resources as $resource)
                    <li>{{ $resource->title }}</li>
                @endforeach
            </ul>
        </div>
    @else
        <ul class="mt-6 list-disc list-inside text-gray-700 space-y-1">
            <li>Business Plan Template</li>
            <li>Business Pipeline Tracker</li>
            <li>GTM Strategy Template</li>
        </ul>
    @endif

    @if($settings->google_drive_link)
        <div class="mt-6">
            <a href="{{ $settings->google_drive_link }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-orange-600 font-semibold hover:underline">
                Download pack (Google Drive)
            </a>
        </div>
    @endif

    @if($settings->whatsapp_group_link)
        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-gray-700 mb-3">Join the <strong>Lifepointe GLK Builders</strong> community on WhatsApp:</p>
            <a href="{{ $settings->whatsapp_group_link }}" target="_blank" rel="noopener"
                class="inline-flex items-center gap-2 rounded-lg bg-green-600 text-white px-5 py-2.5 text-sm font-semibold hover:bg-green-700">
                Join WhatsApp group
            </a>
        </div>
    @endif

    <p class="mt-8 text-sm text-gray-600">Someone from the Lifepointe Business &amp; Career Unit will reach out to you personally within 48 hours.</p>
    <p class="mt-2 text-sm font-medium text-slate-800">Welcome to the community!</p>
</div>
@endsection

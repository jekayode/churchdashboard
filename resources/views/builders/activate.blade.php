@extends('builders.layout')

@section('title', 'Activate your account')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 max-w-md mx-auto">
    <h1 class="text-xl font-bold text-slate-900">Set your password</h1>
    <p class="mt-2 text-sm text-gray-600">Create a password to activate your account and access your Business Starter Pack.</p>

    <form method="POST" action="{{ request()->fullUrl() }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" name="password" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
            @error('password')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Confirm password</label>
            <input type="password" name="password_confirmation" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <button type="submit" class="builders-primary-btn w-full rounded-lg py-2.5 text-sm font-semibold">
            Activate &amp; continue
        </button>
    </form>
</div>
@endsection

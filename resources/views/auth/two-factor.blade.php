<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Two-Factor Authentication') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-church rounded-xl">
                <div class="p-6 text-gray-900">
                    @if (session('status'))
                        <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 border border-green-200 rounded-lg p-4">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (!$user->two_factor_enabled)
                        <!-- Setup Two-Factor Authentication -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                <span class="flex items-center">
                                    <svg class="w-5 h-5 text-church-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Enable Two-Factor Authentication
                                </span>
                            </h3>
                            <p class="text-gray-600 mb-6">
                                Add an extra layer of security to your church dashboard account by enabling two-factor authentication.
                                You'll need to scan the QR code with an authenticator app like Google Authenticator or Authy.
                            </p>

                            @if ($qrCodeUrl)
                                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                                    <div class="text-center">
                                        <h4 class="font-semibold text-gray-900 mb-4">Scan this QR Code</h4>
                                        <div class="inline-block p-4 bg-white rounded-lg shadow-sm">
                                            <img src="{{ $qrCodeUrl }}" alt="Two-Factor Authentication QR Code" class="mx-auto">
                                        </div>
                                        <p class="text-sm text-gray-600 mt-4">
                                            Scan this QR code with your authenticator app, then enter the 6-digit code below to enable two-factor authentication.
                                        </p>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('two-factor.enable') }}" class="space-y-4">
                                    @csrf
                                    <div>
                                        <x-input-label for="code" :value="__('Authentication Code')" />
                                        <x-text-input 
                                            id="code" 
                                            name="code" 
                                            type="text" 
                                            class="mt-1 block w-full" 
                                            placeholder="Enter 6-digit code"
                                            maxlength="6"
                                            required 
                                            autofocus 
                                        />
                                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <x-primary-button>
                                            {{ __('Enable Two-Factor Authentication') }}
                                        </x-primary-button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    @else
                        <!-- Two-Factor Authentication Enabled -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-green-700 mb-4">
                                <span class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Two-Factor Authentication Enabled
                                </span>
                            </h3>
                            <p class="text-gray-600 mb-6">
                                Your account is protected with two-factor authentication. 
                                Enabled on {{ $user->two_factor_confirmed_at->format('F j, Y \a\t g:i A') }}.
                            </p>

                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-green-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <h4 class="font-semibold text-green-800">Account Secured</h4>
                                        <p class="text-green-700 text-sm">Your church dashboard account is now protected with two-factor authentication.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <a href="{{ route('two-factor.recovery-codes') }}" 
                                       class="inline-flex items-center px-4 py-2 bg-church-100 border border-church-300 rounded-lg font-semibold text-xs text-church-700 uppercase tracking-widest hover:bg-church-200 focus:outline-none focus:ring-2 focus:ring-church-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        View Recovery Codes
                                    </a>
                                </div>

                                <form method="POST" action="{{ route('two-factor.disable') }}" class="inline">
                                    @csrf
                                    <div class="space-y-4">
                                        <div>
                                            <x-input-label for="password" :value="__('Confirm Password to Disable')" />
                                            <x-text-input 
                                                id="password" 
                                                name="password" 
                                                type="password" 
                                                class="mt-1 block w-full" 
                                                placeholder="Enter your password"
                                                required 
                                            />
                                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                        </div>

                                        <button type="submit" 
                                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                                onclick="return confirm('Are you sure you want to disable two-factor authentication? This will make your account less secure.')">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                                            </svg>
                                            Disable Two-Factor Authentication
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    <!-- Information Section -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="font-semibold text-gray-900 mb-3">About Two-Factor Authentication</h4>
                        <div class="text-sm text-gray-600 space-y-2">
                            <p>• Two-factor authentication adds an extra layer of security to your church dashboard account.</p>
                            <p>• You'll need an authenticator app like Google Authenticator, Authy, or Microsoft Authenticator.</p>
                            <p>• Keep your recovery codes in a safe place - you'll need them if you lose access to your authenticator app.</p>
                            <p>• Contact your church administrator if you need help with two-factor authentication.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 
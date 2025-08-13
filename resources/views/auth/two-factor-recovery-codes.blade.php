<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Two-Factor Recovery Codes') }}
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

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 text-church-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                </svg>
                                Recovery Codes
                            </span>
                        </h3>
                        <p class="text-gray-600 mb-6">
                            Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two-factor authentication device is lost.
                        </p>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <div class="flex">
                                <svg class="w-5 h-5 text-yellow-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <h4 class="font-semibold text-yellow-800">Important Security Notice</h4>
                                    <p class="text-yellow-700 text-sm">Each recovery code can only be used once. Store them safely and treat them like passwords.</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-6 mb-6">
                            <div class="grid grid-cols-2 gap-4">
                                @foreach ($recoveryCodes as $code)
                                    <div class="bg-white border border-gray-200 rounded-lg p-3 font-mono text-center text-lg font-semibold text-gray-800">
                                        {{ $code }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center gap-4 mb-6">
                            <button onclick="printRecoveryCodes()" 
                                    class="inline-flex items-center px-4 py-2 bg-church-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-church-600 focus:outline-none focus:ring-2 focus:ring-church-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zM5 9a1 1 0 000 2h1V9H5zm9 0v2h1a1 1 0 100-2h-1z" clip-rule="evenodd"/>
                                </svg>
                                Print Codes
                            </button>

                            <button onclick="copyRecoveryCodes()" 
                                    class="inline-flex items-center px-4 py-2 bg-church-100 border border-church-300 rounded-lg font-semibold text-xs text-church-700 uppercase tracking-widest hover:bg-church-200 focus:outline-none focus:ring-2 focus:ring-church-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"/>
                                    <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z"/>
                                </svg>
                                Copy to Clipboard
                            </button>
                        </div>

                        <form method="POST" action="{{ route('two-factor.recovery-codes.regenerate') }}" class="space-y-4">
                            @csrf
                            <div>
                                <x-input-label for="password" :value="__('Confirm Password to Regenerate Codes')" />
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
                                    class="inline-flex items-center px-4 py-2 bg-secondary-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-secondary-600 focus:outline-none focus:ring-2 focus:ring-secondary-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    onclick="return confirm('Are you sure you want to regenerate recovery codes? This will invalidate all existing codes.')">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                                </svg>
                                Regenerate Recovery Codes
                            </button>
                        </form>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-2">Need Help?</h4>
                                <p class="text-sm text-gray-600">Contact your church administrator if you need assistance with recovery codes.</p>
                            </div>
                            <a href="{{ route('two-factor.show') }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                </svg>
                                Back to 2FA Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function printRecoveryCodes() {
            const codes = @json($recoveryCodes);
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Two-Factor Recovery Codes - {{ config('app.name') }}</title>
                        <style>
                            body { font-family: Arial, sans-serif; padding: 20px; }
                            .header { text-align: center; margin-bottom: 30px; }
                            .codes { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin: 20px 0; }
                            .code { border: 1px solid #ccc; padding: 10px; text-align: center; font-family: monospace; font-size: 16px; }
                            .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1>{{ config('app.name') }}</h1>
                            <h2>Two-Factor Authentication Recovery Codes</h2>
                            <p>Generated on: ${new Date().toLocaleDateString()}</p>
                        </div>
                        <div class="warning">
                            <strong>Important:</strong> Store these codes in a secure location. Each code can only be used once.
                        </div>
                        <div class="codes">
                            ${codes.map(code => `<div class="code">${code}</div>`).join('')}
                        </div>
                        <p><strong>Account:</strong> {{ auth()->user()->email }}</p>
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }

        function copyRecoveryCodes() {
            const codes = @json($recoveryCodes);
            const text = codes.join('\n');
            navigator.clipboard.writeText(text).then(() => {
                alert('Recovery codes copied to clipboard!');
            }).catch(() => {
                alert('Failed to copy recovery codes. Please copy them manually.');
            });
        }
    </script>
</x-app-layout> 
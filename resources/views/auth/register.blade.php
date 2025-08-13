<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" x-data="{ selectedRole: '{{ old('role_id', '') }}' }">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Full Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Branch Selection -->
        <div class="mt-4">
            <x-input-label for="branch_id" :value="__('Church Campus')" />
            <select id="branch_id" name="branch_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                <option value="">{{ __('Select Your Campus') }}</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('branch_id')" class="mt-2" />
        </div>

        <!-- Role Selection -->
        <div class="mt-4">
            <x-input-label for="role_id" :value="__('I am joining as')" />
            <select id="role_id" name="role_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required x-model="selectedRole">
                <option value="">{{ __('Select Your Role') }}</option>
                @foreach($roles as $role)
                    @if($role->name !== 'super_admin') {{-- Don't allow super admin registration --}}
                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                            {{ $role->display_name }}
                        </option>
                    @endif
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
            
            <!-- Role Description -->
            <div class="mt-2 text-sm text-gray-600">
                <div x-show="selectedRole == '{{ $roles->where('name', 'branch_pastor')->first()?->id }}'">
                    {{ __('Lead and oversee all activities within your campus branch.') }}
                </div>
                <div x-show="selectedRole == '{{ $roles->where('name', 'ministry_leader')->first()?->id }}'">
                    {{ __('Lead a specific ministry (Worship, Youth, Outreach, etc.) within your campus.') }}
                </div>
                <div x-show="selectedRole == '{{ $roles->where('name', 'department_leader')->first()?->id }}'">
                    {{ __('Lead a department within a ministry (Music, Ushering, Media, etc.).') }}
                </div>
                <div x-show="selectedRole == '{{ $roles->where('name', 'church_member')->first()?->id }}'">
                    {{ __('Participate in church activities, events, and small groups.') }}
                </div>
                <div x-show="selectedRole == '{{ $roles->where('name', 'public_user')->first()?->id }}'">
                    {{ __('Access public information and events. Perfect for visitors and newcomers.') }}
                </div>
            </div>
        </div>

        <!-- Phone Number -->
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Phone Number (Optional)')" />
            <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Terms and Conditions -->
        <div class="mt-4">
            <label class="flex items-center">
                <input type="checkbox" name="terms" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" required {{ old('terms') ? 'checked' : '' }}>
                <span class="ml-2 text-sm text-gray-600">
                    {{ __('I agree to the church') }} 
                    <a href="#" class="underline text-indigo-600 hover:text-indigo-900">{{ __('terms and conditions') }}</a>
                    {{ __('and') }}
                    <a href="#" class="underline text-indigo-600 hover:text-indigo-900">{{ __('privacy policy') }}</a>
                </span>
            </label>
            <x-input-error :messages="$errors->get('terms')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Join Our Church') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>


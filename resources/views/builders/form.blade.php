@extends('builders.layout')

@section('title', 'Get Your Free Business Starter Pack')

@section('content')
<div x-data="builderWizard()" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="builders-hero px-6 py-8">
        <h1 class="text-2xl font-bold text-white">Get Your Free Business Starter Pack</h1>
        <p class="mt-2 text-sm opacity-90">Lifepointe Business &amp; Career Unit</p>
        @if($settings->intro_text)
            <p class="mt-3 text-sm">{{ $settings->intro_text }}</p>
        @else
            <p class="mt-3 text-sm">Fill this short form and your free Business Starter Pack downloads immediately. Takes less than 2 minutes.</p>
        @endif
    </div>

  <div class="px-6 pt-6">
        <div class="flex gap-2 mb-6">
            <template x-for="(label, i) in ['Your details', 'Your business', 'Registration']" :key="i">
                <div class="flex-1">
                    <div class="h-1 rounded-full"
                         :class="step > i ? 'builders-step-active' : (step === i + 1 ? 'builders-step-current' : 'bg-gray-200')"></div>
                    <p class="text-xs mt-1 text-gray-500 hidden sm:block" x-text="label"></p>
                </div>
            </template>
        </div>
    </div>

    <form method="POST" action="{{ route('builders.store') }}" class="px-6 pb-8 space-y-6" @submit="if(!validateStep(3)) $event.preventDefault()">
        @csrf

        <div x-show="step === 1" x-cloak class="space-y-4">
            <h2 class="text-lg font-semibold text-slate-900">Your details</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="full_name" x-model="form.full_name" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                @error('full_name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Phone Number (WhatsApp) <span class="text-red-500">*</span></label>
                <input type="tel" name="phone" x-model="form.phone" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                <p class="text-xs text-gray-500 mt-1">We will reach out personally within 48 hours and add you to our Business &amp; Career community.</p>
                @error('phone')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
                <input type="email" name="email" x-model="form.email" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                <p class="text-xs text-gray-500 mt-1">Your download link will also be sent here.</p>
                @error('email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div x-show="step === 2" x-cloak class="space-y-4">
            <h2 class="text-lg font-semibold text-slate-900">Tell us about what you are building</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700">Business name <span class="text-red-500">*</span></label>
                <input type="text" name="business_name" x-model="form.business_name" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                <p class="text-xs text-gray-500 mt-1">If you have not decided yet, write "Not decided yet."</p>
                @error('business_name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">What does your business do and who is it for? <span class="text-red-500">*</span></label>
                <textarea name="business_description" x-model="form.business_description" rows="3" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></textarea>
                @error('business_description')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">What stage is your business at? <span class="text-red-500">*</span></label>
                <div class="space-y-2">
                    @foreach($stages as $stage)
                        <label class="flex items-start gap-2 text-sm">
                            <input type="radio" name="business_stage" value="{{ $stage->value }}" x-model="form.business_stage" class="mt-1 border-gray-300 text-orange-600">
                            <span>{{ $stage->label() }}</span>
                        </label>
                    @endforeach
                </div>
                @error('business_stage')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Industry <span class="text-red-500">*</span></label>
                <div class="space-y-2">
                    @foreach($industries as $industry)
                        <label class="flex items-start gap-2 text-sm">
                            <input type="radio" name="industry" value="{{ $industry->value }}" x-model="form.industry" class="mt-1 border-gray-300 text-orange-600">
                            <span>{{ $industry->label() }}</span>
                        </label>
                    @endforeach
                </div>
                <div x-show="form.industry === 'other'" class="mt-2">
                    <input type="text" name="industry_other" x-model="form.industry_other" placeholder="Please specify" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                </div>
                @error('industry')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                @error('industry_other')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Biggest challenge right now <span class="text-red-500">*</span></label>
                <div class="space-y-2">
                    @foreach($challenges as $challenge)
                        <label class="flex items-start gap-2 text-sm">
                            <input type="radio" name="biggest_challenge" value="{{ $challenge->value }}" x-model="form.biggest_challenge" class="mt-1 border-gray-300 text-orange-600">
                            <span>{{ $challenge->label() }}</span>
                        </label>
                    @endforeach
                </div>
                @error('biggest_challenge')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Success in the next 12 months <span class="text-red-500">*</span></label>
                <textarea name="success_vision" x-model="form.success_vision" rows="3" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></textarea>
                @error('success_vision')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div x-show="step === 3" x-cloak class="space-y-4">
            <h2 class="text-lg font-semibold text-slate-900">Business registration</h2>
            <p class="text-sm text-gray-600">We are exploring how to support members with formal business registration. Your answer helps us plan.</p>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Is your business registered with CAC? <span class="text-red-500">*</span></label>
                <div class="space-y-2">
                    @foreach($cacStatuses as $cac)
                        <label class="flex items-start gap-2 text-sm">
                            <input type="radio" name="cac_status" value="{{ $cac->value }}" x-model="form.cac_status" class="mt-1 border-gray-300 text-orange-600">
                            <span>{{ $cac->label() }}</span>
                        </label>
                    @endforeach
                </div>
                @error('cac_status')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex justify-between items-center pt-6 mt-2 border-t border-gray-200">
            <button type="button" x-show="step > 1" x-cloak @click="step--"
                    class="builders-secondary-btn px-4 py-2 text-sm font-medium rounded-lg">Back</button>
            <span x-show="step === 1" class="w-0" aria-hidden="true"></span>
            <div class="ml-auto flex gap-2">
                <button type="button" x-show="step < 3" @click="nextStep()"
                        class="builders-primary-btn px-6 py-2.5 text-sm font-semibold rounded-lg shadow-sm">
                    Continue
                </button>
                <button type="submit" x-show="step === 3"
                        class="builders-primary-btn px-6 py-2.5 text-sm font-semibold rounded-lg shadow-sm">
                    Submit &amp; get my pack
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function builderWizard() {
    return {
        step: 1,
        form: {
            full_name: @json(old('full_name', '')),
            phone: @json(old('phone', '')),
            email: @json(old('email', '')),
            business_name: @json(old('business_name', '')),
            business_description: @json(old('business_description', '')),
            business_stage: @json(old('business_stage', '')),
            industry: @json(old('industry', '')),
            industry_other: @json(old('industry_other', '')),
            biggest_challenge: @json(old('biggest_challenge', '')),
            success_vision: @json(old('success_vision', '')),
            cac_status: @json(old('cac_status', '')),
        },
        nextStep() {
            if (this.validateStep(this.step)) this.step++;
        },
        validateStep(s) {
            if (s === 1) return this.form.full_name && this.form.phone && this.form.email;
            if (s === 2) {
                const base = this.form.business_name && this.form.business_description && this.form.business_stage
                    && this.form.industry && this.form.biggest_challenge && this.form.success_vision;
                if (this.form.industry === 'other') return base && this.form.industry_other;
                return base;
            }
            if (s === 3) return !!this.form.cac_status;
            return true;
        }
    };
}
</script>
<style>[x-cloak]{display:none!important}</style>
@endsection

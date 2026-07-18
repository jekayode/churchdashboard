<x-sidebar-layout :title="'Edit '.$plan->name">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Plan settings') }}</h2>
            <a href="{{ route('pastor.reading-plans') }}" class="text-sm font-medium text-church-600 hover:text-church-700">
                {{ __('All plans') }}
            </a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('pastor.reading-plans.update', $plan) }}">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input id="name" name="name" type="text" required value="{{ old('name', $plan->name) }}"
                           class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">{{ old('description', $plan->description) }}</textarea>
                </div>

                <div>
                    <label for="attribution" class="block text-sm font-medium text-gray-700 mb-1">Attribution</label>
                    <input id="attribution" name="attribution" type="text"
                           value="{{ old('attribution', $plan->attribution) }}"
                           class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                    <p class="mt-1 text-xs text-gray-500">
                        Credit line shown with the plan. Clear this once the content is entirely your own.
                    </p>
                </div>

                <div>
                    <label for="tone" class="block text-sm font-medium text-gray-700 mb-1">Accent colour</label>
                    <select id="tone" name="tone" class="w-full rounded-lg border-gray-300 focus:border-church-500 focus:ring-church-500">
                        @foreach (['orange' => 'Burnt orange', 'purple' => 'Purple', 'amber' => 'Amber', 'lemon' => 'Lemon'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('tone', $plan->tone) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <label class="flex items-start gap-3">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $plan->is_published))
                           class="mt-1 rounded border-gray-300 text-church-600 focus:ring-church-500">
                    <span>
                        <span class="block text-sm font-medium text-gray-900">Published</span>
                        <span class="block text-xs text-gray-500">Members can follow this plan.</span>
                    </span>
                </label>

                <label class="flex items-start gap-3">
                    <input type="hidden" name="is_default" value="0">
                    <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $plan->is_default))
                           class="mt-1 rounded border-gray-300 text-church-600 focus:ring-church-500">
                    <span>
                        <span class="block text-sm font-medium text-gray-900">Default plan</span>
                        <span class="block text-xs text-gray-500">Shown to members who haven't chosen one. Only one plan can be default.</span>
                    </span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 mt-6">
                <a href="{{ route('pastor.reading-plans') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-5 py-2 rounded-lg bg-church-500 text-white font-semibold hover:bg-church-600">Save changes</button>
            </div>
        </form>
    </div>
</x-sidebar-layout>

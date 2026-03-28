<x-sidebar-layout :title="$event !== null ? __('Edit event') : __('Create event')">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $event !== null ? __('Edit event') : __('Create event') }}
            </h2>
            <a href="{{ route('pastor.events') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                {{ __('Back to events') }}
            </a>
        </div>
    </x-slot>

    <style>
        .form-field-item {
            transition: all 0.2s ease;
        }
        .form-field-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .drag-handle {
            cursor: grab;
        }
        .drag-handle:active {
            cursor: grabbing;
        }
        .form-builder-empty {
            background: linear-gradient(45deg, #f8f9fa 25%, transparent 25%),
                linear-gradient(-45deg, #f8f9fa 25%, transparent 25%),
                linear-gradient(45deg, transparent 75%, #f8f9fa 75%),
                linear-gradient(-45deg, transparent 75%, #f8f9fa 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
        }
        .field-type-icon {
            width: 16px;
            height: 16px;
        }
        .add-option-btn {
            transition: all 0.15s ease;
        }
        .add-option-btn:hover {
            transform: scale(1.05);
        }
    </style>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-8">
                    @include('pastor.events.partials.event-form-body', ['event' => $event])
                </div>
            </div>
        </div>
    </div>

    @include('pastor.events.partials.event-form-page-script')
</x-sidebar-layout>

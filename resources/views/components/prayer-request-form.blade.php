@props([
    'action' => '#',
    'method' => 'POST'
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-worship border border-worship-100 overflow-hidden']) }}>
    <!-- Header -->
    <div class="bg-gradient-to-r from-worship-500 to-worship-600 px-6 py-4">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-white">Submit Prayer Request</h3>
                <p class="text-worship-100 text-sm">Share your prayer needs with our community</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ $action }}" method="{{ $method }}" class="p-6 space-y-6" x-data="{ 
        anonymous: false, 
        urgent: false,
        category: 'general',
        characterCount: 0,
        maxLength: 500
    }">
        @csrf

        <!-- Prayer Category -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Prayer Category</label>
            <select x-model="category" name="category" class="w-full rounded-lg border-gray-300 focus:border-worship-500 focus:ring-worship-500">
                <option value="general">General Prayer</option>
                <option value="healing">Healing & Health</option>
                <option value="family">Family & Relationships</option>
                <option value="guidance">Guidance & Wisdom</option>
                <option value="thanksgiving">Thanksgiving & Praise</option>
                <option value="ministry">Ministry & Service</option>
                <option value="urgent">Urgent Request</option>
            </select>
        </div>

        <!-- Prayer Request Text -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Prayer Request</label>
            <textarea 
                name="request" 
                rows="4" 
                x-model="$el.value"
                @input="characterCount = $el.value.length"
                :maxlength="maxLength"
                class="w-full rounded-lg border-gray-300 focus:border-worship-500 focus:ring-worship-500 resize-none"
                placeholder="Please share your prayer request here. Be as specific or general as you feel comfortable..."
                required></textarea>
            <div class="flex justify-between items-center mt-2">
                <p class="text-xs text-gray-500">Your request will be handled with care and confidentiality</p>
                <span class="text-xs text-gray-400" x-text="`${characterCount}/${maxLength}`"></span>
            </div>
        </div>

        <!-- Options -->
        <div class="space-y-4">
            <!-- Anonymous Option -->
            <div class="flex items-center">
                <input 
                    type="checkbox" 
                    id="anonymous" 
                    name="anonymous" 
                    x-model="anonymous"
                    class="rounded border-gray-300 text-worship-600 focus:ring-worship-500">
                <label for="anonymous" class="ml-2 text-sm text-gray-700">
                    Submit anonymously
                    <span class="text-gray-500">(your name will not be shared)</span>
                </label>
            </div>

            <!-- Urgent Option -->
            <div class="flex items-center">
                <input 
                    type="checkbox" 
                    id="urgent" 
                    name="urgent" 
                    x-model="urgent"
                    class="rounded border-gray-300 text-ministry-600 focus:ring-ministry-500">
                <label for="urgent" class="ml-2 text-sm text-gray-700">
                    Mark as urgent
                    <span class="text-gray-500">(will be prioritized for immediate prayer)</span>
                </label>
            </div>

            <!-- Public Sharing Option -->
            <div class="flex items-center">
                <input 
                    type="checkbox" 
                    id="public_sharing" 
                    name="public_sharing"
                    class="rounded border-gray-300 text-community-600 focus:ring-community-500">
                <label for="public_sharing" class="ml-2 text-sm text-gray-700">
                    Allow sharing with prayer team
                    <span class="text-gray-500">(request may be shared with designated prayer warriors)</span>
                </label>
            </div>
        </div>

        <!-- Contact Information (if not anonymous) -->
        <div x-show="!anonymous" x-transition class="space-y-4">
            <div class="border-t border-gray-200 pt-4">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Contact Information (Optional)</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Phone Number</label>
                        <input 
                            type="tel" 
                            name="phone" 
                            class="w-full rounded-lg border-gray-300 focus:border-worship-500 focus:ring-worship-500"
                            placeholder="(555) 123-4567">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Preferred Contact Method</label>
                        <select name="contact_method" class="w-full rounded-lg border-gray-300 focus:border-worship-500 focus:ring-worship-500">
                            <option value="email">Email</option>
                            <option value="phone">Phone</option>
                            <option value="text">Text Message</option>
                            <option value="none">No Contact Needed</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
            <div class="flex items-center text-sm text-gray-500">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                </svg>
                <span>All requests are confidential</span>
            </div>
            
            <div class="flex items-center space-x-3">
                <button 
                    type="button" 
                    class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
                <button 
                    type="submit" 
                    class="px-6 py-2 bg-gradient-worship text-white rounded-lg hover:opacity-90 transition-opacity font-medium">
                    Submit Prayer Request
                </button>
            </div>
        </div>
    </form>
</div> 
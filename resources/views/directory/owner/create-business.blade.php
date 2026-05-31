<x-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Business</h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto" x-data="businessCreateWizard()" x-init="init()">
        <div x-show="showToast" x-transition class="mb-4 rounded-md p-3"
            :class="toastType === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
            <span x-text="toastMessage"></span>
        </div>
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between gap-4">
                    <div class="text-sm text-gray-600">
                        Step <span class="font-medium text-gray-900" x-text="step"></span> of 7
                    </div>
                    <div class="text-sm text-gray-600">
                        <template x-if="businessSlug">
                            <span class="inline-flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                Draft created
                            </span>
                        </template>
                        <template x-if="!businessSlug">
                            <span class="inline-flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-gray-300"></span>
                                Draft not created yet
                            </span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Step Navigation -->
            <div class="border-b border-gray-200 px-6 py-3 bg-gray-50">
                <nav class="flex flex-wrap gap-2" aria-label="Create business steps">
                    <button type="button" class="px-3 py-1 rounded-lg text-sm"
                        :class="step===1 ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200'"
                        @click="go(1)">
                        Basics
                    </button>
                    <button type="button" class="px-3 py-1 rounded-lg text-sm"
                        :class="step===2 ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200'"
                        @click="go(2)" :disabled="!businessSlug">
                        Contact & Location
                    </button>
                    <button type="button" class="px-3 py-1 rounded-lg text-sm"
                        :class="step===3 ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200'"
                        @click="go(3)" :disabled="!businessSlug">
                        Team
                    </button>
                    <button type="button" class="px-3 py-1 rounded-lg text-sm"
                        :class="step===4 ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200'"
                        @click="go(4)" :disabled="!businessSlug">
                        Services & Products
                    </button>
                    <button type="button" class="px-3 py-1 rounded-lg text-sm"
                        :class="step===5 ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200'"
                        @click="go(5)" :disabled="!businessSlug">
                        Images
                    </button>
                    <button type="button" class="px-3 py-1 rounded-lg text-sm"
                        :class="step===6 ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200'"
                        @click="go(6)" :disabled="!businessSlug">
                        Social links
                    </button>
                    <button type="button" class="px-3 py-1 rounded-lg text-sm"
                        :class="step===7 ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200'"
                        @click="go(7)" :disabled="!businessSlug">
                        Review & Submit
                    </button>
                </nav>
            </div>

            <div class="p-6">
                <!-- Step 1 -->
                <section x-show="step===1" x-transition>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Business name</label>
                            <input x-model="form.name" class="w-full rounded border-gray-300" type="text" placeholder="e.g. Bella Glow Beauty Salon" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Tagline</label>
                            <input x-model="form.tagline" class="w-full rounded border-gray-300" type="text" placeholder="Short one-liner" />
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-medium text-gray-700">Description</label>
                            <textarea x-model="form.description" class="w-full rounded border-gray-300" rows="4" placeholder="Tell people what you do and why they should choose you"></textarea>
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-gray-700">Categories</label>
                                <span class="text-xs text-gray-500" x-text="selectedCategories.length + ' selected'"></span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <template x-for="c in categories" :key="c.id">
                                    <label class="flex items-start gap-3 p-3 rounded border border-gray-200 cursor-pointer bg-white">
                                        <input type="checkbox" class="mt-1"
                                            :value="c.id"
                                            x-model="form.category_ids" />
                                        <div>
                                            <div class="text-sm font-medium text-gray-900" x-text="c.name"></div>
                                            <div class="text-xs text-gray-500" x-text="c.slug"></div>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button type="button" class="px-4 py-2 rounded-lg bg-indigo-600 text-white" @click="saveStep1()">
                            Create Draft
                        </button>
                    </div>
                </section>

                <!-- Step 2 -->
                <section x-show="step===2" x-transition>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">WhatsApp</label>
                            <input x-model="form.whatsapp_number" class="w-full rounded border-gray-300" type="text" placeholder="WhatsApp number" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Phone</label>
                            <input x-model="form.phone" class="w-full rounded border-gray-300" type="text" placeholder="Phone number" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Email</label>
                            <input x-model="form.email" class="w-full rounded border-gray-300" type="email" placeholder="Email address" />
                        </div>
                        <div class="space-y-2 md:col-span-1">
                            <label class="text-sm font-medium text-gray-700">Website</label>
                            <input x-model="form.website" class="w-full rounded border-gray-300" type="url" placeholder="https://example.com" />
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <label class="text-sm font-medium text-gray-700">Address</label>
                            <input x-model="form.address" class="w-full rounded border-gray-300" type="text" placeholder="Street address, area, etc." />
                            <div class="text-xs text-gray-500">Map pin uses Latitude/Longitude (optional).</div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Latitude</label>
                            <input x-model="form.latitude" class="w-full rounded border-gray-300" type="text" placeholder="e.g. -6.5244" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Longitude</label>
                            <input x-model="form.longitude" class="w-full rounded border-gray-300" type="text" placeholder="e.g. 3.3792" />
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-gray-700">Opening & closing times</label>
                                <span class="text-xs text-gray-500">Saved into `working_hours`</span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="text-left text-xs text-gray-500">
                                        <tr>
                                            <th class="py-2 pr-3">Day</th>
                                            <th class="py-2 pr-3">Open</th>
                                            <th class="py-2 pr-3">Close</th>
                                            <th class="py-2">Closed</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="day in days" :key="day.key">
                                            <tr class="border-t border-gray-100">
                                                <td class="py-2 pr-3" x-text="day.label"></td>
                                                <td class="py-2 pr-3">
                                                    <input type="time" class="rounded border-gray-300 w-full"
                                                        x-model="form.working_hours[day.key].open" />
                                                </td>
                                                <td class="py-2 pr-3">
                                                    <input type="time" class="rounded border-gray-300 w-full"
                                                        x-model="form.working_hours[day.key].close" />
                                                </td>
                                                <td class="py-2">
                                                    <input type="checkbox"
                                                        x-model="form.working_hours[day.key].closed" />
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between gap-3">
                        <button type="button" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-800" @click="step=1">Back</button>
                        <button type="button" class="px-4 py-2 rounded-lg bg-indigo-600 text-white" @click="saveStep2()">Next: Team</button>
                    </div>
                </section>

                <!-- Step 3 -->
                <section x-show="step===3" x-transition>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Team members</h3>
                            <button type="button" class="text-sm text-indigo-700" @click="addTeamMember()">+ Add row</button>
                        </div>

                        <template x-for="(member, idx) in form.team_members" :key="member.localKey">
                            <div class="p-4 rounded-lg border border-gray-200 bg-white space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700">Name</label>
                                        <input x-model="member.name" type="text" class="w-full rounded border-gray-300" />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700">Role</label>
                                        <input x-model="member.role" type="text" class="w-full rounded border-gray-300" placeholder="e.g. Senior Beautician" />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700">Sort order</label>
                                        <input x-model.number="member.sort_order" type="number" class="w-full rounded border-gray-300" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">Bio</label>
                                    <textarea x-model="member.bio" rows="2" class="w-full rounded border-gray-300"></textarea>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">Photo (optional)</label>
                                    <input type="file" accept="image/*" @change="member.photoFile = $event.target.files[0]" class="w-full" />
                                </div>
                                <div class="flex items-center justify-end">
                                    <button type="button" class="text-sm text-red-600 hover:underline" @click="removeTeamMember(idx)">Remove</button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-6 flex items-center justify-between gap-3">
                        <button type="button" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-800" @click="step=2">Back</button>
                        <button type="button" class="px-4 py-2 rounded-lg bg-indigo-600 text-white" @click="saveStep3()">Next: Services & Products</button>
                    </div>
                </section>

                <!-- Step 4 -->
                <section x-show="step===4" x-transition>
                    <div class="grid grid-cols-1 gap-6">
                        <div class="space-y-3">
                            <h3 class="text-lg font-medium text-gray-900">Services</h3>
                            <button type="button" class="text-sm text-indigo-700" @click="addService()">+ Add service</button>
                            <template x-for="(svc, idx) in form.services" :key="svc.localKey">
                                <div class="mt-3 p-4 rounded-lg border border-gray-200 bg-white space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div class="space-y-2 md:col-span-2">
                                            <label class="text-sm font-medium text-gray-700">Service name</label>
                                            <input x-model="svc.name" type="text" class="w-full rounded border-gray-300" />
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-gray-700">Duration</label>
                                            <input x-model="svc.duration_text" type="text" class="w-full rounded border-gray-300" placeholder="e.g. 1 hour" />
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-gray-700">Price</label>
                                            <input x-model="svc.price_text" type="text" class="w-full rounded border-gray-300" placeholder="e.g. from ₦50,000" />
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700">Sort order</label>
                                        <input x-model.number="svc.sort_order" type="number" class="w-full rounded border-gray-300" />
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <button type="button" class="text-sm text-indigo-700" @click="svc.is_active = !svc.is_active" x-text="svc.is_active ? 'Active' : 'Inactive'"></button>
                                        <button type="button" class="text-sm text-red-600 hover:underline" @click="removeService(idx)">Remove</button>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700">Image (optional)</label>
                                        <input type="file" accept="image/*" @change="svc.imageFile = $event.target.files[0]" class="w-full" />
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="space-y-3">
                            <h3 class="text-lg font-medium text-gray-900">Products</h3>
                            <button type="button" class="text-sm text-indigo-700" @click="addProduct()">+ Add product</button>
                            <template x-for="(prd, idx) in form.products" :key="prd.localKey">
                                <div class="mt-3 p-4 rounded-lg border border-gray-200 bg-white space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="space-y-2 md:col-span-1">
                                            <label class="text-sm font-medium text-gray-700">Product name</label>
                                            <input x-model="prd.name" type="text" class="w-full rounded border-gray-300" />
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-gray-700">Price</label>
                                            <input x-model="prd.price_text" type="text" class="w-full rounded border-gray-300" placeholder="e.g. from ₦10,000" />
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-gray-700">Sort order</label>
                                            <input x-model.number="prd.sort_order" type="number" class="w-full rounded border-gray-300" />
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700">Description</label>
                                        <textarea x-model="prd.description" rows="2" class="w-full rounded border-gray-300"></textarea>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <button type="button" class="text-sm text-indigo-700" @click="prd.is_active = !prd.is_active" x-text="prd.is_active ? 'Active' : 'Inactive'"></button>
                                        <button type="button" class="text-sm text-red-600 hover:underline" @click="removeProduct(idx)">Remove</button>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700">Image (optional)</label>
                                        <input type="file" accept="image/*" @change="prd.imageFile = $event.target.files[0]" class="w-full" />
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between gap-3">
                        <button type="button" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-800" @click="step=3">Back</button>
                        <button type="button" class="px-4 py-2 rounded-lg bg-indigo-600 text-white" @click="saveStep4()">Next: Images</button>
                    </div>
                </section>

                <!-- Step 5 -->
                <section x-show="step===5" x-transition>
                    <div class="grid grid-cols-1 gap-6">
                        <div class="space-y-3">
                            <h3 class="text-lg font-medium text-gray-900">Cover & logo</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">Cover image</label>
                                    <input type="file" accept="image/*" @change="coverFile = $event.target.files[0]" class="w-full" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">Logo</label>
                                    <input type="file" accept="image/*" @change="logoFile = $event.target.files[0]" class="w-full" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">Gallery (max 10)</h3>
                                <span class="text-xs text-gray-500" x-text="galleryFiles.length + '/10'"></span>
                            </div>

                            <div class="rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-6 text-center"
                                @dragover.prevent
                                @drop.prevent="handleGalleryDrop($event)">
                                <div class="text-sm font-medium text-gray-900">Drag & drop images here</div>
                                <div class="text-xs text-gray-500 mt-1">Or pick files</div>
                                <input type="file" accept="image/*" multiple class="mt-3 w-full"
                                    @change="handleGalleryPick($event)" />
                            </div>

                            <template x-if="galleryFiles.length > 0">
                                <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                                    <template x-for="(f, idx) in galleryFiles" :key="idx">
                                        <div class="rounded border border-gray-200 overflow-hidden bg-white">
                                            <div class="text-xs text-gray-600 p-2" x-text="f.name"></div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between gap-3">
                        <button type="button" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-800" @click="step=4">Back</button>
                        <button type="button" class="px-4 py-2 rounded-lg bg-indigo-600 text-white" @click="saveStep5()">Next: Social</button>
                    </div>
                </section>

                <!-- Step 6 -->
                <section x-show="step===6" x-transition>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Facebook</label>
                            <input x-model="form.social_facebook" class="w-full rounded border-gray-300" type="url" placeholder="https://facebook.com/..." />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Instagram</label>
                            <input x-model="form.social_instagram" class="w-full rounded border-gray-300" type="url" placeholder="https://instagram.com/..." />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Twitter/X</label>
                            <input x-model="form.social_twitter" class="w-full rounded border-gray-300" type="url" placeholder="https://twitter.com/..." />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">TikTok</label>
                            <input x-model="form.social_tiktok" class="w-full rounded border-gray-300" type="url" placeholder="https://tiktok.com/@..." />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">YouTube</label>
                            <input x-model="form.social_youtube" class="w-full rounded border-gray-300" type="url" placeholder="https://youtube.com/..." />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">LinkedIn</label>
                            <input x-model="form.social_linkedin" class="w-full rounded border-gray-300" type="url" placeholder="https://linkedin.com/in/..." />
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between gap-3">
                        <button type="button" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-800" @click="step=5">Back</button>
                        <button type="button" class="px-4 py-2 rounded-lg bg-indigo-600 text-white" @click="saveStep6()">Next: Review</button>
                    </div>
                </section>

                <!-- Step 7 -->
                <section x-show="step===7" x-transition>
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">Review & submit</h3>
                        <div class="rounded-lg border border-gray-200 bg-white p-4">
                            <div class="text-sm text-gray-600">Business</div>
                            <div class="text-gray-900 font-medium" x-text="form.name"></div>
                            <div class="text-xs text-gray-500 mt-1">
                                Categories: <span x-text="selectedCategoryNames"></span>
                            </div>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-white p-4">
                            <div class="text-sm text-gray-600">Contact</div>
                            <div class="text-gray-900 font-medium" x-text="form.whatsapp_number || form.phone || '—'"></div>
                            <div class="text-xs text-gray-500 mt-1" x-text="form.address || 'No address provided'"></div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between gap-3">
                        <button type="button" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-800" @click="step=6">Back</button>
                        <button type="button" class="px-4 py-2 rounded-lg bg-indigo-600 text-white" @click="submit()">
                            Submit for approval
                        </button>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
        function businessCreateWizard() {
            return {
                step: 1,
                businessSlug: null,
                categories: @json($categories),
                days: [
                    { key: 'monday', label: 'Mon' },
                    { key: 'tuesday', label: 'Tue' },
                    { key: 'wednesday', label: 'Wed' },
                    { key: 'thursday', label: 'Thu' },
                    { key: 'friday', label: 'Fri' },
                    { key: 'saturday', label: 'Sat' },
                    { key: 'sunday', label: 'Sun' },
                ],
                form: {
                    name: '',
                    tagline: '',
                    description: '',
                    category_ids: [],

                    // Step 2
                    whatsapp_number: '',
                    phone: '',
                    email: '',
                    website: '',
                    address: '',
                    latitude: null,
                    longitude: null,
                    working_hours: {},

                    // Step 3
                    team_members: [],

                    // Step 4
                    services: [],
                    products: [],

                    // Step 6
                    social_facebook: '',
                    social_instagram: '',
                    social_twitter: '',
                    social_tiktok: '',
                    social_youtube: '',
                    social_linkedin: '',
                },

                coverFile: null,
                logoFile: null,
                galleryFiles: [],
                showToast: false,
                toastMessage: '',
                toastType: 'success',

                async init() {
                    // Default working hours structure
                    for (const day of this.days) {
                        this.form.working_hours[day.key] = { open: '09:00', close: '17:00', closed: false };
                    }

                    // Initial rows
                    this.addTeamMember();
                    this.addService();
                    this.addProduct();
                },

                go(nextStep) {
                    if (nextStep !== 1 && !this.businessSlug) return;
                    this.step = nextStep;
                },

                get selectedCategories() {
                    return this.form.category_ids || [];
                },

                get selectedCategoryNames() {
                    const ids = new Set(this.form.category_ids || []);
                    return (this.categories || []).filter(c => ids.has(c.id)).map(c => c.name).join(', ') || '—';
                },

                headers() {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                    const token = document.querySelector('meta[name="api-token"]')?.content;
                    const h = { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, };
                    if (token) h['Authorization'] = 'Bearer ' + token;
                    return h;
                },
                cleanNullable(value) {
                    if (value === undefined) return null;
                    if (value === null) return null;
                    if (typeof value === 'string' && value.trim() === '') return null;
                    return value;
                },

                notify(message, type = 'success') {
                    this.toastMessage = message;
                    this.toastType = type;
                    this.showToast = true;
                    setTimeout(() => {
                        this.showToast = false;
                    }, 5000);
                },

                async saveStep1() {
                    const payload = {
                        name: this.form.name,
                        tagline: this.form.tagline,
                        description: this.form.description,
                        category_ids: this.form.category_ids,
                    };

                    const res = await fetch('/api/biz/businesses', {
                        method: 'POST',
                        headers: { ...this.headers(), 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload),
                    });

                    const json = await res.json();
                    if (!json.success) {
                        this.notify(json.message || 'Failed to create draft', 'error');
                        return;
                    }

                    this.businessSlug = json.data.slug;
                    this.notify(json.message || 'Draft created!');
                    this.step = 2;
                },

                async saveStep2() {
                    const payload = {
                        whatsapp_number: this.cleanNullable(this.form.whatsapp_number),
                        phone: this.cleanNullable(this.form.phone),
                        email: this.cleanNullable(this.form.email),
                        website: this.cleanNullable(this.form.website),
                        address: this.cleanNullable(this.form.address),
                        latitude: this.cleanNullable(this.form.latitude),
                        longitude: this.cleanNullable(this.form.longitude),
                        working_hours: this.form.working_hours,
                    };

                    const res = await fetch(`/api/biz/businesses/${this.businessSlug}`, {
                        method: 'PUT',
                        headers: { ...this.headers(), 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload),
                    });

                    const json = await res.json();
                    if (!json.success) {
                        this.notify(json.message || 'Failed to save contact', 'error');
                        return;
                    }
                    this.notify(json.message || 'Contact saved.');
                    this.step = 3;
                },

                addTeamMember() {
                    const nextOrder = (this.form.team_members || []).length + 1;
                    this.form.team_members.push({
                        localKey: crypto.randomUUID(),
                        name: '',
                        role: '',
                        bio: '',
                        sort_order: nextOrder,
                        photoFile: null,
                    });
                },

                removeTeamMember(idx) {
                    this.form.team_members.splice(idx, 1);
                },

                async saveStep3() {
                    for (const member of this.form.team_members) {
                        if (!member.name) continue;

                        const fd = new FormData();
                        fd.append('name', member.name);
                        fd.append('role', member.role || '');
                        fd.append('bio', member.bio || '');
                        fd.append('sort_order', member.sort_order ?? 1);
                        if (member.photoFile) fd.append('photo', member.photoFile);

                        const res = await fetch(`/api/biz/businesses/${this.businessSlug}/team-members`, {
                            method: 'POST',
                            headers: this.headers(),
                            body: fd,
                        });

                        const json = await res.json();
                        if (!json.success) {
                            this.notify(json.message || 'Failed to save team member', 'error');
                            return;
                        }
                    }

                    this.notify('Team saved.');
                    this.step = 4;
                },

                addService() {
                    const nextOrder = (this.form.services || []).length + 1;
                    this.form.services.push({
                        localKey: crypto.randomUUID(),
                        name: '',
                        description: '',
                        duration_text: '',
                        price_text: '',
                        sort_order: nextOrder,
                        is_active: true,
                        imageFile: null,
                    });
                },

                removeService(idx) {
                    this.form.services.splice(idx, 1);
                },

                addProduct() {
                    const nextOrder = (this.form.products || []).length + 1;
                    this.form.products.push({
                        localKey: crypto.randomUUID(),
                        name: '',
                        description: '',
                        price_text: '',
                        sort_order: nextOrder,
                        is_active: true,
                        imageFile: null,
                    });
                },

                removeProduct(idx) {
                    this.form.products.splice(idx, 1);
                },

                async saveStep4() {
                    // Services
                    for (const svc of this.form.services) {
                        if (!svc.name) continue;

                        const fd = new FormData();
                        fd.append('name', svc.name);
                        fd.append('description', svc.description || '');
                        fd.append('duration_text', svc.duration_text || '');
                        fd.append('price_text', svc.price_text || '');
                        fd.append('sort_order', svc.sort_order ?? 1);
                        fd.append('is_active', svc.is_active ? '1' : '0');
                        if (svc.imageFile) fd.append('image', svc.imageFile);

                        const res = await fetch(`/api/biz/businesses/${this.businessSlug}/services`, {
                            method: 'POST',
                            headers: this.headers(),
                            body: fd,
                        });

                        const json = await res.json();
                        if (!json.success) {
                            this.notify(json.message || 'Failed to save service', 'error');
                            return;
                        }
                    }

                    // Products
                    for (const prd of this.form.products) {
                        if (!prd.name) continue;

                        const fd = new FormData();
                        fd.append('name', prd.name);
                        fd.append('description', prd.description || '');
                        fd.append('price_text', prd.price_text || '');
                        fd.append('sort_order', prd.sort_order ?? 1);
                        fd.append('is_active', prd.is_active ? '1' : '0');
                        if (prd.imageFile) fd.append('image', prd.imageFile);

                        const res = await fetch(`/api/biz/businesses/${this.businessSlug}/products`, {
                            method: 'POST',
                            headers: this.headers(),
                            body: fd,
                        });

                        const json = await res.json();
                        if (!json.success) {
                            this.notify(json.message || 'Failed to save product', 'error');
                            return;
                        }
                    }

                    this.notify('Services and products saved.');
                    this.step = 5;
                },

                handleGalleryPick(e) {
                    const files = Array.from(e.target.files || []);
                    const total = this.galleryFiles.length + files.length;
                    this.galleryFiles = [...this.galleryFiles, ...files].slice(0, 10);
                    if (total > 10) {
                        this.notify('Max 10 gallery images.', 'error');
                    }
                },

                handleGalleryDrop(e) {
                    const files = Array.from(e.dataTransfer.files || []);
                    const total = this.galleryFiles.length + files.length;
                    this.galleryFiles = [...this.galleryFiles, ...files].slice(0, 10);
                    if (total > 10) {
                        this.notify('Max 10 gallery images.', 'error');
                    }
                },

                async saveStep5() {
                    if (this.coverFile) {
                        const fd = new FormData();
                        fd.append('cover', this.coverFile);
                        const res = await fetch(`/api/biz/businesses/${this.businessSlug}/cover`, {
                            method: 'POST',
                            headers: this.headers(),
                            body: fd,
                        });
                        const json = await res.json();
                        if (!json.success) {
                            this.notify(json.message || 'Failed to upload cover', 'error');
                            return;
                        }
                    }

                    if (this.logoFile) {
                        const fd = new FormData();
                        fd.append('logo', this.logoFile);
                        const res = await fetch(`/api/biz/businesses/${this.businessSlug}/logo`, {
                            method: 'POST',
                            headers: this.headers(),
                            body: fd,
                        });
                        const json = await res.json();
                        if (!json.success) {
                            this.notify(json.message || 'Failed to upload logo', 'error');
                            return;
                        }
                    }

                    if (this.galleryFiles.length > 0) {
                        const fd = new FormData();
                        // API expects `images[]`
                        for (const file of this.galleryFiles) fd.append('images[]', file);
                        const res = await fetch(`/api/biz/businesses/${this.businessSlug}/gallery`, {
                            method: 'POST',
                            headers: this.headers(),
                            body: fd,
                        });
                        const json = await res.json();
                        if (!json.success) {
                            this.notify(json.message || 'Failed to upload gallery', 'error');
                            return;
                        }
                    }

                    this.notify('Images saved.');
                    this.step = 6;
                },

                async saveStep6() {
                    const payload = {
                        social_facebook: this.cleanNullable(this.form.social_facebook),
                        social_instagram: this.cleanNullable(this.form.social_instagram),
                        social_twitter: this.cleanNullable(this.form.social_twitter),
                        social_tiktok: this.cleanNullable(this.form.social_tiktok),
                        social_youtube: this.cleanNullable(this.form.social_youtube),
                        social_linkedin: this.cleanNullable(this.form.social_linkedin),
                    };

                    const res = await fetch(`/api/biz/businesses/${this.businessSlug}`, {
                        method: 'PUT',
                        headers: { ...this.headers(), 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload),
                    });

                    const json = await res.json();
                    if (!json.success) {
                        this.notify(json.message || 'Failed to save socials', 'error');
                        return;
                    }
                    this.notify(json.message || 'Social links saved.');
                    this.step = 7;
                },

                async submit() {
                    const res = await fetch(`/api/biz/businesses/${this.businessSlug}/submit`, {
                        method: 'POST',
                        headers: this.headers(),
                    });

                    const json = await res.json();
                    if (!json.success) {
                        this.notify(json.message || 'Submit failed', 'error');
                        return;
                    }

                    this.notify(json.message || 'Submitted!');
                    window.location.href = @json(route('biz.owner'));
                },
            };
        }
    </script>
</x-sidebar-layout>


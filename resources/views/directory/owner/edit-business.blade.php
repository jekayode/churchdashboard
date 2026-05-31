<x-sidebar-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Business</h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto" x-data="businessEditWizard()" x-init="init()">
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
                        <template x-if="form.status">
                            <span class="inline-flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-gray-300"></span>
                                <span x-text="form.status"></span>
                            </span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Step Navigation -->
            <div class="border-b border-gray-200 px-6 py-3 bg-gray-50">
                <nav class="flex flex-wrap gap-2" aria-label="Edit business steps">
                    <button type="button" class="px-3 py-1 rounded-lg text-sm"
                        :class="step===1 ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200'"
                        @click="go(1)">
                        Basics
                    </button>
                    <button type="button" class="px-3 py-1 rounded-lg text-sm"
                        :class="step===2 ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200'"
                        @click="go(2)">
                        Contact & Location
                    </button>
                    <button type="button" class="px-3 py-1 rounded-lg text-sm"
                        :class="step===3 ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200'"
                        @click="go(3)">
                        Team
                    </button>
                    <button type="button" class="px-3 py-1 rounded-lg text-sm"
                        :class="step===4 ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200'"
                        @click="go(4)">
                        Services & Products
                    </button>
                    <button type="button" class="px-3 py-1 rounded-lg text-sm"
                        :class="step===5 ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200'"
                        @click="go(5)">
                        Images
                    </button>
                    <button type="button" class="px-3 py-1 rounded-lg text-sm"
                        :class="step===6 ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200'"
                        @click="go(6)">
                        Social
                    </button>
                    <button type="button" class="px-3 py-1 rounded-lg text-sm"
                        :class="step===7 ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200'"
                        @click="go(7)">
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
                            <input x-model="form.name" class="w-full rounded border-gray-300" type="text" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Tagline</label>
                            <input x-model="form.tagline" class="w-full rounded border-gray-300" type="text" />
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-medium text-gray-700">Description</label>
                            <textarea x-model="form.description" class="w-full rounded border-gray-300" rows="4"></textarea>
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-gray-700">Categories</label>
                                <span class="text-xs text-gray-500" x-text="selectedCategories.length + ' selected'"></span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <template x-for="c in categories" :key="c.id">
                                    <label class="flex items-start gap-3 p-3 rounded border border-gray-200 cursor-pointer bg-white">
                                        <input type="checkbox" class="mt-1" :value="c.id" x-model="form.category_ids" />
                                        <div>
                                            <div class="text-sm font-medium text-gray-900" x-text="c.name"></div>
                                            <div class="text-xs text-gray-500" x-text="c.slug"></div>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end">
                        <button type="button" class="px-4 py-2 rounded-lg bg-indigo-600 text-white" @click="saveStep1()">
                            Save & continue
                        </button>
                    </div>
                </section>

                <!-- Step 2 -->
                <section x-show="step===2" x-transition>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">WhatsApp</label>
                            <input x-model="form.whatsapp_number" class="w-full rounded border-gray-300" type="text" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Phone</label>
                            <input x-model="form.phone" class="w-full rounded border-gray-300" type="text" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Email</label>
                            <input x-model="form.email" class="w-full rounded border-gray-300" type="email" />
                        </div>
                        <div class="space-y-2 md:col-span-1">
                            <label class="text-sm font-medium text-gray-700">Website</label>
                            <input x-model="form.website" class="w-full rounded border-gray-300" type="url" />
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <label class="text-sm font-medium text-gray-700">Address</label>
                            <input x-model="form.address" class="w-full rounded border-gray-300" type="text" />
                            <div class="text-xs text-gray-500">Map pin uses Latitude/Longitude (optional).</div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Latitude</label>
                            <input x-model="form.latitude" class="w-full rounded border-gray-300" type="text" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Longitude</label>
                            <input x-model="form.longitude" class="w-full rounded border-gray-300" type="text" />
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
                        <button type="button" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-800" @click="go(1)">Back</button>
                        <button type="button" class="px-4 py-2 rounded-lg bg-indigo-600 text-white" @click="saveStep2()">Save & continue</button>
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
                                    <template x-if="member.photo_url">
                                        <div class="text-xs text-gray-500 mt-1">Current photo is set.</div>
                                    </template>
                                </div>

                                <div class="flex items-center justify-end">
                                    <button type="button" class="text-sm text-red-600 hover:underline" @click="removeTeamMember(idx)">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-6 flex items-center justify-between gap-3">
                        <button type="button" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-800" @click="go(2)">Back</button>
                        <button type="button" class="px-4 py-2 rounded-lg bg-indigo-600 text-white" @click="saveStep3()">Save & continue</button>
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
                        <button type="button" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-800" @click="go(3)">Back</button>
                        <button type="button" class="px-4 py-2 rounded-lg bg-indigo-600 text-white" @click="saveStep4()">Save & continue</button>
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
                                    <template x-if="existingCoverUrl">
                                        <div class="mt-2">
                                            <img :src="existingCoverUrl" alt="Cover" class="h-16 w-16 object-cover rounded border border-gray-200" />
                                        </div>
                                    </template>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">Logo</label>
                                    <input type="file" accept="image/*" @change="logoFile = $event.target.files[0]" class="w-full" />
                                    <template x-if="existingLogoUrl">
                                        <div class="mt-2">
                                            <img :src="existingLogoUrl" alt="Logo" class="h-16 w-16 object-cover rounded border border-gray-200" />
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">Gallery</h3>
                                <span class="text-xs text-gray-500" x-text="(existingGallery.length + galleryFiles.length) + '/10'"></span>
                            </div>

                            <div class="rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-4"
                                @dragover.prevent
                                @drop.prevent="handleGalleryDrop($event)">
                                <div class="text-sm font-medium text-gray-900">Drag & drop new images</div>
                                <div class="text-xs text-gray-500 mt-1">Existing images stay unless you remove them.</div>
                                <input type="file" accept="image/*" multiple class="mt-3 w-full"
                                    @change="handleGalleryPick($event)" />
                            </div>

                            <template x-if="existingGallery.length > 0">
                                <div class="mt-4">
                                    <div class="mb-3 flex items-center justify-between">
                                        <h4 class="text-sm font-medium text-gray-700">Current images</h4>
                                        <button type="button"
                                            class="text-sm text-red-600 hover:underline"
                                            @click="removeAllGalleryImages()">
                                            Remove all
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                        <template x-for="img in existingGallery" :key="img.id">
                                            <div class="relative rounded border border-gray-200 overflow-hidden bg-white">
                                                <img :src="img.url" class="w-full h-24 object-cover" />
                                                <button type="button"
                                                    class="absolute top-1 right-1 h-6 w-6 rounded-full bg-red-600 text-white shadow flex items-center justify-center hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300"
                                                    :aria-label="`Remove image ${img.id}`"
                                                    title="Remove image"
                                                    @click="removeGalleryImage(img.id)">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <template x-if="galleryFiles.length > 0">
                                <div class="mt-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">New files</h4>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                        <template x-for="(f, idx) in galleryFiles" :key="idx">
                                            <div class="rounded border border-gray-200 overflow-hidden bg-white">
                                                <div class="text-xs text-gray-600 p-2" x-text="f.name"></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between gap-3">
                        <button type="button" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-800" @click="go(4)">Back</button>
                        <button type="button" class="px-4 py-2 rounded-lg bg-indigo-600 text-white" @click="saveStep5()">Save & continue</button>
                    </div>
                </section>

                <!-- Step 6 -->
                <section x-show="step===6" x-transition>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Facebook</label>
                            <input x-model="form.social_facebook" class="w-full rounded border-gray-300" type="url" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Instagram</label>
                            <input x-model="form.social_instagram" class="w-full rounded border-gray-300" type="url" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Twitter/X</label>
                            <input x-model="form.social_twitter" class="w-full rounded border-gray-300" type="url" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">TikTok</label>
                            <input x-model="form.social_tiktok" class="w-full rounded border-gray-300" type="url" />
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-sm font-medium text-gray-700">YouTube</label>
                            <input x-model="form.social_youtube" class="w-full rounded border-gray-300" type="url" />
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-sm font-medium text-gray-700">LinkedIn</label>
                            <input x-model="form.social_linkedin" class="w-full rounded border-gray-300" type="url" />
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between gap-3">
                        <button type="button" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-800" @click="go(5)">Back</button>
                        <button type="button" class="px-4 py-2 rounded-lg bg-indigo-600 text-white" @click="saveStep6()">Save & continue</button>
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
                        <button type="button" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-800" @click="go(6)">Back</button>
                        <button type="button" class="px-4 py-2 rounded-lg bg-indigo-600 text-white" @click="submit()">Submit</button>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
        function businessEditWizard() {
            return {
                step: 1,
                businessSlug: @json($business->slug),
                categories: @json($categories),
                coverFile: null,
                logoFile: null,
                galleryFiles: [],
                existingCoverUrl: @json($coverUrl),
                existingLogoUrl: @json($logoUrl),
                existingGallery: @json($galleryMedia),
                showToast: false,
                toastMessage: '',
                toastType: 'success',
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
                    status: @json($businessStatus),
                    name: @json($business->name),
                    tagline: @json($business->tagline),
                    description: @json($business->description),
                    category_ids: @json($categoryIds),

                    whatsapp_number: @json($business->whatsapp_number),
                    phone: @json($business->phone),
                    email: @json($business->email),
                    website: @json($business->website),
                    address: @json($business->address),
                    latitude: @json($business->latitude),
                    longitude: @json($business->longitude),
                    working_hours: @json($workingHours),

                    team_members: @json($teamMembersForm),
                    services: @json($servicesForm),
                    products: @json($productsForm),

                    social_facebook: @json($business->social_facebook),
                    social_instagram: @json($business->social_instagram),
                    social_twitter: @json($business->social_twitter),
                    social_tiktok: @json($business->social_tiktok),
                    social_youtube: @json($business->social_youtube),
                    social_linkedin: @json($business->social_linkedin),
                },

                headers() {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                    const token = document.querySelector('meta[name="api-token"]')?.content;
                    const h = { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf };
                    if (token) h['Authorization'] = 'Bearer ' + token;
                    return h;
                },

                cleanNullable(value) {
                    if (value === undefined || value === null) return null;
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

                init() {
                    // Ensure working_hours has all keys
                    for (const day of this.days) {
                        if (!this.form.working_hours[day.key]) {
                            this.form.working_hours[day.key] = { open: '09:00', close: '17:00', closed: false };
                        }
                    }
                },

                go(n) { this.step = n; },

                get selectedCategories() {
                    return (this.form.category_ids || []).slice();
                },

                get selectedCategoryNames() {
                    const ids = new Set(this.form.category_ids || []);
                    return (this.categories || []).filter(c => ids.has(c.id)).map(c => c.name).join(', ') || '—';
                },

                addTeamMember() {
                    const nextOrder = (this.form.team_members || []).length + 1;
                    this.form.team_members.push({
                        localKey: crypto.randomUUID(),
                        id: null,
                        name: '',
                        role: '',
                        bio: '',
                        sort_order: nextOrder,
                        photoFile: null,
                    });
                },

                async removeTeamMember(idx) {
                    const member = this.form.team_members[idx];
                    if (member?.id) {
                        await fetch(`/api/biz/businesses/${this.businessSlug}/team-members/${member.id}`, {
                            method: 'DELETE',
                            headers: this.headers(),
                        }).then(r => r.json()).then(j => {
                            if (!j.success) throw new Error(j.message || 'Failed to delete team member');
                        });
                    }
                    this.form.team_members.splice(idx, 1);
                },

                addService() {
                    const nextOrder = (this.form.services || []).length + 1;
                    this.form.services.push({
                        localKey: crypto.randomUUID(),
                        id: null,
                        name: '',
                        description: '',
                        duration_text: '',
                        price_text: '',
                        sort_order: nextOrder,
                        is_active: true,
                        imageFile: null,
                    });
                },

                async removeService(idx) {
                    const svc = this.form.services[idx];
                    if (svc?.id) {
                        await fetch(`/api/biz/businesses/${this.businessSlug}/services/${svc.id}`, {
                            method: 'DELETE',
                            headers: this.headers(),
                        }).then(r => r.json()).then(j => {
                            if (!j.success) throw new Error(j.message || 'Failed to delete service');
                        });
                    }
                    this.form.services.splice(idx, 1);
                },

                addProduct() {
                    const nextOrder = (this.form.products || []).length + 1;
                    this.form.products.push({
                        localKey: crypto.randomUUID(),
                        id: null,
                        name: '',
                        description: '',
                        price_text: '',
                        sort_order: nextOrder,
                        is_active: true,
                        imageFile: null,
                    });
                },

                async removeProduct(idx) {
                    const prd = this.form.products[idx];
                    if (prd?.id) {
                        await fetch(`/api/biz/businesses/${this.businessSlug}/products/${prd.id}`, {
                            method: 'DELETE',
                            headers: this.headers(),
                        }).then(r => r.json()).then(j => {
                            if (!j.success) throw new Error(j.message || 'Failed to delete product');
                        });
                    }
                    this.form.products.splice(idx, 1);
                },

                async saveStep1() {
                    const payload = {
                        name: this.cleanNullable(this.form.name),
                        tagline: this.cleanNullable(this.form.tagline),
                        description: this.cleanNullable(this.form.description),
                        category_ids: this.form.category_ids,
                    };

                    const res = await fetch(`/api/biz/businesses/${this.businessSlug}`, {
                        method: 'PUT',
                        headers: { ...this.headers(), 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload),
                    });

                    const json = await res.json();
                    if (!json.success) {
                        this.notify(json.message || 'Failed to save basics', 'error');
                        return;
                    }
                    this.notify('Basics saved.');
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
                    this.notify('Contact saved.');
                    this.step = 3;
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

                        if (member.id) {
                            const res = await fetch(`/api/biz/businesses/${this.businessSlug}/team-members/${member.id}`, {
                                method: 'PUT',
                                headers: this.headers(),
                                body: fd,
                            });
                            const json = await res.json();
                            if (!json.success) {
                                this.notify(json.message || 'Failed to update team member', 'error');
                                return;
                            }
                        } else {
                            const res = await fetch(`/api/biz/businesses/${this.businessSlug}/team-members`, {
                                method: 'POST',
                                headers: this.headers(),
                                body: fd,
                            });
                            const json = await res.json();
                            if (!json.success) {
                                this.notify(json.message || 'Failed to add team member', 'error');
                                return;
                            }
                        }
                    }

                    this.notify('Team saved.');
                    this.step = 4;
                },

                async saveStep4() {
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

                        if (svc.id) {
                            const res = await fetch(`/api/biz/businesses/${this.businessSlug}/services/${svc.id}`, {
                                method: 'PUT',
                                headers: this.headers(),
                                body: fd,
                            });
                            const json = await res.json();
                            if (!json.success) {
                                this.notify(json.message || 'Failed to update service', 'error');
                                return;
                            }
                        } else {
                            const res = await fetch(`/api/biz/businesses/${this.businessSlug}/services`, {
                                method: 'POST',
                                headers: this.headers(),
                                body: fd,
                            });
                            const json = await res.json();
                            if (!json.success) {
                                this.notify(json.message || 'Failed to add service', 'error');
                                return;
                            }
                        }
                    }

                    for (const prd of this.form.products) {
                        if (!prd.name) continue;

                        const fd = new FormData();
                        fd.append('name', prd.name);
                        fd.append('description', prd.description || '');
                        fd.append('price_text', prd.price_text || '');
                        fd.append('sort_order', prd.sort_order ?? 1);
                        fd.append('is_active', prd.is_active ? '1' : '0');
                        if (prd.imageFile) fd.append('image', prd.imageFile);

                        if (prd.id) {
                            const res = await fetch(`/api/biz/businesses/${this.businessSlug}/products/${prd.id}`, {
                                method: 'PUT',
                                headers: this.headers(),
                                body: fd,
                            });
                            const json = await res.json();
                            if (!json.success) {
                                this.notify(json.message || 'Failed to update product', 'error');
                                return;
                            }
                        } else {
                            const res = await fetch(`/api/biz/businesses/${this.businessSlug}/products`, {
                                method: 'POST',
                                headers: this.headers(),
                                body: fd,
                            });
                            const json = await res.json();
                            if (!json.success) {
                                this.notify(json.message || 'Failed to add product', 'error');
                                return;
                            }
                        }
                    }

                    this.notify('Services and products saved.');
                    this.step = 5;
                },

                handleGalleryPick(e) {
                    const files = Array.from(e.target.files || []);
                    const remaining = 10 - this.existingGallery.length;
                    this.galleryFiles = [...this.galleryFiles, ...files].slice(0, remaining);
                    if (files.length > remaining) {
                        this.notify('Max 10 gallery images.', 'error');
                    }
                },

                handleGalleryDrop(e) {
                    const files = Array.from(e.dataTransfer.files || []);
                    const remaining = 10 - this.existingGallery.length;
                    this.galleryFiles = [...this.galleryFiles, ...files].slice(0, remaining);
                    if (files.length > remaining) {
                        this.notify('Max 10 gallery images.', 'error');
                    }
                },

                async removeGalleryImage(mediaId) {
                    await fetch(`/api/biz/businesses/${this.businessSlug}/gallery/${mediaId}`, {
                        method: 'DELETE',
                        headers: this.headers(),
                    }).then(r => r.json()).then(j => {
                        if (!j.success) {
                            this.notify(j.message || 'Failed to delete gallery image', 'error');
                            return;
                        }
                    });

                    this.existingGallery = this.existingGallery.filter(img => img.id !== mediaId);
                    this.notify('Gallery image removed.');
                },

                async removeAllGalleryImages() {
                    if (!this.existingGallery.length) {
                        this.notify('No gallery images to remove.', 'error');
                        return;
                    }

                    const ok = confirm('Remove all current gallery images?');
                    if (!ok) {
                        return;
                    }

                    const imageIds = this.existingGallery.map((img) => img.id);

                    for (const mediaId of imageIds) {
                        const res = await fetch(`/api/biz/businesses/${this.businessSlug}/gallery/${mediaId}`, {
                            method: 'DELETE',
                            headers: this.headers(),
                        });
                        const json = await res.json();
                        if (!json.success) {
                            this.notify(json.message || 'Failed while removing gallery images.', 'error');
                            return;
                        }
                    }

                    this.existingGallery = [];
                    this.notify('All gallery images removed.');
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

                        this.galleryFiles = [];
                        this.notify('Gallery uploaded.');
                    }

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
                        this.notify(json.message || 'Failed to save social links', 'error');
                        return;
                    }
                    this.notify('Social links saved.');
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


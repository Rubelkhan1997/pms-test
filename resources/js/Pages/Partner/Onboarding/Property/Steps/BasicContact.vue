<script setup lang="ts">
import { ref, computed } from 'vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormSelect from '@/Components/Form/FormSelect.vue'

// ─── Types ────────────────────────────────────────────────────────────
export interface BasicContactForm {
    name: string
    type: string
    numberOfRooms: string
    description: string
    country: string
    street: string
    city: string
    area: string
    postalCode: string
    latitude: string
    longitude: string
    phone: string
    email: string
    logoFile: File | null
    logoPreview: string
    coverPreview: string // passed from gallery step if available
}

// ─── Props & Emits ────────────────────────────────────────────────────
const props = withDefaults(defineProps<{
    modelValue: BasicContactForm
}>(), {})

const emit = defineEmits<{
    'update:modelValue': [value: BasicContactForm]
    next: []
}>()

// ─── Local proxy ──────────────────────────────────────────────────────
// Use a local reactive ref so individual field updates are clean
const form = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
})

function update<K extends keyof BasicContactForm>(key: K, value: BasicContactForm[K]) {
    emit('update:modelValue', { ...props.modelValue, [key]: value })
}

// ─── Static options ───────────────────────────────────────────────────
const propertyTypes = [
    { value: 'hotel', label: 'Hotel' },
    { value: 'resort', label: 'Resort' },
    { value: 'apartment', label: 'Apartment' },
    { value: 'villa', label: 'Villa' },
    { value: 'hostel', label: 'Hostel' },
]

const countryOptions = [
    { value: 'BD', label: 'Bangladesh' },
    { value: 'US', label: 'United States' },
    { value: 'GB', label: 'United Kingdom' },
    { value: 'IN', label: 'India' },
    { value: 'AE', label: 'UAE' },
]

// ─── Logo upload ──────────────────────────────────────────────────────
function onLogoChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0]
    if (!file) return
    const preview = URL.createObjectURL(file)
    emit('update:modelValue', {
        ...props.modelValue,
        logoFile: file,
        logoPreview: preview,
    })
}

// ─── Map iframe src (OpenStreetMap embed) ─────────────────────────────
const mapSrc = computed(() => {
    const lat = parseFloat(form.value.latitude) || 23.8103
    const lng = parseFloat(form.value.longitude) || 90.4125
    const delta = 0.012
    return (
        `https://www.openstreetmap.org/export/embed.html` +
        `?bbox=${lng - delta},${lat - delta},${lng + delta},${lat + delta}` +
        `&layer=mapnik&marker=${lat},${lng}`
    )
})

const hasCoords = computed(
    () => !!form.value.latitude && !!form.value.longitude,
)

const locationLabel = computed(() =>
    [form.value.area, form.value.city].filter(Boolean).join(', '),
)
</script>

<template>
    <div class="flex gap-8 items-start">

        <div class="flex-1 min-w-0 space-y-6">

            <!-- ── Property details ── -->
            <section class="">
                <div class="pb-3 mb-4 border-b border-gray-10">
                    <h2 class="text-base font-semibold text-gray-800 tracking-wide mb-1">
                        Property details
                    </h2>
                    <p class="text-sm text-gray-500">This information provides the basic details about your property.
                    </p>
                </div>



                <div class="grid grid-cols-2 gap-x-4">
                    <div class="col-span-2">
                        <FormInput :modelValue="form.name" label="Property name" name="name"
                            placeholder="e.g. The Grand Seaside Hotel" :required="true"
                            @update:modelValue="update('name', $event)" />
                    </div>

                    <FormSelect :modelValue="form.type" label="Property type" name="type" :options="propertyTypes"
                        :required="true" @update:modelValue="update('type', $event)" />

                    <FormInput :modelValue="form.numberOfRooms" label="Number of rooms" name="numberOfRooms"
                        type="number" min="1" placeholder="e.g. 40"
                        @update:modelValue="update('numberOfRooms', $event)" />

                    <div class="col-span-2 mt-2 relative mb-4">
                        <div class="pb-4">
                            <h3 class="text-[15px] font-medium text-gray-800">About</h3>
                            <p class="text-sm text-gray-600">"Description" introduces your hotel and what makes it
                                special, while "location info" highlights the surroundings and attractions that make
                                your area appealing to guests.</p>
                        </div>
                        <FormTextarea v-model="form.description" :rows="3" placeholder="Describe your property briefly…"
                            wrapper-class="relative" label="Description" input-class="text-sm resize-none" />
                    </div>
                </div>
                <!-- Logo row -->
                <div class="flex items-center gap-4 mb-4 p-3 rounded-xl bg-gray-50 border border-gray-100">
                    <div
                        class="w-14 h-14 rounded-full border border-gray-200 bg-white flex items-center justify-center overflow-hidden shrink-0 shadow-sm">
                        <img v-if="form.logoPreview" :src="form.logoPreview" class="w-full h-full object-cover"
                            alt="Logo preview" />
                        <svg v-else class="w-6 h-6 text-gray-300" viewBox="0 0 24 24" fill="none">
                            <rect x="4" y="4" width="16" height="16" rx="2" stroke="currentColor" stroke-width="1.5" />
                            <path d="M4 15l4-4 3 3 3-3 6 5" stroke="currentColor" stroke-width="1.5"
                                stroke-linejoin="round" />
                            <circle cx="8.5" cy="9.5" r="1.5" fill="currentColor" />
                        </svg>
                    </div>
                    <div>
                        <label
                            class="cursor-pointer inline-block px-3 py-1.5 border border-gray-200 rounded-lg text-sm text-gray-600 bg-white hover:bg-gray-50 transition-colors shadow-xs">
                            Upload logo
                            <input type="file" accept="image/*" class="hidden" @change="onLogoChange" />
                        </label>
                        <p class="text-xs text-gray-400 mt-1">PNG or SVG, at least 200×200px</p>
                    </div>
                </div>
            </section>

            <!-- ── Address & Contact ── -->
            <section class="pt-2">

                <div class="pb-3 mb-4 border-b border-gray-10">
                    <h2 class="text-base font-semibold text-gray-800 tracking-wide mb-1">
                        Address &amp; contact
                    </h2>
                    <p class="text-sm text-gray-500">Provide your property's address and make sure all details are
                        accurate. Guests will see these information on your booking engine and sales channels.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-x-4">
                    <FormSelect :modelValue="form.country" label="Country" name="country" :options="countryOptions"
                        :required="true" @update:modelValue="update('country', $event)" />

                    <FormInput :modelValue="form.city" label="City" name="city" placeholder="e.g. Dhaka"
                        :required="true" @update:modelValue="update('city', $event)" />

                    <div class="col-span-2">
                        <FormInput :modelValue="form.street" label="Street address" name="street"
                            placeholder="e.g. 12 Gulshan Avenue" @update:modelValue="update('street', $event)" />
                    </div>

                    <FormInput :modelValue="form.area" label="Area" name="area" placeholder="e.g. Gulshan"
                        @update:modelValue="update('area', $event)" />

                    <FormInput :modelValue="form.postalCode" label="ZIP / Postal code" name="postalCode"
                        placeholder="e.g. 1212" @update:modelValue="update('postalCode', $event)" />

                    <FormInput :modelValue="form.phone" label="Phone" name="phone" placeholder="+880 1234 567890"
                        @update:modelValue="update('phone', $event)" />

                    <FormInput :modelValue="form.email" label="Email" name="email" type="email"
                        placeholder="info@property.com" @update:modelValue="update('email', $event)" />
                </div>
            </section>

            <!-- ── Coordinates ── -->
            <section>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-widest mb-3">
                    GPS coordinates
                </p>
                <div class="grid grid-cols-2 gap-x-4">
                    <FormInput :modelValue="form.latitude" label="Latitude" name="latitude" placeholder="23.7942"
                        @update:modelValue="update('latitude', $event)" />
                    <FormInput :modelValue="form.longitude" label="Longitude" name="longitude" placeholder="90.4044"
                        @update:modelValue="update('longitude', $event)" />
                </div>
            </section>
        </div>

        <!-- ══════════════════════════════════
         RIGHT — Live Preview
    ══════════════════════════════════════ -->
        <aside class="w-72 shrink-0 space-y-4 sticky top-6">

            <!-- ── Property card preview ── -->
            <div class="bg-white rounded-[10px] border border-gray-100 shadow-xs overflow-hidden">

                <!-- Card header -->
                <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">

                    <span class="text-sm font-semibold text-gray-800">Preview</span>
                </div>

                <!-- Cover / hero -->
                <div class="relative h-36 bg-linear-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                    <img v-if="form.coverPreview" :src="form.coverPreview"
                        class="w-full h-full object-cover absolute inset-0" alt="Cover" />
                    <div v-else class="flex flex-col items-center gap-2 text-gray-300 select-none">
                        <svg class="w-9 h-9" viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.2" />
                            <path d="M3 16l5-5 4 4 3-3 6 5" stroke="currentColor" stroke-width="1.2"
                                stroke-linejoin="round" />
                            <circle cx="8" cy="9" r="2" fill="currentColor" opacity=".3" />
                        </svg>
                        <span class="text-[11px]">No cover photo yet</span>
                    </div>
                </div>

                <!-- Logo badge + Info body -->
                <div class="relative">

                    <!-- Logo badge overlapping cover bottom -->
                    <div
                        class="absolute -top-5 left-4 w-10 h-10 rounded-full border-2 border-white bg-gray-50 shadow-md flex items-center justify-center overflow-hidden z-10">
                        <img v-if="form.logoPreview" :src="form.logoPreview" class="w-full h-full object-cover"
                            alt="Logo" />
                        <svg v-else class="w-4 h-4 text-gray-300" viewBox="0 0 24 24" fill="none">
                            <rect x="4" y="4" width="16" height="16" rx="2" stroke="currentColor" stroke-width="1.5" />
                            <path d="M4 15l4-4 3 3 3-3 6 5" stroke="currentColor" stroke-width="1.5"
                                stroke-linejoin="round" />
                        </svg>
                    </div>

                    <!-- Info body -->
                    <div class="px-4 pt-7 pb-4 space-y-2">

                        <!-- Name + type badge -->
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-sm font-semibold text-gray-900 leading-snug">
                                {{ form.name || 'Property Name' }}
                            </h3>
                            <span
                                class="text-[10px] px-2 py-0.5 bg-gray-100 rounded-full text-gray-500 capitalize shrink-0 mt-0.5 leading-snug">
                                {{ form.type || 'hotel' }}
                            </span>
                        </div>

                        <!-- Location -->
                        <p class="text-xs text-gray-400 flex items-center gap-1">
                            <svg class="w-3 h-3 shrink-0" viewBox="0 0 12 12" fill="none">
                                <path
                                    d="M6 1a3.5 3.5 0 0 1 3.5 3.5C9.5 7.5 6 11 6 11S2.5 7.5 2.5 4.5A3.5 3.5 0 0 1 6 1z"
                                    stroke="currentColor" stroke-width="1.2" />
                                <circle cx="6" cy="4.5" r="1" fill="currentColor" />
                            </svg>
                            <span v-if="locationLabel">{{ locationLabel }}</span>
                            <span v-else class="text-gray-300 italic">Location not set</span>
                        </p>

                        <!-- Description -->
                        <p class="text-xs text-gray-500 leading-relaxed line-clamp-3 min-h-12">
                            {{ form.description || 'Your property description will appear here.' }}
                        </p>

                        <!-- Room count -->
                        <div v-if="form.numberOfRooms" class="flex items-center gap-1 text-xs text-gray-400">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 14 14" fill="none">
                                <rect x="1" y="5" width="12" height="8" rx="1" stroke="currentColor"
                                    stroke-width="1.2" />
                                <path d="M4 5V3a3 3 0 0 1 6 0v2" stroke="currentColor" stroke-width="1.2" />
                            </svg>
                            {{ form.numberOfRooms }} rooms
                        </div>

                        <!-- Contact info -->
                        <div v-if="form.phone || form.email" class="pt-2 border-t border-gray-100 space-y-1">
                            <p v-if="form.phone" class="text-xs text-gray-400 flex items-center gap-1.5">
                                <svg class="w-3 h-3 shrink-0" viewBox="0 0 12 12" fill="none">
                                    <path
                                        d="M2 2h3l1 2.5-1.5 1a7 7 0 0 0 2 2l1-1.5L10 7v3a1 1 0 0 1-1 1A9 9 0 0 1 1 3a1 1 0 0 1 1-1z"
                                        stroke="currentColor" stroke-width="1.1" />
                                </svg>
                                {{ form.phone }}
                            </p>
                            <p v-if="form.email" class="text-xs text-gray-400 flex items-center gap-1.5">
                                <svg class="w-3 h-3 shrink-0" viewBox="0 0 12 12" fill="none">
                                    <rect x="1" y="2.5" width="10" height="7" rx="1" stroke="currentColor"
                                        stroke-width="1.1" />
                                    <path d="M1 4l5 3 5-3" stroke="currentColor" stroke-width="1.1" />
                                </svg>
                                {{ form.email }}
                            </p>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ── Map preview ── -->
            <div class="bg-white rounded-[10px] border border-gray-100 shadow-xs overflow-hidden">

                <!-- Header -->
                <div class="px-4 py-2.5 border-b border-gray-100 flex items-center gap-2">
                  
                    <span class="text-sm font-semibold text-gray-800">Location preview</span>
                </div>

                <!-- Map iframe -->
                <div class="relative h-44">
                    <iframe :src="mapSrc" class="w-full h-full border-0" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade" title="Property location map" />
                    <!-- Overlay when no coords entered -->
                    <Transition name="fade">
                        <div v-if="!hasCoords"
                            class="absolute inset-0 bg-white/75 backdrop-blur-[2px] flex flex-col items-center justify-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-400" viewBox="0 0 14 14" fill="none">
                                    <path d="M7 1.5A4 4 0 0 1 11 5.5C11 9 7 12.5 7 12.5S3 9 3 5.5A4 4 0 0 1 7 1.5z"
                                        stroke="currentColor" stroke-width="1.3" />
                                    <circle cx="7" cy="5.5" r="1.4" fill="currentColor" opacity=".45" />
                                </svg>
                            </div>
                            <p class="text-xs text-gray-400 text-center leading-relaxed">
                                Enter latitude &amp; longitude<br>to pin your property
                            </p>
                        </div>
                    </Transition>
                </div>

                <!-- Coords footer -->
                <div v-if="hasCoords"
                    class="px-4 py-2 flex items-center gap-3 text-xs text-gray-400 border-t border-gray-100">
                    <span class="flex items-center gap-1">
                        <span class="font-medium text-gray-500">Lat</span>
                        {{ form.latitude }}
                    </span>
                    <span class="w-px h-3 bg-gray-200" />
                    <span class="flex items-center gap-1">
                        <span class="font-medium text-gray-500">Lng</span>
                        {{ form.longitude }}
                    </span>
                </div>
            </div>

        </aside>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
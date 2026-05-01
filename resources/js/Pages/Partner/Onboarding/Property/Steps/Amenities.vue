<script setup lang="ts">
import { computed, ref } from 'vue'
import { BasicContactForm } from './BasicContact.vue'
import { Search } from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────
export interface AmenityItem {
    label: string
    selected: boolean
}

export interface AmenityGroup {
    name: string
    items: AmenityItem[]
}

export interface AmenitiesForm {
    selectedFeatured: number[]
    amenityGroups: AmenityGroup[]
}

// ─── Props & Emits ────────────────────────────────────────────────────
const props = defineProps<{
    modelValue: AmenitiesForm
    basicContact: BasicContactForm        // ← নতুন
    // ← নতুন
}>()
const emit = defineEmits<{ 'update:modelValue': [value: AmenitiesForm] }>()

const form = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
})

const searchQuery = ref('')

// Featured amenities filter
const filteredFeatured = computed(() => {
    if (!searchQuery.value) return featuredAmenities
    const q = searchQuery.value.toLowerCase()
    return featuredAmenities.filter(a => a.label.toLowerCase().includes(q))
})

// Grouped amenities filter
const filteredGroups = computed(() => {
    if (!searchQuery.value) return form.value.amenityGroups
    const q = searchQuery.value.toLowerCase()
    return form.value.amenityGroups
        .map(g => ({
            ...g,
            items: g.items.filter(it => it.label.toLowerCase().includes(q)),
        }))
        .filter(g => g.items.length > 0)
})
// ─── Featured amenities list ──────────────────────────────────────────
const featuredAmenities = [
    { label: 'Swimming Pool' },
    { label: 'Free Parking' },
    { label: 'Free Wi-Fi' },
    { label: 'Breakfast' },
    { label: 'Gym' },
    { label: '24h Front Desk' },
    { label: 'Restaurant' },
    { label: 'Accessible' },
    { label: 'Private Bath' },
    { label: 'Air Conditioning' },
    { label: 'Garden' },
    { label: 'Airport Shuttle' },
]

// ─── Helpers ──────────────────────────────────────────────────────────
function isFeaturedSelected(index: number) {
    return form.value.selectedFeatured.includes(index)
}

function toggleFeatured(index: number) {
    const current = form.value.selectedFeatured
    const next = current.includes(index)
        ? current.filter((i) => i !== index)
        : [...current, index]

    emit('update:modelValue', { ...form.value, selectedFeatured: next })
}

function toggleGroupItem(groupName: string, itemLabel: string) {
    const groups = form.value.amenityGroups.map(g =>
        g.name !== groupName ? g : {
            ...g,
            items: g.items.map(item =>
                item.label !== itemLabel ? item : { ...item, selected: !item.selected }
            ),
        }
    )
    emit('update:modelValue', { ...form.value, amenityGroups: groups })
}

const locationLabel = computed(() => {
    const { city, country } = props.basicContact
    return [city, country].filter(Boolean).join(', ')
})

const selectedAmenityPreviews = computed(() => {
    const featured = form.value.selectedFeatured.map(i => ({ label: featuredAmenities[i].label }))
    const grouped = form.value.amenityGroups
        .flatMap(g => g.items.filter(it => it.selected))
        .map(it => ({ label: it.label }))
    return [...featured, ...grouped]
})

// --- Accordion  ---------------
const openGroups = ref<Set<string>>(new Set(
    // default সব open
    props.modelValue.amenityGroups.map(g => g.name)
))

function toggleGroup(name: string) {
    const next = new Set(openGroups.value)
    next.has(name) ? next.delete(name) : next.add(name)
    openGroups.value = next
}
// ─── Preview computed ─────────────────────────────────────────────────
const totalSelected = computed(() => {
    const featuredCount = form.value.selectedFeatured.length
    const groupCount = form.value.amenityGroups
        .flatMap((g) => g.items)
        .filter((it) => it.selected).length
    return featuredCount + groupCount
})
</script>

<template>
    <div class="flex gap-6 items-start">

        <!-- ══════════════════════════════════
         LEFT — Form
    ══════════════════════════════════════ -->
        <div class="flex-1 min-w-0 space-y-6">
            <div class="pb-3 mb-4">
                <h2 class="text-base font-semibold text-gray-800 tracking-wide mb-1">
                    Amenities
                </h2>
                <p class="text-sm text-gray-500">List the facilities and services that belong to your property. These
                    details appear on your website and may be shared with your online channels. Room-specific amenities
                    can be added separately in the room setup.
                </p>
            </div>

            <div class="relative mt-4">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2  text-gray-400 pointer-events-none w-4 h-4" />
                <input v-model="searchQuery" type="text" placeholder="Search amenities…"
                    class="w-full pl-8 pr-4 py-2 text-sm border border-gray-200 rounded-md focus:outline-none focus:border-gray-400 placeholder:text-gray-400" />
                <button v-if="searchQuery" type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-500 transition-colors"
                    @click="searchQuery = ''">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 14 14" fill="none">
                        <path d="M2 2l10 10M12 2L2 12" stroke="currentColor" stroke-width="1.5"
                            stroke-linecap="round" />
                    </svg>
                </button>
            </div>
            <!-- ── Featured amenities ── -->
            <section>
                <p class="text-[15px] font-medium text-gray-700 capitalize  mb-3">
                    Featured amenities
                </p>

                <div class="grid grid-cols-3 gap-2">
                    <button v-for="(amenity, i) in filteredFeatured" :key="i" type="button" :class="[
                        'flex items-center justify-between gap-2 px-3.5 py-3 border rounded-md text-xs transition-colors text-left',
                        isFeaturedSelected(i)
                            ? 'border-primary bg-gray-50 text-gray-900'
                            : 'border-gray-200 text-gray-500 hover:bg-gray-50',
                    ]" @click="toggleFeatured(i)">
                        <span>{{ amenity.label }}</span>
                        <svg v-if="isFeaturedSelected(i)" class="w-3 h-3 shrink-0 text-primary" viewBox="0 0 10 10"
                            fill="none">
                            <path d="M1.5 5l2.5 2.5 4.5-4.5" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
                <p v-if="filteredFeatured.length === 0 && searchQuery" class="text-xs text-gray-400 italic">
                    No featured amenities found.
                </p>
            </section>

            <!-- ── Grouped amenities ── -->
            <section v-for="(group, gi) in filteredGroups" :key="gi">
                

                <!-- ── Grouped amenities ── -->
                <div class="space-y-2">
                    <div v-for="(group, gi) in filteredGroups" :key="gi" class=" overflow-hidden">
                        <!-- Accordion header -->
                        <button type="button"
                            class="w-full flex cursor-pointer hover:bg-gray-50 items-center justify-between py-3  transition-colors"
                            @click="toggleGroup(group.name)">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-gray-600 uppercase tracking-widest">
                                    {{ group.name }}
                                </span>
                                <span v-if="group.items.filter(it => it.selected).length > 0"
                                    class="text-[10px] px-2 py-1.5  text-gray-700 border border-gray-300 rounded-md font-medium leading-none">
                                    {{group.items.filter(it => it.selected).length}}
                                </span>
                            </div>

                            <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200"
                                :class="openGroups.has(group.name) ? 'rotate-180' : ''" viewBox="0 0 14 14" fill="none">
                                <path d="M2.5 5l4.5 4.5L11.5 5" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <!-- Accordion body -->
                        <Transition enter-active-class="transition-all duration-200 ease-out"
                            enter-from-class="opacity-0 -translate-y-1"
                            leave-active-class="transition-all duration-150 ease-in"
                            leave-to-class="opacity-0 -translate-y-1">
                            <div v-if="openGroups.has(group.name)" class=" pb-4 pt-1">
                                <div class="grid grid-cols-2 gap-2">
                                    <button v-for="(item, ii) in group.items" :key="ii" type="button" :class="[
                                        'flex items-center gap-2 px-3 py-2.5 border rounded-md text-sm transition-colors text-left',
                                        item.selected
                                            ? 'border-primary bg-gray-50 text-gray-900'
                                            : 'border-gray-200 text-gray-500 hover:bg-gray-50',
                                    ]" @click="toggleGroupItem(group.name, item.label)">
                                        <span :class="[
                                            'w-4 h-4 rounded border flex items-center justify-center shrink-0 transition-colors',
                                            item.selected ? 'bg-primary border-primary' : 'border-gray-300',
                                        ]">
                                            <svg v-if="item.selected" class="w-2.5 h-2.5 text-white" viewBox="0 0 10 10"
                                                fill="none">
                                                <path d="M1.5 5l2.5 2.5 4.5-4.5" stroke="currentColor"
                                                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                        <span class="text-xs">{{ item.label }}</span>
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </section>

        </div>

        <!-- ══════════════════════════════════
         RIGHT — Live Preview
    ══════════════════════════════════════ -->
        <aside class="w-72 shrink-0 sticky top-6 space-y-4">

            <!-- Property card preview -->
            <div class="bg-white rounded-[10px] border border-gray-100 shadow-xs overflow-hidden">

                <!-- Card header -->
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-800">Preview</span>
                    <span v-if="totalSelected > 0"
                        class="text-[10px] px-2 py-0.5 bg-gray-900 text-white rounded-full font-medium">
                        {{ totalSelected }} amenities
                    </span>
                </div>

                <!-- Cover / hero -->
                <div class="relative h-36 bg-linear-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                    <img v-if="props.basicContact.coverPreview" :src="props.basicContact.coverPreview"
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
                    <div
                        class="absolute -top-5 left-4 w-10 h-10 rounded-full border-2 border-white bg-gray-50 shadow-md flex items-center justify-center overflow-hidden z-10">
                        <img v-if="props.basicContact.logoPreview" :src="props.basicContact.logoPreview"
                            class="w-full h-full object-cover" alt="Logo" />
                        <svg v-else class="w-4 h-4 text-gray-300" viewBox="0 0 24 24" fill="none">
                            <rect x="4" y="4" width="16" height="16" rx="2" stroke="currentColor" stroke-width="1.5" />
                            <path d="M4 15l4-4 3 3 3-3 6 5" stroke="currentColor" stroke-width="1.5"
                                stroke-linejoin="round" />
                        </svg>
                    </div>

                    <div class="px-4 pt-7 pb-4 space-y-2">

                        <!-- Name + type badge -->
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-sm font-semibold text-gray-900 leading-snug">
                                {{ props.basicContact.name || 'Property Name' }}
                            </h3>
                            <span
                                class="text-[10px] px-2 py-0.5 bg-gray-100 rounded-full text-gray-500 capitalize shrink-0 mt-0.5 leading-snug">
                                {{ props.basicContact.type || 'hotel' }}
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
                            {{ props.basicContact.description || 'Your property description will appear here.' }}
                        </p>

                        <!-- Room count -->
                        <div v-if="props.basicContact.numberOfRooms"
                            class="flex items-center gap-1 text-xs text-gray-400">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 14 14" fill="none">
                                <rect x="1" y="5" width="12" height="8" rx="1" stroke="currentColor"
                                    stroke-width="1.2" />
                                <path d="M4 5V3a3 3 0 0 1 6 0v2" stroke="currentColor" stroke-width="1.2" />
                            </svg>
                            {{ props.basicContact.numberOfRooms }} rooms
                        </div>

                        <!-- Amenities preview -->
                        <Transition enter-active-class="transition-all duration-200 ease-out"
                            enter-from-class="opacity-0 -translate-y-1"
                            leave-active-class="transition-all duration-150 ease-in"
                            leave-to-class="opacity-0 -translate-y-1">
                            <div v-if="selectedAmenityPreviews.length > 0" class="pt-2 border-t border-gray-100">
                                <p class="text-[10px] font-medium text-gray-400 uppercase tracking-widest mb-1.5">
                                    Amenities</p>
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="amenity in selectedAmenityPreviews.slice(0, 6)" :key="amenity.label"
                                        class="inline-flex items-center px-2 py-0.5 bg-gray-50 border border-gray-100 rounded-full text-[10px] text-gray-500">
                                        {{ amenity.label }}
                                    </span>
                                    <span v-if="selectedAmenityPreviews.length > 6"
                                        class="inline-flex items-center px-2 py-0.5 bg-gray-50 border border-gray-100 rounded-full text-[10px] text-gray-400">
                                        +{{ selectedAmenityPreviews.length - 6 }} more
                                    </span>
                                </div>
                            </div>
                        </Transition>

                    </div>
                </div>
            </div>

        </aside>

    </div>
</template>
<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePropertyStore } from '@/Stores/FrontDesk/propertyStore'
import { ArrowRight, ChevronLeft } from "lucide-vue-next"

// ── Step components ───────────────────────────────────────────────────
import BasicContact from './Steps/BasicContact.vue'
import HouseRules from './Steps/HouseRules.vue'
import Amenities from './Steps/Amenities.vue'
import Gallery from './Steps/Gallery.vue'

// ── Types ─────────────────────────────────────────────────────────────
import type { BasicContactForm } from './Steps/BasicContact.vue'
import type { HouseRulesForm } from './Steps/HouseRules.vue'
import type { AmenitiesForm } from './Steps/Amenities.vue'
import type { GalleryForm } from './Steps/Gallery.vue'

defineOptions({ layout: AppLayout })

const store = usePropertyStore()
const currentStep = ref(0)
const loading = ref(false)
const error = ref('')

const steps = [
  { label: 'Basic & Contact' },
  { label: 'House Rules' },
  { label: 'Amenities' },
  { label: 'Gallery' },
]

const isFirst = computed(() => currentStep.value === 0)
const isLast = computed(() => currentStep.value === steps.length - 1)

// function next() { if (!isLast.value) currentStep.value++ }
function back() { if (!isFirst.value) currentStep.value-- }

// ── Form state per step ───────────────────────────────────────────────
const basicContact = ref<BasicContactForm>({
  name: '',
  type: 'hotel',
  numberOfRooms: '',
  description: '',
  country: 'BD',
  street: '',
  city: '',
  area: '',
  postalCode: '',
  latitude: '',
  longitude: '',
  phone: '',
  email: '',
  logoFile: null,
  logoPreview: '',
  coverPreview: '',
})

const houseRules = ref<HouseRulesForm>({
  timezone: 'Asia/Dhaka',
  currency: 'BDT',
  seasonType: 'all_year',
  openingDate: '',
  closingDate: '',
  checkInTime: '14:00',
  checkOutTime: '11:00',
  petsAllowed: false,
  childrenAllowed: true,
})

const amenities = ref<AmenitiesForm>({
  selectedFeatured: [],
  amenityGroups: [
    {
      name: 'Bathroom',
      items: [
        { label: 'Bathtub', selected: false },
        { label: 'Hair Dryer', selected: false },
        { label: 'Toiletries', selected: false },
        { label: 'Towels', selected: false },
      ],
    },
    {
      name: 'Room Features',
      items: [
        { label: 'Flat-Screen TV', selected: false },
        { label: 'Mini Bar', selected: false },
        { label: 'Safe', selected: false },
        { label: 'Balcony', selected: false },
        { label: 'City View', selected: false },
      ],
    },
    {
      name: 'Services',
      items: [
        { label: 'Room Service', selected: false },
        { label: 'Laundry', selected: false },
        { label: 'Concierge', selected: false },
        { label: 'Car Rental', selected: false },
      ],
    },
  ],
})

const gallery = ref<GalleryForm>({
  files: [],
})


function onGalleryUpdate(val: GalleryForm) {
  gallery.value = val
  basicContact.value.coverPreview = val.files[0]?.preview ?? ''
}

// ── Submit ────────────────────────────────────────────────────────────
async function submit() {
  loading.value = true
  error.value = ''
  try {
    const payload = {
      ...basicContact.value,
      ...houseRules.value,
      featuredAmenities: amenities.value.selectedFeatured,
      amenityGroups: amenities.value.amenityGroups.map((g) => ({
        name: g.name,
        items: g.items.filter((it) => it.selected).map((it) => it.label),
      })),
    }

    const property = await store.create(payload as any)
     router.visit('/property/tex/create')

  } catch {
    error.value = 'Failed to save property. Please try again.'
  } finally {
    loading.value = false
  }
}
function next() {
  if (isLast.value) {
    router.visit('/property/policies/create')
    return
  }
  currentStep.value++
}
</script>

<template>

  <Head title="Property Setup — Onboarding" />

  <div class="mx-auto">

    <!-- ── Stepper ── -->
    <nav class="flex items-center pt-2 mb-4" aria-label="Onboarding steps">
      <template v-for="(step, i) in steps" :key="i">
        <div class="flex items-center group">

          <!-- Step Circle -->
          <div :class="[
            'relative w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-bold shrink-0 transition-all duration-200',
            i < currentStep
              ? 'bg-primary text-white shadow-sm shadow-orange-200'
              : i === currentStep
                ? 'bg-orange-50 border-2 border-primary text-primary shadow-sm shadow-orange-100'
                : 'bg-slate-50 text-slate-400 border border-slate-200 group-hover:border-slate-300',
          ]">
            <!-- Completed: Check Icon -->
            <svg v-if="i < currentStep" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12" />
            </svg>

            <!-- Current/Upcoming: Step Number -->
            <span v-else>{{ i + 1 }}</span>

            <!-- Active glow ring -->
            <span v-if="i === currentStep"
              class="absolute -inset-1 rounded-full border border-primary/30 animate-pulse" />
          </div>

          <!-- Step Label -->
          <span :class="[
            'ml-2.5 text-[12px] font-medium whitespace-nowrap transition-colors',
            i === currentStep
              ? 'text-[#2B2457]'
              : i < currentStep
                ? 'text-slate-500'
                : 'text-slate-400 group-hover:text-slate-500',
          ]">
            {{ step.label }}
          </span>
        </div>

        <!-- Connector Line -->
        <div v-if="i < steps.length - 1" class="flex-1 h-px mx-3 rounded-full overflow-hidden"
          :class="i < currentStep ? 'bg-primary/30' : 'bg-slate-200'">
          <div v-if="i < currentStep" class="h-full bg-gradient-to-r from-primary to-orange-400" />
        </div>

      </template>
    </nav>

    <!-- ── Card ── -->
    <div class="bg-white rounded-xl border border-gray-200 px-5 pt-5 space-y-6">

      <BasicContact v-if="currentStep === 0" v-model="basicContact" />

      <HouseRules v-if="currentStep === 1" v-model="houseRules" />

      <Amenities     v-if="currentStep === 2"
    v-model="amenities"
    :basic-contact="basicContact"
     />

      <Gallery v-if="currentStep === 3" v-model="gallery" @update:modelValue="onGalleryUpdate" />

      <p v-if="error" class="text-red-500 text-sm">{{ error }}</p>

      <!-- ── Actions ── -->
      <div class="flex items-center sticky py-5 -bottom-7 justify-between  border-t border-gray-200 bg-white">
        <button type="button"
          :class="['pl-3 pr-4 bg-gray-200 py-2 flex items-center gap-1 border border-gray-200 rounded-[7px] cursor-pointer text-sm text-gray-600 hover:bg-gray-50 transition-colors', { invisible: isFirst }]"
          @click="back">
          <ChevronLeft class="w-4 h-4" />
          Back
        </button>

        <!-- <span class="text-xs text-gray-400">Step {{ currentStep + 1 }} of {{ steps.length }}</span> -->

        <!-- <button v-if="!isLast" type="button"
          class="px-5 py-2 flex cursor-pointer items-center gap-1 group bg-primary text-white rounded-[7px] text-sm font-medium hover:bg-secondary transition-colors"
          @click="next">
          Continue
          <ArrowRight class="w-4 group-hover:translate-x-0.5 transition-all h-4" />
        </button>

        <button v-else type="button" :disabled="loading"
          class="px-5 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-700 disabled:opacity-50 transition-colors"
          @click="submit">
          {{ loading ? 'Saving…' : 'Finish Setup' }}
        </button> -->
        <button type="button"
  class="px-5 py-2 flex cursor-pointer items-center gap-1 group bg-primary text-white rounded-[7px] text-sm font-medium hover:bg-secondary transition-colors"
  @click="next">
  {{ isLast ? 'Finish Setup' : 'Continue' }}
  <ArrowRight class="w-4 group-hover:translate-x-0.5 transition-all h-4" />
</button>
      </div>

    </div>
  </div>
</template>
<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePropertyStore } from '@/Stores/FrontDesk/propertyStore'

defineOptions({ layout: AppLayout })

const store = usePropertyStore()

const step = ref(1)
const loading = ref(false)
const error = ref('')

const propertyForm = ref({
  name: '',
  slug: '',
  type: 'hotel',
  description: '',
  phone: '',
  email: '',
  country: 'US',
  state: '',
  city: '',
  area: '',
  street: '',
  postalCode: '',
  timezone: 'America/New_York',
  currency: 'USD',
})

const propertyTypes = [
  { value: 'hotel', label: 'Hotel' },
  { value: 'resort', label: 'Resort' },
  { value: 'apartment', label: 'Apartment' },
  { value: 'villa', label: 'Villa' },
  { value: 'hostel', label: 'Hostel' },
]

const submit = async () => {
  loading.value = true
  error.value = ''
  try {
    const property = await store.create(propertyForm.value as any)
    router.visit('/onboarding/room-type/create', {
      data: { propertyId: property.id },
    })
  } catch (e) {
    error.value = 'Failed to create property'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <Head title="Property Setup - Onboarding" />

  <div class="max-w-2xl mx-auto py-8">
    <div class="mb-8">
      <h1 class="text-2xl font-semibold text-gray-900">Setup Your Property</h1>
      <p class="text-gray-600 mt-1">Step {{ step }} of 3</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Property Name *</label>
          <input
            v-model="propertyForm.name"
            type="text"
            required
            class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Property Type *</label>
          <select
            v-model="propertyForm.type"
            class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"
          >
            <option v-for="t in propertyTypes" :key="t.value" :value="t.value">
              {{ t.label }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Description</label>
          <textarea
            v-model="propertyForm.description"
            rows="3"
            class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"
          />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Phone</label>
            <input
              v-model="propertyForm.phone"
              type="text"
              class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input
              v-model="propertyForm.email"
              type="email"
              class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"
            />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Country</label>
            <input
              v-model="propertyForm.country"
              type="text"
              maxlength="2"
              class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">City</label>
            <input
              v-model="propertyForm.city"
              type="text"
              class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"
            />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Timezone</label>
            <select
              v-model="propertyForm.timezone"
              class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"
            >
              <option value="America/New_York">America/New_York</option>
              <option value="America/Chicago">America/Chicago</option>
              <option value="America/Los_Angeles">America/Los_Angeles</option>
              <option value="Europe/London">Europe/London</option>
              <option value="Asia/Dhaka">Asia/Dhaka</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Currency</label>
            <select
              v-model="propertyForm.currency"
              class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"
            >
              <option value="USD">USD</option>
              <option value="EUR">EUR</option>
              <option value="GBP">GBP</option>
              <option value="BDT">BDT</option>
            </select>
          </div>
        </div>

        <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
        >
          {{ loading ? 'Saving...' : 'Save Property' }}
        </button>
      </form>
    </div>
  </div>
</template>
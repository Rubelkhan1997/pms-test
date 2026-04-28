<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useRoomTypeStore } from '@/Stores/FrontDesk/roomTypeStore'

defineOptions({ layout: AppLayout })

const store = useRoomTypeStore()
const propertyId = ref(0)

const form = ref({
  propertyId: 0,
  name: '',
  code: '',
  type: 'room',
  floor: '',
  maxOccupancy: 2,
  adultOccupancy: 2,
  numBedrooms: 1,
  numBathrooms: 1,
  areaSqm: null,
  bedTypes: [] as string[],
  baseRate: 0,
  amenities: [] as string[],
  roomQuantity: 0,
  startNumber: '1',
})

const loading = ref(false)
const error = ref('')

const roomTypes = [
  { value: 'room', label: 'Room' },
  { value: 'suite', label: 'Suite' },
  { value: 'cottage', label: 'Cottage' },
  { value: 'villa', label: 'Villa' },
  { value: 'dormitory', label: 'Dormitory' },
]

const bedTypeOptions = ['Single', 'Double', 'Twin', 'King', 'Queen']

const submit = async () => {
  loading.value = true
  error.value = ''
  try {
    await store.create(form.value as any)
    router.visit('/onboarding/rate-plan/create')
  } catch (e) {
    error.value = 'Failed to create room type'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <Head title="Room Types - Onboarding" />

  <div class="max-w-2xl mx-auto py-8">
    <div class="mb-8">
      <h1 class="text-2xl font-semibold text-gray-900">Create Room Types</h1>
      <p class="text-gray-600 mt-1">Step 2 of 3</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <form @submit.prevent="submit" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Room Type Name *</label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="Standard Double"
              class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Code *</label>
            <input
              v-model="form.code"
              type="text"
              required
              placeholder="STD-DBL"
              class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"
            />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Room Type *</label>
            <select v-model="form.type" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2">
              <option v-for="t in roomTypes" :key="t.value" :value="t.value">
                {{ t.label }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Floor</label>
            <input
              v-model="form.floor"
              type="text"
              placeholder="1"
              class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"
            />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Max Occupancy *</label>
            <input
              v-model.number="form.maxOccupancy"
              type="number"
              min="1"
              max="20"
              required
              class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Adult Occupancy *</label>
            <input
              v-model.number="form.adultOccupancy"
              type="number"
              min="1"
              max="20"
              required
              class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"
            />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Bedrooms</label>
            <input
              v-model.number="form.numBedrooms"
              type="number"
              min="1"
              class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Bathrooms</label>
            <input
              v-model.number="form.numBathrooms"
              type="number"
              min="1"
              class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Base Rate *</label>
          <input
            v-model.number="form.baseRate"
            type="number"
            step="0.01"
            min="0"
            required
            class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Number of Rooms to Generate</label>
          <input
            v-model.number="form.roomQuantity"
            type="number"
            min="0"
            class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"
          />
        </div>

        <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
        >
          {{ loading ? 'Saving...' : 'Create Room Type' }}
        </button>
      </form>
    </div>
  </div>
</template>
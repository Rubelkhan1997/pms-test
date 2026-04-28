<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const step = ref(3)
const loading = ref(false)
const error = ref('')

const form = ref({
  name: 'Default',
  code: 'DEFAULT',
  baseRate: 0,
  isActive: true,
})

const submit = async () => {
  loading.value = true
  error.value = ''
  try {
    await new Promise(resolve => setTimeout(resolve, 500))
    router.visit('/dashboard')
  } catch (e) {
    error.value = 'Failed to complete onboarding'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <Head title="Rate Plan - Onboarding" />

  <div class="max-w-2xl mx-auto py-8">
    <div class="mb-8">
      <h1 class="text-2xl font-semibold text-gray-900">Create Rate Plan</h1>
      <p class="text-gray-600 mt-1">Step {{ step }} of 3</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Rate Plan Name</label>
          <input
            v-model="form.name"
            type="text"
            required
            class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Code</label>
          <input
            v-model="form.code"
            type="text"
            required
            class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Base Rate</label>
          <input
            v-model.number="form.baseRate"
            type="number"
            step="0.01"
            min="0"
            required
            class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"
          />
        </div>

        <div class="flex items-center gap-2">
          <input v-model="form.isActive" type="checkbox" id="isActive" class="rounded" />
          <label for="isActive" class="text-sm text-gray-700">Active</label>
        </div>

        <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
        >
          {{ loading ? 'Completing...' : 'Complete Setup' }}
        </button>
      </form>
    </div>
  </div>
</template>
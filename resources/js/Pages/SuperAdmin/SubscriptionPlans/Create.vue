<script lang="ts">
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
export default { layout: SuperAdminLayout };
</script>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
  plan?: {
    id: number
    name: string
    price: number
    billing_cycle: string
    features: string
    status: string
  }
}>();

console.log('Plan prop received:', props.plan);

const form = useForm({
  name: props.plan?.name ?? '',
  price: props.plan?.price ?? '',
  billing_cycle: props.plan?.billing_cycle ?? 'monthly',
  description: '',
  features: props.plan?.features ?? '',
  status: props.plan?.status ?? 'active',
});

function submit(): void {
  if (props.plan?.id) {
    form.put(`/plans/${props.plan.id}`, {
      onSuccess: () => form.reset(),
    });
  } else {
    form.post('/plans', {
      onSuccess: () => form.reset(),
    });
  }
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-semibold text-[#1B1E51]">{{ props.plan ? 'Edit Subscription Plan' : 'Create Subscription Plan' }}</h1>
      <p class="text-gray-500 text-sm mt-1">{{ props.plan ? 'Update plan details' : 'Add a new subscription plan' }}</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 max-w-2xl">
      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <label for="name" class="block text-sm font-medium text-[#1B1E51] mb-2">Plan Name</label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            :class="{ 'border-red-500': form.errors.name }"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F47F20] focus:border-[#F47F20] transition text-[#1B1E51]"
            placeholder="Premium Plan"
          />
          <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
        </div>

        <div>
          <label for="price" class="block text-sm font-medium text-[#1B1E51] mb-2">Price (USD)</label>
          <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">$</span>
            <input
              id="price"
              v-model="form.price"
              type="number"
              step="0.01"
              required
              :class="{ 'border-red-500': form.errors.price }"
              class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F47F20] focus:border-[#F47F20] transition text-[#1B1E51]"
              placeholder="29.99"
            />
          </div>
          <p v-if="form.errors.price" class="mt-1 text-sm text-red-500">{{ form.errors.price }}</p>
        </div>

        <div>
          <label for="billing_cycle" class="block text-sm font-medium text-[#1B1E51] mb-2">Billing Cycle</label>
          <select
            id="billing_cycle"
            v-model="form.billing_cycle"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F47F20] focus:border-[#F47F20] transition text-[#1B1E51] bg-white"
          >
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
          </select>
        </div>

        <div>
          <label for="description" class="block text-sm font-medium text-[#1B1E51] mb-2">Description</label>
          <textarea
            id="description"
            v-model="form.description"
            rows="3"
            :class="{ 'border-red-500': form.errors.description }"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F47F20] focus:border-[#F47F20] transition text-[#1B1E51]"
            placeholder="Describe the plan features..."
          ></textarea>
          <p v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</p>
        </div>

        <div>
          <label for="features" class="block text-sm font-medium text-[#1B1E51] mb-2">Features (one per line)</label>
          <textarea
            id="features"
            v-model="form.features"
            rows="4"
            :class="{ 'border-red-500': form.errors.features }"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F47F20] focus:border-[#F47F20] transition text-[#1B1E51]"
            placeholder="Unlimited rooms&#10;Priority support&#10;Advanced analytics"
          ></textarea>
          <p v-if="form.errors.features" class="mt-1 text-sm text-red-500">{{ form.errors.features }}</p>
        </div>

        <div>
          <label for="status" class="block text-sm font-medium text-[#1B1E51] mb-2">Status</label>
          <select
            id="status"
            v-model="form.status"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F47F20] focus:border-[#F47F20] transition text-[#1B1E51] bg-white"
          >
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>

        <div class="flex items-center gap-4 pt-4">
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-3 bg-[#F47F20] text-white font-semibold rounded-lg hover:bg-[#E06D10] transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
          >
            <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ form.processing ? (props.plan ? 'Updating...' : 'Creating...') : (props.plan ? 'Update Plan' : 'Create Plan') }}
          </button>
          <Link href="/plans" class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
            Cancel
          </Link>
        </div>
      </form>
    </div>
  </div>
</template>

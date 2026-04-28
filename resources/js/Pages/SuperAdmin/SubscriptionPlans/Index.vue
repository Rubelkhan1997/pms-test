<script lang="ts">
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
export default { layout: SuperAdminLayout };
</script>

<script setup lang="ts">
const props = defineProps<{
  plans?: Array<{
    id: number
    name: string
    price: number
    billing_cycle: string
    status: string
    created_at: string
  }>
}>();
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-[#1B1E51]">Subscription Plans</h1>
        <p class="text-gray-500 text-sm mt-1">Manage subscription plans</p>
      </div>
      <Link
        href="/plans/create"
        class="flex items-center gap-2 px-4 py-2.5 bg-[#F47F20] text-white font-medium rounded-lg hover:bg-[#E06D10] transition"
      >
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Create Plan
      </Link>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div v-if="!props.plans?.length" class="p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </div>
        <p class="text-gray-500 mb-2">No subscription plans found</p>
        <p class="text-sm text-gray-400">Create your first plan to get started</p>
      </div>
      <table v-else class="w-full">
        <thead>
          <tr class="border-b border-gray-100">
            <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
            <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Price</th>
            <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Billing</th>
            <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
            <th class="text-right px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="plan in props.plans" :key="plan.id" class="hover:bg-gray-50 transition">
            <td class="px-6 py-4">
              <p class="font-medium text-[#1B1E51]">{{ plan.name }}</p>
            </td>
            <td class="px-6 py-4">
              <p class="text-gray-600">${{ plan.price }}</p>
            </td>
            <td class="px-6 py-4">
              <p class="text-gray-600 capitalize">{{ plan.billing_cycle }}</p>
            </td>
            <td class="px-6 py-4">
              <span :class="plan.status === 'active' ? 'bg-green-50 text-green-600' : 'bg-gray-50 text-gray-600'" class="px-3 py-1 rounded-full text-xs font-medium">
                {{ plan.status }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <Link :href="`/plans/${plan.id}/edit`" class="text-sm text-[#F47F20] hover:text-[#E06D10] font-medium">
                Edit
              </Link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

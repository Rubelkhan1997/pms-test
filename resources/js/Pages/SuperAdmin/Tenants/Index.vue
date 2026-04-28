<script lang="ts">
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
export default { layout: SuperAdminLayout };
</script>

<script setup lang="ts">
const props = defineProps<{
  tenants?: Array<{
    id: number
    name: string
    domain: string
    status: string
    created_at: string
  }>
}>();
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-[#1B1E51]">Tenants</h1>
        <p class="text-gray-500 text-sm mt-1">Manage all tenant accounts</p>
      </div>
      <Link
        href="/tenants/create"
        class="flex items-center gap-2 px-4 py-2.5 bg-[#F47F20] text-white font-medium rounded-lg hover:bg-[#E06D10] transition"
      >
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Tenant
      </Link>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div v-if="!props.tenants?.length" class="p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
        </div>
        <p class="text-gray-500 mb-2">No tenants found</p>
        <p class="text-sm text-gray-400">Create your first tenant to get started</p>
      </div>
      <table v-else class="w-full">
        <thead>
          <tr class="border-b border-gray-100">
            <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
            <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Domain</th>
            <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
            <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Created</th>
            <th class="text-right px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="tenant in props.tenants" :key="tenant.id" class="hover:bg-gray-50 transition">
            <td class="px-6 py-4">
              <p class="font-medium text-[#1B1E51]">{{ tenant.name }}</p>
            </td>
            <td class="px-6 py-4">
              <p class="text-gray-600">{{ tenant.domain }}</p>
            </td>
            <td class="px-6 py-4">
              <span :class="tenant.status === 'active' ? 'bg-green-50 text-green-600' : 'bg-gray-50 text-gray-600'" class="px-3 py-1 rounded-full text-xs font-medium">
                {{ tenant.status }}
              </span>
            </td>
            <td class="px-6 py-4">
              <p class="text-sm text-gray-500">{{ tenant.created_at }}</p>
            </td>
            <td class="px-6 py-4 text-right">
              <Link :href="`/tenants/${tenant.id}`" class="text-sm text-[#F47F20] hover:text-[#E06D10] font-medium">
                View
              </Link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

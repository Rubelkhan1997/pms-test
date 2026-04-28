<script lang="ts">
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
export default { layout: SuperAdminLayout };
</script>

<script setup lang="ts">
defineProps<{
  stats: {
    totalActiveTenants: number
    totalActiveProperties: number
    pendingInvoices: number
    trialExpiringSoon: number
  }
}>()
</script>

<template>
  <div>
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Active Tenants -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Active Tenants</p>
            <p class="text-3xl font-bold text-[#1B1E51]">{{ stats?.totalActiveTenants ?? 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-[#1B1E51]/10 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-[#1B1E51]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
          </div>
        </div>
        <div class="mt-4 flex items-center gap-2">
          <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded">
            Active
          </span>
        </div>
      </div>

      <!-- Active Properties -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Active Properties</p>
            <p class="text-3xl font-bold text-[#1B1E51]">{{ stats?.totalActiveProperties ?? 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-[#F47F20]/10 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-[#F47F20]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
          </div>
        </div>
        <div class="mt-4 flex items-center gap-2">
          <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded">
            All Properties
          </span>
        </div>
      </div>

      <!-- Pending Invoices -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Pending Invoices</p>
            <p class="text-3xl font-bold" :class="(stats?.pendingInvoices ?? 0) > 0 ? 'text-yellow-500' : 'text-[#1B1E51]'">{{ stats?.pendingInvoices ?? 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-yellow-50 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
        </div>
        <div class="mt-4 flex items-center gap-2">
          <span v-if="(stats?.pendingInvoices ?? 0) > 0" class="text-xs font-medium text-yellow-600 bg-yellow-50 px-2 py-1 rounded">
            Attention Needed
          </span>
          <span v-else class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded">
            All Clear
          </span>
        </div>
      </div>

      <!-- Trials Expiring -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Trials Expiring (7d)</p>
            <p class="text-3xl font-bold" :class="(stats?.trialExpiringSoon ?? 0) > 0 ? 'text-red-500' : 'text-[#1B1E51]'">{{ stats?.trialExpiringSoon ?? 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>
        <div class="mt-4 flex items-center gap-2">
          <span v-if="(stats?.trialExpiringSoon ?? 0) > 0" class="text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded">
            Action Required
          </span>
          <span v-else class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded">
            No Expirations
          </span>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
      <h3 class="text-lg font-semibold text-[#1B1E51] mb-4">Quick Actions</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Link
          href="/tenants/create"
          class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-[#F47F20] hover:bg-orange-50 transition group"
        >
          <div class="w-10 h-10 bg-[#F47F20] rounded-lg flex items-center justify-center group-hover:bg-[#E06D10] transition">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
          </div>
          <div>
            <p class="font-medium text-[#1B1E51]">Add New Tenant</p>
            <p class="text-xs text-gray-500">Create a new tenant account</p>
          </div>
        </Link>

        <Link
          href="/plans/create"
          class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-[#F47F20] hover:bg-orange-50 transition group"
        >
          <div class="w-10 h-10 bg-[#F47F20] rounded-lg flex items-center justify-center group-hover:bg-[#E06D10] transition">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
          <div>
            <p class="font-medium text-[#1B1E51]">Create Plan</p>
            <p class="text-xs text-gray-500">Add a subscription plan</p>
          </div>
        </Link>

        <Link
          href="/tenants"
          class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-[#F47F20] hover:bg-orange-50 transition group"
        >
          <div class="w-10 h-10 bg-[#1B1E51] rounded-lg flex items-center justify-center group-hover:bg-[#252869] transition">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
          </div>
          <div>
            <p class="font-medium text-[#1B1E51]">View All Tenants</p>
            <p class="text-xs text-gray-500">Manage tenant accounts</p>
          </div>
        </Link>
      </div>
    </div>

    <!-- Recent Activity (Placeholder) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <h3 class="text-lg font-semibold text-[#1B1E51] mb-4">Platform Overview</h3>
      <div class="space-y-4">
        <div class="flex items-center justify-between py-3 border-b border-gray-100">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <div>
              <p class="text-sm font-medium text-[#1B1E51]">System Status</p>
              <p class="text-xs text-gray-500">All services operational</p>
            </div>
          </div>
          <span class="text-xs font-medium text-green-600 bg-green-50 px-3 py-1 rounded-full">
            Healthy
          </span>
        </div>
        <div class="flex items-center justify-between py-3 border-b border-gray-100">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
            </div>
            <div>
              <p class="text-sm font-medium text-[#1B1E51]">Last Backup</p>
              <p class="text-xs text-gray-500">Automated daily backup</p>
            </div>
          </div>
          <span class="text-xs font-medium text-blue-600 bg-blue-50 px-3 py-1 rounded-full">
            Today
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

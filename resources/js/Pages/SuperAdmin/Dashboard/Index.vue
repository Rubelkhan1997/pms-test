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
  <div class="p-8">
    <h1 class="text-2xl font-semibold text-gray-800 mb-6">Platform Dashboard</h1>
    <div class="grid grid-cols-4 gap-6">
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <p class="text-sm text-gray-500">Active Tenants</p>
        <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats?.totalActiveTenants ?? 0 }}</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <p class="text-sm text-gray-500">Active Properties</p>
        <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats?.totalActiveProperties ?? 0 }}</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <p class="text-sm text-gray-500">Pending Invoices</p>
        <p class="text-3xl font-bold text-yellow-600 mt-2">{{ stats?.pendingInvoices ?? 0 }}</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <p class="text-sm text-gray-500">Trials Expiring (7d)</p>
        <p class="text-3xl font-bold text-red-600 mt-2">{{ stats?.trialExpiringSoon ?? 0 }}</p>
      </div>
    </div>
  </div>
</template>
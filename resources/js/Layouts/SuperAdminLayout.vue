<script setup lang="ts">
import { Head } from '@inertiajs/vue3'

defineOptions({
  layout: {
    default: {
      header: 'PMS Admin',
    },
  },
})

const nav = [
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Tenants', href: '/tenants' },
  { label: 'Plans', href: '/plans' },
  { label: 'Billing', href: '/billing' },
  { label: 'Reports', href: '/reports' },
  { label: 'Settings', href: '/settings' },
]
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex">
    <aside class="w-64 bg-gray-900 text-white flex flex-col">
      <div class="p-6 border-b border-gray-700">
        <span class="text-xl font-bold tracking-tight">PMS Admin</span>
      </div>
      <nav class="flex-1 p-4 space-y-1">
        <a
          v-for="item in nav"
          :key="item.href"
          :href="item.href"
          class="block px-4 py-2 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition-colors"
        >
          {{ item.label }}
        </a>
      </nav>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">
      <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between">
        <slot name="header">
          <slot />
        </slot>
        <span class="text-sm text-gray-500">Super Admin</span>
      </header>
      <main class="flex-1 overflow-y-auto p-8">
        <slot />
      </main>
    </div>
  </div>
</template>
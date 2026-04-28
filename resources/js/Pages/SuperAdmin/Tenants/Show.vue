<script lang="ts">
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
export default { layout: SuperAdminLayout };
</script>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';

const props = defineProps<{
  tenant?: {
    id: number
    name: string
    domain: string
    status: string
    contact_name?: string
    contact_email?: string
    contact_phone?: string
    plan_id?: number
    created_at: string
  }
}>();

function activate(): void {
  if (confirm('Are you sure you want to activate this tenant?')) {
    router.put(`/tenants/${props.tenant?.id}`, { status: 'active' }, {
      preserveScroll: true,
    });
  }
}

function suspend(): void {
  if (confirm('Are you sure you want to suspend this tenant?')) {
    router.put(`/tenants/${props.tenant?.id}`, { status: 'suspended' }, {
      preserveScroll: true,
    });
  }
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-[#1B1E51]">{{ tenant?.name }}</h1>
        <p class="text-gray-500 text-sm mt-1">Tenant Details</p>
      </div>
      <div class="flex items-center gap-3">
        <Link
          href="/tenants"
          class="px-4 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition"
        >
          Back to List
        </Link>
        <Link
          v-if="tenant"
          :href="`/tenants/${tenant.id}/edit`"
          class="px-4 py-2.5 bg-[#F47F20] text-white font-medium rounded-lg hover:bg-[#E06D10] transition"
        >
          Edit
        </Link>
      </div>
    </div>

    <div v-if="tenant" class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 max-w-2xl">
      <div class="space-y-6">
        <div>
          <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Basic Information</h3>
          <dl class="grid grid-cols-1 gap-4">
            <div>
              <dt class="text-sm font-medium text-gray-500">Name</dt>
              <dd class="mt-1 text-lg font-semibold text-[#1B1E51]">{{ tenant.name }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Domain</dt>
              <dd class="mt-1 text-[#1B1E51]">{{ tenant.domain }}.pms.test</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Status</dt>
              <dd class="mt-1">
                <span :class="tenant.status === 'active' ? 'bg-green-50 text-green-600' : 'bg-gray-50 text-gray-600'" class="px-3 py-1 rounded-full text-xs font-medium">
                  {{ tenant.status }}
                </span>
              </dd>
            </div>
          </dl>
        </div>

        <div v-if="tenant.contact_name || tenant.contact_email || tenant.contact_phone">
          <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Contact Information</h3>
          <dl class="grid grid-cols-1 gap-4">
            <div v-if="tenant.contact_name">
              <dt class="text-sm font-medium text-gray-500">Contact Name</dt>
              <dd class="mt-1 text-[#1B1E51]">{{ tenant.contact_name }}</dd>
            </div>
            <div v-if="tenant.contact_email">
              <dt class="text-sm font-medium text-gray-500">Contact Email</dt>
              <dd class="mt-1 text-[#1B1E51]">{{ tenant.contact_email }}</dd>
            </div>
            <div v-if="tenant.contact_phone">
              <dt class="text-sm font-medium text-gray-500">Contact Phone</dt>
              <dd class="mt-1 text-[#1B1E51]">{{ tenant.contact_phone }}</dd>
            </div>
          </dl>
        </div>

        <div class="pt-4 border-t border-gray-100">
          <p class="text-sm text-gray-500">Created: {{ new Date(tenant.created_at).toLocaleDateString() }}</p>
        </div>

        <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
          <button
            v-if="tenant.status !== 'active'"
            @click="activate"
            class="px-4 py-2.5 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition"
          >
            Activate
          </button>
          <button
            v-if="tenant.status !== 'suspended'"
            @click="suspend"
            class="px-4 py-2.5 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition"
          >
            Suspend
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

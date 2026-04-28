<script lang="ts">
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
export default { layout: SuperAdminLayout };
</script>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
  tenant?: {
    id: number
    name: string
    domain: string
    contact_email?: string
    status?: string
  }
}>();

const form = useForm({
  name: props.tenant?.name ?? '',
  domain: props.tenant?.domain ?? '',
  email: props.tenant?.contact_email ?? '',
  password: '',
  password_confirmation: '',
});

function submit(): void {
  if (props.tenant?.id) {
    form.put(`/tenants/${props.tenant.id}`, {
      onSuccess: () => form.reset('password', 'password_confirmation'),
    });
  } else {
    form.post('/tenants', {
      onSuccess: () => form.reset('password', 'password_confirmation'),
    });
  }
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-semibold text-[#1B1E51]">{{ props.tenant ? 'Edit Tenant' : 'Create Tenant' }}</h1>
      <p class="text-gray-500 text-sm mt-1">{{ props.tenant ? 'Update tenant account' : 'Add a new tenant account' }}</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 max-w-2xl">
      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <label for="name" class="block text-sm font-medium text-[#1B1E51] mb-2">Tenant Name</label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            :class="{ 'border-red-500': form.errors.name }"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F47F20] focus:border-[#F47F20] transition text-[#1B1E51]"
            placeholder="Acme Hotels"
          />
          <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
        </div>

        <div>
          <label for="domain" class="block text-sm font-medium text-[#1B1E51] mb-2">Domain</label>
          <div class="flex items-center">
            <input
              id="domain"
              v-model="form.domain"
              type="text"
              required
              :class="{ 'border-red-500': form.errors.domain }"
              class="flex-1 px-4 py-3 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-[#F47F20] focus:border-[#F47F20] transition text-[#1B1E51]"
              placeholder="acme"
            />
            <span class="px-4 py-3 bg-gray-100 border border-l-0 border-gray-300 rounded-r-lg text-gray-500 text-sm">.pms.test</span>
          </div>
          <p v-if="form.errors.domain" class="mt-1 text-sm text-red-500">{{ form.errors.domain }}</p>
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-[#1B1E51] mb-2">Admin Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            :class="{ 'border-red-500': form.errors.email }"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F47F20] focus:border-[#F47F20] transition text-[#1B1E51]"
            placeholder="admin@acme.com"
          />
          <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</p>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-[#1B1E51] mb-2">Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            :class="{ 'border-red-500': form.errors.password }"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F47F20] focus:border-[#F47F20] transition text-[#1B1E51]"
            placeholder="••••••••"
          />
          <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">{{ form.errors.password }}</p>
        </div>

        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-[#1B1E51] mb-2">Confirm Password</label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F47F20] focus:border-[#F47F20] transition text-[#1B1E51]"
            placeholder="••••••••"
          />
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
            {{ form.processing ? (props.tenant ? 'Updating...' : 'Creating...') : (props.tenant ? 'Update Tenant' : 'Create Tenant') }}
          </button>
          <Link href="/tenants" class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
            Cancel
          </Link>
        </div>
      </form>
    </div>
  </div>
</template>

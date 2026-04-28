import { useTenantsStore } from '@/Stores/SuperAdmin/tenantStore'

export function useTenants() {
  const store = useTenantsStore()

  return {
    ...store,
  }
}
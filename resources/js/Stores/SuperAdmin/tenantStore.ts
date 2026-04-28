import { defineStore } from 'pinia'
import apiClient from '@/Services/apiClient'
import { getErrorMessage } from '@/Helpers/error'
import type { Tenant, TenantFilters, TenantPagination, CreateTenantDto } from '@/Types/SuperAdmin/tenant'
import { mapTenantApiToTenant, mapCreateTenantToApi, mapTenantPaginationApiToPagination } from '@/Utils/Mappers/tenant'

const DEFAULT_PAGINATION: TenantPagination = { currentPage: 1, perPage: 20, total: 0, lastPage: 1 }
const DEFAULT_FILTERS: TenantFilters = { status: '', search: '', perPage: 20 }

export const useTenantsStore = defineStore('admin-tenants', {
  state: () => ({
    tenants: [] as Tenant[],
    selectedTenant: null as Tenant | null,
    pagination: { ...DEFAULT_PAGINATION },
    filters: { ...DEFAULT_FILTERS },
    loading: false,
    loadingList: false,
    loadingDetail: false,
    error: null as string | null,
  }),

  actions: {
    async fetchAll(filters?: Partial<TenantFilters>) {
      this.loadingList = true
      this.error = null
      if (filters) Object.assign(this.filters, filters)
      try {
        const params = {
          page: this.pagination.currentPage,
          per_page: this.filters.perPage,
          status: this.filters.status || undefined,
          search: this.filters.search || undefined,
        }
        const res = await apiClient.get('/api/v1/admin/tenants', { params })
        const payload = res.data?.data ?? {}
        const items = Array.isArray(payload.items) ? payload.items : []
        this.tenants = items.map(mapTenantApiToTenant)
        this.pagination = mapTenantPaginationApiToPagination(payload.pagination ?? {})
      } catch (err) {
        this.error = getErrorMessage(err)
      } finally {
        this.loadingList = false
      }
    },

    async create(dto: CreateTenantDto): Promise<Tenant> {
      this.loading = true
      this.error = null
      try {
        const res = await apiClient.post('/api/v1/admin/tenants', mapCreateTenantToApi(dto))
        const tenant = mapTenantApiToTenant(res.data.data)
        this.tenants.unshift(tenant)
        return tenant
      } catch (err) {
        this.error = getErrorMessage(err)
        throw err
      } finally {
        this.loading = false
      }
    },

    async suspend(id: number): Promise<void> {
      await apiClient.patch(`/api/v1/admin/tenants/${id}/suspend`)
      const t = this.tenants.find(x => x.id === id)
      if (t) t.status = 'suspended'
    },

    async activate(id: number): Promise<void> {
      await apiClient.patch(`/api/v1/admin/tenants/${id}/activate`)
      const t = this.tenants.find(x => x.id === id)
      if (t) t.status = 'active'
    },
  },
})
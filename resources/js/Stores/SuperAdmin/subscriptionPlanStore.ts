import { defineStore } from 'pinia'
import apiClient from '@/Services/apiClient'
import { getErrorMessage } from '@/Helpers/error'
import type { SubscriptionPlan, SubscriptionPlanFilters, SubscriptionPlanPagination, CreateSubscriptionPlanDto } from '@/Types/SuperAdmin/subscriptionPlan'
import { mapSubscriptionPlanApiToPlan, mapCreateSubscriptionPlanToApi, mapSubscriptionPlanPaginationApiToPagination } from '@/Utils/Mappers/subscriptionPlan'

const DEFAULT_PAGINATION: SubscriptionPlanPagination = { currentPage: 1, perPage: 20, total: 0, lastPage: 1 }
const DEFAULT_FILTERS: SubscriptionPlanFilters = { search: '', isActive: true, perPage: 20 }

export const useSubscriptionPlansStore = defineStore('admin-subscription-plans', {
  state: () => ({
    plans: [] as SubscriptionPlan[],
    selectedPlan: null as SubscriptionPlan | null,
    pagination: { ...DEFAULT_PAGINATION },
    filters: { ...DEFAULT_FILTERS },
    loading: false,
    loadingList: false,
    loadingDetail: false,
    error: null as string | null,
  }),

  actions: {
    async fetchAll(filters?: Partial<SubscriptionPlanFilters>) {
      this.loadingList = true
      this.error = null
      if (filters) Object.assign(this.filters, filters)
      try {
        const params = {
          page: this.pagination.currentPage,
          per_page: this.filters.perPage,
          search: this.filters.search || undefined,
        }
        const res = await apiClient.v1.get('/admin/plans', { params })
        const payload = res.data?.data ?? {}
        const items = Array.isArray(payload.items) ? payload.items : []
        this.plans = items.map(mapSubscriptionPlanApiToPlan)
        this.pagination = mapSubscriptionPlanPaginationApiToPagination(payload.pagination ?? {})
      } catch (err) {
        this.error = getErrorMessage(err)
      } finally {
        this.loadingList = false
      }
    },

    async create(dto: CreateSubscriptionPlanDto): Promise<SubscriptionPlan> {
      this.loading = true
      this.error = null
      try {
        const res = await apiClient.v1.post('/admin/plans', mapCreateSubscriptionPlanToApi(dto))
        const plan = mapSubscriptionPlanApiToPlan(res.data.data)
        this.plans.unshift(plan)
        return plan
      } catch (err) {
        this.error = getErrorMessage(err)
        throw err
      } finally {
        this.loading = false
      }
    },

    async update(id: number, dto: CreateSubscriptionPlanDto): Promise<SubscriptionPlan> {
      this.loading = true
      this.error = null
      try {
        const res = await apiClient.v1.put(`/admin/plans/${id}`, mapCreateSubscriptionPlanToApi(dto))
        const plan = mapSubscriptionPlanApiToPlan(res.data.data)
        const idx = this.plans.findIndex(x => x.id === id)
        if (idx !== -1) this.plans[idx] = plan
        return plan
      } catch (err) {
        this.error = getErrorMessage(err)
        throw err
      } finally {
        this.loading = false
      }
    },

    async delete(id: number): Promise<void> {
      await apiClient.v1.delete(`/admin/plans/${id}`)
      this.plans = this.plans.filter(x => x.id !== id)
    },
  },
})
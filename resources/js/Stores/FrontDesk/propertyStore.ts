import { defineStore } from 'pinia'
import apiClient from '@/Services/apiClient'
import { getErrorMessage } from '@/Helpers/error'
import type { Property, PropertyFilters, PropertyPagination, CreatePropertyDto } from '@/Types/FrontDesk/property'
import { mapPropertyApiToProperty, mapCreatePropertyToApi } from '@/Utils/Mappers/property'

const DEFAULT_PAGINATION: PropertyPagination = { currentPage: 1, perPage: 20, total: 0, lastPage: 1 }
const DEFAULT_FILTERS: PropertyFilters = { search: '', perPage: 20 }

export const usePropertyStore = defineStore('property', {
  state: () => ({
    properties: [] as Property[],
    selectedProperty: null as Property | null,
    pagination: { ...DEFAULT_PAGINATION },
    filters: { ...DEFAULT_FILTERS },
    loading: false,
    loadingList: false,
    loadingDetail: false,
    error: null as string | null,
  }),

  actions: {
    async fetchAll(filters?: Partial<PropertyFilters>) {
      this.loadingList = true
      this.error = null
      if (filters) Object.assign(this.filters, filters)
      try {
        const params = {
          page: this.pagination.currentPage,
          per_page: this.filters.perPage,
          search: this.filters.search || undefined,
        }
        const res = await apiClient.v1.get('/front-desk/properties', { params })
        const payload = res.data?.data ?? {}
        const items = Array.isArray(payload.items) ? payload.items : []
        this.properties = items.map(mapPropertyApiToProperty)
        this.pagination = {
          currentPage: payload.pagination?.current_page ?? 1,
          perPage: payload.pagination?.per_page ?? 20,
          total: payload.pagination?.total ?? 0,
          lastPage: payload.pagination?.last_page ?? 1,
        }
      } catch (err) {
        this.error = getErrorMessage(err)
      } finally {
        this.loadingList = false
      }
    },

    async create(dto: CreatePropertyDto): Promise<Property> {
      this.loading = true
      this.error = null
      try {
        const res = await apiClient.v1.post('/front-desk/properties', mapCreatePropertyToApi(dto))
        const property = mapPropertyApiToProperty(res.data.data)
        this.properties.unshift(property)
        return property
      } catch (err) {
        this.error = getErrorMessage(err)
        throw err
      } finally {
        this.loading = false
      }
    },

    async update(id: number, dto: CreatePropertyDto): Promise<Property> {
      this.loading = true
      this.error = null
      try {
        const res = await apiClient.v1.put(`/front-desk/properties/${id}`, mapCreatePropertyToApi(dto))
        const property = mapPropertyApiToProperty(res.data.data)
        const idx = this.properties.findIndex(x => x.id === id)
        if (idx !== -1) this.properties[idx] = property
        return property
      } catch (err) {
        this.error = getErrorMessage(err)
        throw err
      } finally {
        this.loading = false
      }
    },

    async delete(id: number): Promise<void> {
      await apiClient.v1.delete(`/front-desk/properties/${id}`)
      this.properties = this.properties.filter(x => x.id !== id)
    },
  },
})
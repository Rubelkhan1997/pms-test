import { defineStore } from 'pinia'
import apiClient from '@/Services/apiClient'
import { getErrorMessage } from '@/Helpers/error'
import type { RoomType, RoomTypeFilters, CreateRoomTypeDto } from '@/Types/FrontDesk/roomType'
import { mapRoomTypeApiToRoomType, mapCreateRoomTypeToApi } from '@/Utils/Mappers/roomType'

const DEFAULT_FILTERS: RoomTypeFilters = { search: '', perPage: 20 }

export const useRoomTypeStore = defineStore('roomType', {
  state: () => ({
    roomTypes: [] as RoomType[],
    selectedRoomType: null as RoomType | null,
    filters: { ...DEFAULT_FILTERS },
    loading: false,
    loadingList: false,
    error: null as string | null,
  }),

  actions: {
    async fetchAll(propertyId: number) {
      this.loadingList = true
      this.error = null
      try {
        const params = { property_id: propertyId }
        const res = await apiClient.v1.get('/front-desk/room-types', { params })
        const payload = res.data?.data ?? {}
        const items = Array.isArray(payload.items) ? payload.items : []
        this.roomTypes = items.map(mapRoomTypeApiToRoomType)
      } catch (err) {
        this.error = getErrorMessage(err)
      } finally {
        this.loadingList = false
      }
    },

    async create(dto: CreateRoomTypeDto): Promise<RoomType> {
      this.loading = true
      this.error = null
      try {
        const res = await apiClient.v1.post('/front-desk/room-types', mapCreateRoomTypeToApi(dto))
        const roomType = mapRoomTypeApiToRoomType(res.data.data)
        this.roomTypes.unshift(roomType)
        return roomType
      } catch (err) {
        this.error = getErrorMessage(err)
        throw err
      } finally {
        this.loading = false
      }
    },

    async generateRooms(id: number, quantity: number, startNumber: string) {
      await apiClient.v1.post(`/front-desk/room-types/${id}/generate`, {
        quantity,
        start_number: startNumber,
      })
      await this.fetchAll(this.roomTypes[0]?.propertyId)
    },
  },
})
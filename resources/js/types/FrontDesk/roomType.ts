export interface RoomType {
  id: number
  propertyId: number
  name: string
  code: string
  type: 'room' | 'suite' | 'cottage' | 'villa' | 'dormitory'
  floor: string | null
  maxOccupancy: number
  adultOccupancy: number
  numBedrooms: number
  numBathrooms: number
  areaSqm: number | null
  bedTypes: string[] | null
  baseRate: number
  amenities: string[] | null
  isActive: boolean
  roomCount: number
  createdAt: string
}

export interface RoomTypeFilters {
  search: string
  perPage: number
}

export interface CreateRoomTypeDto {
  propertyId: number
  name: string
  code: string
  type: string
  floor?: string
  maxOccupancy: number
  adultOccupancy: number
  numBedrooms: number
  numBathrooms: number
  areaSqm?: number
  bedTypes?: string[]
  baseRate: number
  amenities?: string[]
  roomQuantity?: number
  startNumber?: string
}
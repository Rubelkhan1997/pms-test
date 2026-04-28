import type { RoomType, CreateRoomTypeDto } from '@/Types/FrontDesk/roomType'

export function mapRoomTypeApiToRoomType(api: Record<string, unknown>): RoomType {
  return {
    id:            api.id as number,
    propertyId:     api.property_id as number,
    name:         api.name as string,
    code:         api.code as string,
    type:         api.type as RoomType['type'],
    floor:        api.floor as string | null,
    maxOccupancy:   api.max_occupancy as number,
    adultOccupancy: api.adult_occupancy as number,
    numBedrooms:   api.num_bedrooms as number,
    numBathrooms:  api.num_bathrooms as number,
    areaSqm:      api.area_sqm as number | null,
    bedTypes:     api.bed_types as string[] | null,
    baseRate:     api.base_rate as number,
    amenities:   api.amenities as string[] | null,
    isActive:    api.is_active as boolean,
    roomCount:   api.room_count as number,
    createdAt:    api.created_at as string,
  }
}

export function mapCreateRoomTypeToApi(dto: CreateRoomTypeDto): Record<string, unknown> {
  return {
    property_id:    dto.propertyId,
    name:        dto.name,
    code:        dto.code,
    type:        dto.type,
    floor:       dto.floor,
    max_occupancy:  dto.maxOccupancy,
    adult_occupancy: dto.adultOccupancy,
    num_bedrooms:  dto.numBedrooms,
    num_bathrooms: dto.numBathrooms,
    area_sqm:     dto.areaSqm,
    bed_types:    dto.bedTypes,
    base_rate:   dto.baseRate,
    amenities:  dto.amenities,
    room_quantity: dto.roomQuantity,
    start_number:  dto.startNumber,
  }
}
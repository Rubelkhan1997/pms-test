import type { Property, CreatePropertyDto } from '@/Types/FrontDesk/property'

export function mapPropertyApiToProperty(api: Record<string, unknown>): Property {
  return {
    id:           api.id as number,
    name:         api.name as string,
    slug:         api.slug as string,
    type:         api.type as Property['type'],
    description:  api.description as string | null,
    logoPath:     api.logo_path as string | null,
    featuredImagePath: api.featured_image_path as string | null,
    numberOfRooms: api.number_of_rooms as number,
    country:     api.country as string | null,
    state:       api.state as string | null,
    city:        api.city as string | null,
    area:        api.area as string | null,
    street:      api.street as string | null,
    postalCode:  api.postal_code as string | null,
    phone:       api.phone as string | null,
    email:       api.email as string | null,
    timezone:    api.timezone as string,
    currency:   api.currency as string,
    checkInTime:  api.check_in_time as string | null,
    checkOutTime: api.check_out_time as string | null,
    status:      api.status as Property['status'],
    businessDate: api.business_date as string | null,
    createdAt:    api.created_at as string,
  }
}

export function mapCreatePropertyToApi(dto: CreatePropertyDto): Record<string, unknown> {
  return {
    name:         dto.name,
    slug:        dto.slug,
    type:       dto.type,
    description: dto.description,
    phone:      dto.phone,
    email:      dto.email,
    country:    dto.country,
    state:     dto.state,
    city:      dto.city,
    area:      dto.area,
    street:    dto.street,
    postal_code: dto.postalCode,
    timezone:  dto.timezone,
    currency:  dto.currency,
  }
}
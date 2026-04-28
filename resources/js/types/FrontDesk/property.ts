export interface Property {
  id: number
  name: string
  slug: string
  type: 'hotel' | 'resort' | 'apartment' | 'villa' | 'hostel'
  description: string | null
  logoPath: string | null
  featuredImagePath: string | null
  numberOfRooms: number
  country: string | null
  state: string | null
  city: string | null
  area: string | null
  street: string | null
  postalCode: string | null
  phone: string | null
  email: string | null
  timezone: string
  currency: string
  checkInTime: string | null
  checkOutTime: string | null
  status: 'open' | 'closed'
  businessDate: string | null
  createdAt: string
}

export interface PropertyFilters {
  search: string
  perPage: number
}

export interface PropertyPagination {
  currentPage: number
  perPage: number
  total: number
  lastPage: number
}

export interface CreatePropertyDto {
  name: string
  slug: string
  type: string
  description?: string
  phone?: string
  email?: string
  country?: string
  state?: string
  city?: string
  area?: string
  street?: string
  postalCode?: string
  timezone?: string
  currency?: string
}
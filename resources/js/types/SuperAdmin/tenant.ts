export interface Tenant {
  id: number
  name: string
  slug: string
  domain: string
  database: string
  status: 'pending' | 'active' | 'trial' | 'suspended' | 'cancelled'
  isActive: boolean
  isOnTrial: boolean
  trialEndsAt: string | null
  contactName: string | null
  contactEmail: string | null
  contactPhone: string | null
  planId: number | null
  createdAt: string
}

export interface TenantFilters {
  status: string
  search: string
  perPage: number
}

export interface TenantPagination {
  currentPage: number
  perPage: number
  total: number
  lastPage: number
}

export interface CreateTenantDto {
  name: string
  domain: string
  status: string
  contactName: string
  contactEmail: string
  contactPhone: string
  planId: number | null
}
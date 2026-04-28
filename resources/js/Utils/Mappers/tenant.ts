import type { Tenant, CreateTenantDto, TenantPagination } from '@/Types/SuperAdmin/tenant'

export function mapTenantApiToTenant(api: Record<string, any>): Tenant {
  return {
    id:           api.id,
    name:         api.name,
    slug:         api.slug,
    domain:       api.domain,
    database:     api.database,
    status:       api.status,
    isActive:     api.is_active,
    isOnTrial:    api.is_on_trial,
    trialEndsAt:  api.trial_ends_at,
    contactName:  api.contact_name,
    contactEmail: api.contact_email,
    contactPhone: api.contact_phone,
    planId:       api.plan_id,
    createdAt:    api.created_at,
  }
}

export function mapCreateTenantToApi(dto: CreateTenantDto): Record<string, any> {
  return {
    name:          dto.name,
    domain:        dto.domain,
    status:        dto.status,
    contact_name:  dto.contactName,
    contact_email: dto.contactEmail,
    contact_phone: dto.contactPhone,
    plan_id:       dto.planId,
  }
}

export function mapTenantPaginationApiToPagination(api: Record<string, any>): TenantPagination {
  return {
    currentPage: api.current_page,
    perPage:     api.per_page,
    total:       api.total,
    lastPage:    api.last_page,
  }
}
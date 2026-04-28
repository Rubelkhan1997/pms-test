import type { SubscriptionPlan, CreateSubscriptionPlanDto, SubscriptionPlanPagination } from '@/Types/SuperAdmin/subscriptionPlan'

export function mapSubscriptionPlanApiToPlan(api: Record<string, any>): SubscriptionPlan {
  return {
    id:               api.id,
    name:             api.name,
    slug:             api.slug,
    propertyLimit:   api.property_limit,
    roomLimit:       api.room_limit,
    priceMonthly:    api.price_monthly,
    priceAnnual:     api.price_annual,
    trialEnabled:    api.trial_enabled,
    trialDays:      api.trial_days,
    modulesIncluded: api.modules_included,
    isActive:        api.is_active,
    createdAt:       api.created_at,
  }
}

export function mapCreateSubscriptionPlanToApi(dto: CreateSubscriptionPlanDto): Record<string, any> {
  return {
    name:             dto.name,
    slug:             dto.slug,
    property_limit:   dto.propertyLimit,
    room_limit:       dto.roomLimit,
    price_monthly:    dto.priceMonthly,
    price_annual:     dto.priceAnnual,
    trial_enabled:    dto.trialEnabled,
    trial_days:      dto.trialDays,
    modules_included: dto.modulesIncluded,
    is_active:        dto.isActive,
  }
}

export function mapSubscriptionPlanPaginationApiToPagination(api: Record<string, any>): SubscriptionPlanPagination {
  return {
    currentPage: api.current_page,
    perPage:     api.per_page,
    total:       api.total,
    lastPage:    api.last_page,
  }
}
export interface SubscriptionPlan {
  id: number
  name: string
  slug: string
  propertyLimit: number
  roomLimit: number
  priceMonthly: number
  priceAnnual: number
  trialEnabled: boolean
  trialDays: number
  modulesIncluded: string[]
  isActive: boolean
  createdAt: string
}

export interface SubscriptionPlanFilters {
  search: string
  isActive: boolean
  perPage: number
}

export interface SubscriptionPlanPagination {
  currentPage: number
  perPage: number
  total: number
  lastPage: number
}

export interface CreateSubscriptionPlanDto {
  name: string
  slug: string
  propertyLimit: number
  roomLimit: number
  priceMonthly: number
  priceAnnual: number
  trialEnabled: boolean
  trialDays: number
  modulesIncluded: string[]
  isActive: boolean
}
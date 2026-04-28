import { useSubscriptionPlansStore } from '@/Stores/SuperAdmin/subscriptionPlanStore'

export function useSubscriptionPlans() {
  const store = useSubscriptionPlansStore()

  return {
    ...store,
  }
}
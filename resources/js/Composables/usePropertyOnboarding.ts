import { propertyOnboardingSteps } from '@/Pages/Partner/Onboarding/steps'
import { router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'


export function usePropertyOnboarding() {
    const page = usePage()
    const loading = ref(false)
    const steps = propertyOnboardingSteps

  
    const currentStep = computed(() =>
        steps.findIndex(step => step.route === page.url)
    )

    const nextStep = () => {
        const next = steps[currentStep.value + 1]
        if (next) router.visit(next.route)
    }

    const prevStep = () => {
        const prev = steps[currentStep.value - 1]
        if (prev) router.visit(prev.route)
    }

    return {
        steps,
        currentStep,
        loading,
        nextStep,
        prevStep,
    }
}
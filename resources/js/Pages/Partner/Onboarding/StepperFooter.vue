// FILE: resources/js/Components/Onboarding/StepperFooter.vue
<script setup lang="ts">
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { ChevronLeft, ArrowRight, SkipForward } from 'lucide-vue-next'

const props = withDefaults(defineProps<{
    steps: { label: string; route: string }[]
    currentStep: number
    loading?: boolean
    skippable?: boolean
}>(), {
    loading: false,
    skippable: false,
})

const emit = defineEmits<{
    submit: []
}>()

const isFirst = computed(() => props.currentStep === 0)
const isLast  = computed(() => props.currentStep === props.steps.length - 1)

function back() {
    router.visit(props.steps[props.currentStep - 1].route)
}

function next() {
    router.visit(props.steps[props.currentStep + 1].route)
}

function skip() {
    router.visit(props.steps[props.currentStep + 1].route)
}
</script>

<template>
    <div class="flex items-center sticky px-4 py-5 -bottom-7 justify-between  border-gray-200 rounded-md bg-white mt-6">
        <!-- Back -->
        <button
            type="button"
            :class="[
                'pl-3 pr-4 bg-gray-200 py-2 flex items-center gap-1 border border-gray-200 rounded-[7px] cursor-pointer text-sm text-gray-600 hover:bg-gray-50 transition-colors',
                { invisible: isFirst }
            ]"
            @click="back"
        >
            <ChevronLeft class="w-4 h-4" />
            Back
        </button>

        <!-- Right side -->
        <div class="flex items-center gap-3">

            <!-- Skip (optional) -->
            <button
                v-if="skippable && !isLast"
                type="button"
                class="px-4 py-2 flex items-center gap-1.5 text-sm text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
                @click="skip"
            >
                Skip
                <SkipForward class="w-3.5 h-3.5" />
            </button>

            <!-- Continue -->
            <button
                v-if="!isLast"
                type="button"
                class="px-5 py-2 flex cursor-pointer items-center gap-1 group bg-primary text-white rounded-[7px] text-sm font-medium hover:bg-secondary transition-colors"
                @click="next"
            >
                Continue
                <ArrowRight class="w-4 group-hover:translate-x-0.5 transition-all h-4" />
            </button>

            <!-- Finish -->
            <button
                v-else
                type="button"
                :disabled="loading"
                class="px-5 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-700 disabled:opacity-50 transition-colors"
                @click="emit('submit')"
            >
                {{ loading ? 'Saving…' : 'Finish Setup' }}
            </button>

        </div>
    </div>
</template>
// FILE: resources/js/Pages/Partner/Onboarding/policies/Create.vue
<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { Plus, FileText, Trash2, X } from 'lucide-vue-next'
import {
    Drawer,
    DrawerContent,
    DrawerHeader,
    DrawerTitle,
    DrawerDescription,
    DrawerClose,
} from '@/Components/ui/drawer'
import StepperFooter from '../StepperFooter.vue';
import { propertyOnboardingSteps } from '../steps';
import { usePropertyOnboarding } from '@/Composables/usePropertyOnboarding';

defineProps<{
    headerTitle: string
}>()

// ─── Types ────────────────────────────────────────────────────
interface CancellationPolicy {
    id: number
    guestCanCancel: boolean
    daysBefore: string
    cancellationFee: string
    noShowFee: string
}
const { steps, currentStep, loading, handleSubmit } = usePropertyOnboarding()
// ─── Tabs ─────────────────────────────────────────────────────
const activeTab = ref<'cancellation' | 'other'>('cancellation')

// ─── Drawer ───────────────────────────────────────────────────
const drawerOpen = ref(false)

// ─── Form state ───────────────────────────────────────────────
const form = ref({
    guestCanCancel: true,
    daysBefore: '',
    cancellationFee: '',
    noShowFee: '',
})

const errors = ref({
    daysBefore: false,
    cancellationFee: false,
})

const daysBeforeOptions = [
    { value: '1', label: 'Until 1 day before arrival' },
    { value: '2', label: 'Until 2 days before arrival' },
    { value: '3', label: 'Until 3 days before arrival' },
    { value: '7', label: 'Until 7 days before arrival' },
    { value: '14', label: 'Until 14 days before arrival' },
    { value: '30', label: 'Until 30 days before arrival' },
]

const noShowOptions = [
    { value: 'same', label: 'Same as the cancellation fee' },
    { value: 'first_night', label: 'The cost of the first night' },
    { value: '50_percent', label: '50% of the total price' },
    { value: '100_percent', label: '100% of the total price' },
]

// const steps = propertyOnboardingSteps
// const currentStep = 2
// const loading = ref(false)

// function handleSubmit() {
//     loading.value = true
//     // submit logic
// }
// ─── Policies list ────────────────────────────────────────────
const cancellationPolicies = ref<CancellationPolicy[]>([])

// ─── Actions ──────────────────────────────────────────────────
function openDrawer() {
    form.value = { guestCanCancel: true, daysBefore: '', cancellationFee: '', noShowFee: '' }
    errors.value = { daysBefore: false, cancellationFee: false }
    drawerOpen.value = true
}

function validate(): boolean {
    errors.value.daysBefore = form.value.guestCanCancel && !form.value.daysBefore
    errors.value.cancellationFee = !form.value.cancellationFee
    return !errors.value.daysBefore && !errors.value.cancellationFee
}

function handleCreate() {
    if (!validate()) return

    cancellationPolicies.value.push({
        id: Date.now(),
        guestCanCancel: form.value.guestCanCancel,
        daysBefore: form.value.daysBefore,
        cancellationFee: form.value.cancellationFee,
        noShowFee: form.value.noShowFee,
    })

    drawerOpen.value = false
}

function deletePolicy(id: number) {
    cancellationPolicies.value = cancellationPolicies.value.filter(p => p.id !== id)
}

function getDaysLabel(value: string): string {
    return daysBeforeOptions.find(o => o.value === value)?.label ?? value
}

function getNoShowLabel(value: string): string {
    return noShowOptions.find(o => o.value === value)?.label ?? value
}

function getFeeLabel(value: string): string {
    const map: Record<string, string> = {
        first_night: 'The cost of the first night',
        '50_percent': '50% of the total price',
        '100_percent': '100% of the total price',
    }
    return map[value] ?? value
}
</script>

<template>

    <div class="space-y-5">

        <!-- ── Header row ── -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-[15px] font-semibold text-slate-800">Policies</h2>
                <p class="text-sm text-slate-400 mt-0.5">Manage your property policies for guests.</p>
            </div>
        </div>

        <!-- ── Tabs ── -->
        <div class="border-b border-gray-200">
            <nav class="flex gap-0" aria-label="Policy tabs">
                <button @click="activeTab = 'cancellation'" :class="[
                    'px-5 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px',
                    activeTab === 'cancellation'
                        ? 'border-primary text-primary'
                        : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'
                ]">
                    Cancellation Policy
                </button>
                <button @click="activeTab = 'other'" :class="[
                    'px-5 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px',
                    activeTab === 'other'
                        ? 'border-primary text-primary'
                        : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'
                ]">
                    Other Policy
                </button>
            </nav>
        </div>

        <!-- ── Cancellation Tab ── -->
        <div v-if="activeTab === 'cancellation'">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm text-slate-500">Define how guests can cancel their bookings.</p>
                <button @click="openDrawer"
                    class="flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-[7px] hover:bg-secondary transition-colors cursor-pointer">
                    <Plus class="w-4 h-4" />
                    Create Policy
                </button>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
                <!-- Empty state -->
                <div v-if="cancellationPolicies.length === 0"
                    class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mb-3">
                        <FileText class="w-5 h-5 text-slate-400" />
                    </div>
                    <p class="text-sm font-medium text-slate-600">No cancellation policies yet</p>
                    <p class="text-xs text-slate-400 mt-1">Click "Create Policy" to add your first one.</p>
                </div>

                <!-- Policy rows -->
                <div v-for="policy in cancellationPolicies" :key="policy.id"
                    class="flex items-start justify-between gap-4 px-5 py-4 hover:bg-slate-50 transition-colors">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                            <FileText class="w-4 h-4 text-primary" />
                        </div>
                        <div class="space-y-0.5">
                            <p class="text-sm font-medium text-slate-800">Cancellation Policy</p>
                            <p class="text-xs text-slate-400">
                                Guest can cancel:
                                <span class="text-slate-600 font-medium">{{ policy.guestCanCancel ? 'Yes' : 'No'
                                }}</span>
                            </p>
                            <p v-if="policy.guestCanCancel" class="text-xs text-slate-400">
                                {{ getDaysLabel(policy.daysBefore) }}
                            </p>
                            <p class="text-xs text-slate-400">
                                Fee: <span class="text-slate-600 font-medium">{{ getFeeLabel(policy.cancellationFee)
                                }}</span>
                            </p>
                            <p v-if="policy.noShowFee" class="text-xs text-slate-400">
                                No-show: <span class="text-slate-600 font-medium">{{ getNoShowLabel(policy.noShowFee)
                                }}</span>
                            </p>
                        </div>
                    </div>

                    <button @click="deletePolicy(policy.id)"
                        class="p-1.5 rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 transition-colors cursor-pointer shrink-0"
                        title="Delete policy">
                        <Trash2 class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Other Policy Tab ── -->
        <div v-if="activeTab === 'other'">
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mb-3">
                        <FileText class="w-5 h-5 text-slate-400" />
                    </div>
                    <p class="text-sm font-medium text-slate-600">No other policies yet</p>
                    <p class="text-xs text-slate-400 mt-1">Other policy options coming soon.</p>
                </div>
            </div>
        </div>
         <StepperFooter
        :steps="steps"
        :current-step="currentStep"
        :loading="loading"
        :skippable="true"
        @submit="handleSubmit"
    />
    </div>

    <!-- ══════════════════════════════════════════
             DRAWER — Create New Cancellation Policy
        ══════════════════════════════════════════════ -->
    <Drawer v-model:open="drawerOpen" direction="right">
        <DrawerContent class="w-105! max-w-105! h-full ml-auto rounded-l-2xl rounded-r-none flex flex-col">

            <!-- Header -->
            <DrawerHeader class="border-b border-gray-100 pb-4 flex-shrink-0">
                <div class="flex items-start justify-between">
                    <div>
                        <DrawerTitle class="text-[15px] font-semibold text-slate-800">
                            Create New Cancellation Policy
                        </DrawerTitle>
                        <DrawerDescription class="text-sm text-slate-400 mt-0.5">
                            Can the guest cancel without a fee during a specific time?
                        </DrawerDescription>
                    </div>
                    <DrawerClose as-child>
                        <button
                            class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors cursor-pointer">
                            <X class="w-4 h-4" />
                        </button>
                    </DrawerClose>
                </div>
            </DrawerHeader>

            <!-- Body -->
            <!-- Body -->
            <div class="flex-1 overflow-y-auto px-6 py-5 space-y-6">

                <!-- Q1: Can guest cancel? -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700 block mb-2">
                        Can the guest cancel without a fee during a specific time?
                    </label>
                    <FormRadio name="guestCanCancel" label="Yes" :value="true" v-model="form.guestCanCancel" />
                    <FormRadio name="guestCanCancel" label="No" :value="false" v-model="form.guestCanCancel" />
                </div>

                <!-- Q2: Days before (only if yes) -->
                <div v-if="form.guestCanCancel" class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700">
                        How many days before arrival?
                    </label>
                    <select v-model="form.daysBefore" :class="[
                        'w-full px-3 py-2 rounded-[7px] border text-sm text-slate-700 bg-white appearance-none outline-none transition-colors',
                        errors.daysBefore
                            ? 'border-red-400 focus:border-red-400'
                            : 'border-gray-200 focus:border-primary'
                    ]">
                        <option value="" disabled>Select days</option>
                        <option v-for="opt in daysBeforeOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <p v-if="errors.daysBefore" class="text-xs text-red-500">
                        Please specify how many days before check-in cancellation is allowed.
                    </p>
                </div>

                <!-- Q3: Cancellation fee -->
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700 block mb-2">
                        What is the cancellation fee for guests within of arrival?
                    </label>
                    <FormRadio name="cancellationFee" label="The cost of the first night" value="first_night"
                        v-model="form.cancellationFee" />
                    <FormRadio name="cancellationFee" label="50% of the total price" value="50_percent"
                        v-model="form.cancellationFee" />
                    <FormRadio name="cancellationFee" label="100% of the total price" value="100_percent"
                        v-model="form.cancellationFee" />
                    <p v-if="errors.cancellationFee" class="text-xs text-red-500">
                        The cancellation fee field is required.
                    </p>
                </div>

                <!-- Q4: No-show fee -->
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700">
                        What is the fee for a no-show?
                    </label>
                    <select v-model="form.noShowFee"
                        class="w-full px-3 py-2 rounded-[7px] border border-gray-200 focus:border-primary text-sm text-slate-700 bg-white appearance-none outline-none transition-colors">
                        <option value="" disabled>Select option</option>
                        <option v-for="opt in noShowOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                </div>

            </div>

            <!-- Footer -->
            <div class="flex-shrink-0 border-t border-gray-100 px-6 py-4 flex items-center justify-end gap-3">
                <DrawerClose as-child>
                    <button
                        class="px-4 py-2 text-sm text-slate-600 border border-gray-200 rounded-[7px] hover:bg-slate-50 transition-colors cursor-pointer">
                        Cancel
                    </button>
                </DrawerClose>
                <button @click="handleCreate"
                    class="px-5 py-2 bg-primary text-white text-sm font-medium rounded-[7px] hover:bg-secondary transition-colors cursor-pointer">
                    Create
                </button>
            </div>

        </DrawerContent>
    </Drawer>
</template>
<template>
    <div class="space-y-5">

        <!-- Page Header -->
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-[21px] font-semibold text-slate-800 tracking-tight leading-none mb-1.5">
                    Payment Methods
                </h1>
                <p class="text-[14px] text-slate-500  leading-relaxed">
                    Payment methods allow you to define how your property accepts payments. HotelRunner is not a payment
                    provider, so you need to obtain your payment details from your bank account to connect. The payment
                    type cannot be activated until this information is entered.
                </p>
            </div>
        </div>

   

        <!-- Table Card -->
        <div class="bg-white rounded-xl border border-slate-100 overflow-hidden">

            <!-- Toolbar -->
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 gap-3 flex-wrap">
                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Search -->
                    <div class="relative">
                        <Search
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-300 pointer-events-none"
                            :stroke-width="2" />
                        <input v-model="search" type="text" placeholder="Search by name"
                            class="pl-9 pr-4 py-2 text-[13px] bg-slate-50 border border-slate-300 rounded-md w-52 focus:outline-none focus:ring-0 focus:border-primary transition-all placeholder:text-slate-400" />
                    </div>

                   
                </div>

               
            </div>

            <!-- Table -->
            <table class="w-full table-content">
                <thead>
                    <tr class="bg-slate-50/70">
                       
                        <th class="cursor-pointer hover:text-slate-600 select-none"
                            @click="sort('name')">
                            <span class="flex items-center gap-1">
                                Name
                                <ChevronsUpDown class="w-3 h-3" :stroke-width="2" />
                            </span>
                        </th>
                        <th class="cursor-pointer hover:text-slate-600 select-none"
                            @click="sort('visibility')">
                            <span class="flex items-center gap-1">
                                Visibility
                                <ChevronsUpDown class="w-3 h-3" :stroke-width="2" />
                            </span>
                        </th>
                        <th class="cursor-pointer hover:text-slate-600 select-none"
                            @click="sort('provider')">
                            <span class="flex items-center gap-1">
                                Provider
                                <ChevronsUpDown class="w-3 h-3" :stroke-width="2" />
                            </span>
                        </th>
                        <th class="cursor-pointer hover:text-slate-600 select-none"
                            @click="sort('status')">
                            <span class="flex items-center gap-1">
                                Status
                                <ChevronsUpDown class="w-3 h-3" :stroke-width="2" />
                            </span>
                        </th>
                        <th class="text-right">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="method in filteredMethods" :key="method.id"
                        class="border-t border-slate-50 hover:bg-slate-50/50 transition-colors">
                        <!-- Name -->
                        <td class="px-4 py-3.5">
                            <span class="text-[14px] font-semibold text-slate-700">{{ method.name }}</span>
                        </td>

                        <!-- Visibility -->
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span v-for="v in method.visibility" :key="v"
                                    class="inline-block text-[11px] font-medium px-2 py-0.5 rounded"
                                    :class="{
                                        'bg-sky-100 text-sky-700': v === 'Guest',
                                        'bg-violet-100 text-violet-700': v === 'Property',
                                        'bg-emerald-100 text-emerald-700': v === 'OTA',
                                    }">
                                    {{ v }}
                                </span>
                            </div>
                        </td>

                        <!-- Provider -->
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-1.5">
                                <span class="text-[14px] text-slate-500">{{ method.provider }}</span>
                                <Info class="w-3.5 h-3.5 text-slate-300 cursor-pointer hover:text-slate-500 transition-colors"
                                    :stroke-width="1.8" />
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center gap-1.5 text-[14px] font-medium"
                                :class="method.status === 'Active' ? 'text-emerald-600' : 'text-slate-400'">
                                <span class="w-1.5 h-1.5 rounded-full"
                                    :class="method.status === 'Active' ? 'bg-emerald-500' : 'bg-slate-300'" />
                                {{ method.status }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="px-4 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                <button @click="openDialog(method)"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-cyan-50 hover:text-cyan-600 transition-colors"
                                    title="Edit">
                                    <Pencil class="w-3.5 h-3.5" :stroke-width="1.8" />
                                </button>
                                <button
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-violet-50 hover:text-violet-600 transition-colors"
                                    title="Details">
                                    <Info class="w-3.5 h-3.5" :stroke-width="1.8" />
                                </button>
                                <button @click="deleteMethod(method)"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-500 transition-colors"
                                    title="Delete">
                                    <Trash2 class="w-3.5 h-3.5" :stroke-width="1.8" />
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Empty state -->
                    <tr v-if="filteredMethods.length === 0">
                        <td colspan="6" class="px-5 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center">
                                    <SearchX class="w-5 h-5 text-slate-300" :stroke-width="1.5" />
                                </div>
                                <p class="text-[13px] text-slate-400">No payment methods found</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

         
        </div>
        <StepperFooter :steps="steps" :current-step="currentStep" :loading="loading" :skippable="true"
            @submit="handleSubmit" />

        <!-- Add/Edit Dialog -->
        <!-- <PaymentDialog v-model:open="dialogOpen" :method="selectedMethod" @saved="onSaved" /> -->
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Search, Pencil, Info, Trash2, SearchX, ChevronsUpDown } from 'lucide-vue-next'
import { propertyOnboardingSteps } from '../steps'
import StepperFooter from '../StepperFooter.vue'

// ─── Types ───────────────────────────────────────────────
interface PaymentMethod {
    id: number
    name: string
    visibility: string[]
    provider: string
    status: 'Active' | 'Inactive'
}

// ─── Stepper ─────────────────────────────────────────────
const steps       = propertyOnboardingSteps
const currentStep = 3
const loading     = ref(false)

// ─── State ───────────────────────────────────────────────
const search     = ref('')
const sortKey    = ref<keyof PaymentMethod>('name')
const sortDir    = ref<'asc' | 'desc'>('asc')
const dialogOpen = ref(false)
const selectedMethod = ref<PaymentMethod | null>(null)

// ─── Data (replace with API call) ────────────────────────
const methods = ref<PaymentMethod[]>([
    { id: 1, name: 'Cash',          visibility: ['Guest', 'Property'], provider: 'cash',          status: 'Active'   },
    { id: 2, name: 'Credit Card',   visibility: ['Guest'],             provider: 'stripe',        status: 'Active'   },
    { id: 3, name: 'Bank Transfer', visibility: ['Property'],          provider: 'bank_transfer', status: 'Inactive' },
    { id: 4, name: 'PayPal',        visibility: ['Guest', 'OTA'],      provider: 'paypal',        status: 'Active'   },
])


// ─── Filtering & Sorting ─────────────────────────────────
const filteredMethods = computed(() => {
    let list = methods.value.filter(m =>
        !search.value ||
        m.name.toLowerCase().includes(search.value.toLowerCase()) ||
        m.provider.toLowerCase().includes(search.value.toLowerCase())
    )

    return list.sort((a, b) => {
        const av = String(a[sortKey.value])
        const bv = String(b[sortKey.value])
        return sortDir.value === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av)
    })
})

function sort(key: keyof PaymentMethod) {
    sortDir.value = sortKey.value === key && sortDir.value === 'asc' ? 'desc' : 'asc'
    sortKey.value = key
}

// ─── Dialog ──────────────────────────────────────────────
function openDialog(method?: PaymentMethod) {
    selectedMethod.value = method ?? null
    dialogOpen.value = true
}

// ─── CRUD ────────────────────────────────────────────────
async function onSaved(data: Partial<PaymentMethod>) {
    if (selectedMethod.value) {
       
        const idx = methods.value.findIndex(m => m.id === selectedMethod.value!.id)
        if (idx !== -1) methods.value[idx] = { ...methods.value[idx], ...data }
    } else {
        
        methods.value.push({ id: Date.now(), ...data } as PaymentMethod)
    }
    dialogOpen.value = false
}

async function deleteMethod(method: PaymentMethod) {
    /*
        TODO: await axios.delete(`/api/payment-methods/${method.id}`)
    */
    methods.value = methods.value.filter(m => m.id !== method.id)
}

// ─── Stepper Submit ──────────────────────────────────────
async function handleSubmit() {
    loading.value = true
    /*
        TODO: submit/save logic
        await axios.post('/api/onboarding/payment-methods/complete')
    */
    loading.value = false
}
</script>
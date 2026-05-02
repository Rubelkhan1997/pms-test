<template>
    <div class="space-y-5">

        <!-- Page Header -->
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-[22px] font-semibold text-slate-800 tracking-tight leading-none mb-1.5">
                    Payment Methods
                </h1>
                <p class="text-[13px] text-slate-400 max-w-2xl leading-relaxed">
                    Payment methods allow you to define how your property accepts payments. HotelRunner is not a payment
                    provider, so you need to obtain your payment details from your bank account to connect. The payment
                    type cannot be activated until this information is entered.
                </p>
            </div>
            <button @click="openDialog()"
                class="flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-secondary text-white text-[13px] font-medium rounded-md transition-colors shrink-0 shadow-sm">
                <Plus class="w-4 h-4" :stroke-width="2.5" />
                Add Payment Method
            </button>
        </div>

        <!-- Alert Banner -->
        <div class="flex items-start gap-3 px-4 py-3 bg-amber-50 border border-amber-200 rounded-lg">
            <AlertTriangle class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" :stroke-width="2" />
            <p class="text-[13px] text-amber-700 leading-relaxed">
                Bank transfer and mail order payments cannot be tracked through HotelRunner; you should follow them
                through your bank account.
            </p>
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
                            class="pl-9 pr-4 py-2 text-[13px] bg-slate-50 border border-slate-300 rounded-md w-48 focus:outline-none focus:ring-0 focus:border-primary transition-all placeholder:text-slate-400" />
                    </div>

                    <!-- Filter Buttons -->
                    <button @click="toggleFilter('visibility')"
                        class="flex items-center gap-1.5 px-3 py-2 text-[13px] border rounded-md transition-colors"
                        :class="activeFilters.visibility
                            ? 'bg-primary/10 border-primary text-primary'
                            : 'bg-slate-50 border-slate-300 text-slate-500 hover:border-slate-400'">
                        <Plus class="w-3 h-3" :stroke-width="2.5" />
                        Visibility
                    </button>
                    <button @click="toggleFilter('provider')"
                        class="flex items-center gap-1.5 px-3 py-2 text-[13px] border rounded-md transition-colors"
                        :class="activeFilters.provider
                            ? 'bg-primary/10 border-primary text-primary'
                            : 'bg-slate-50 border-slate-300 text-slate-500 hover:border-slate-400'">
                        <Plus class="w-3 h-3" :stroke-width="2.5" />
                        Provider
                    </button>
                    <button @click="toggleFilter('status')"
                        class="flex items-center gap-1.5 px-3 py-2 text-[13px] border rounded-md transition-colors"
                        :class="activeFilters.status
                            ? 'bg-primary/10 border-primary text-primary'
                            : 'bg-slate-50 border-slate-300 text-slate-500 hover:border-slate-400'">
                        <Plus class="w-3 h-3" :stroke-width="2.5" />
                        Status
                    </button>
                </div>

                <!-- Right Icons -->
                <div class="flex items-center gap-1.5">
                    <button
                        class="w-8 h-8 flex items-center justify-center rounded-md border border-slate-200 text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors">
                        <Printer class="w-3.5 h-3.5" :stroke-width="1.8" />
                    </button>
                    <button
                        class="w-8 h-8 flex items-center justify-center rounded-md border border-slate-200 text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors">
                        <Download class="w-3.5 h-3.5" :stroke-width="1.8" />
                    </button>
                    <button
                        class="w-8 h-8 flex items-center justify-center rounded-md border border-slate-200 text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors">
                        <SlidersHorizontal class="w-3.5 h-3.5" :stroke-width="1.8" />
                    </button>
                </div>
            </div>

            <!-- Table -->
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50/70">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-10">
                            <input type="checkbox" v-model="selectAll" class="accent-primary w-3.5 h-3.5 cursor-pointer" />
                        </th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider cursor-pointer hover:text-slate-600 select-none"
                            @click="sort('name')">
                            <span class="flex items-center gap-1">
                                Name
                                <ChevronsUpDown class="w-3 h-3" :stroke-width="2" />
                            </span>
                        </th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider cursor-pointer hover:text-slate-600 select-none"
                            @click="sort('visibility')">
                            <span class="flex items-center gap-1">
                                Visibility
                                <ChevronsUpDown class="w-3 h-3" :stroke-width="2" />
                            </span>
                        </th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider cursor-pointer hover:text-slate-600 select-none"
                            @click="sort('provider')">
                            <span class="flex items-center gap-1">
                                Provider
                                <ChevronsUpDown class="w-3 h-3" :stroke-width="2" />
                            </span>
                        </th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider cursor-pointer hover:text-slate-600 select-none"
                            @click="sort('status')">
                            <span class="flex items-center gap-1">
                                Status
                                <ChevronsUpDown class="w-3 h-3" :stroke-width="2" />
                            </span>
                        </th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="method in paginatedMethods" :key="method.id"
                        class="border-t border-slate-50 hover:bg-slate-50/50 transition-colors">

                        <!-- Checkbox -->
                        <td class="px-5 py-3.5">
                            <input type="checkbox" v-model="selected" :value="method.id"
                                class="accent-primary w-3.5 h-3.5 cursor-pointer" />
                        </td>

                        <!-- Name -->
                        <td class="px-4 py-3.5">
                            <span class="text-[13px] font-semibold text-slate-700">{{ method.name }}</span>
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
                                <span class="text-[13px] text-slate-500">{{ method.provider }}</span>
                                <Info class="w-3.5 h-3.5 text-slate-300 cursor-pointer hover:text-slate-500 transition-colors"
                                    :stroke-width="1.8" />
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center gap-1.5 text-[12px] font-medium"
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
                    <tr v-if="paginatedMethods.length === 0">
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

            <!-- Pagination -->
            <div class="flex items-center justify-between px-5 py-3 border-t border-slate-100">
                <p class="text-[12px] text-slate-400">
                    {{ paginationInfo }}
                </p>
                <div class="flex items-center gap-2">
                    <button @click="currentPage--" :disabled="currentPage === 1"
                        class="w-7 h-7 flex items-center justify-center rounded-md border border-slate-200 text-slate-400 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                        <ChevronLeft class="w-3.5 h-3.5" :stroke-width="2" />
                    </button>
                    <button v-for="p in totalPages" :key="p" @click="currentPage = p"
                        class="w-7 h-7 flex items-center justify-center rounded-md border text-[12px] font-medium transition-colors"
                        :class="currentPage === p
                            ? 'border-primary text-primary bg-primary/5'
                            : 'border-slate-200 text-slate-500 hover:bg-slate-50'">
                        {{ p }}
                    </button>
                    <button @click="currentPage++" :disabled="currentPage === totalPages"
                        class="w-7 h-7 flex items-center justify-center rounded-md border border-slate-200 text-slate-400 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                        <ChevronRight class="w-3.5 h-3.5" :stroke-width="2" />
                    </button>
                    <select v-model="perPage"
                        class="py-1.5 px-2 text-[12px] border border-slate-200 rounded-md text-slate-500 bg-slate-50 focus:outline-none focus:border-primary cursor-pointer">
                        <option :value="10">10 / page</option>
                        <option :value="20">20 / page</option>
                        <option :value="50">50 / page</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Add/Edit Dialog -->
        <!-- <PaymentDialog v-model:open="dialogOpen" :method="selectedMethod" @saved="onSaved" /> -->
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import {
    Plus, Search, Pencil, Info, Trash2, SearchX,
    AlertTriangle, Printer, Download, SlidersHorizontal,
    ChevronsUpDown, ChevronLeft, ChevronRight
} from 'lucide-vue-next'
// import PaymentDialog from './PaymentDialog.vue'

interface PaymentMethod {
    id: number
    name: string
    visibility: string[]
    provider: string
    status: 'Active' | 'Inactive'
}

const search = ref('')
const dialogOpen = ref(false)
const selectedMethod = ref<PaymentMethod | null>(null)
const selected = ref<number[]>([])
const currentPage = ref(1)
const perPage = ref(20)
const sortKey = ref<keyof PaymentMethod>('name')
const sortDir = ref<'asc' | 'desc'>('asc')

const activeFilters = ref({
    visibility: false,
    provider: false,
    status: false,
})

const methods = ref<PaymentMethod[]>([
    { id: 1, name: 'Cash',          visibility: ['Guest', 'Property'], provider: 'cash',         status: 'Active' },
    { id: 2, name: 'Credit Card',   visibility: ['Guest'],             provider: 'stripe',       status: 'Active' },
    { id: 3, name: 'Bank Transfer', visibility: ['Property'],          provider: 'bank_transfer', status: 'Inactive' },
    { id: 4, name: 'PayPal',        visibility: ['Guest', 'OTA'],      provider: 'paypal',       status: 'Active' },
])

const selectAll = computed({
    get: () => selected.value.length === methods.value.length,
    set: (val) => { selected.value = val ? methods.value.map(m => m.id) : [] }
})

function toggleFilter(key: keyof typeof activeFilters.value) {
    activeFilters.value[key] = !activeFilters.value[key]
}

function sort(key: keyof PaymentMethod) {
    if (sortKey.value === key) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortKey.value = key
        sortDir.value = 'asc'
    }
}

const filteredMethods = computed(() => {
    let list = methods.value.filter(m =>
        !search.value || m.name.toLowerCase().includes(search.value.toLowerCase())
    )
    list = [...list].sort((a, b) => {
        const av = String(a[sortKey.value])
        const bv = String(b[sortKey.value])
        return sortDir.value === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av)
    })
    return list
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredMethods.value.length / perPage.value)))

const paginatedMethods = computed(() => {
    const start = (currentPage.value - 1) * perPage.value
    return filteredMethods.value.slice(start, start + perPage.value)
})

const paginationInfo = computed(() => {
    const total = filteredMethods.value.length
    const start = (currentPage.value - 1) * perPage.value + 1
    const end = Math.min(currentPage.value * perPage.value, total)
    return total === 0 ? '0 items' : `${start}-${end} of ${total} items`
})

function openDialog(method?: PaymentMethod) {
    selectedMethod.value = method ?? null
    dialogOpen.value = true
}

function deleteMethod(method: PaymentMethod) {
    methods.value = methods.value.filter(m => m.id !== method.id)
}

function onSaved(data: Partial<PaymentMethod>) {
    if (selectedMethod.value) {
        const idx = methods.value.findIndex(m => m.id === selectedMethod.value!.id)
        if (idx !== -1) methods.value[idx] = { ...methods.value[idx], ...data }
    } else {
        methods.value.push({ id: Date.now(), ...data } as PaymentMethod)
    }
    dialogOpen.value = false
}
</script>
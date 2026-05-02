<template>
    <div class="space-y-5">

        <!-- Page Header -->
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-[22px] font-semibold text-slate-800 tracking-tight leading-none mb-1.5">
                    Tax
                </h1>
                <p class="text-[13px] text-slate-400 max-w-2xl leading-relaxed">
                    Build adaptive tax structures with flexible percentage, fixed rates, slab-based rates, and exemption
                    rules.
                    Manage and apply them across rooms, extras, and payouts through one unified view.
                </p>
            </div>
            <button @click="openDrawer()"
                class="flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-secondary text-white text-[13px] font-medium rounded-md transition-colors shrink-0 shadow-sm shadow-cyan-200">
                <Plus class="w-4 h-4" :stroke-width="2.5" />
                Add Tax
            </button>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-xl border border-slate-100 overflow-hidden">

            <!-- Toolbar -->
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100">
                <div class="relative">
                    <Search
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-300 pointer-events-none"
                        :stroke-width="2" />
                    <input v-model="search" type="text" placeholder="Search tax..."
                        class="pl-9 pr-4 py-2 text-[13px] bg-slate-50 border border-slate-300 rounded-md w-60 focus:outline-none focus:ring-0  focus:border-primary transition-all placeholder:text-slate-500" />
                </div>
                <div class="flex items-center gap-2">
                    <select v-model="filterApplies"
                        class="py-2 px-3 text-[13px] bg-slate-50 border border-slate-300 rounded-md w-60 focus:outline-none focus:ring-0  focus:border-primary transition-all placeholder:text-slate-500">
                        <option value="">All types</option>
                        <option value="Rooms">Rooms</option>
                        <option value="Rooms + Extras">Rooms + Extras</option>
                        <option value="Extras">Extras</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <table class="w-full table-content">
                <thead>
                    <tr class="bg-slate-50/70">
                        <th
                            class="w-12 px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                        </th>
                        <th class="">
                            Tax Name</th>
                        <th class="">
                            Tax Code</th>
                        <th class="">
                            Rule Type</th>
                        <th class="">
                            Applies To</th>
                        <th class="">
                            Validity</th>
                        <th class="">
                            Exemption</th>
                        <th class="text-right">
                            Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(tax, i) in filteredTaxes" :key="tax.id"
                        class="border-t border-slate-50 hover:bg-slate-50/50 transition-colors group">

                        <!-- Toggle -->
                        <td class="px-5 py-3.5">
                            <button @click="toggleTax(tax)"
                                class="relative inline-flex items-center w-9 h-5 rounded-full transition-colors duration-200 focus:outline-none"
                                :class="tax.enabled ? 'bg-primary' : 'bg-slate-200'">
                                <span
                                    class="inline-block w-3.5 h-3.5 bg-white rounded-full shadow-sm transform transition-transform duration-200"
                                    :class="tax.enabled ? 'translate-x-[18px]' : 'translate-x-[3px]'" />
                            </button>
                        </td>

                        <!-- Tax Name -->
                        <td class="px-4 py-3.5">
                            <span class="text-[13px] font-semibold text-slate-700">{{ tax.name }}</span>
                        </td>

                        <!-- Tax Code -->
                        <td class="px-4 py-3.5">
                            <span
                                class="inline-block text-[11px] font-mono font-medium text-slate-500 bg-slate-100 rounded-md px-2 py-0.5">
                                {{ tax.code }}
                            </span>
                        </td>

                        <!-- Rule Type -->
                        <td class="px-4 py-3.5 max-w-[200px]">
                            <span class="text-[13px] text-slate-500 truncate block" :title="tax.rule">{{ tax.rule
                            }}</span>
                        </td>

                        <!-- Applies To -->
                        <td class="px-4 py-3.5">
                            <span
                                class="inline-flex items-center gap-1.5 text-[11px] font-medium rounded-full px-2.5 py-1"
                                :class="{
                                    'bg-cyan-50 text-cyan-700': tax.appliesTo === 'Rooms',
                                    'bg-violet-50 text-violet-700': tax.appliesTo === 'Rooms + Extras',
                                    'bg-amber-50 text-amber-700': tax.appliesTo === 'Extras',
                                }">
                                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" :class="{
                                    'bg-cyan-500': tax.appliesTo === 'Rooms',
                                    'bg-violet-500': tax.appliesTo === 'Rooms + Extras',
                                    'bg-amber-500': tax.appliesTo === 'Extras',
                                }"></span>
                                {{ tax.appliesTo }}
                            </span>
                        </td>

                        <!-- Validity -->
                        <td class="px-4 py-3.5">
                            <span class="text-[13px] text-slate-500">{{ tax.validity }}</span>
                        </td>

                        <!-- Exemption -->
                        <td class="px-4 py-3.5">
                            <span class="text-[13px]"
                                :class="tax.exemption === 'No exemption' ? 'text-slate-400' : 'text-emerald-600 font-medium'">
                                {{ tax.exemption }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="px-4 py-3.5">
                            <div class="flex items-center justify-end gap-1  transition-opacity">
                                <button @click="openDrawer(tax)"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-cyan-50 hover:text-cyan-600 transition-colors"
                                    title="Edit">
                                    <Pencil class="w-3.5 h-3.5" :stroke-width="1.8" />
                                </button>
                                <button
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-violet-50 hover:text-violet-600 transition-colors"
                                    title="Details">
                                    <Info class="w-3.5 h-3.5" :stroke-width="1.8" />
                                </button>
                                <button
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    title="Rate">
                                    <DollarSign class="w-3.5 h-3.5" :stroke-width="1.8" />
                                </button>
                                <button @click="deleteTax(tax)"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-500 transition-colors"
                                    title="Delete">
                                    <Trash2 class="w-3.5 h-3.5" :stroke-width="1.8" />
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Empty state -->
                    <tr v-if="filteredTaxes.length === 0">
                        <td colspan="8" class="px-5 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center">
                                    <SearchX class="w-5 h-5 text-slate-300" :stroke-width="1.5" />
                                </div>
                                <p class="text-[13px] text-slate-400">No taxes found</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <StepperFooter :steps="steps" :current-step="currentStep" :loading="loading" :skippable="true"
            @submit="handleSubmit" />
        <!-- Drawer -->
        <Taxdrawer v-model:open="drawerOpen" :tax="selectedTax" @saved="onSaved" />
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Plus, Search, Pencil, Info, DollarSign, Trash2, SearchX } from 'lucide-vue-next'
import Taxdrawer from './Taxdrawer.vue'
import { propertyOnboardingSteps } from '../steps'
import StepperFooter from "../StepperFooter.vue"


interface Tax {
    id: number
    enabled: boolean
    name: string
    code: string
    rule: string
    appliesTo: string
    validity: string
    exemption: string
}

const search = ref('')
const filterApplies = ref('')
const drawerOpen = ref(false)
const selectedTax = ref<Tax | null>(null)

const steps = propertyOnboardingSteps
const currentStep = 2
const loading = ref(false)

function handleSubmit() {
    loading.value = true
    // submit logic
}

const taxes = ref<Tax[]>([
    { id: 1, enabled: true, name: 'Accommodation', code: 'ACCOM', rule: '10.0000% of Room Rate', appliesTo: 'Rooms', validity: 'Current (Since Dec 2021)', exemption: 'Exempt after 365 nights' },
    { id: 2, enabled: true, name: 'CGST', code: 'CGST', rule: 'Slab: ≤7499 → 6.0000% · ≥7500 & ≤10000…', appliesTo: 'Rooms + Extras', validity: 'Current (Since Jan 2025)', exemption: 'No exemption' },
    { id: 3, enabled: true, name: 'CGST Old', code: 'CGST-OLD', rule: 'Slab: ≤7500 → 6.0000% · ≥7501 & ≤10000…', appliesTo: 'Rooms', validity: 'Current → Next Change: Mar…', exemption: 'No exemption' },
    { id: 4, enabled: true, name: 'Commercial', code: 'COM', rule: 'Rs 0.0000 Per Night', appliesTo: 'Rooms', validity: 'Current (Since Oct 2021)', exemption: 'No exemption' },
    { id: 5, enabled: true, name: 'GST', code: 'GST', rule: '5.0000% of Room Rate', appliesTo: 'Rooms + Extras', validity: 'Current (Since Dec 2021)', exemption: 'No exemption' },
    { id: 6, enabled: false, name: 'PST', code: 'PROV1', rule: '7.0000% of Room Rate', appliesTo: 'Rooms + Extras', validity: 'Current (Since Dec 2021)', exemption: 'No exemption' },
])

const stats = computed(() => [
    { label: 'Total Taxes', value: taxes.value.length, color: 'text-slate-700' },
    { label: 'Active', value: taxes.value.filter(t => t.enabled).length, color: 'text-cyan-600' },
    { label: 'Inactive', value: taxes.value.filter(t => !t.enabled).length, color: 'text-slate-400' },
    { label: 'With Exemption', value: taxes.value.filter(t => t.exemption !== 'No exemption').length, color: 'text-emerald-600' },
])

const filteredTaxes = computed(() =>
    taxes.value.filter(t => {
        const matchSearch = !search.value || t.name.toLowerCase().includes(search.value.toLowerCase()) || t.code.toLowerCase().includes(search.value.toLowerCase())
        const matchFilter = !filterApplies.value || t.appliesTo === filterApplies.value
        return matchSearch && matchFilter
    })
)

function toggleTax(tax: Tax) {
    tax.enabled = !tax.enabled
}

function openDrawer(tax?: Tax) {
    selectedTax.value = tax ?? null
    drawerOpen.value = true
}

function deleteTax(tax: Tax) {
    taxes.value = taxes.value.filter(t => t.id !== tax.id)
}

function onSaved(data: Partial<Tax>) {
    if (selectedTax.value) {
        const idx = taxes.value.findIndex(t => t.id === selectedTax.value!.id)
        if (idx !== -1) taxes.value[idx] = { ...taxes.value[idx], ...data }
    } else {
        taxes.value.push({ id: Date.now(), enabled: true, ...data } as Tax)
    }
    drawerOpen.value = false
}
</script>
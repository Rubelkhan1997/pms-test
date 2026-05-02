<template>
    <div class="space-y-6">

        <!-- Page Header -->
        <div class="flex pb-1 flex-col justify-between">
            <h1 class="text-xl font-semibold text-slate-800">Markets</h1>
            <p class="text-gray-700 text-sm pt-1">Market segment categorization helps hotels understand where revenue
                comes from, optimize daily operations, and make smarter financial decisions.</p>
            <!-- <button 
                class="flex items-center gap-2 text-sm font-medium text-cyan-600 hover:text-cyan-700 transition-colors"
                @click="openCreateModal">
                <Plus class="w-4 h-4" :stroke-width="2" />
                Add Market
            </button> -->
        </div>

        <!-- Markets List -->
        <div class="space-y-3">
            <div v-for="market in markets" :key="market.id"
                class="bg-white border border-slate-200 rounded-lg overflow-hidden transition-all"
                :class="expandedMarkets.includes(market.id) ? '' : 'hover:border-primary'">

                <!-- Clickable Header -->
                <button @click="toggleMarket(market.id)"
                    class="w-full flex items-center justify-between p-5 text-left hover:bg-slate-50/50 transition-colors"
                    :class="{ 'cursor-default': market.id === firstMarketId }">

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center shrink-0"
                            :class="expandedMarkets.includes(market.id) ? 'bg-cyan-50 border-cyan-100' : ''">
                            <Globe class="w-5 h-5"
                                :class="expandedMarkets.includes(market.id) ? 'text-cyan-600' : 'text-slate-500'"
                                :stroke-width="1.8" />
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-slate-800">{{ market.name }}</h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ market.countries.length }} countries · {{ market.currency }}

                            </p>
                        </div>
                    </div>


                    <ChevronDown v-if="market.id !== firstMarketId"
                        class="w-5 h-5 text-slate-400 transition-transform duration-200 shrink-0"
                        :class="{ 'rotate-180 text-primary': expandedMarkets.includes(market.id) }" :stroke-width="2" />
                    <ChevronDown v-else class="w-5 h-5 text-primary rotate-180 shrink-0" :stroke-width="2" />
                </button>

                <!-- Collapsible Content -->
                <Transition name="expand">
                    <div v-if="expandedMarkets.includes(market.id)">
                        <div class="h-px bg-slate-100 mx-5" />

                        <div class="p-5 pt-4">
                            <!-- Details Grid -->
                            <div class="grid grid-cols-2 gap-4 mb-4">

                                <!-- Countries -->
                                <div class="flex items-start gap-3">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center shrink-0">
                                        <Globe class="w-4 h-4 text-slate-500" :stroke-width="1.8" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-700">Countries</p>
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            <span v-for="c in market.countries.slice(0, 4)" :key="c"
                                                class="text-[11px] bg-slate-100 text-slate-600 rounded px-1.5 py-0.5">
                                                {{ c }}
                                            </span>
                                            <span v-if="market.countries.length > 4"
                                                class="text-[11px] text-slate-400 px-1">
                                                +{{ market.countries.length - 4 }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Rates -->
                                <div class="flex items-start gap-3">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center shrink-0">
                                        <Percent class="w-4 h-4 text-slate-500" :stroke-width="1.8" />
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-slate-700">Tax Rates</p>
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            <span v-if="market.rates?.length" v-for="r in market.rates" :key="r.name"
                                                class="text-[11px] bg-amber-50 text-amber-700 border border-amber-100 rounded px-1.5 py-0.5">
                                                {{ r.name }}: {{ r.value }}%
                                            </span>
                                            <span v-else class="text-[11px] text-slate-400">No rates</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payments -->
                                <div class="flex items-start gap-3">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center shrink-0">
                                        <CreditCard class="w-4 h-4 text-slate-500" :stroke-width="1.8" />
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-slate-700">Payments</p>
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            <span v-if="market.payments?.length" v-for="p in market.payments" :key="p"
                                                class="text-[11px] bg-emerald-50 text-emerald-700 border border-emerald-100 rounded px-1.5 py-0.5">
                                                {{ p }}
                                            </span>
                                            <span v-else class="text-[11px] text-slate-400">Not configured</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Currencies -->
                                <div class="flex items-start gap-3">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center shrink-0">
                                        <DollarSign class="w-4 h-4 text-slate-500" :stroke-width="1.8" />
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-slate-700">Currencies</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span
                                                class="text-[11px] text-cyan-600 font-medium bg-cyan-50 px-1.5 py-0.5 rounded">
                                                Base: {{ market.currency }}
                                            </span>
                                            <span
                                                v-if="market.displayCurrency && market.displayCurrency !== market.currency"
                                                class="text-[11px] text-violet-600 font-medium bg-violet-50 px-1.5 py-0.5 rounded">
                                                Display: {{ market.displayCurrency }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                                <button @click.stop="editMarket(market)"
                                    class="flex items-center gap-1.5 text-[12px] font-medium text-slate-600 hover:text-cyan-600 px-3 py-1.5 rounded-lg hover:bg-slate-50 transition-colors">
                                    <Pencil class="w-3.5 h-3.5" :stroke-width="2" />
                                    Edit
                                </button>
                                <button @click.stop="deleteMarket(market)"
                                    class="flex items-center gap-1.5 text-[12px] font-medium text-red-500 hover:text-red-600 px-3 py-1.5 rounded-lg hover:bg-red-50 transition-colors">
                                    <Trash2 class="w-3.5 h-3.5" :stroke-width="2" />
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>

            <!-- Empty State -->
            <div v-if="markets.length === 0" class="text-center py-12 bg-white border border-slate-200 rounded-xl">
                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                    <Globe class="w-6 h-6 text-slate-400" :stroke-width="1.5" />
                </div>
                <p class="text-sm font-medium text-slate-600">No markets configured</p>
                <p class="text-xs text-slate-400 mt-1">Click "Add Market" to get started</p>
            </div>
        </div>
        <StepperFooter :steps="steps" :current-step="currentStep" :loading="loading" :skippable="true"
            @submit="handleSubmit" />
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import {
    Globe, Percent, CreditCard, DollarSign, ChevronDown,
    Plus, Pencil, Trash2
} from 'lucide-vue-next'
import { propertyOnboardingSteps } from '../steps';
import StepperFooter from '../StepperFooter.vue';

interface Rate { name: string; value: number }
interface Market {
    id: number
    name: string
    currency: string
    displayCurrency?: string
    countries: string[]
    rates?: Rate[]
    payments?: string[]
}

// ─── Data ───────────────────────────────────────────────
const markets = ref<Market[]>([
    { id: 1, name: 'All countries', currency: 'BDT', displayCurrency: 'BDT', countries: ['Afghanistan', 'Albania', 'Algeria', 'American Samoa', 'Andorra', 'Angola', 'Bangladesh', 'India'], rates: [], payments: [] },
    { id: 2, name: 'Europe', currency: 'EUR', displayCurrency: 'EUR', countries: ['Germany', 'France', 'Italy', 'Spain', 'Netherlands', 'Belgium', 'Austria'], rates: [{ name: 'VAT Standard', value: 20 }, { name: 'VAT Reduced', value: 10 }], payments: ['Stripe', 'PayPal', 'Bank Transfer'] },
    { id: 3, name: 'North America', currency: 'USD', displayCurrency: 'CAD', countries: ['United States', 'Canada', 'Mexico'], rates: [{ name: 'Sales Tax', value: 8 }, { name: 'GST', value: 5 }], payments: ['Stripe', 'Square', 'PayPal'] }
])

// ─── Stepper ─────────────────────────────────────────────
const steps = propertyOnboardingSteps
const currentStep = 4
const loading = ref(false)


// ─── State ──────────────────────────────────────────────
const firstMarketId = computed(() => markets.value[0]?.id)

const expandedMarkets = ref<number[]>([firstMarketId.value].filter(Boolean))

// ─── Actions ────────────────────────────────────────────
function toggleMarket(id: number) {

    if (id === firstMarketId.value) return

    const index = expandedMarkets.value.indexOf(id)
    if (index === -1) {
        expandedMarkets.value.push(id)
    } else {
        expandedMarkets.value.splice(index, 1)
    }
}

function openCreateModal() { console.log('Open create modal') }
function editMarket(m: Market) { console.log('Edit:', m.name) }
function deleteMarket(m: Market) {
    if (confirm(`Delete "${m.name}"?`)) {
        markets.value = markets.value.filter(x => x.id !== m.id)
        if (m.id === firstMarketId.value && markets.value.length > 0) {
            expandedMarkets.value = [markets.value[0].id]
        } else {
            expandedMarkets.value = expandedMarkets.value.filter(x => x !== m.id)
        }
    }
}
async function handleSubmit() {
    loading.value = true
    /*
        TODO: submit/save logic
        await axios.post('/api/onboarding/payment-methods/complete')
    */
    loading.value = false
}
</script>


<style scoped>
.expand-enter-active,
.expand-leave-active {
    transition: max-height 0.25s ease-in-out, opacity 0.2s ease, padding 0.2s ease;
    overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
    max-height: 0;
    opacity: 0;
    padding-top: 0;
    padding-bottom: 0;
}

.expand-enter-to,
.expand-leave-from {
    max-height: 600px;
    opacity: 1;
}
</style>
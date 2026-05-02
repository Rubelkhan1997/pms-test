<template>
    <!-- Backdrop -->
    <Teleport to="body">
        <Transition name="drawer-overlay">
            <div v-if="open" 
                class="fixed inset-0 bg-black/30 backdrop-blur-[2px] z-[100]" 
                @click="emit('update:open', false)" />
        </Transition>

        <!-- Drawer Panel -->
        <Transition name="drawer-slide">
            <div v-if="open" 
                class="fixed right-0 top-0 bottom-0 w-[520px] bg-white shadow-2xl z-[101] flex flex-col border-l border-slate-100">
                
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 flex-shrink-0">
                    <div>
                        <h2 class="text-[16px] font-semibold text-slate-800">
                            {{ isEditing ? 'Edit Market' : 'Create Market' }}
                        </h2>
                        <p class="text-[13px] text-slate-400 mt-0.5">
                            Step {{ currentStep }} of {{ steps.length }} — {{ steps[currentStep - 1].label }}
                        </p>
                    </div>
                    <button @click="emit('update:open', false)"
                        class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors">
                        <X class="w-4 h-4" :stroke-width="2" />
                    </button>
                </div>

                <!-- Progress Steps -->
                <div class="flex items-center gap-0 px-6 py-3 border-b border-slate-100 flex-shrink-0">
                    <template v-for="(step, i) in steps" :key="step.label">
                        <div class="flex flex-col items-center gap-1.5">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-medium transition-all"
                                :class="currentStep > i + 1 
                                    ? 'bg-cyan-500 text-white' 
                                    : currentStep === i + 1 
                                        ? 'bg-cyan-500 text-white ring-2 ring-cyan-100' 
                                        : 'bg-slate-100 text-slate-400'">
                                <Check v-if="currentStep > i + 1" class="w-3 h-3" :stroke-width="3" />
                                <span v-else>{{ i + 1 }}</span>
                            </div>
                        </div>
                        <div v-if="i < steps.length - 1" 
                            class="flex-1 h-px mx-2 transition-all"
                            :class="currentStep > i + 1 ? 'bg-cyan-400' : 'bg-slate-200'" />
                    </template>
                </div>

                <!-- Scrollable Content -->
                <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">
                    
                    <!-- Step 1: Basic Info -->
                    <template v-if="currentStep === 1">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[13px] font-medium text-slate-600 mb-1.5">
                                    Market name <span class="text-red-400">*</span>
                                </label>
                                <input v-model="form.name" type="text" placeholder="e.g. Europe, North America"
                                    class="w-full h-10 border border-slate-200 rounded-lg px-3.5 text-[14px] text-slate-800 placeholder-slate-300 outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 transition-all" />
                                <p v-if="errors.name" class="text-[12px] text-red-400 mt-1">{{ errors.name }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[13px] font-medium text-slate-600 mb-1.5">Sales currency</label>
                                    <select v-model="form.currency"
                                        class="w-full h-10 border border-slate-200 rounded-lg px-3.5 text-[14px] text-slate-800 outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 transition-all appearance-none bg-white">
                                        <option v-for="c in currencies" :key="c" :value="c">{{ c }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[13px] font-medium text-slate-600 mb-1.5">Display currency</label>
                                    <select v-model="form.displayCurrency" :disabled="!form.dualCurrency"
                                        class="w-full h-10 border border-slate-200 rounded-lg px-3.5 text-[14px] text-slate-800 outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 transition-all appearance-none bg-white disabled:bg-slate-50 disabled:text-slate-400">
                                        <option v-for="c in currencies" :key="c" :value="c">{{ c }}</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Dual Currency Toggle -->
                            <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-lg border border-slate-100">
                                <div>
                                    <p class="text-[14px] text-slate-700 font-medium">Enable dual currency</p>
                                    <p class="text-[12px] text-slate-400 mt-0.5">Show prices in two currencies at checkout</p>
                                </div>
                                <button @click="form.dualCurrency = !form.dualCurrency"
                                    class="w-10 h-6 rounded-full transition-colors duration-200 relative flex-shrink-0"
                                    :class="form.dualCurrency ? 'bg-cyan-500' : 'bg-slate-200'">
                                    <span class="absolute top-0.5 w-5 h-5 bg-white rounded-full transition-transform duration-200 shadow-sm"
                                        :class="form.dualCurrency ? 'translate-x-4' : 'translate-x-0.5'" />
                                </button>
                            </div>

                            <div>
                                <label class="block text-[13px] font-medium text-slate-600 mb-1.5">Default language</label>
                                <select v-model="form.language"
                                    class="w-full h-10 border border-slate-200 rounded-lg px-3.5 text-[14px] text-slate-800 outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 transition-all appearance-none bg-white">
                                    <option v-for="l in languages" :key="l" :value="l">{{ l }}</option>
                                </select>
                            </div>
                        </div>
                    </template>

                    <!-- Step 2: Countries -->
                    <template v-if="currentStep === 2">
                        <div class="space-y-4">
                            <!-- Search -->
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" :stroke-width="1.8" />
                                <input v-model="countrySearch" type="text" placeholder="Search countries..."
                                    class="w-full h-10 border border-slate-200 rounded-lg pl-9 pr-4 text-[14px] text-slate-800 placeholder-slate-300 outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 transition-all" />
                            </div>

                            <!-- Selected Chips -->
                            <div v-if="form.countries.length" class="flex flex-wrap gap-1.5">
                                <span v-for="c in form.countries" :key="c"
                                    class="inline-flex items-center gap-1.5 text-[12px] bg-cyan-50 text-cyan-700 border border-cyan-100 rounded-lg px-2.5 py-1">
                                    {{ c }}
                                    <button @click="toggleCountry(c)" class="hover:text-red-500 transition-colors">
                                        <X class="w-3 h-3" :stroke-width="2.5" />
                                    </button>
                                </span>
                            </div>

                            <!-- Country Groups -->
                            <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                                <div v-for="region in filteredRegions" :key="region.name" 
                                    class="border border-slate-100 rounded-lg overflow-hidden">
                                    <button @click="toggleRegionExpand(region.name)"
                                        class="w-full flex items-center justify-between px-4 py-2.5 bg-slate-50 hover:bg-slate-100 transition-colors">
                                        <div class="flex items-center gap-2.5">
                                            <input type="checkbox" :checked="isRegionSelected(region)" 
                                                @change.stop="toggleRegion(region)" @click.stop
                                                class="w-4 h-4 accent-cyan-500 cursor-pointer rounded" />
                                            <span class="text-[13px] font-medium text-slate-700">{{ region.name }}</span>
                                            <span class="text-[11px] text-slate-400">({{ region.countries.length }})</span>
                                        </div>
                                        <ChevronDown class="w-3.5 h-3.5 text-slate-400 transition-transform"
                                            :class="{ 'rotate-180': expandedRegions.includes(region.name) }" :stroke-width="2" />
                                    </button>
                                    
                                    <div v-if="expandedRegions.includes(region.name)" 
                                        class="grid grid-cols-2 divide-x divide-y divide-slate-100 bg-white">
                                        <label v-for="country in region.countries" :key="country"
                                            class="flex items-center gap-2.5 px-4 py-2.5 cursor-pointer hover:bg-slate-50 transition-colors">
                                            <input type="checkbox" :checked="form.countries.includes(country)"
                                                @change="toggleCountry(country)"
                                                class="w-4 h-4 accent-cyan-500 cursor-pointer rounded flex-shrink-0" />
                                            <span class="text-[13px] text-slate-700 truncate">{{ country }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Step 3: Rates & Taxes -->
                    <template v-if="currentStep === 3">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <p class="text-[12px] font-medium text-slate-500 uppercase tracking-wide">Tax rates</p>
                                <button @click="addRate"
                                    class="flex items-center gap-1.5 text-[12px] text-cyan-600 bg-cyan-50 hover:bg-cyan-100 border border-cyan-100 rounded-md px-2.5 py-1.5 transition-colors">
                                    <Plus class="w-3 h-3" :stroke-width="2" /> Add rate
                                </button>
                            </div>

                            <div v-if="!form.rates.length" 
                                class="flex flex-col items-center justify-center py-8 border border-dashed border-slate-200 rounded-lg">
                                <Percent class="w-6 h-6 text-slate-300 mb-2" :stroke-width="1.5" />
                                <p class="text-[13px] text-slate-400">No tax rates configured</p>
                            </div>

                            <div v-else class="space-y-2.5">
                                <div v-for="(rate, i) in form.rates" :key="i"
                                    class="flex items-center gap-3 p-3.5 border border-slate-100 rounded-lg bg-slate-50/50">
                                    <div class="flex-1">
                                        <input v-model="rate.name" type="text" placeholder="Rate name (e.g. VAT)"
                                            class="w-full h-8 border border-slate-200 rounded-md px-2.5 text-[13px] text-slate-800 placeholder-slate-300 outline-none focus:border-cyan-400 bg-white transition-all" />
                                    </div>
                                    <div class="w-24">
                                        <div class="flex">
                                            <input v-model="rate.value" type="number" placeholder="0" min="0" max="100"
                                                class="w-full h-8 border border-slate-200 rounded-l-md px-2.5 text-[13px] text-slate-800 outline-none focus:border-cyan-400 bg-white transition-all" />
                                            <span class="h-8 px-2 flex items-center text-[13px] text-slate-500 bg-slate-100 border border-l-0 border-slate-200 rounded-r-md">%</span>
                                        </div>
                                    </div>
                                    <button @click="removeRate(i)"
                                        class="w-7 h-7 flex items-center justify-center rounded-md hover:bg-red-50 text-slate-300 hover:text-red-500 transition-colors">
                                        <Trash2 class="w-3.5 h-3.5" :stroke-width="2" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Step 4: Payments -->
                    <template v-if="currentStep === 4">
                        <div class="space-y-3">
                            <label v-for="method in paymentMethods" :key="method.id"
                                class="flex items-center gap-4 p-4 border rounded-lg cursor-pointer transition-all hover:border-slate-200"
                                :class="form.payments.includes(method.id) ? 'border-cyan-200 bg-cyan-50/50' : 'border-slate-100 bg-white'">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                                    :class="form.payments.includes(method.id) ? 'bg-cyan-100' : 'bg-slate-100'">
                                    <component :is="method.icon" class="w-5 h-5"
                                        :class="form.payments.includes(method.id) ? 'text-cyan-600' : 'text-slate-400'" :stroke-width="1.8" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[14px] font-medium text-slate-800">{{ method.name }}</p>
                                    <p class="text-[12px] text-slate-400 mt-0.5">{{ method.description }}</p>
                                </div>
                                <input type="checkbox" :checked="form.payments.includes(method.id)"
                                    @change="togglePayment(method.id)"
                                    class="w-4 h-4 accent-cyan-500 cursor-pointer rounded flex-shrink-0" />
                            </label>
                        </div>
                    </template>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between px-6 py-4 border-t border-slate-100 bg-white flex-shrink-0">
                    <button v-if="currentStep > 1" @click="currentStep--"
                        class="flex items-center gap-2 text-[14px] text-slate-500 hover:text-slate-700 px-4 py-2 rounded-lg hover:bg-slate-50 transition-colors">
                        <ChevronLeft class="w-4 h-4" :stroke-width="2" /> Back
                    </button>
                    <span v-else />

                    <div class="flex items-center gap-3">
                        <button @click="emit('update:open', false)"
                            class="text-[14px] text-slate-500 hover:text-slate-700 px-4 py-2 rounded-lg hover:bg-slate-50 transition-colors">
                            Cancel
                        </button>
                        <button v-if="currentStep < steps.length" @click="nextStep"
                            class="flex items-center gap-2 text-[14px] font-medium text-white bg-cyan-600 hover:bg-cyan-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg px-5 py-2 transition-colors">
                            Continue <ChevronRight class="w-4 h-4" :stroke-width="2" />
                        </button>
                        <button v-else @click="handleSubmit"
                            class="flex items-center gap-2 text-[14px] font-medium text-white bg-cyan-600 hover:bg-cyan-700 rounded-lg px-5 py-2 transition-colors">
                            <Check class="w-4 h-4" :stroke-width="2.5" /> {{ isEditing ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { 
    X, Check, ChevronDown, ChevronLeft, ChevronRight, 
    Search, Plus, Trash2, Percent,
    CreditCard, Wallet, Building2, Smartphone, Banknote,
    type LucideIcon
} from 'lucide-vue-next'

// ─── Types ───────────────────────────────────────────────
interface Market {
    id: number; name: string; region: string; currency: string;
    displayCurrency?: string; dualCurrency: boolean; language: string;
    countries: string[]; rates: Array<{ name: string; value: number }>;
    payments: string[]; status: 'Active' | 'Inactive' | 'Draft';
    icon: LucideIcon;
}

interface PaymentMethod {
    id: string; name: string; description: string; icon: LucideIcon;
}

interface Region { name: string; countries: string[] }

interface FormState {
    name: string; currency: string; displayCurrency: string;
    dualCurrency: boolean; language: string; countries: string[];
    rates: Array<{ name: string; value: number }>; payments: string[];
}

// ─── Props & Emits ───────────────────────────────────────
const props = defineProps<{
    open: boolean;
    market?: Market | null;
}>()

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'saved', data: Partial<Market>): void;
}>()

// ─── Constants ───────────────────────────────────────────
const steps = [
    { label: 'Basic info' },
    { label: 'Countries' },
    { label: 'Rates' },
    { label: 'Payments' },
]

const currencies = ['BDT', 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'SGD', 'AED', 'INR']
const languages = ['English (US)', 'English (UK)', 'Bengali', 'Arabic', 'French', 'German', 'Spanish']

const countryRegions: Region[] = [
    { name: 'South Asia', countries: ['Bangladesh', 'India', 'Pakistan', 'Sri Lanka', 'Nepal', 'Maldives'] },
    { name: 'Southeast Asia', countries: ['Thailand', 'Vietnam', 'Indonesia', 'Malaysia', 'Philippines', 'Singapore'] },
    { name: 'Europe', countries: ['Germany', 'France', 'United Kingdom', 'Italy', 'Spain', 'Netherlands', 'Belgium'] },
    { name: 'North America', countries: ['United States', 'Canada', 'Mexico'] },
    { name: 'Middle East', countries: ['UAE', 'Saudi Arabia', 'Qatar', 'Kuwait', 'Bahrain', 'Oman'] },
    { name: 'East Asia', countries: ['China', 'Japan', 'South Korea', 'Taiwan', 'Hong Kong'] },
]

const paymentMethods: PaymentMethod[] = [
    { id: 'stripe', name: 'Stripe', description: 'Credit & debit cards, digital wallets', icon: CreditCard },
    { id: 'paypal', name: 'PayPal', description: 'PayPal balance, cards and bank accounts', icon: Wallet },
    { id: 'bank', name: 'Bank transfer', description: 'Direct bank-to-bank wire transfer', icon: Building2 },
    { id: 'mobile', name: 'Mobile banking', description: 'bKash, Nagad, Rocket and similar', icon: Smartphone },
    { id: 'cash', name: 'Cash on arrival', description: 'Collect payment at check-in', icon: Banknote },
]

// ─── State ───────────────────────────────────────────────
const currentStep = ref(1)
const countrySearch = ref('')
const expandedRegions = ref<string[]>(['South Asia'])
const errors = ref<Record<string, string>>({})

const defaultForm: FormState = {
    name: '', currency: 'BDT', displayCurrency: 'BDT',
    dualCurrency: false, language: 'English (US)',
    countries: [], rates: [], payments: [],
}

const form = ref<FormState>({ ...defaultForm })

const isEditing = computed(() => !!props.market)

// ─── Filtering ───────────────────────────────────────────
const filteredRegions = computed(() => {
    if (!countrySearch.value.trim()) return countryRegions
    const q = countrySearch.value.toLowerCase()
    return countryRegions
        .map(r => ({ ...r, countries: r.countries.filter(c => c.toLowerCase().includes(q)) }))
        .filter(r => r.countries.length > 0)
})

// ─── Country Selection Logic ─────────────────────────────
function toggleCountry(country: string) {
    const i = form.value.countries.indexOf(country)
    if (i >= 0) form.value.countries.splice(i, 1)
    else form.value.countries.push(country)
}

function isRegionSelected(region: Region) {
    return region.countries.every(c => form.value.countries.includes(c)) && region.countries.length > 0
}

function toggleRegion(region: Region) {
    if (isRegionSelected(region)) {
        form.value.countries = form.value.countries.filter(c => !region.countries.includes(c))
    } else {
        region.countries.forEach(c => {
            if (!form.value.countries.includes(c)) form.value.countries.push(c)
        })
    }
}

function toggleRegionExpand(name: string) {
    const i = expandedRegions.value.indexOf(name)
    if (i >= 0) expandedRegions.value.splice(i, 1)
    else expandedRegions.value.push(name)
}

// ─── Rates & Payments ────────────────────────────────────
function addRate() { form.value.rates.push({ name: '', value: 0 }) }
function removeRate(i: number) { form.value.rates.splice(i, 1) }

function togglePayment(id: string) {
    const i = form.value.payments.indexOf(id)
    if (i >= 0) form.value.payments.splice(i, 1)
    else form.value.payments.push(id)
}

// ─── Navigation ──────────────────────────────────────────
function nextStep() {
    if (currentStep.value === 1) {
        if (!form.value.name.trim()) {
            errors.value.name = 'Market name is required'
            return
        }
        errors.value.name = ''
    }
    if (currentStep.value < steps.length) currentStep.value++
}

function resetForm() {
    currentStep.value = 1
    countrySearch.value = ''
    expandedRegions.value = ['South Asia']
    errors.value = {}
    form.value = { ...defaultForm }
}

// ─── Submit ──────────────────────────────────────────────
function handleSubmit() {
    // Basic validation
    if (!form.value.name.trim()) {
        errors.value.name = 'Market name is required'
        currentStep.value = 1
        return
    }

    const payload: Partial<Market> = {
        name: form.value.name,
        currency: form.value.currency,
        displayCurrency: form.value.dualCurrency ? form.value.displayCurrency : undefined,
        dualCurrency: form.value.dualCurrency,
        language: form.value.language,
        countries: form.value.countries,
        rates: form.value.rates.filter(r => r.name && r.value > 0),
        payments: form.value.payments,
        status: 'Draft',
    }

    emit('saved', payload)
    resetForm()
}

// ─── Watchers ────────────────────────────────────────────
watch(() => props.market, (market) => {
    if (market) {
        form.value = {
            name: market.name,
            currency: market.currency,
            displayCurrency: market.displayCurrency || 'BDT',
            dualCurrency: !!market.displayCurrency,
            language: market.language || 'English (US)',
            countries: [...market.countries],
            rates: market.rates ? [...market.rates] : [],
            payments: market.payments ? [...market.payments] : [],
        }
    } else {
        resetForm()
    }
}, { immediate: true })

watch(() => props.open, (open) => {
    if (!open) resetForm()
})
</script>

<style scoped>
/* Overlay Transition */
.drawer-overlay-enter-active, .drawer-overlay-leave-active {
    transition: opacity 0.25s ease;
}
.drawer-overlay-enter-from, .drawer-overlay-leave-to {
    opacity: 0;
}

/* Slide Transition */
.drawer-slide-enter-active {
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.drawer-slide-leave-active {
    transition: transform 0.25s ease-in;
}
.drawer-slide-enter-from, .drawer-slide-leave-to {
    transform: translateX(100%);
}
</style>
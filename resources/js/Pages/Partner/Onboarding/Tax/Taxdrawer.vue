<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="flex flex-col max-w-4xl! max-h-[90vh] p-0">

            <!-- Header -->
            <DialogHeader class="px-6 py-5 border-b border-slate-100 shrink-0">
                <div class="flex items-center justify-between gap-3">
                    <DialogTitle>{{ isEditing ? 'Update Tax' : 'Add Tax' }}</DialogTitle>
                    
                </div>
            </DialogHeader>

            <!-- Scrollable Body -->
            <div class="flex-1 overflow-y-auto">
                <div class="px-6 py-2 space-y-4">

                    <!-- ── Basic Info ── -->
                    <section>
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest mb-3">Basic information</p>

                        <div class="grid grid-cols-4 gap-4">
                            <FormInput
                                :modelValue="form.code"
                                label="Tax Code"
                                name="code"
                                placeholder="GST"
                                @update:modelValue="form.code = $event"
                            />
                            <FormInput
                                :modelValue="form.name"
                                label="Tax Name"
                                name="name"
                                placeholder="Goods & Services Tax"
                                :required="true"
                                :error="hasErrors && !form.name ? 'Tax name is required.' : ''"
                                @update:modelValue="form.name = $event"
                            />
                            <FormInput
                                :modelValue="form.startDate"
                                label="Start Date"
                                name="startDate"
                                type="date"
                                :required="true"
                                @update:modelValue="form.startDate = $event"
                            />
                            <div class="space-y-1.5">
                                <p class="text-gray-800 font-medium mb-2 text-[14px] block">
                                    Exempt After Stay Duration
                                    
                                </p>
                                <div class="flex items-center gap-2">
                                    <FormInput
                                        :modelValue="form.exemptAfter"
                                        name="exemptAfter"
                                        type="number"
                                        placeholder="0"
                                        class="flex-1"
                                        @update:modelValue="form.exemptAfter = $event"
                                    />
                                    <span class="text-[13px] text-slate-400 font-medium shrink-0">Day(s)</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <hr class="border-slate-100" />

                    <!-- ── Tax Rule ── -->
                    <section>
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest mb-3">Tax rule</p>
                       <div class="grid grid-cols-2 gap-x-4">

                           <FormSelect
                               :modelValue="form.ruleType"
                               label="Rule type"
                               name="ruleType"
                               :options="ruleTypeOptions"
                               @update:modelValue="form.ruleType = $event"
                           />
   
                           <div v-if="form.ruleType === 'fixed_pct' || form.ruleType === 'fixed_amount'" class="mb-4">
                               <div class="flex items-center gap-2">
                                   <FormInput
                                       :modelValue="form.ruleValue"
                                       :label="form.ruleType === 'fixed_pct' ? 'Percentage' : 'Amount'"
                                       name="ruleValue"
                                       type="number"
                                       :placeholder="form.ruleType === 'fixed_pct' ? '0.00' : '0.0000'"
                                       class="flex-1"
                                       @update:modelValue="form.ruleValue = $event"
                                   />
                                   <span class="w-9 h-9 flex items-center justify-center rounded-md bg-slate-100 text-[13px] font-semibold text-slate-500 shrink-0 mt-3">
                                       {{ form.ruleType === 'fixed_pct' ? '%' : '₹' }}
                                   </span>
                               </div>
                           </div>
                       </div> 

                        <div v-if="form.ruleType?.startsWith('slab')" class="mt-4 rounded-xl border border-slate-100 bg-slate-50/50 p-4">
                            <p class="text-[12px] font-medium text-slate-500 mb-3">Slab configuration</p>
                            <div v-for="(slab, idx) in form.slabs" :key="idx" class="flex items-center gap-2 mb-2 last:mb-0">
                                <span class="text-[12px] text-slate-400 w-4 shrink-0">{{ idx + 1 }}.</span>
                                <FormInput
                                    :modelValue="slab.upTo"
                                    name="slabUpTo"
                                    type="number"
                                    placeholder="Up to"
                                    class="flex-1"
                                    @update:modelValue="slab.upTo = $event"
                                />
                                <span class="text-[12px] text-slate-400 shrink-0">→</span>
                                <FormInput
                                    :modelValue="slab.rate"
                                    name="slabRate"
                                    type="number"
                                    placeholder="Rate"
                                    class="flex-1"
                                    @update:modelValue="slab.rate = $event"
                                />
                                <span class="text-[12px] text-slate-400 shrink-0">%</span>
                                <button @click="removeSlab(idx)"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-300 hover:text-red-400 hover:bg-red-50 transition-colors shrink-0">
                                    <X class="w-3.5 h-3.5" :stroke-width="2" />
                                </button>
                            </div>
                            <button @click="addSlab"
                                class="mt-2 flex items-center gap-1.5 text-[12px] text-cyan-600 font-medium hover:text-cyan-700 transition-colors">
                                <Plus class="w-3.5 h-3.5" :stroke-width="2.5" />
                                Add slab
                            </button>
                        </div>
                    </section>

                    <hr class="border-slate-100" />

                    <!-- ── Application Settings ── -->
                    <section>
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest mb-3">Application settings</p>

                        <FormSelect
                            :modelValue="form.applyAfter"
                            label="Apply After Other Taxes"
                            name="applyAfter"
                            :options="applyAfterOptions"
                            @update:modelValue="form.applyAfter = $event"
                        />

                        <div class="flex items-center justify-between mt-4 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                            <div>
                                <p class="text-[14px] font-medium text-slate-700">Apply tax on rack rate</p>
                                <p class="text-[12px] text-slate-400 mt-0.5">Use rack rate as base instead of net rate</p>
                            </div>
                            <button @click="form.onRackRate = !form.onRackRate"
                                class="relative inline-flex items-center w-9 h-5 rounded-full transition-colors duration-200 focus:outline-none shrink-0"
                                :class="form.onRackRate ? 'bg-cyan-500' : 'bg-slate-200'">
                                <span class="inline-block w-3.5 h-3.5 bg-white rounded-full shadow-sm transform transition-transform duration-200"
                                    :class="form.onRackRate ? 'translate-x-[18px]' : 'translate-x-[3px]'" />
                            </button>
                        </div>

                        <div class="mt-4">
                            <p class="text-[14px] font-medium text-slate-600 mb-2.5">How should the tax be applied?</p>
                            <div class="flex items-center gap-5">
                                <FormRadio
                                    v-for="opt in discountOptions"
                                    :key="opt.value"
                                    name="discountApplication"
                                    :label="opt.label"
                                    :value="opt.value"
                                    v-model="form.discountApplication"
                                />
                            </div>
                        </div>
                    </section>

                    <hr class="border-slate-100" />

                    <!-- ── Apply Tax To ── -->
                    <section class="pb-4">
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest mb-3">Apply tax to</p>
                        <div class="space-y-2">
                            <label v-for="opt in applyToOptions" :key="opt.value"
                                class="flex items-start gap-3 p-3 rounded-md border border-slate-100 hover:border-cyan-200 hover:bg-cyan-50/30 transition-colors cursor-pointer group">
                                <input type="checkbox" v-model="form.applyTo" :value="opt.value"
                                    class="mt-0.5 accent-cyan-600 w-3.5 h-3.5 shrink-0 cursor-pointer" />
                                <div>
                                    <p class="text-[13px] font-medium text-slate-700 group-hover:text-slate-900">{{ opt.label }}</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ opt.hint }}</p>
                                </div>
                            </label>
                        </div>
                    </section>

                </div>
            </div>

            <!-- Footer -->
            <DialogFooter class="px-6 py-4 border-t border-slate-100 shrink-0 flex-row items-center justify-between gap-3 bg-white">
                <p v-if="hasErrors" class="text-[12px] text-red-500 flex items-center gap-1.5">
                    <AlertCircle class="w-3.5 h-3.5" :stroke-width="2" />
                    Please fill in all required fields.
                </p>
                <div class="flex items-center gap-2 ml-auto">
                    <button
                        @click="$emit('update:open', false)"
                        class="h-9 px-4 rounded-lg border border-slate-200 bg-white text-[13px] font-medium text-slate-500 hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button @click="handleSave" :disabled="isSaving"
                        class="h-9 px-5 rounded-lg bg-cyan-600 hover:bg-cyan-700 disabled:opacity-60 text-white text-[13px] font-medium transition-colors flex items-center gap-1.5 shadow-sm shadow-cyan-100">
                        <Loader2 v-if="isSaving" class="w-3.5 h-3.5 animate-spin" />
                        {{ isEditing ? 'Update Tax' : 'Save Tax' }}
                    </button>
                </div>
            </DialogFooter>

        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { X, Plus, AlertCircle, Loader2 } from 'lucide-vue-next'
import {
    Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle,
} from '@/components/ui/dialog'


const props = defineProps<{
    open: boolean
    tax?: any
}>()

const emit = defineEmits<{
    'update:open': [val: boolean]
    saved: [data: any]
}>()

const isSaving = ref(false)
const hasErrors = ref(false)
const isEditing = computed(() => !!props.tax)

const defaultForm = () => ({
    code: '',
    name: '',
    startDate: new Date().toISOString().slice(0, 10),
    exemptAfter: 0,
    ruleType: 'fixed_pct',
    ruleValue: '',
    slabs: [{ upTo: '', rate: '' }],
    applyAfter: 'none',
    onRackRate: false,
    discountApplication: 'after',
    applyTo: ['revenue'] as string[],
})

const form = ref(defaultForm())

watch(() => props.open, (val) => {
    if (val) {
        hasErrors.value = false
        form.value = props.tax ? { ...defaultForm(), ...props.tax } : defaultForm()
    }
})

// ── Options ──────────────────────────────────────────────
const ruleTypeOptions = [
    { value: 'fixed_pct',    label: 'Fixed percentage per night' },
    { value: 'fixed_amount', label: 'Fixed amount per night' },
    { value: 'slab_pct',     label: 'Slab-based percentage' },
    { value: 'slab_amount',  label: 'Slab-based amount' },
]

const applyAfterOptions = [
    { value: 'none',  label: '— Select tax —' },
    { value: 'GST',   label: 'GST' },
    { value: 'CGST',  label: 'CGST' },
    { value: 'ACCOM', label: 'Accommodation' },
]

const discountOptions = [
    { value: 'before', label: 'Before Discount' },
    { value: 'after',  label: 'After Discount' },
]

const applyToOptions = [
    { value: 'revenue',        label: 'Revenue Accounts',               hint: 'Apply to all revenue account transactions' },
    { value: 'accommodations', label: 'Selected Accommodations',        hint: 'Choose specific room types' },
    { value: 'extras',         label: 'Extras',                         hint: 'Apply to add-on charges and services' },
    { value: 'payouts',        label: 'Partner Settlements (Payouts)',   hint: 'Include in OTA and partner payouts' },
]

// ── Slab helpers ─────────────────────────────────────────
function addSlab() {
    form.value.slabs.push({ upTo: '', rate: '' })
}
function removeSlab(idx: number) {
    form.value.slabs.splice(idx, 1)
}

// ── Save ─────────────────────────────────────────────────
async function handleSave() {
    if (!form.value.name.trim()) {
        hasErrors.value = true
        return
    }
    isSaving.value = true
    await new Promise(r => setTimeout(r, 600))
    isSaving.value = false
    emit('saved', { ...form.value })
}
</script>
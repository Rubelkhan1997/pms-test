<script setup lang="ts">
import { computed } from 'vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormSelect from '@/Components/Form/FormSelect.vue'

// ─── Type ─────────────────────────────────────────────────────────────
export interface HouseRulesForm {
    timezone: string
    currency: string
    seasonType: 'all_year' | 'period'
    openingDate: string
    closingDate: string
    checkInTime: string
    checkOutTime: string
    petsAllowed: boolean
    childrenAllowed: boolean
    // Child age policy
    childAgeRateEnabled: boolean
    freeStayAgeLimit: string
    maxChildAge: string
    extraChargeByChildCount: boolean
}

// ─── Props & Emits ────────────────────────────────────────────────────
const props = defineProps<{ modelValue: HouseRulesForm }>()
const emit = defineEmits<{ 'update:modelValue': [value: HouseRulesForm] }>()

const form = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
})

function update<K extends keyof HouseRulesForm>(key: K, value: HouseRulesForm[K]) {
    emit('update:modelValue', { ...props.modelValue, [key]: value })
}

// ─── Static options ───────────────────────────────────────────────────
const timezoneOptions = [
    { value: 'Asia/Dhaka', label: 'Asia/Dhaka' },
    { value: 'America/New_York', label: 'America/New_York' },
    { value: 'America/Chicago', label: 'America/Chicago' },
    { value: 'America/Los_Angeles', label: 'America/Los_Angeles' },
    { value: 'Europe/London', label: 'Europe/London' },
]

const currencyOptions = [
    { value: 'BDT', label: 'BDT — Bangladeshi Taka' },
    { value: 'USD', label: 'USD — US Dollar' },
    { value: 'EUR', label: 'EUR — Euro' },
    { value: 'GBP', label: 'GBP — British Pound' },
]

const seasonOptions = [
    { value: 'all_year', label: 'Open all year' },
    { value: 'period', label: 'Open for a period' },
]

const toggles: { key: 'petsAllowed' | 'childrenAllowed'; title: string; subtitle: string }[] = [
    { key: 'petsAllowed', title: 'Pets allowed', subtitle: 'Allow guests to bring pets' },
    { key: 'childrenAllowed', title: 'Children allowed', subtitle: 'Allow guests to bring children' },
]
</script>

<template>
    <div class="space-y-6">

        <!-- ── Operational settings ── -->
        <section>
            <div class="pb-4">
                <h3 class="text-base pb-1 font-semibold text-gray-800">
                    Operational settings
                </h3>
                <p class="text-sm text-gray-700">
                    Set the time zone of your property and define whether your property is open all year or for a
                    specific period. These settings help maintain accurate calendars and availability.
                </p>
            </div>

            <div class="grid border-b mb-4 grid-cols-2 gap-x-4">
                <FormSelect :modelValue="form.timezone" label="Timezone" name="timezone" :options="timezoneOptions"
                    @update:modelValue="update('timezone', $event)" />
                <div class="mb-4">
                    <label class="text-gray-700 font-medium text-[14px] block mb-2">Season type</label>
                    <div class="flex mb-3 gap-3">
                        <label v-for="opt in seasonOptions" :key="opt.value" :class="[
                            'flex items-center gap-2 px-4 py-2 border rounded-[6px] text-sm cursor-pointer transition-colors select-none',
                            form.seasonType === opt.value
                                ? 'border-gray-900 text-gray-900 bg-gray-50'
                                : 'border-gray-200 text-gray-500 hover:bg-gray-50',
                        ]">
                            <input type="radio" :value="opt.value" :checked="form.seasonType === opt.value"
                                class="accent-gray-900"
                                @change="update('seasonType', opt.value as HouseRulesForm['seasonType'])" />
                            {{ opt.label }}
                        </label>
                    </div>
                    <!-- Period dates — only visible when seasonType === 'period' -->
                    <Transition enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 -translate-y-1"
                        leave-active-class="transition-all duration-150 ease-in"
                        leave-to-class="opacity-0 -translate-y-1">
                        <div v-if="form.seasonType === 'period'" class="grid grid-cols-2 gap-x-4">
                            <FormInput :modelValue="form.openingDate" label="Opening date" name="openingDate"
                                type="date" @update:modelValue="update('openingDate', $event)" />
                            <FormInput :modelValue="form.closingDate" label="Closing date" name="closingDate"
                                type="date" @update:modelValue="update('closingDate', $event)" />
                        </div>
                    </Transition>
                </div>
            </div>

            <div class="grid border-b pb-2 mb-5 grid-cols-2 gap-x-4">
                <div>
                    <h3 class="text-[15px] pb-1 font-semibold text-gray-800">Currency</h3>
                    <p class="text-sm text-gray-700">Select your sales currency.</p>
                </div>
                <!-- Season type toggle -->
                <FormSelect :modelValue="form.currency" label="Sales currency" name="currency"
                    :options="currencyOptions" @update:modelValue="update('currency', $event)" />
            </div>


        </section>

        <!-- ── Check-in / Check-out ── -->
        <section class="pb-5 border-b mb-5">
            <div class="pb-5">
                <h3 class="text-base pb-1 font-semibold text-gray-800">
                    Rules
                </h3>
                <p class="text-sm text-gray-700">
                    Define your property’s key rules such as check-in and check-out times and pet permissions.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-x-4">
                <FormInput :modelValue="form.checkInTime" label="Check-in time" name="checkInTime" type="time"
                    @update:modelValue="update('checkInTime', $event)" />
                <FormInput :modelValue="form.checkOutTime" label="Check-out time" name="checkOutTime" type="time"
                    @update:modelValue="update('checkOutTime', $event)" />
                <div class="border mt-3 border-gray-100 rounded-[10px] overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Pets allowed</p>
                            <p class="text-xs text-gray-400">Allow guests to bring pets</p>
                        </div>
                        <button type="button" role="switch" :aria-checked="form.petsAllowed" :class="[
                            'relative w-10 h-6 rounded-full transition-colors duration-200 focus:outline-none',
                            form.petsAllowed ? 'bg-primary' : 'bg-gray-200',
                        ]" @click="update('petsAllowed', !form.petsAllowed)">
                            <span :class="[
                                'absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200',
                                form.petsAllowed ? 'translate-x-4' : 'translate-x-0',
                            ]" />
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Guest policy toggles ── -->
        <!-- ── Guest policy toggles ── -->
        <section>

            <div class="grid grid-cols-2 gap-x-3">
                <div class="pb-5">
                    <h3 class="text-base pb-1 font-semibold text-gray-800">
                        Children
                    </h3>
                    <p class="text-sm text-gray-700">
                        Child age ranges are used to calculate pricing in your rate plans.
                    </p>
                </div>

                <!-- Children -->
                <div>
                    <div class="flex items-center gap-3 px-4 py-3">

                        <button type="button" role="switch" :aria-checked="form.childrenAllowed" :class="[
                            'relative w-10 h-6 rounded-full transition-colors duration-200 focus:outline-none',
                            form.childrenAllowed ? 'bg-primary' : 'bg-gray-200',
                        ]" @click="update('childrenAllowed', !form.childrenAllowed)">
                            <span :class="[
                                'absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200',
                                form.childrenAllowed ? 'translate-x-4' : 'translate-x-0',
                            ]" />
                        </button>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Define child age ranges</p>

                        </div>
                    </div>


                </div>


            </div>
            <!-- Child Age Policy — expands when childrenAllowed is true -->
            <Transition enter-active-class="transition-all duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-1" leave-active-class="transition-all duration-150 ease-in"
                leave-to-class="opacity-0 -translate-y-1">
                <div v-if="form.childrenAllowed"
                    class=" mb-3 rounded-lg border border-gray-100 bg-gray-50 px-4 py-3 space-y-4">

                    <p class="text-sm font-semibold text-gray-700">Child Age Policy</p>

          

                    <!-- Age inputs -->
                    <div class="grid grid-cols-2 gap-x-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500 block mb-1.5">Free Stay Age
                                Limit</label>
                            <div class="flex items-center gap-2">
                                <input type="number" min="0" :value="form.freeStayAgeLimit" placeholder="0"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-400 bg-white"
                                    @input="update('freeStayAgeLimit', ($event.target as HTMLInputElement).value)" />
                                <span class="text-xs text-gray-400 shrink-0">Year(s)</span>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 block mb-1.5">Maximum Age Considered
                                a Child</label>
                            <div class="flex items-center gap-2">
                                <input type="number" min="0" :value="form.maxChildAge" placeholder="0"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-400 bg-white"
                                    @input="update('maxChildAge', ($event.target as HTMLInputElement).value)" />
                                <span class="text-xs text-gray-400 shrink-0">Year(s)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Extra charge by child count toggle -->
                    <div class="flex r flex-col gap-2 pt-1">
                        <p class="text-sm text-gray-700">Allow extra charge based on number of children</p>
                        <button type="button" role="switch" :aria-checked="form.extraChargeByChildCount" :class="[
                            'relative w-10 h-6 rounded-full transition-colors duration-200 focus:outline-none shrink-0',
                            form.extraChargeByChildCount ? 'bg-primary' : 'bg-gray-200',
                        ]" @click="update('extraChargeByChildCount', !form.extraChargeByChildCount)">
                            <span :class="[
                                'absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200',
                                form.extraChargeByChildCount ? 'translate-x-4' : 'translate-x-0',
                            ]" />
                        </button>
                    </div>

                </div>
            </Transition>
        </section>

    </div>
</template>
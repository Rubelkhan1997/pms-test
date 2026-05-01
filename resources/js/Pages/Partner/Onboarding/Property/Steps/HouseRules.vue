<script setup lang="ts">
import { computed } from 'vue'
import FormInput  from '@/Components/Form/FormInput.vue'
import FormSelect from '@/Components/Form/FormSelect.vue'

// ─── Type ─────────────────────────────────────────────────────────────
export interface HouseRulesForm {
  timezone        : string
  currency        : string
  seasonType      : 'all_year' | 'period'
  openingDate     : string
  closingDate     : string
  checkInTime     : string
  checkOutTime    : string
  petsAllowed     : boolean
  childrenAllowed : boolean
}

// ─── Props & Emits ────────────────────────────────────────────────────
const props = defineProps<{ modelValue: HouseRulesForm }>()
const emit  = defineEmits<{ 'update:modelValue': [value: HouseRulesForm] }>()

const form = computed({
  get : () => props.modelValue,
  set : (val) => emit('update:modelValue', val),
})

function update<K extends keyof HouseRulesForm>(key: K, value: HouseRulesForm[K]) {
  emit('update:modelValue', { ...props.modelValue, [key]: value })
}

// ─── Static options ───────────────────────────────────────────────────
const timezoneOptions = [
  { value: 'Asia/Dhaka',          label: 'Asia/Dhaka'          },
  { value: 'America/New_York',    label: 'America/New_York'    },
  { value: 'America/Chicago',     label: 'America/Chicago'     },
  { value: 'America/Los_Angeles', label: 'America/Los_Angeles' },
  { value: 'Europe/London',       label: 'Europe/London'       },
]

const currencyOptions = [
  { value: 'BDT', label: 'BDT — Bangladeshi Taka' },
  { value: 'USD', label: 'USD — US Dollar'         },
  { value: 'EUR', label: 'EUR — Euro'              },
  { value: 'GBP', label: 'GBP — British Pound'     },
]

const seasonOptions = [
  { value: 'all_year', label: 'Open all year'      },
  { value: 'period',   label: 'Open for a period'  },
]

const toggles: { key: 'petsAllowed' | 'childrenAllowed'; title: string; subtitle: string }[] = [
  { key: 'petsAllowed',     title: 'Pets allowed',     subtitle: 'Allow guests to bring pets'     },
  { key: 'childrenAllowed', title: 'Children allowed', subtitle: 'Allow guests to bring children' },
]
</script>

<template>
  <div class="space-y-6">

    <!-- ── Operational settings ── -->
    <section>
      <p class="text-xs font-medium text-gray-400 uppercase tracking-widest mb-3">
        Operational settings
      </p>

      <div class="grid grid-cols-2 gap-x-4">
        <FormSelect
          :modelValue="form.timezone"
          label="Timezone"
          name="timezone"
          :options="timezoneOptions"
          @update:modelValue="update('timezone', $event)"
        />
        <FormSelect
          :modelValue="form.currency"
          label="Currency"
          name="currency"
          :options="currencyOptions"
          @update:modelValue="update('currency', $event)"
        />
      </div>

      <!-- Season type toggle -->
      <div class="mb-4">
        <label class="text-gray-700 font-medium text-[16px] block mb-2">Season type</label>
        <div class="flex gap-3">
          <label
            v-for="opt in seasonOptions"
            :key="opt.value"
            :class="[
              'flex items-center gap-2 px-4 py-2 border rounded-lg text-sm cursor-pointer transition-colors select-none',
              form.seasonType === opt.value
                ? 'border-gray-900 text-gray-900 bg-gray-50'
                : 'border-gray-200 text-gray-500 hover:bg-gray-50',
            ]"
          >
            <input
              type="radio"
              :value="opt.value"
              :checked="form.seasonType === opt.value"
              class="accent-gray-900"
              @change="update('seasonType', opt.value as HouseRulesForm['seasonType'])"
            />
            {{ opt.label }}
          </label>
        </div>
      </div>

      <!-- Period dates — only visible when seasonType === 'period' -->
      <Transition
        enter-active-class="transition-all duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-1"
        leave-active-class="transition-all duration-150 ease-in"
        leave-to-class="opacity-0 -translate-y-1"
      >
        <div v-if="form.seasonType === 'period'" class="grid grid-cols-2 gap-x-4">
          <FormInput
            :modelValue="form.openingDate"
            label="Opening date"
            name="openingDate"
            type="date"
            @update:modelValue="update('openingDate', $event)"
          />
          <FormInput
            :modelValue="form.closingDate"
            label="Closing date"
            name="closingDate"
            type="date"
            @update:modelValue="update('closingDate', $event)"
          />
        </div>
      </Transition>
    </section>

    <!-- ── Check-in / Check-out ── -->
    <section>
      <p class="text-xs font-medium text-gray-400 uppercase tracking-widest mb-3">
        Check-in &amp; check-out
      </p>

      <div class="grid grid-cols-2 gap-x-4">
        <FormInput
          :modelValue="form.checkInTime"
          label="Check-in time"
          name="checkInTime"
          type="time"
          @update:modelValue="update('checkInTime', $event)"
        />
        <FormInput
          :modelValue="form.checkOutTime"
          label="Check-out time"
          name="checkOutTime"
          type="time"
          @update:modelValue="update('checkOutTime', $event)"
        />
      </div>
    </section>

    <!-- ── Guest policy toggles ── -->
    <section>
      <p class="text-xs font-medium text-gray-400 uppercase tracking-widest mb-3">
        Guest policy
      </p>

      <div class="divide-y divide-gray-100 border border-gray-100 rounded-xl overflow-hidden">
        <div
          v-for="toggle in toggles"
          :key="toggle.key"
          class="flex items-center justify-between px-4 py-3"
        >
          <div>
            <p class="text-sm font-medium text-gray-700">{{ toggle.title }}</p>
            <p class="text-xs text-gray-400">{{ toggle.subtitle }}</p>
          </div>

          <button
            type="button"
            :aria-checked="form[toggle.key]"
            role="switch"
            :class="[
              'relative w-10 h-6 rounded-full transition-colors duration-200 focus:outline-none',
              form[toggle.key] ? 'bg-gray-900' : 'bg-gray-200',
            ]"
            @click="update(toggle.key, !form[toggle.key])"
          >
            <span
              :class="[
                'absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200',
                form[toggle.key] ? 'translate-x-4' : 'translate-x-0',
              ]"
            />
          </button>
        </div>
      </div>
    </section>

  </div>
</template>
<template>
  <Drawer :open="open" @update:open="$emit('update:open', $event)" direction="right">
    <DrawerContent class="!w-[740px] !max-w-[740px] h-full ml-auto rounded-none flex flex-col overflow-hidden">

      <DrawerHeader class="border-b border-slate-200 px-6 py-4 shrink-0">
        <div class="flex items-center gap-2 mb-0.5">
          <button type="button" @click="$emit('update:open', false)" class="w-6 h-6 flex items-center justify-center rounded hover:bg-slate-100 text-slate-500 transition-colors">
            <ChevronLeft class="w-4 h-4" :stroke-width="2.5" />
          </button>
          <DrawerTitle class="text-[15px] font-semibold text-slate-800">
            {{ editing ? 'Edit Rate Type' : 'Add Rate Type' }}
          </DrawerTitle>
        </div>
        <DrawerDescription class="text-[12.5px] text-slate-400 ml-8">
          Create and configure a pricing structure with included benefits and add-ons.
        </DrawerDescription>
      </DrawerHeader>

      <div class="flex-1 overflow-y-auto">

        <div class="px-6 py-5">
          <div class="grid grid-cols-2 gap-5">
            <FormInput id="rate_type_name" v-model="form.name" label="Rate Type Name" placeholder="Enter Rate Type Name" :required="true" wrapper-class="mb-0" />
            <FormInput id="short_code" v-model="form.shortCode" label="Short Code" :required="true" wrapper-class="mb-0 max-w-[160px]" />
          </div>
        </div>

        <div class="h-px bg-slate-100" />

        <div class="px-6 py-5">
          <div class="flex items-center gap-3">
            <span class="text-[13px] text-slate-600">Does this rate type include meals?</span>
            <ToggleSwitch v-model="form.includeMeals" />
          </div>
          <Transition name="slide">
            <div v-if="form.includeMeals" class="mt-4 space-y-4">
              <div>
                <label class="block text-[12px] font-medium text-slate-600 mb-1.5">Meal Plan</label>
                <MultiSelect v-model="form.mealPlans" :options="mealPlanOptions" placeholder="Select meal plans..." class="max-w-sm" />
              </div>
              <div v-if="form.mealPlans.length > 0" class="border border-slate-200 rounded-lg overflow-hidden">
                <table class="w-full">
                  <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                      <th class="text-left px-4 py-2.5 text-[11.5px] font-semibold text-slate-500 uppercase tracking-wide w-1/2">Meal Plan</th>
                      <th class="text-left px-4 py-2.5 text-[11.5px] font-semibold text-slate-500 uppercase tracking-wide w-1/2">Meal Timing</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100">
                    <tr v-for="meal in form.mealPlans" :key="meal">
                      <td class="px-4 py-3 text-[13px] text-slate-700">{{ meal }}</td>
                      <td class="px-4 py-3 text-[12.5px] text-slate-400">checkin, stayover, checkout</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </Transition>
        </div>

        <div class="h-px bg-slate-100" />

        <div class="px-6 py-5">
          <div class="flex items-center gap-3">
            <span class="text-[13px] text-slate-600">Does this rate type include chargeable add-ons?</span>
            <ToggleSwitch v-model="form.includeAddons" />
          </div>
          <Transition name="slide">
            <div v-if="form.includeAddons" class="mt-4 space-y-4">
              <div class="flex items-end gap-6 flex-wrap">
                <div>
                  <label class="block text-[12px] font-medium text-slate-600 mb-1.5">Add-ons</label>
                  <MultiSelect v-model="form.addons" :options="addonOptions" placeholder="Select add-ons..." class="w-56" />
                </div>
                <div class="flex items-center gap-3 pb-0.5">
                  <span class="text-[13px] text-slate-600 whitespace-nowrap">Do you want to set rate validity &amp; stay restrictions?</span>
                  <ToggleSwitch v-model="form.includeValidity" />
                </div>
              </div>

              <Transition name="slide">
                <div v-if="form.includeValidity" class="flex items-center gap-3 flex-wrap">
                  <div class="flex items-center gap-1.5">
                    <FormInput id="valid_from" v-model="form.validFrom" type="date" placeholder="Valid From" wrapper-class="mb-0 w-36" />
                    <ArrowRight class="w-3.5 h-3.5 text-slate-400 shrink-0" :stroke-width="2" />
                    <FormInput id="valid_to" v-model="form.validTo" type="date" placeholder="Valid To" wrapper-class="mb-0 w-36" />
                  </div>
                  <FormInput id="min_nights" v-model="form.minNights" type="number" placeholder="Min. Nights" wrapper-class="mb-0 w-28" />
                  <FormInput id="max_nights" v-model="form.maxNights" type="number" placeholder="Max. Nights" wrapper-class="mb-0 w-28" />
                </div>
              </Transition>

              <div v-if="form.addons.length > 0" class="border border-slate-200 rounded-lg overflow-hidden">
                <table class="w-full">
                  <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                      <th class="text-left px-4 py-2.5 text-[11.5px] font-semibold text-slate-500 uppercase tracking-wide w-[22%]">Add-ons</th>
                      <th class="text-left px-4 py-2.5 text-[11.5px] font-semibold text-slate-500 uppercase tracking-wide w-[28%]">Posting Rule</th>
                      <th class="text-left px-4 py-2.5 text-[11.5px] font-semibold text-slate-500 uppercase tracking-wide w-[28%]">Charge Basis</th>
                      <th class="text-left px-4 py-2.5 text-[11.5px] font-semibold text-slate-500 uppercase tracking-wide w-[22%]">Base Rate (Rs)</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100">
                    <tr v-for="addon in form.addons" :key="addon">
                      <td class="px-4 py-3 text-[13px] text-slate-700">{{ addon }}</td>
                      <td class="px-4 py-3">
                        <div class="relative">
                          <select v-model="addonConfig[addon].postingRule" class="w-full appearance-none px-3 py-1.5 text-[12.5px] border border-slate-200 rounded-lg focus:outline-none focus:border-violet-400 bg-white text-slate-600 pr-7 transition-colors">
                            <option value="" disabled>Posting Rule</option>
                            <option v-for="r in postingRuleOptions" :key="r">{{ r }}</option>
                          </select>
                          <ChevronDown class="absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 text-slate-400 pointer-events-none" :stroke-width="2" />
                        </div>
                      </td>
                      <td class="px-4 py-3">
                        <div class="relative">
                          <select v-model="addonConfig[addon].chargeBasis" class="w-full appearance-none px-3 py-1.5 text-[12.5px] border border-slate-200 rounded-lg focus:outline-none focus:border-violet-400 bg-white text-slate-600 pr-7 transition-colors">
                            <option value="" disabled>Charge Basis</option>
                            <option v-for="c in chargeBasisOptions" :key="c">{{ c }}</option>
                          </select>
                          <ChevronDown class="absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 text-slate-400 pointer-events-none" :stroke-width="2" />
                        </div>
                      </td>
                      <td class="px-4 py-3">
                        <FormInput :id="`base_rate_${addon}`" v-model="addonConfig[addon].baseRate" type="number" wrapper-class="mb-0" />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

            </div>
          </Transition>
        </div>

      </div>

      <DrawerFooter class="border-t border-slate-200 px-6 py-4 flex-row justify-end gap-2.5 shrink-0">
        <DrawerClose as-child>
          <button type="button" class="px-5 py-2 text-[12.5px] font-medium text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
            Cancel
          </button>
        </DrawerClose>
        <button
          type="button"
          @click="handleSave"
          :disabled="!form.name.trim() || !form.shortCode.trim()"
          class="px-6 py-2 text-[12.5px] font-semibold bg-slate-800 hover:bg-slate-900 disabled:opacity-40 disabled:cursor-not-allowed text-white rounded-lg transition-colors"
        >
          {{ editing ? 'Update' : 'Save' }}
        </button>
      </DrawerFooter>

    </DrawerContent>
  </Drawer>
</template>

<script setup lang="ts">
import { ref, watch, reactive } from 'vue'
import { ChevronLeft, ChevronDown, ArrowRight } from 'lucide-vue-next'
import { Drawer, DrawerClose, DrawerContent, DrawerDescription, DrawerFooter, DrawerHeader, DrawerTitle } from '../ui/drawer'
import MultiSelect from '../ui/MultiSelect.vue'
import ToggleSwitch from '../ui/ToggleSwitch.vue'

// ─── Props / Emits ───────────────────────────────────────
const props = defineProps<{
  open: boolean
  editing?: any | null  // editing rate pass করলে form prefill হবে
}>()

const emit = defineEmits<{
  'update:open': [value: boolean]
  'save': [data: any]
}>()

// ─── Options ─────────────────────────────────────────────
const mealPlanOptions    = ['All inclusive', 'Brunch', 'Breakfast', 'Half Board', 'Full Board']
const addonOptions       = ['Anchor', 'ABC', 'Airport Transfer', 'Spa', 'Parking']
const postingRuleOptions = ['On Custom Date', 'On Check-in', 'On Check-out', 'Daily']
const chargeBasisOptions = ['Per Person', 'Per Room', 'Per Night', 'Fixed']

// ─── Default Form ─────────────────────────────────────────
function defaultForm() {
  return {
    name: '',
    shortCode: '',
    includeMeals: false,
    mealPlans: [] as string[],
    includeAddons: false,
    addons: [] as string[],
    includeValidity: false,
    validFrom: '',
    validTo: '',
    minNights: null as number | null,
    maxNights: null as number | null,
  }
}

const form = ref(defaultForm())
const addonConfig = reactive<Record<string, { postingRule: string; chargeBasis: string; baseRate: number | null }>>({})

// ─── Drawer খুললে form reset / prefill ──────────────────
watch(() => props.open, (isOpen) => {
  if (!isOpen) return

  if (props.editing?._raw) {
    // Edit mode — _raw থেকে prefill
    form.value = { ...defaultForm(), ...props.editing._raw }
  } else {
    // Add mode — fresh form
    form.value = defaultForm()
    Object.keys(addonConfig).forEach(k => delete addonConfig[k])
  }
})

// ─── Addon config sync ────────────────────────────────────
watch(() => form.value.addons, (addons) => {
  addons.forEach(a => {
    if (!addonConfig[a]) addonConfig[a] = { postingRule: '', chargeBasis: '', baseRate: null }
  })
})

// ─── Save ────────────────────────────────────────────────
function handleSave() {
  if (!form.value.name.trim() || !form.value.shortCode.trim()) return

  const payload = {
    ...form.value,
    addonConfig: { ...addonConfig }, // addon-এর posting rule, charge basis, base rate
  }

  emit('save', payload)
  emit('update:open', false)
}
</script>

<style scoped>
.slide-enter-active, .slide-leave-active { transition: all 0.2s ease; overflow: hidden; }
.slide-enter-from, .slide-leave-to { opacity: 0; max-height: 0; transform: translateY(-4px); }
.slide-enter-to, .slide-leave-from { opacity: 1; max-height: 600px; }
</style>
<template>
  <div class="flex flex-col h-full">

    <div class="mb-5">
      <h2 class="text-xl font-semibold text-slate-800">Rate Types</h2>
      <p class="text-[13px] text-slate-500 mt-0.5">Manage and configure pricing structures and included services.</p>
    </div>

    <div class="flex items-center justify-between mb-4">
      <div class="relative">
        <input
          v-model="search"
          type="text"
          placeholder="Search Rate Type"
          class="pl-3.5 pr-9 py-2 text-[12.5px] border border-slate-200 rounded-md w-72 focus:outline-none focus:border-orange-400 transition-all placeholder:text-slate-400 bg-white"
        />
        <Search class="absolute right-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none" :stroke-width="2" />
      </div>

      <button
        type="button"
        @click="openDrawer()"
        class="flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-orange-600 text-white text-[13px] font-medium rounded-md transition-colors"
      >
        <Plus class="w-3.5 h-3.5" :stroke-width="2.5" />
        Add rate type
      </button>
    </div>

    <div class="border border-slate-200 rounded-lg overflow-hidden bg-white">
      <table class="w-full table-content">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200">
            <th class="w-[35%]">Rate Type</th>
            <th class="w-[30%]">Included Meal Plans</th>
            <th class="w-[25%]">Add-ons</th>
            <th class="text-right w-[10%]">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="rate in filteredRates" :key="rate.id" class="hover:bg-slate-50/60 transition-colors">
            <td class="px-5 py-3">
              <span class="text-[13px] font-medium text-slate-700">{{ rate.name }}</span>
            </td>
            <td class="px-5 py-3">
              <div class="flex flex-wrap gap-1.5">
                <span
                  v-for="meal in rate.mealPlans" :key="meal"
                  class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-orange-50 text-orange-600 border border-orange-100"
                >{{ meal }}</span>
                <span v-if="rate.mealPlans.length === 0" class="text-[12.5px] text-slate-300">—</span>
              </div>
            </td>
            <td class="px-5 py-3">
              <div class="flex flex-wrap gap-1.5">
                <span
                  v-for="addon in rate.addons" :key="addon"
                  class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-600"
                >{{ addon }}</span>
                <span v-if="rate.addons.length === 0" class="text-[12.5px] text-slate-300">—</span>
              </div>
            </td>
            <td class="px-5 py-3 text-right">
              <div class="flex items-center justify-end gap-1">
                <button type="button" @click="editRate(rate)" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                  <Pencil class="w-3.5 h-3.5" :stroke-width="1.8" />
                </button>
                <button type="button" @click="removeRate(rate.id)" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-400 transition-colors">
                  <Trash2 class="w-3.5 h-3.5" :stroke-width="1.8" />
                </button>
              </div>
            </td>
          </tr>

          <tr v-if="filteredRates.length === 0">
            <td colspan="4" class="px-5 py-16 text-center">
              <div class="flex flex-col items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center">
                  <Tag class="w-4.5 h-4.5 text-slate-300" :stroke-width="1.5" />
                </div>
                <p class="text-[13px] text-slate-400">No rate types added yet</p>
                <button type="button" @click="openDrawer()" class="text-[12px] text-orange-500 hover:underline font-medium">
                  + Add your first rate type
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <AddRateTypeDrawer
      v-model:open="drawerOpen"
      :editing="editingRate"
      @save="handleSave"
    />

  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Search, Plus, Trash2, Pencil, Tag } from 'lucide-vue-next'
import AddRateTypeDrawer from '@/Components/RateTypes/AddRateTypeDrawer.vue'

// ─── Types ───────────────────────────────────────────────
interface RateType {
  id: number
  name: string
  mealPlans: string[]
  addons: string[]
  _raw: any 
}

// ─── State ───────────────────────────────────────────────
const search = ref('')
const drawerOpen = ref(false)
const editingRate = ref<RateType | null>(null)
let nextId = 4

// ─── Default Array ───────────────────────────────────────
const rates = ref<RateType[]>([
  {
    id: 1,
    name: 'Room Only',
    mealPlans: [],
    addons: ['Parking'],
    _raw: {
      name: 'Room Only', shortCode: 'RO',
      includeMeals: false, mealPlans: [],
      includeAddons: true, addons: ['Parking'],
      includeValidity: false, validFrom: '', validTo: '',
      minNights: null, maxNights: null,
    },
  },
  {
    id: 2,
    name: 'Bed & Breakfast',
    mealPlans: ['Breakfast'],
    addons: [],
    _raw: {
      name: 'Bed & Breakfast', shortCode: 'BB',
      includeMeals: true, mealPlans: ['Breakfast'],
      includeAddons: false, addons: [],
      includeValidity: false, validFrom: '', validTo: '',
      minNights: null, maxNights: null,
    },
  },
  {
    id: 3,
    name: 'All Inclusive',
    mealPlans: ['Breakfast', 'Half Board', 'Full Board'],
    addons: ['Airport Transfer', 'Spa'],
    _raw: {
      name: 'All Inclusive', shortCode: 'AI',
      includeMeals: true, mealPlans: ['Breakfast', 'Half Board', 'Full Board'],
      includeAddons: true, addons: ['Airport Transfer', 'Spa'],
      includeValidity: true, validFrom: '2025-01-01', validTo: '2025-12-31',
      minNights: 2, maxNights: 14,
    },
  },
])

// ─── Computed ────────────────────────────────────────────
const filteredRates = computed(() =>
  !search.value.trim()
    ? rates.value
    : rates.value.filter(r => r.name.toLowerCase().includes(search.value.toLowerCase()))
)

// ─── Methods ─────────────────────────────────────────────
function openDrawer() {
  editingRate.value = null
  drawerOpen.value = true
}

function editRate(rate: RateType) {
  editingRate.value = rate
  drawerOpen.value = true
}

function removeRate(id: number) {
  rates.value = rates.value.filter(r => r.id !== id)
}

function handleSave(data: any) {
 
  const tableRow: RateType = {
    id: editingRate.value?.id ?? nextId++,
    name: data.name,
    mealPlans: data.includeMeals ? data.mealPlans : [],
    addons: data.includeAddons ? data.addons : [],
    _raw: data, // সম্পূর্ণ data _raw-এ রাখি
  }

  if (editingRate.value) {
    const idx = rates.value.findIndex(r => r.id === tableRow.id)
    if (idx !== -1) rates.value[idx] = tableRow
  } else {
    rates.value.push(tableRow)
  }

  
  // await saveToBackend(data)
}

async function saveToBackend(data: any) {
 
  // await $fetch('/api/rate-types', { method: 'POST', body: data })
}
</script>
<template>
    <div class="space-y-6">

        <!-- Page Header -->
        <div class="flex items-start justify-between gap-7">
            <div>
                <h1 class="text-[20px] font-semibold text-slate-800 tracking-tight leading-none mb-1.5">
                    Room Types
                </h1>
                <p class="text-[15px] pt-1 text-slate-600 leading-relaxed">
                    Add all room types available at your property. Clearly define each room by its name, size, and
                    amenities to make it easier for guests to choose the right one. The information you provide here
                    may be shared both on your website and with connected sales channels.
                </p>
            </div>
            <button @click="drawerOpen = true"
                class="flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-secondary text-white text-[14.5px] cursor-pointer font-medium rounded-sm transition-colors shrink-0">
                <Plus class="w-4 h-4" :stroke-width="2.2" />
                Add Room Type
            </button>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-xl border border-slate-100 overflow-hidden">

            <!-- Toolbar -->
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 gap-3 flex-wrap">
                <div class="flex items-center gap-2">
                    <!-- Search -->
                    <div class="relative">
                        <Search
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 pointer-events-none"
                            :stroke-width="2" />
                        <input v-model="search" type="text" placeholder="Search by name"
                            class="pl-9 pr-4 py-2 text-[14px] bg-slate-50 border border-slate-200 rounded-md w-72 focus:outline-none focus:border-primary transition-all placeholder:text-slate-400" />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <table class="w-full table-content">
                <thead>
                    <tr class="bg-slate-50/70">
                        <th class="cursor-pointer text-left hover:text-slate-600 select-none" @click="sort('name')">
                            <span class="flex items-center gap-1">
                                Name
                                <ChevronsUpDown class="w-3 h-3" :stroke-width="2" />
                            </span>
                        </th>
                        <th class="cursor-pointer text-center hover:text-slate-600 select-none"
                            @click="sort('mealPlan')">
                            <span class="flex justify-center items-center gap-1">
                                Meal Plan
                                <ChevronsUpDown class="w-3 h-3" :stroke-width="2" />
                            </span>
                        </th>
                        <th class="cursor-pointer text-center hover:text-slate-600 select-none"
                            @click="sort('adultCapacity')">
                            <span class="flex justify-center items-center gap-1">
                                <Users class="w-3 h-3" :stroke-width="2" />
                                Adult Capacity
                                <ChevronsUpDown class="w-3 h-3" :stroke-width="2" />
                            </span>
                        </th>
                        <th class="cursor-pointer text-center hover:text-slate-600 select-none"
                            @click="sort('totalCapacity')">
                            <span class="flex justify-center items-center gap-1">
                                <Users class="w-3 h-3" :stroke-width="2" />
                                Total Capacity
                                <ChevronsUpDown class="w-3 h-3" :stroke-width="2" />
                            </span>
                        </th>
                        <th class="text-right">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="room in filteredRooms" :key="room.id"
                        class="border-t border-slate-50 hover:bg-slate-50/50 transition-colors">

                        <!-- Name -->
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center shrink-0">
                                    <BedDouble class="w-4 h-4 text-primary" :stroke-width="1.8" />
                                </div>
                                <div>
                                    <p class="text-[14px] font-semibold text-slate-700">{{ room.name }}</p>
                                    <p class="text-[12px] text-slate-400">{{ room.size }} m²</p>
                                </div>
                            </div>
                        </td>

                        <!-- Meal Plan -->
                        <td class="px-5 text-center py-3.5">
                            <span class="inline-block text-[11px] font-medium px-2.5 py-1 rounded-md"
                                :class="mealPlanClass(room.mealPlan)">
                                {{ room.mealPlan }}
                            </span>
                        </td>

                        <!-- Adult Capacity -->
                        <td class="px-5 text-center py-3.5">
                            <div class="flex justify-center items-center gap-1.5 text-[14px] text-slate-600">
                                <User class="w-3.5 h-3.5 text-slate-500" :stroke-width="1.8" />
                                {{ room.adultCapacity }}
                            </div>
                        </td>

                        <!-- Total Capacity -->
                        <td class="px-5 text-center py-3.5">
                            <div class="flex items-center justify-center  gap-1.5 text-[14px] text-slate-600">
                                <Users class="w-3.5 h-3.5 text-slate-500" :stroke-width="1.8" />
                                {{ room.totalCapacity }}
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                <button @click="openDialog(room)"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-500 hover:bg-cyan-50 hover:text-cyan-600 transition-colors"
                                    title="Edit">
                                    <Pencil class="w-3.5 h-3.5" :stroke-width="1.8" />
                                </button>
                                <button
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-500 hover:bg-violet-50 hover:text-violet-600 transition-colors"
                                    title="Details">
                                    <Info class="w-3.5 h-3.5" :stroke-width="1.8" />
                                </button>
                                <button @click="deleteRoom(room)"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-500 hover:bg-red-50 hover:text-red-500 transition-colors"
                                    title="Delete">
                                    <Trash2 class="w-3.5 h-3.5" :stroke-width="1.8" />
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Empty state -->
                    <tr v-if="filteredRooms.length === 0">
                        <td colspan="5" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center">
                                    <SearchX class="w-5 h-5 text-slate-300" :stroke-width="1.5" />
                                </div>
                                <p class="text-[13px] text-slate-400">No room types found</p>
                                <button @click="openDialog()"
                                    class="mt-1 text-[13px] text-cyan-600 hover:underline font-medium">
                                    + Add your first room type
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Drawer -->
    <RoomTypeDrawer v-model:open="drawerOpen" />
    <StepperFooter :steps="steps" :current-step="currentStep" :loading="loading" :skippable="true"
        @submit="handleSubmit" />
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import {
    Search, Pencil, Info, Trash2, SearchX, ChevronsUpDown,
    Plus, BedDouble, Users, User, Printer, Download, SlidersHorizontal
} from 'lucide-vue-next'
import { propertyOnboardingSteps } from '../steps'
import StepperFooter from '../StepperFooter.vue'
import RoomTypeDrawer from '@/Components/Rooms/RoomTypeDrawer.vue'

// ─── Types ───────────────────────────────────────────────
interface RoomType {
    id: number
    name: string
    size: number
    mealPlan: 'Breakfast' | 'Half Board' | 'Full Board' | 'All Inclusive' | 'None'
    adultCapacity: number
    totalCapacity: number
}
const drawerOpen = ref(false)
// ─── Stepper ─────────────────────────────────────────────
const steps = propertyOnboardingSteps
const currentStep = 6
const loading = ref(false)

// ─── State ───────────────────────────────────────────────
const search = ref('')
const sortKey = ref<keyof RoomType>('name')
const sortDir = ref<'asc' | 'desc'>('asc')
const dialogOpen = ref(false)
const selectedRoom = ref<RoomType | null>(null)

// ─── Data (replace with API call) ────────────────────────
const rooms = ref<RoomType[]>([
    { id: 1, name: 'Standard Single', size: 18, mealPlan: 'Breakfast', adultCapacity: 1, totalCapacity: 1 },
    { id: 2, name: 'Deluxe Double', size: 28, mealPlan: 'Half Board', adultCapacity: 2, totalCapacity: 3 },
    { id: 3, name: 'Superior Twin', size: 30, mealPlan: 'None', adultCapacity: 2, totalCapacity: 2 },
    { id: 4, name: 'Junior Suite', size: 45, mealPlan: 'Full Board', adultCapacity: 2, totalCapacity: 4 },
    { id: 5, name: 'Presidential Suite', size: 90, mealPlan: 'All Inclusive', adultCapacity: 4, totalCapacity: 6 },
])

// ─── Stepper Submit ──────────────────────────────────────
async function handleSubmit() {
    loading.value = true
    /*
        TODO: submit/save logic
        await axios.post('/api/onboarding/payment-methods/complete')
    */
    loading.value = false
}

// ─── Meal plan badge colors ───────────────────────────────
function mealPlanClass(plan: RoomType['mealPlan']) {
    return {
        'Breakfast': 'bg-amber-100 text-amber-700',
        'Half Board': 'bg-violet-100 text-violet-700',
        'Full Board': 'bg-sky-100 text-sky-700',
        'All Inclusive': 'bg-emerald-100 text-emerald-700',
        'None': 'bg-slate-100 text-slate-500',
    }[plan]
}

// ─── Filtering & Sorting ─────────────────────────────────
const filteredRooms = computed(() => {
    let list = rooms.value.filter(r =>
        !search.value ||
        r.name.toLowerCase().includes(search.value.toLowerCase())
    )
    return list.sort((a, b) => {
        const av = String(a[sortKey.value])
        const bv = String(b[sortKey.value])
        return sortDir.value === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av)
    })
})

function sort(key: keyof RoomType) {
    sortDir.value = sortKey.value === key && sortDir.value === 'asc' ? 'desc' : 'asc'
    sortKey.value = key
}

// ─── Dialog ──────────────────────────────────────────────
function openDialog(room?: RoomType) {
    selectedRoom.value = room ?? null
    dialogOpen.value = true
}

// ─── CRUD ────────────────────────────────────────────────
function deleteRoom(room: RoomType) {
    // TODO: confirm dialog + API call
    rooms.value = rooms.value.filter(r => r.id !== room.id)
}
</script>
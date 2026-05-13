<template>
    <div class="space-y-4">

        <!-- Toolbar -->
        <div class="flex items-center gap-2 flex-wrap">
            <!-- Search -->
            <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none"
                    :stroke-width="2" />
                <input v-model="search" type="text" placeholder="Search room identifier"
                    class="pl-9 pr-4 py-2 text-[13px] border border-slate-200 rounded-md w-64 focus:outline-none focus:border-primary transition-all placeholder:text-slate-400 bg-white" />
            </div>
        </div>

        <!-- Table -->
        <div class="border border-slate-200 rounded-lg overflow-hidden">
            <table class="w-full table-content">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">

                        <th class="text-left">
                            <span class="flex items-center gap-1.5">
                                <Hash class="w-3 h-3" :stroke-width="2" /> Room Identifier
                            </span>
                        </th>
                        <th class="text-left">
                            <span class="flex items-center gap-1.5">
                                <Layers class="w-3 h-3" :stroke-width="2" /> Floor
                            </span>
                        </th>
                        <th class="text-left">
                            <span class="flex items-center gap-1.5">
                                <CircleDot class="w-3 h-3" :stroke-width="2" /> Status
                            </span>
                        </th>
                        <th class="text-left">
                            <span class="flex items-center gap-1.5">
                                <BedDouble class="w-3 h-3" :stroke-width="2" /> N° Bedrooms
                            </span>
                        </th>
                        <th class="text-left">
                            <span class="flex items-center gap-1.5">
                                <Bath class="w-3 h-3" :stroke-width="2" /> N° Bathrooms
                            </span>
                        </th>
                        <th class="text-left">
                            <span class="flex items-center gap-1.5">
                                <Maximize2 class="w-3 h-3" :stroke-width="2" /> Area (m²)
                            </span>
                        </th>
                        <th class="text-left">
                            <span class="flex items-center gap-1.5">
                                <BedDouble class="w-3 h-3" :stroke-width="2" /> Bed Types
                            </span>
                        </th>
                        <th class="text-center w-50">
                            <span class="flex justify-center items-center gap-1.5">
                                <Eye class="w-3 h-3" :stroke-width="2" /> View
                            </span>
                        </th>
                        <!-- <th class="px-4 py-3 w-10"></th> -->
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="(room, i) in filteredRooms" :key="room.id" class="hover:bg-slate-50/60 transition-colors">

                        <!-- Room identifier -->
                        <td class="px-4 py-2.5">
                            <input v-model="room.identifier" type="text"
                                class="w-24 px-2.5 py-1.5 text-[13px] border border-slate-200 rounded-lg focus:outline-none focus:border-primary transition-colors bg-white" />
                        </td>

                        <!-- Floor -->
                        <td class="px-4 py-2.5">
                            <button @click="cycleFloor(room)" type="button"
                                class="text-[13px] font-medium text-primary hover:underline min-w-6 text-left">
                                {{ room.floor }}
                            </button>
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-2.5">
                            <div class="relative">
                                <button type="button" class="flex items-center gap-1.5 text-[13px] font-medium"
                                    :class="statusColor(room.status)" @click="toggleStatusDropdown(room.id)">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="statusDot(room.status)" />
                                    {{ room.status }}
                                    <ChevronDown class="w-3 h-3 text-slate-400" :stroke-width="2" />
                                </button>
                                <!-- Status dropdown -->
                                <div v-if="statusDropdown === room.id"
                                    class="absolute top-full left-0 mt-1 z-50 bg-white border border-slate-200 rounded-xl shadow-lg py-1.5 min-w-35">
                                    <button v-for="s in statusOptions" :key="s" type="button"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-[12.5px] hover:bg-slate-50 transition-colors"
                                        :class="statusColor(s)" @click="room.status = s; statusDropdown = null">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="statusDot(s)" />
                                        {{ s }}
                                    </button>
                                </div>
                            </div>
                        </td>

                        <!-- N° Bedrooms -->
                        <td class="px-4 py-2.5">
                            <input v-model.number="room.bedrooms" type="number" min="1"
                                class="w-16 px-2.5 py-1.5 text-[13px] border border-slate-200 rounded-lg focus:outline-none focus:border-primary transition-colors bg-white text-center" />
                        </td>

                        <!-- N° Bathrooms -->
                        <td class="px-4 py-2.5">
                            <input v-model.number="room.bathrooms" type="number" min="1"
                                class="w-16 px-2.5 py-1.5 text-[13px] border border-slate-200 rounded-lg focus:outline-none focus:border-primary transition-colors bg-white text-center" />
                        </td>

                        <!-- Area -->
                        <td class="px-4 py-2.5">
                            <input v-model.number="room.area" type="number" min="0"
                                class="w-20 px-2.5 py-1.5 text-[13px] border border-slate-200 rounded-lg focus:outline-none focus:border-primary transition-colors bg-white text-center" />
                        </td>

                        <!-- Bed Types multiselect -->
                        <td class="px-4 py-2.5">

                            <MultiSelect v-model="room.bedTypes" :options="bedTypeOptions"
                                placeholder="Select bed types" :searchable="true" class="max-w-50" />
                        </td>

                        <!-- View multiselect -->
                        <td class="px-4 py-2.5">
                            <MultiSelect v-model="room.views" :options="viewOptions" class="max-w-50" placeholder="Select..."
                                :searchable="true" />

                        </td>

                        <!-- Delete -->
                        <!-- <td class="px-4 py-2.5">
                            <button @click="removeRoom(room.id)" type="button"
                                class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-400 transition-colors">
                                <Trash2 class="w-3.5 h-3.5" :stroke-width="1.8" />
                            </button>
                        </td> -->
                    </tr>

                    <!-- Empty -->
                    <tr v-if="filteredRooms.length === 0">
                        <td colspan="10" class="px-5 py-14 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center">
                                    <BedDouble class="w-4.5 h-4.5 text-slate-300" :stroke-width="1.5" />
                                </div>
                                <p class="text-[13px] text-slate-400">No rooms added yet</p>

                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Bulk delete bar -->


    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import {
    Search, ChevronDown,
    BedDouble, Bath, Maximize2, Eye, Hash, Layers, CircleDot
} from 'lucide-vue-next'
import MultiSelect from '../ui/MultiSelect.vue'

// ─── Types ───────────────────────────────────────────────
interface Room {
    id: number
    identifier: string
    floor: number
    status: string
    bedrooms: number
    bathrooms: number
    area: number
    bedTypes: string[]
    views: string[]
}

// ─── Options ─────────────────────────────────────────────
const statusOptions = ['Usable', 'Maintenance', 'Inactive']
const bedTypeOptions = ['Single', 'Double', 'Twin', 'King', 'Queen', 'Bunk']
const viewOptions = ['Beach view', 'Sea view', 'Garden view', 'Pool view', 'City view', 'Mountain view', 'No view']

// ─── State ───────────────────────────────────────────────
const search = ref('')
const statusDropdown = ref<number | null>(null)

const rooms = ref<Room[]>(
    Array.from({ length: 10 }, (_, i) => ({
        id: 100 + i,
        identifier: String(100 + i),
        floor: 1,
        status: 'Usable',
        bedrooms: 2,
        bathrooms: 1,
        area: 330,
        bedTypes: [],
        views: [],
    }))
)

// ─── Computed ────────────────────────────────────────────
const filteredRooms = computed(() =>
    !search.value.trim()
        ? rooms.value
        : rooms.value.filter(r =>
            r.identifier.toLowerCase().includes(search.value.toLowerCase())
          )
)

// ─── Methods ─────────────────────────────────────────────
function cycleFloor(room: Room) {
    room.floor = room.floor >= 20 ? 1 : room.floor + 1
}

function toggleStatusDropdown(id: number) {
    statusDropdown.value = statusDropdown.value === id ? null : id
}

function statusColor(s: string) {
    return {
        'Usable': 'text-emerald-600',
        'Maintenance': 'text-amber-600',
        'Inactive': 'text-slate-400',
    }[s] ?? 'text-slate-600'
}

function statusDot(s: string) {
    return {
        'Usable': 'bg-emerald-500',
        'Maintenance': 'bg-amber-400',
        'Inactive': 'bg-slate-300',
    }[s] ?? 'bg-slate-300'
}
</script>
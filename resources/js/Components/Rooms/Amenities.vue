<template>
    <div class="space-y-4">

        <!-- Search -->
        <div class="relative w-2xl">
            <Search
                class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none"
                :stroke-width="2" />
            <input
                v-model="search"
                type="text"
                placeholder="Search amenities..."
                class="w-full pl-9 pr-4 py-2.5 text-[13px] border border-gray-300 rounded-md focus:outline-none focus:border-primary transition-all placeholder:text-slate-700 bg-white"
            />
        </div>

        <!-- Summary bar -->
        <div class="flex items-center justify-between">
            <p class="text-[12.5px] text-slate-600">
                <template v-if="totalSelected > 0">
                    <span class="font-semibold text-slate-700">{{ totalSelected }}</span> amenities selected
                </template>
                <template v-else>
                    Select amenities available in this room type
                </template>
            </p>
            <button
                v-if="totalSelected > 0"
                type="button"
                class="text-[12px] text-slate-400 hover:text-red-500 transition-colors"
                @click="clearAll">
                Clear all
            </button>
        </div>

        <!-- Groups -->
        <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl overflow-hidden">

            <div v-for="(group, gi) in filteredGroups" :key="gi">

                <!-- Header -->
                <button
                    type="button"
                    class="w-full flex items-center justify-between px-4 py-3 bg-white hover:bg-slate-50 transition-colors"
                    @click="toggleGroup(group.name)">
                    <div class="flex items-center gap-2.5">
                        <component
                            :is="groupIcon(group.name)"
                            class="w-3.5 h-3.5 text-slate-700"
                            :stroke-width="1.8" />
                        <span class="text-[12.5px] font-semibold text-slate-800 uppercase tracking-wider">
                            {{ group.name }}
                        </span>
                        <span
                            v-if="selectedCount(group) > 0"
                            class="inline-flex items-center justify-center min-w-4.5 h-4.5 px-1.5 text-[10px] font-bold bg-primary/10 text-primary rounded-full">
                            {{ selectedCount(group) }}
                        </span>
                    </div>
                    <ChevronDown
                        class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                        :class="openGroups.has(group.name) ? 'rotate-180' : ''"
                        :stroke-width="2" />
                </button>

                <!-- Body -->
                <Transition
                    enter-active-class="transition-all duration-200 ease-out"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition-all duration-150 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0">
                    <div v-if="openGroups.has(group.name)" class="px-4 py-3 bg-slate-50/30 border-t border-slate-100">
                        <div class="grid grid-cols-4 gap-3">
                            <button
                                v-for="(item, ii) in group.items"
                                :key="ii"
                                type="button"
                                :class="[
                                    'flex items-center gap-2.5 px-3 py-2.5 rounded-md border text-left transition-all duration-150',
                                    item.selected
                                        ? 'border-primary bg-white text-slate-800'
                                        : 'border-gray-300 bg-white text-slate-700 hover:border-primary hover:text-slate-700',
                                ]"
                                @click="toggleGroupItem(group.name, item.label)">

                                <!-- Checkbox -->
                                <span :class="[
                                    'w-4 h-4 rounded flex items-center justify-center shrink-0 transition-all border',
                                    item.selected
                                        ? 'bg-primary border-primary'
                                        : 'border-slate-300 bg-white',
                                ]">
                                    <svg v-if="item.selected" class="w-2 h-2 text-white" viewBox="0 0 10 10" fill="none">
                                        <path d="M1.5 5l2.5 2.5 4.5-4.5" stroke="currentColor"
                                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>

                                <span class="text-[13px] leading-tight">{{ item.label }}</span>
                            </button>
                        </div>
                    </div>
                </Transition>

            </div>

            <!-- No results -->
            <div v-if="filteredGroups.length === 0" class="py-12 text-center bg-white">
                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                    <SearchX class="w-4.5 h-4.5 text-slate-300" :stroke-width="1.5" />
                </div>
                <p class="text-[13px] font-medium text-slate-500">No results for "{{ search }}"</p>
                <p class="text-[12px] text-slate-400 mt-0.5">Try a different keyword</p>
            </div>

        </div>

    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import {
    Search, SearchX, ChevronDown,
    Wifi, Bath, BedDouble, Tv, UtensilsCrossed, Sun, Settings
} from 'lucide-vue-next'
import type { Component } from 'vue'

interface AmenityItem  { label: string; selected: boolean }
interface AmenityGroup { name: string;  items: AmenityItem[] }

// ─── Data ────────────────────────────────────────────────
const groups = ref<AmenityGroup[]>([
    {
        name: 'General',
        items: [
            { label: 'Free WiFi',        selected: false },
            { label: 'Air Conditioning', selected: false },
            { label: 'Heating',          selected: false },
            { label: 'Elevator',         selected: false },
            { label: '24h Front Desk',   selected: false },
            { label: 'Luggage Storage',  selected: false },
        ],
    },
    {
        name: 'Bathroom',
        items: [
            { label: 'Private Bathroom', selected: false },
            { label: 'Bathtub',          selected: false },
            { label: 'Rain Shower',      selected: false },
            { label: 'Hair Dryer',       selected: false },
            { label: 'Towels',           selected: false },
            { label: 'Toiletries',       selected: false },
        ],
    },
    {
        name: 'Bedroom',
        items: [
            { label: 'King Bed',           selected: false },
            { label: 'Extra Pillows',      selected: false },
            { label: 'Blackout Curtains',  selected: false },
            { label: 'Wardrobe',           selected: false },
            { label: 'Safe',               selected: false },
            { label: 'Iron & Board',       selected: false },
        ],
    },
    {
        name: 'Entertainment',
        items: [
            { label: 'Flat-screen TV',    selected: false },
            { label: 'Cable Channels',    selected: false },
            { label: 'Streaming Service', selected: false },
            { label: 'Sound System',      selected: false },
        ],
    },
    {
        name: 'Food & Drink',
        items: [
            { label: 'Minibar',         selected: false },
            { label: 'Coffee Maker',    selected: false },
            { label: 'Electric Kettle', selected: false },
            { label: 'Refrigerator',    selected: false },
            { label: 'Microwave',       selected: false },
        ],
    },
    {
        name: 'Outdoors',
        items: [
            { label: 'Balcony',      selected: false },
            { label: 'Terrace',      selected: false },
            { label: 'Garden View',  selected: false },
            { label: 'Sea View',     selected: false },
            { label: 'Pool View',    selected: false },
        ],
    },
])

// ─── State ───────────────────────────────────────────────
const search     = ref('')
const openGroups = ref<Set<string>>(new Set([groups.value[0].name]))

// ─── Computed ────────────────────────────────────────────
const filteredGroups = computed(() => {
    if (!search.value.trim()) return groups.value
    const q = search.value.toLowerCase()
    return groups.value
        .map(g => ({ ...g, items: g.items.filter(i => i.label.toLowerCase().includes(q)) }))
        .filter(g => g.items.length > 0)
})

const totalSelected = computed(() =>
    groups.value.reduce((sum, g) => sum + g.items.filter(i => i.selected).length, 0)
)

function selectedCount(group: AmenityGroup) {
    return group.items.filter(i => i.selected).length
}

// ─── Icon map ────────────────────────────────────────────
function groupIcon(name: string): Component {
    const map: Record<string, Component> = {
        'General':       Wifi,
        'Bathroom':      Bath,
        'Bedroom':       BedDouble,
        'Entertainment': Tv,
        'Food & Drink':  UtensilsCrossed,
        'Outdoors':      Sun,
    }
    return map[name] ?? Settings
}

// ─── Methods ─────────────────────────────────────────────
function toggleGroup(name: string) {
    openGroups.value.has(name)
        ? openGroups.value.delete(name)
        : openGroups.value.add(name)
}

function toggleGroupItem(groupName: string, label: string) {
    const item = groups.value
        .find(g => g.name === groupName)
        ?.items.find(i => i.label === label)
    if (item) item.selected = !item.selected
}

function clearAll() {
    groups.value.forEach(g => g.items.forEach(i => i.selected = false))
}
</script>
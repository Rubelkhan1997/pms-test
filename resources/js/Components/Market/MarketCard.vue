<template>
    <div class="bg-white rounded-xl border border-slate-100 p-4 hover:shadow-lg hover:shadow-slate-200/50 hover:border-cyan-200 transition-all duration-200 flex flex-col h-full group">
        
        <!-- Header -->
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center flex-shrink-0 group-hover:bg-cyan-50 group-hover:border-cyan-100 transition-colors">
                    <component :is="market.icon" class="w-5 h-5 text-slate-600 group-hover:text-cyan-600 transition-colors" :stroke-width="1.8" />
                </div>
                <div>
                    <h3 class="text-[14px] font-semibold text-slate-800 leading-tight">{{ market.name }}</h3>
                    <p class="text-[12px] text-slate-400 mt-0.5">{{ market.region }}</p>
                </div>
            </div>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide"
                :class="{
                    'bg-emerald-100 text-emerald-700': market.status === 'Active',
                    'bg-amber-100 text-amber-700': market.status === 'Draft',
                    'bg-slate-100 text-slate-500': market.status === 'Inactive',
                }">
                {{ market.status }}
            </span>
        </div>

        <!-- Details -->
        <div class="space-y-2 mb-4 flex-1">
            <div class="flex items-center justify-between text-[13px]">
                <span class="text-slate-400">Currency</span>
                <span class="font-medium text-slate-700">{{ market.currency }}{{ market.dualCurrency ? ` / ${market.displayCurrency}` : '' }}</span>
            </div>
            <div class="flex items-center justify-between text-[13px]">
                <span class="text-slate-400">Countries</span>
                <span class="font-medium text-slate-700">{{ market.countries.length }}</span>
            </div>
            <div v-if="market.rates?.length" class="flex items-center justify-between text-[13px]">
                <span class="text-slate-400">Tax rates</span>
                <span class="font-medium text-slate-700">{{ market.rates.length }} configured</span>
            </div>
        </div>

        <!-- Payments Preview -->
        <div v-if="market.payments?.length" class="flex flex-wrap gap-1.5 mb-4">
            <span v-for="p in market.payments.slice(0, 3)" :key="p" 
                class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-50 text-[11px] font-medium text-emerald-700 border border-emerald-100">
                {{ p }}
            </span>
            <span v-if="market.payments.length > 3" class="text-[11px] text-slate-400 px-1">+{{ market.payments.length - 3 }}</span>
        </div>

        <!-- Actions -->
        <div class="pt-3 border-t border-slate-50 flex items-center justify-between mt-auto opacity-0 group-hover:opacity-100 transition-opacity">
            <button @click="$emit('edit')" 
                class="inline-flex items-center gap-1.5 text-[12px] font-medium text-cyan-600 hover:text-cyan-700 transition-colors">
                <Pencil class="w-3.5 h-3.5" :stroke-width="2" /> Edit
            </button>
            <button @click="$emit('delete')" 
                class="inline-flex items-center gap-1.5 text-[12px] font-medium text-red-500 hover:text-red-600 transition-colors">
                <Trash2 class="w-3.5 h-3.5" :stroke-width="2" /> Delete
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Market } from '@/Pages/Partner/Onboarding/Market/Create.vue';
import { Pencil, Trash2 } from 'lucide-vue-next'


defineProps<{ market: Market }>()
defineEmits<{
    (e: 'edit'): void
    (e: 'delete'): void
}>()
</script>
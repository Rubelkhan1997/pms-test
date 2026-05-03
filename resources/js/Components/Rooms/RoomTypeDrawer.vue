<template>
    <Drawer v-model:open="open" direction="bottom">
        <DrawerContent class="rounded-none flex flex-col h-[calc(100vh-40px)]! max-h-[calc(100vh-40px)]!">

            <!-- Header -->
            <DrawerHeader class="flex! flex-row! items-center justify-between border-b border-slate-200 px-6 pt-0 pb-5 shrink-0">
                <h2 class="text-gray-800 text-[16.5px] font-semibold">Add Room Type</h2>
                <DrawerClose as-child>
                    <button class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                        <X class="w-4 h-4" :stroke-width="2" />
                    </button>
                </DrawerClose>
            </DrawerHeader>

            <!-- Step Progress -->
            <div class="px-6 py-4 border-b border-slate-100 shrink-0">
                <div class="flex items-center gap-0">
                    <template v-for="(step, i) in steps" :key="step.key">
                        <!-- Step -->
                        <div class="flex items-center gap-2 cursor-pointer" @click="goToStep(i)">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-semibold transition-all"
                                :class="i < currentStep
                                    ? 'bg-primary text-white'
                                    : i === currentStep
                                        ? 'bg-primary text-white ring-4 ring-primary/20'
                                        : 'bg-slate-100 text-slate-400'">
                                <Check v-if="i < currentStep" class="w-3 h-3" :stroke-width="2.5" />
                                <span v-else>{{ i + 1 }}</span>
                            </div>
                            <span class="text-[13px] font-medium transition-colors"
                                :class="i === currentStep ? 'text-primary' : i < currentStep ? 'text-slate-600' : 'text-slate-400'">
                                {{ step.label }}
                            </span>
                        </div>
                        <!-- Connector -->
                        <div v-if="i < steps.length - 1" class="flex-1 h-px mx-3 transition-colors"
                            :class="i < currentStep ? 'bg-primary' : 'bg-slate-200'" />
                    </template>
                </div>
            </div>

            <!-- Step Content -->
            <div class="flex-1 overflow-y-auto px-6 py-6">
                <component :is="steps[currentStep].component" />
            </div>

            <!-- Footer -->
            <DrawerFooter class="flex! flex-row! items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 shrink-0">
                <button @click="open = false"
                    class="px-5 py-2.5 text-[13px] font-medium text-slate-500 hover:bg-slate-100 rounded-lg transition-colors">
                    Cancel
                </button>
                <button @click="handleContinue"
                    class="flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-secondary text-white text-[13px] font-medium rounded-sm transition-colors">
                    {{ currentStep === steps.length - 1 ? 'Save Room Type' : 'Continue' }}
                    <ArrowRight v-if="currentStep < steps.length - 1" class="w-3.5 h-3.5" :stroke-width="2.2" />
                    <Check v-else class="w-3.5 h-3.5" :stroke-width="2.2" />
                </button>
            </DrawerFooter>

        </DrawerContent>
    </Drawer>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { X, Check, ArrowRight } from 'lucide-vue-next'
import {
    Drawer, DrawerClose, DrawerContent,
    DrawerFooter, DrawerHeader,
} from '../ui/drawer'

import BasicInformation from './BasicInformation.vue'
import Amenities        from './Amenities.vue'
import RoomImage           from './RoomImage.vue'
import Rooms            from './Rooms.vue'

const open = defineModel<boolean>('open', { required: true })

const currentStep = ref(0)

const steps = [
    { key: 'basic',      label: 'Basic Information', component: BasicInformation },
    { key: 'amenities',  label: 'Amenities',          component: Amenities },
    { key: 'Room Image',     label: 'Room Image',             component: RoomImage },
    { key: 'rooms',      label: 'Rooms',              component: Rooms },
]

function goToStep(i: number) {
    if (i < currentStep.value) currentStep.value = i
}

function handleContinue() {
    if (currentStep.value < steps.length - 1) {
        currentStep.value++
    } else {
        // TODO: submit
        open.value = false
        currentStep.value = 0
    }
}
</script>
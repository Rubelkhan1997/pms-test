<template>
    <div class="relative" ref="el">
        <!-- Trigger -->
        <button
            type="button"
            class="flex items-center gap-1 flex-wrap min-h-8.5 w-full px-2.5 py-1 border border-slate-200 rounded-md bg-white hover:border-slate-300 transition-colors text-left"
            @click="toggleOpen">

            <span v-if="modelValue.length === 0" class="text-[12.5px] text-slate-500 flex-1">
                {{ placeholder }}
            </span>

            <template v-else>
                <span
                    v-for="val in modelValue" :key="val"
                    class="inline-flex items-center gap-1 text-[11.5px] font-medium px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded">
                    {{ val }}
                    <span
                        class="hover:text-red-500 cursor-pointer leading-none"
                        @mousedown.stop="remove(val)">×</span>
                </span>
            </template>

            <ChevronDown
                class="w-3 h-3 text-slate-400 ml-auto shrink-0 transition-transform duration-200"
                :class="open ? 'rotate-180' : ''"
                :stroke-width="2" />
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition-all duration-150 ease-out"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-100 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0">
            <div
                v-if="open"
                :class="[
                    'absolute left-0 z-50 bg-white border border-slate-200 rounded-lg shadow-lg py-2 w-full',
                    dropUp ? 'bottom-full mb-1' : 'top-full mt-1'
                ]">

                <!-- Search inside dropdown -->
                <div v-if="searchable" class="px-2.5 pb-1.5 border-b border-slate-100 mb-1">
                    <input
                        v-model="innerSearch"
                        type="text"
                        placeholder="Search..."
                        class="w-full px-2.5 py-1.5 text-[12px] border border-slate-200 rounded-sm focus:outline-none focus:border-primary placeholder:text-slate-300 transition-colors" />
                </div>

                <div class="max-h-52 overflow-y-auto">
                    <button
                        v-for="opt in filteredOptions" :key="opt"
                        type="button"
                        class="w-full flex items-center gap-2.5 px-3 py-2 text-[12.5px] hover:bg-slate-50 transition-colors"
                        @click="toggle(opt)">
                        <span :class="[
                            'w-3.5 h-3.5 rounded border flex items-center justify-center shrink-0 transition-colors',
                            modelValue.includes(opt) ? 'bg-primary border-primary' : 'border-slate-300',
                        ]">
                            <svg v-if="modelValue.includes(opt)" class="w-2 h-2 text-white" viewBox="0 0 10 10" fill="none">
                                <path d="M1.5 5l2.5 2.5 4.5-4.5" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <span :class="modelValue.includes(opt) ? 'text-slate-800 font-medium' : 'text-slate-600'">
                            {{ opt }}
                        </span>
                    </button>

                    <p v-if="filteredOptions.length === 0" class="px-3 py-3 text-[12px] text-slate-400 text-center">
                        No results
                    </p>
                </div>

                <!-- Footer: clear all -->
                <div v-if="modelValue.length > 0" class="border-t border-slate-100 mt-1 px-3 pt-2 pb-1">
                    <button
                        type="button"
                        class="text-[11.5px] text-slate-400 hover:text-red-500 transition-colors"
                        @click="$emit('update:modelValue', [])">
                        Clear all
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue'
import { ChevronDown } from 'lucide-vue-next'

// ─── Props ───────────────────────────────────────────────
const props = defineProps<{
    modelValue:  string[]
    options:     string[]
    placeholder?: string
    searchable?:  boolean
}>()

const emit = defineEmits<{
    'update:modelValue': [value: string[]]
}>()

// ─── State ───────────────────────────────────────────────
const open        = ref(false)
const dropUp      = ref(false)           // ← নতুন
const innerSearch = ref('')
const el          = ref<HTMLElement | null>(null)

// ─── Computed ────────────────────────────────────────────
const filteredOptions = computed(() =>
    !innerSearch.value.trim()
        ? props.options
        : props.options.filter(o => o.toLowerCase().includes(innerSearch.value.toLowerCase()))
)

// ─── Methods ─────────────────────────────────────────────

// dropdown খোলার আগে জায়গা check করে দিক ঠিক করে
async function toggleOpen() {
    open.value = !open.value

    if (open.value) {
        await nextTick()
        checkDropDirection()
    }
}

function checkDropDirection() {
    if (!el.value) return

    const DROPDOWN_HEIGHT = 240          // max-h-52 ≈ 208px + padding
    const MARGIN          = 8            // একটু extra গ্যাপ

    const triggerRect = el.value.getBoundingClientRect()
    const spaceBelow  = window.innerHeight - triggerRect.bottom
    const spaceAbove  = triggerRect.top

    // নিচে জায়গা কম এবং উপরে বেশি জায়গা থাকলে উপরে খুলবে
    dropUp.value = spaceBelow < DROPDOWN_HEIGHT + MARGIN && spaceAbove > spaceBelow
}

function toggle(val: string) {
    const next = props.modelValue.includes(val)
        ? props.modelValue.filter(v => v !== val)
        : [...props.modelValue, val]
    emit('update:modelValue', next)
}

function remove(val: string) {
    emit('update:modelValue', props.modelValue.filter(v => v !== val))
}

function onClickOutside(e: MouseEvent) {
    if (el.value && !el.value.contains(e.target as Node)) {
        open.value = false
        innerSearch.value = ''
    }
}

// scroll বা resize হলে দিক recalculate
function onScrollOrResize() {
    if (open.value) checkDropDirection()
}

onMounted(() => {
    document.addEventListener('mousedown', onClickOutside)
    window.addEventListener('scroll', onScrollOrResize, true)
    window.addEventListener('resize', onScrollOrResize)
})

onUnmounted(() => {
    document.removeEventListener('mousedown', onClickOutside)
    window.removeEventListener('scroll', onScrollOrResize, true)
    window.removeEventListener('resize', onScrollOrResize)
})
</script>
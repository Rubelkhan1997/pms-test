// FILE: resources/js/Components/Form/FormRadio.vue
<template>
    <div class="form-control flex-row">
        <label class="label cursor-pointer flex items-center gap-2">
            <span
                :class="[
                    'relative flex items-center justify-center w-4 h-4 rounded-full border-2 shrink-0 transition-all duration-150',
                    isChecked
                        ? 'border-primary bg-primary'
                        : 'border-gray-300 bg-white',
                ]"
            >
                <span
                    :class="[
                        'w-1.5 h-1.5 rounded-full bg-white transition-all duration-150',
                        isChecked ? 'opacity-100 scale-100' : 'opacity-0 scale-0',
                    ]"
                />
            </span>
            <input
                type="radio"
                :name="name"
                :value="value"
                :checked="isChecked"
                class="sr-only"
                @change="onChange"
            />
            <span class="label-text text-sm text-slate-600">{{ label }}</span>
        </label>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(defineProps<{
    label?: string
    name: string
    value: string | number | boolean
    modelValue?: string | number | boolean | null
    current?: string | number | boolean | null
}>(), {
    label: '',
    modelValue: null,
    current: null,
})

const emit = defineEmits<{
    'update:modelValue': [value: string | number | boolean]
}>()

const isChecked = computed(() => {
    if (props.modelValue !== null && props.modelValue !== undefined) {
        return props.modelValue === props.value
    }
    return props.current === props.value
})

function onChange(): void {
    emit('update:modelValue', props.value)
}
</script>
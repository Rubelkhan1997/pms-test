<template>
    <div :class="wrapperClass">
        <label
            v-if="label"
            :for="resolvedId"
            :class="['text-gray-800 font-medium mb-2', labelClass || 'text-[14px] block']"
        >
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <select
            :id="resolvedId"
            :name="name || resolvedId"
            :required="required"
            :disabled="disabled"
            :class="[
                'file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                error ? 'border-red-500' : 'border-gray-300',
                inputClass,
            ]"
            :value="modelValue ?? ''"
            @change="onChange"
            @blur="$emit('blur', $event)"
        >
            <option v-if="placeholder" value="">{{ placeholder }}</option>
            <option
                v-for="option in options"
                :key="String(option[optionValue])"
                :value="option[optionValue]"
            >
                {{ option[optionLabel] }}
            </option>
        </select>

        <span v-if="error" class="text-red-500 pt-1 block text-sm">{{ error }}</span>
    </div>
</template>

<script setup lang="ts">
    import { computed } from 'vue';

    // Generic select option shape with configurable label/value keys.
    type SelectOption = Record<string, string | number>;

    // Props for label, options list, validation state, and custom key mapping.
    const props = withDefaults(defineProps<{
        modelValue?: string | number | null;
        label?: string | null;
        id?: string;
        name?: string;
        required?: boolean;
        disabled?: boolean;
        placeholder?: string;
        error?: string;
        options?: SelectOption[];
        optionLabel?: string;
        optionValue?: string;
        wrapperClass?: string;
        labelClass?: string;
        inputClass?: string;
    }>(), {
        modelValue: '',
        label: null,
        id: '',
        name: '',
        required: false,
        disabled: false,
        placeholder: '',
        error: '',
        options: () => [],
        optionLabel: 'label',
        optionValue: 'value',
        wrapperClass: 'relative mb-4',
        labelClass: '',
        inputClass: '',
    });

    // Emits selected option value and blur event for parent form integration.
    const emit = defineEmits<{
        'update:modelValue': [value: string];
        blur: [event: FocusEvent];
    }>();

    // Resolve select element id for label binding and accessibility.
    const resolvedId = computed(() => props.id || props.name || undefined);

    // Normalize selected value and propagate it via v-model.
    function onChange(event: Event): void {
        const target = event.target as HTMLSelectElement;
        emit('update:modelValue', target.value);
    }
</script>

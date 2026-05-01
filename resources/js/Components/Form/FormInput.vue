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

        <input
            :id="resolvedId"
            :name="name || resolvedId"
            :type="type"
            :value="modelValue ?? ''"
            :required="required"
            :placeholder="placeholder"
            :min="min"
            :max="max"
            :step="step"
           

            :class="[
                'file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                error ? 'border-red-500' : 'border-gray-300',
                inputClass,
            ]"
            @input="onInput"
            @blur="$emit('blur', $event)"
        />

        <span v-if="error" class="text-red-500 pt-1 block text-sm">{{ error }}</span>
    </div>
</template>

<script setup lang="ts">
    import { computed } from 'vue';

    // Configurable props to support text/email/number and validation feedback.
    const props = withDefaults(defineProps<{
        modelValue?: string | number | null;
        label?: string | null;
        id?: string;
        name?: string;
        type?: string;
        placeholder?: string;
        required?: boolean;
        min?: string | number;
        max?: string | number;
        step?: string | number;
        error?: string;
        wrapperClass?: string;
        labelClass?: string;
        inputClass?: string;
    }>(), {
        modelValue: '',
        label: null,
        id: '',
        name: '',
        type: 'text',
        placeholder: '',
        required: false,
        min: '',
        max: '',
        step: '',
        error: '',
        wrapperClass: 'relative mb-4',
        labelClass: '',
        inputClass: '',
    });

    // Emits v-model updates and blur event for parent form handling.
    const emit = defineEmits<{
        'update:modelValue': [value: string];
        blur: [event: FocusEvent];
    }>();

    // Resolve a stable input id from explicit id, fallback name.
    const resolvedId = computed(() => props.id || props.name || undefined);

    // Normalize input event payload to string for v-model binding.
    function onInput(event: Event): void {
        const target = event.target as HTMLInputElement;
        emit('update:modelValue', target.value);
    }
</script>

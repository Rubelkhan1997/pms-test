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

        <textarea
            :id="resolvedId"
            :name="name || resolvedId"
            :rows="rows"
            :required="required"
            :placeholder="placeholder"
           :class="[
            'file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input  w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
            'focus-visible:border-primary focus-visible:ring-0 focus-visible:outline-none',
            'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
            error ? 'border-red-500' : 'border-gray-300',
            inputClass,
        ]"
            :value="modelValue ?? ''"
            @input="onInput"
            @blur="$emit('blur', $event)"
        ></textarea>

        <span v-if="error" class="text-red-500 pt-1 block text-sm">{{ error }}</span>
    </div>
</template>

<script setup lang="ts">
    import { computed } from 'vue';

    // Textarea props mirror FormInput behavior for consistent API.
    const props = withDefaults(defineProps<{
        modelValue?: string | null;
        label?: string | null;
        id?: string;
        name?: string;
        rows?: number;
        placeholder?: string;
        required?: boolean;
        error?: string;
        wrapperClass?: string;
        labelClass?: string;
        inputClass?: string;
    }>(), {
        modelValue: '',
        label: null,
        id: '',
        name: '',
        rows: 3,
        placeholder: '',
        required: false,
        error: '',
        wrapperClass: 'relative mb-4',
        labelClass: '',
        inputClass: '',
    });

    // Emits textarea value updates and blur event.
    const emit = defineEmits<{
        'update:modelValue': [value: string];
        blur: [event: FocusEvent];
    }>();

    // Resolve textarea id from explicit id or name.
    const resolvedId = computed(() => props.id || props.name || undefined);

    // Emit current textarea content on each input event.
    function onInput(event: Event): void {
        const target = event.target as HTMLTextAreaElement;
        emit('update:modelValue', target.value);
    }
</script>

<template>
    <div class="form-group">
        <button
            :type="type"
            :disabled="disabled"
            :class="[
                'btn inline-flex items-center justify-center rounded-lg px-4 py-2 transition disabled:opacity-50 disabled:cursor-not-allowed',
                colorClass,
                buttonClass,
            ]"
        >
            <slot>{{ name }}</slot>
        </button>
    </div>
</template>

<script setup lang="ts">
    import { computed } from 'vue';

    // Public API for the reusable button component.
    const props = withDefaults(defineProps<{
        type?: 'button' | 'submit' | 'reset';
        color?: 'primary' | 'secondary' | 'success' | 'danger' | 'warning' | string;
        name?: string;
        disabled?: boolean;
        buttonClass?: string;
    }>(), {
        type: 'button',
        color: 'primary',
        name: '',
        disabled: false,
        buttonClass: '',
    });

    // Maps semantic color names to Tailwind utility classes.
    const colorClass = computed(() => {
        const map: Record<string, string> = {
            primary: 'bg-blue-600 text-white hover:bg-blue-700',
            secondary: 'bg-slate-200 text-slate-700 hover:bg-slate-300',
            success: 'bg-green-600 text-white hover:bg-green-700',
            danger: 'bg-red-600 text-white hover:bg-red-700',
            warning: 'bg-amber-500 text-white hover:bg-amber-600',
        };

        return map[props.color] || props.color;
    });
</script>

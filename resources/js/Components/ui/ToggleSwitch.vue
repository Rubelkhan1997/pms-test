<!-- components/ui/ToggleSwitch.vue -->
<script setup lang="ts">
defineProps<{ modelValue: boolean; disabled?: boolean }>()
defineEmits<{ 'update:modelValue': [value: boolean] }>()
</script>

<template>
  <button
    type="button"
    role="switch"
    :aria-checked="modelValue"
    :disabled="disabled"
    @click="$emit('update:modelValue', !modelValue)"
    class="relative inline-flex h-6 w-11 shrink-0 rounded-full p-[3px] transition-colors duration-200 ease-in-out focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary disabled:cursor-not-allowed disabled:opacity-40"
    :class="modelValue ? 'bg-primary' : 'bg-slate-200'"
  >
    <span
      class="pointer-events-none block h-[18px] w-[18px] rounded-full bg-white shadow-[0_1px_2px_rgba(0,0,0,0.18),0_0_0_0.5px_rgba(0,0,0,0.06)] transition-transform duration-200 ease-in-out will-change-transform"
      :class="modelValue ? 'translate-x-5' : 'translate-x-0'"
    />
  </button>
</template>
<script setup lang="ts">
import { computed } from "vue";
import { cn } from "@/lib/utils";

const props = defineProps<{
    value?: number;
    class?: string;
}>();

const width = computed(() => {
    const safe = Math.min(100, Math.max(0, props.value ?? 0));
    return `${safe}%`;
});
</script>

<template>
    <div
        :class="cn('relative h-2 w-full overflow-hidden rounded-full bg-secondary', props.class)"
    >
        <div
            class="h-full bg-primary transition-all"
            :style="{ width }"
        ></div>
    </div>
</template>

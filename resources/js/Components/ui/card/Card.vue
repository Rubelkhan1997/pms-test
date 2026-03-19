<template>
    <div :class="cardClass">
        <slot />
    </div>
</template>

<script setup lang="ts">
import { cn } from "@/lib/utils";

const props = defineProps<{
    class?: string;
}>();

const cardClass = cn(
    "rounded-lg border border-border bg-card text-card-foreground shadow-sm",
    props.class,
);
</script>

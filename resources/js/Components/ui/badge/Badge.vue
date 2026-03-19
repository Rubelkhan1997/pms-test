<script setup lang="ts">
import { cva, type VariantProps } from "class-variance-authority";
import { cn } from "@/lib/utils";

const badgeVariants = cva(
    "inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2",
    {
        variants: {
            variant: {
                default:
                    "border-transparent bg-primary text-primary-foreground hover:bg-primary/80",
                secondary:
                    "border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80",
                outline: "text-foreground",
                success:
                    "border-transparent bg-emerald-100 text-emerald-700",
                warning:
                    "border-transparent bg-amber-100 text-amber-700",
                danger: "border-transparent bg-rose-100 text-rose-700",
            },
        },
        defaultVariants: {
            variant: "default",
        },
    },
);

type BadgeVariants = VariantProps<typeof badgeVariants>;

const props = defineProps<{
    variant?: BadgeVariants["variant"];
    class?: string;
}>();
</script>

<template>
    <span :class="cn(badgeVariants({ variant: props.variant }), props.class)">
        <slot />
    </span>
</template>

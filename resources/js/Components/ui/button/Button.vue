<script setup lang="ts">
import { cva, type VariantProps } from "class-variance-authority";
import { cn } from "@/lib/utils";

const buttonVariants = cva(
    "inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 ring-offset-background",
    {
        variants: {
            variant: {
                default: "bg-primary text-primary-foreground hover:bg-primary/90",
                secondary:
                    "bg-secondary text-secondary-foreground hover:bg-secondary/80",
                outline:
                    "border border-input bg-background hover:bg-accent hover:text-accent-foreground",
                ghost: "hover:bg-accent hover:text-accent-foreground",
                destructive:
                    "bg-destructive text-destructive-foreground hover:bg-destructive/90",
                link: "text-primary underline-offset-4 hover:underline",
            },
            size: {
                default: "h-10 px-4 py-2",
                sm: "h-9 px-3",
                lg: "h-11 px-8",
                icon: "h-10 w-10",
            },
        },
        defaultVariants: {
            variant: "default",
            size: "default",
        },
    },
);

type ButtonVariants = VariantProps<typeof buttonVariants>;

type AsType = string | object;

const props = defineProps<{
    variant?: ButtonVariants["variant"];
    size?: ButtonVariants["size"];
    as?: AsType;
    class?: string;
    type?: "button" | "submit" | "reset";
}>();
</script>

<template>
    <component
        :is="props.as ?? 'button'"
        :class="cn(buttonVariants({ variant: props.variant, size: props.size }), props.class)"
        :type="props.type ?? (props.as ? undefined : 'button')"
    >
        <slot />
    </component>
</template>

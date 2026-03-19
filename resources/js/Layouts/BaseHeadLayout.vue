<template>
    <div>
        <Head :title="pageTitle" />
        <slot />
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { Head, usePage } from "@inertiajs/vue3";

const page = usePage();

const pageTitle = computed(() => {
    const component = String(page.component ?? "");
    if (!component) {
        return "Hotel PMS";
    }

    const parts = component.split("/").filter(Boolean);
    let name = parts[parts.length - 1] ?? "Hotel PMS";

    if (name === "Index" && parts.length > 1) {
        name = parts[parts.length - 2];
    }

    const pretty = name
        .replace(/([a-z0-9])([A-Z])/g, "$1 $2")
        .replace(/_/g, " ")
        .trim();

    return pretty || "Hotel PMS";
});
</script>

<template>
    <div class="space-y-6">
        <Head title="Tenant Details" />
        <Card>
            <CardHeader class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="space-y-2">
                    <div class="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                        Tenant profile
                    </div>
                    <CardTitle class="flex items-center gap-2">
                        <Building2 class="h-5 w-5 text-muted-foreground" />
                        {{ tenant.name }}
                    </CardTitle>
                    <CardDescription>
                        {{ tenant.email }}
                    </CardDescription>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Badge :variant="statusVariant">{{ tenant.status }}</Badge>
                    <Button :as="Link" :href="route('central.tenants.index')" variant="outline">
                        Back to tenants
                    </Button>
                </div>
            </CardHeader>
            <CardContent class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2 text-sm">
                    <div class="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                        Details
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Subdomain</span>
                        <code class="rounded bg-muted px-2 py-1 text-xs">
                            {{ tenant.subdomain }}.pms.test
                        </code>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Database</span>
                        <span class="font-medium">{{ tenant.database_name }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Created</span>
                        <span class="font-medium">{{ formatDate(tenant.created_at) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Activated</span>
                        <span class="font-medium">
                            {{ tenant.activated_at ? formatDate(tenant.activated_at) : "Not yet" }}
                        </span>
                    </div>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                        Owner
                    </div>
                    <div v-if="primaryOwner" class="space-y-1">
                        <div class="flex items-center gap-2">
                            <UserCircle2 class="h-4 w-4 text-muted-foreground" />
                            <span class="font-medium">{{ primaryOwner.name }}</span>
                        </div>
                        <div class="text-xs text-muted-foreground">{{ primaryOwner.email }}</div>
                    </div>
                    <div v-else class="text-xs text-muted-foreground">
                        No owner assigned
                    </div>
                </div>
            </CardContent>
        </Card>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Card>
                <CardHeader class="space-y-2">
                    <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                        <span>Rooms</span>
                        <BedDouble class="h-4 w-4" />
                    </div>
                    <div class="text-2xl font-semibold">{{ stats.total_rooms }}</div>
                </CardHeader>
                <CardContent>
                    <CardDescription>Total rooms in tenant DB</CardDescription>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="space-y-2">
                    <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                        <span>Reservations</span>
                        <CalendarCheck class="h-4 w-4" />
                    </div>
                    <div class="text-2xl font-semibold">{{ stats.total_reservations }}</div>
                </CardHeader>
                <CardContent>
                    <CardDescription>Reservations on record</CardDescription>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="space-y-2">
                    <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                        <span>Guests</span>
                        <Users class="h-4 w-4" />
                    </div>
                    <div class="text-2xl font-semibold">{{ stats.total_guests }}</div>
                </CardHeader>
                <CardContent>
                    <CardDescription>Guests served</CardDescription>
                </CardContent>
            </Card>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import CentralLayout from "../../../Layouts/CentralLayout.vue";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import {
    BedDouble,
    Building2,
    CalendarCheck,
    UserCircle2,
    Users,
} from "lucide-vue-next";

defineOptions({ layout: CentralLayout });

const props = defineProps({
    tenant: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
});

const primaryOwner = computed(() => {
    return props.tenant.owners?.[0] ?? null;
});

const statusVariant = computed(() => {
    if (props.tenant.status === "active") return "success";
    if (props.tenant.status === "pending") return "warning";
    return "danger";
});

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}
</script>

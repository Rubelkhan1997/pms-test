<template>
    <div class="space-y-6">
        <Card>
            <CardHeader class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                        Tenant control
                    </div>
                    <CardTitle>Tenant management</CardTitle>
                    <CardDescription>
                        Review the lifecycle of every property in your portfolio.
                    </CardDescription>
                </div>
                <Button :as="Link" :href="route('central.tenants.create')">
                    Add tenant
                </Button>
            </CardHeader>
            <CardContent>
                <div class="flex flex-wrap gap-2">
                    <Badge variant="warning">Pending {{ stats.pending }}</Badge>
                    <Badge variant="success">Active {{ stats.active }}</Badge>
                    <Badge variant="danger">Suspended {{ stats.suspended }}</Badge>
                </div>
            </CardContent>
        </Card>

        <div v-if="$page.props.flash?.success" class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ $page.props.flash.success }}
        </div>

        <div v-if="$page.props.flash?.error" class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {{ $page.props.flash.error }}
        </div>

        <Card>
            <CardContent>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b">
                            <tr class="text-left text-xs uppercase tracking-[0.25em] text-muted-foreground">
                                <th class="py-3">Tenant</th>
                                <th class="py-3">Subdomain</th>
                                <th class="py-3">Admin</th>
                                <th class="py-3">Status</th>
                                <th class="py-3">Created</th>
                                <th class="py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="tenant in tenants.data" :key="tenant.id">
                                <td class="py-3">
                                    <div class="font-medium">{{ tenant.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ tenant.email }}</div>
                                </td>
                                <td class="py-3">
                                    <code class="rounded bg-muted px-2 py-1 text-xs">
                                        {{ tenant.subdomain }}.pms.test
                                    </code>
                                </td>
                                <td class="py-3">
                                    <div v-if="tenant.owners && tenant.owners.length > 0">
                                        <div class="font-medium">{{ tenant.owners[0].name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ tenant.owners[0].email }}</div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <Badge :variant="tenant.status === 'active'
                                        ? 'success'
                                        : tenant.status === 'pending'
                                        ? 'warning'
                                        : 'danger'">
                                        {{ tenant.status }}
                                    </Badge>
                                </td>
                                <td class="py-3">{{ formatDate(tenant.created_at) }}</td>
                                <td class="py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <Button
                                            :as="Link"
                                            :href="route('central.tenants.show', tenant.id)"
                                            variant="outline"
                                            size="sm"
                                        >
                                            View
                                        </Button>

                                        <template v-if="tenant.status === 'pending'">
                                            <Button
                                                @click="approveTenant(tenant)"
                                                variant="secondary"
                                                size="sm"
                                                :disabled="processing"
                                            >
                                                Approve
                                            </Button>
                                            <Button
                                                @click="rejectTenant(tenant)"
                                                variant="destructive"
                                                size="sm"
                                                :disabled="processing"
                                            >
                                                Reject
                                            </Button>
                                        </template>

                                        <template v-else-if="tenant.status === 'active'">
                                            <Button
                                                @click="suspendTenant(tenant)"
                                                variant="secondary"
                                                size="sm"
                                                :disabled="processing"
                                            >
                                                Suspend
                                            </Button>
                                        </template>

                                        <template v-else-if="tenant.status === 'suspended'">
                                            <Button
                                                @click="reactivateTenant(tenant)"
                                                variant="secondary"
                                                size="sm"
                                                :disabled="processing"
                                            >
                                                Reactivate
                                            </Button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <div class="flex flex-wrap justify-center gap-2">
            <Button
                v-for="(link, index) in tenants.links"
                :key="index"
                :as="link.url ? Link : 'button'"
                :href="link.url ?? undefined"
                variant="outline"
                size="sm"
                :class="link.active ? 'border-primary text-primary' : ''"
                :disabled="!link.url"
            >
                <span v-html="link.label"></span>
            </Button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import CentralLayout from "../../../Layouts/CentralLayout.vue";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";

defineOptions({ layout: CentralLayout });

const props = defineProps({
    tenants: {
        type: Object,
        required: true,
    },
});

const processing = ref(false);

const stats = computed(() => ({
    pending: props.tenants.data.filter((t) => t.status === "pending").length,
    active: props.tenants.data.filter((t) => t.status === "active").length,
    suspended: props.tenants.data.filter((t) => t.status === "suspended").length,
}));

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}

function approveTenant(tenant: { id: number; name: string }) {
    if (
        confirm(
            `Approve tenant "${tenant.name}"? This will provision their database.`,
        )
    ) {
        processing.value = true;
        router.post(route("central.tenants.approve", tenant.id));
    }
}

function rejectTenant(tenant: { id: number; name: string }) {
    const reason = prompt("Rejection reason (optional):");
    if (reason !== null) {
        processing.value = true;
        router.post(route("central.tenants.reject", tenant.id), {
            rejection_reason: reason,
        });
    }
}

function suspendTenant(tenant: { id: number; name: string }) {
    if (confirm(`Suspend tenant "${tenant.name}"?`)) {
        processing.value = true;
        router.post(route("central.tenants.suspend", tenant.id));
    }
}

function reactivateTenant(tenant: { id: number; name: string }) {
    if (confirm(`Reactivate tenant "${tenant.name}"?`)) {
        processing.value = true;
        router.post(route("central.tenants.reactivate", tenant.id));
    }
}
</script>

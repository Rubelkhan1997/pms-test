<template>
    <div class="space-y-6">
        <Head title="Central Dashboard" />
        <div class="grid gap-4 lg:grid-cols-[2fr_1fr]">
            <Card>
                <CardHeader class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                            Central overview
                        </div>
                        <Building2 class="h-5 w-5 text-muted-foreground" />
                    </div>
                    <CardTitle>Central overview for consistent SAAS control.</CardTitle>
                    <CardDescription>
                        Welcome back, {{ $page.props.auth?.user?.name || "Admin" }}.
                        Monitor tenant health, approvals, and adoption signals from one workspace.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-3 text-sm text-muted-foreground">
                        <div class="flex items-center justify-between">
                            <span>Pending approvals</span>
                            <span class="font-semibold text-foreground">{{ stats.pending_tenants }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Active tenants</span>
                            <span class="font-semibold text-foreground">{{ stats.active_tenants }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Suspended</span>
                            <span class="font-semibold text-foreground">{{ stats.suspended_tenants }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card class="bg-muted/40">
                <CardHeader class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                            Tenants onboarded
                        </div>
                        <Users class="h-5 w-5 text-muted-foreground" />
                    </div>
                    <div class="text-3xl font-semibold">{{ stats.total_tenants }}</div>
                </CardHeader>
                <CardContent>
                    <div class="text-sm text-muted-foreground">
                        Total admins: <span class="font-semibold text-foreground">{{ stats.total_users }}</span>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <Card>
                <CardHeader class="space-y-2">
                    <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                        <span>Tenants</span>
                        <Building2 class="h-4 w-4" />
                    </div>
                    <div class="text-2xl font-semibold">{{ stats.total_tenants }}</div>
                </CardHeader>
                <CardContent>
                    <CardDescription>Total tenant count</CardDescription>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="space-y-2">
                    <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                        <span>Pending</span>
                        <Clock class="h-4 w-4" />
                    </div>
                    <div class="text-2xl font-semibold">{{ stats.pending_tenants }}</div>
                </CardHeader>
                <CardContent>
                    <CardDescription>Awaiting approval</CardDescription>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="space-y-2">
                    <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                        <span>Active</span>
                        <CheckCircle2 class="h-4 w-4" />
                    </div>
                    <div class="text-2xl font-semibold">{{ stats.active_tenants }}</div>
                </CardHeader>
                <CardContent>
                    <CardDescription>Operational tenants</CardDescription>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="space-y-2">
                    <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                        <span>Suspended</span>
                        <ShieldAlert class="h-4 w-4" />
                    </div>
                    <div class="text-2xl font-semibold">{{ stats.suspended_tenants }}</div>
                </CardHeader>
                <CardContent>
                    <CardDescription>Require review</CardDescription>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="space-y-2">
                    <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                        <span>Users</span>
                        <UserCircle2 class="h-4 w-4" />
                    </div>
                    <div class="text-2xl font-semibold">{{ stats.total_users }}</div>
                </CardHeader>
                <CardContent>
                    <CardDescription>Tenant admins</CardDescription>
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader class="flex flex-row items-start justify-between">
                <div class="flex items-start gap-3">
                    <div class="mt-1 flex h-9 w-9 items-center justify-center rounded-full bg-muted">
                        <ClipboardList class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <div>
                        <CardTitle>Recent tenants</CardTitle>
                        <CardDescription>Newest properties across your network.</CardDescription>
                    </div>
                </div>
                <Button :as="Link" :href="route('central.tenants.index')" variant="outline">
                    View all tenants
                </Button>
            </CardHeader>
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
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="tenant in recentTenants" :key="tenant.id">
                                <td class="py-3">
                                    <div class="font-medium">{{ tenant.name }}</div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ tenant.email }}
                                    </div>
                                </td>
                                <td class="py-3">
                                    <code class="rounded bg-muted px-2 py-1 text-xs">
                                        {{ tenant.subdomain }}.pms.test
                                    </code>
                                </td>
                                <td class="py-3">
                                    <div v-if="tenant.owners && tenant.owners.length > 0">
                                        {{ tenant.owners[0].name }}
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
                            </tr>
                            <tr v-if="recentTenants.length === 0">
                                <td colspan="5" class="py-8 text-center text-muted-foreground">
                                    No tenants yet
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Quick actions</CardTitle>
                <CardDescription>Jump straight to priority workflows.</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <Button :as="Link" :href="route('central.tenants.index')" variant="outline" class="justify-start">
                        <Building2 class="mr-2 h-4 w-4" />
                        Manage tenants
                    </Button>
                    <Button :as="Link" :href="route('central.profile')" variant="outline" class="justify-start">
                        <UserCircle2 class="mr-2 h-4 w-4" />
                        Profile settings
                    </Button>
                    <Button as="a" href="/central/tenants?status=pending" variant="outline" class="justify-start">
                        <Clock class="mr-2 h-4 w-4" />
                        Pending approvals
                    </Button>
                    <Button as="a" href="#" variant="outline" class="justify-start">
                        <BarChart3 class="mr-2 h-4 w-4" />
                        System reports
                    </Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import CentralLayout from "../../Layouts/CentralLayout.vue";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import {
    BarChart3,
    Building2,
    CheckCircle2,
    ClipboardList,
    Clock,
    ShieldAlert,
    UserCircle2,
    Users,
} from "lucide-vue-next";

defineOptions({ layout: CentralLayout });

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    recentTenants: {
        type: Array,
        required: true,
    },
    auth: {
        type: Object,
        default: () => ({ user: null }),
    },
});

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}
</script>

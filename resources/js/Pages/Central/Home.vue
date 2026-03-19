<template>
    <div class="bg-muted/30">
        <Head title="Central Home" />
        <div class="mx-auto max-w-6xl px-6 py-20">
            <div class="grid gap-10 lg:grid-cols-[2fr_1fr]">
                <div class="space-y-6">
                    <div class="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                        Hotel SAAS platform
                    </div>
                    <h1 class="text-4xl font-semibold tracking-tight md:text-5xl">
                        Build a resilient property network in one place.
                    </h1>
                    <p class="max-w-2xl text-lg text-muted-foreground">
                        Centralized PMS operations with rapid onboarding, approval workflows, and deep visibility across every tenant.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <Button :as="Link" :href="route('central.register')">Start free trial</Button>
                        <Button :as="Link" :href="route('central.login')" variant="outline">Sign in</Button>
                    </div>
                </div>

                <Card class="bg-background">
                    <CardHeader>
                        <CardTitle>Central signals</CardTitle>
                        <CardDescription>Operations snapshots you can trust.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3 text-sm text-muted-foreground">
                        <div class="flex items-center justify-between">
                            <span>Approval time</span>
                            <span class="font-medium text-foreground">Under 5 minutes</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Provisioning success</span>
                            <span class="font-medium text-foreground">99.9 percent</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Central accuracy</span>
                            <span class="font-medium text-foreground">Realtime dashboards</span>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <div class="mx-auto max-w-6xl px-6 pb-16">
            <Card>
                <CardHeader>
                    <CardTitle>Everything central needs</CardTitle>
                    <CardDescription>Plan, monitor, and scale across every property.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <div class="rounded-lg border border-border bg-background p-4">
                            <div class="font-medium">Property oversight</div>
                            <p class="text-sm text-muted-foreground">
                                Track rooms, reservations, and operational health in one view.
                            </p>
                        </div>
                        <div class="rounded-lg border border-border bg-background p-4">
                            <div class="font-medium">Channel coverage</div>
                            <p class="text-sm text-muted-foreground">
                                Coordinate OTA syncs and pricing strategy from the hub.
                            </p>
                        </div>
                        <div class="rounded-lg border border-border bg-background p-4">
                            <div class="font-medium">Revenue controls</div>
                            <p class="text-sm text-muted-foreground">
                                Audit financial performance with secure, auditable workflows.
                            </p>
                        </div>
                        <div class="rounded-lg border border-border bg-background p-4">
                            <div class="font-medium">Housekeeping signal</div>
                            <p class="text-sm text-muted-foreground">
                                Monitor service readiness and staffing impact.
                            </p>
                        </div>
                        <div class="rounded-lg border border-border bg-background p-4">
                            <div class="font-medium">Analytics layers</div>
                            <p class="text-sm text-muted-foreground">
                                See occupancy, RevPAR, and channel performance quickly.
                            </p>
                        </div>
                        <div class="rounded-lg border border-border bg-background p-4">
                            <div class="font-medium">Secure onboarding</div>
                            <p class="text-sm text-muted-foreground">
                                Govern tenant creation with approvals and provisioning checks.
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="mx-auto max-w-6xl px-6 pb-20">
            <Card>
                <CardHeader>
                    <CardTitle>Simple pricing that scales</CardTitle>
                    <CardDescription>Plans aligned with growing hospitality groups.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="rounded-lg border border-border bg-background p-5">
                            <div class="text-sm font-semibold uppercase tracking-[0.25em] text-muted-foreground">
                                Starter
                            </div>
                            <div class="mt-4 text-3xl font-semibold">$49<span class="text-base text-muted-foreground">/month</span></div>
                            <ul class="mt-4 space-y-2 text-sm text-muted-foreground">
                                <li>Up to 20 rooms</li>
                                <li>1 property</li>
                                <li>Essential reporting</li>
                                <li>Email support</li>
                                <li>No channel manager</li>
                            </ul>
                            <Button :as="Link" :href="route('central.register')" variant="outline" class="mt-6 w-full">
                                Get started
                            </Button>
                        </div>
                        <div class="rounded-lg border border-primary/40 bg-background p-5">
                            <div class="text-xs font-semibold uppercase tracking-[0.25em] text-primary">Most popular</div>
                            <div class="mt-2 text-sm font-semibold uppercase tracking-[0.25em] text-muted-foreground">
                                Professional
                            </div>
                            <div class="mt-4 text-3xl font-semibold">$99<span class="text-base text-muted-foreground">/month</span></div>
                            <ul class="mt-4 space-y-2 text-sm text-muted-foreground">
                                <li>Up to 100 rooms</li>
                                <li>5 properties</li>
                                <li>Advanced reporting</li>
                                <li>Priority support</li>
                                <li>Channel manager</li>
                            </ul>
                            <Button :as="Link" :href="route('central.register')" class="mt-6 w-full">
                                Get started
                            </Button>
                        </div>
                        <div class="rounded-lg border border-border bg-background p-5">
                            <div class="text-sm font-semibold uppercase tracking-[0.25em] text-muted-foreground">
                                Enterprise
                            </div>
                            <div class="mt-4 text-3xl font-semibold">$249<span class="text-base text-muted-foreground">/month</span></div>
                            <ul class="mt-4 space-y-2 text-sm text-muted-foreground">
                                <li>Unlimited rooms</li>
                                <li>Unlimited properties</li>
                                <li>Custom reporting</li>
                                <li>24/7 support</li>
                                <li>Full integration</li>
                            </ul>
                            <Button :as="Link" :href="route('central.register')" variant="outline" class="mt-6 w-full">
                                Contact sales
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <footer class="border-t border-border py-6 text-center text-sm text-muted-foreground">
            2026 Hotel PMS. All rights reserved.
        </footer>
    </div>
</template>

<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
</script>

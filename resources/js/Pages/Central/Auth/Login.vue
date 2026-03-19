<template>
    <div class="min-h-screen bg-muted/30 px-6 py-12">
        <div class="mx-auto grid max-w-5xl items-center gap-8 lg:grid-cols-[1fr_1fr]">
            <Card>
                <CardHeader>
                    <div class="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                        Central access
                    </div>
                    <CardTitle>Central admin login</CardTitle>
                    <CardDescription>Sign in to manage tenants across the platform.</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div v-if="$page.props.flash?.success" class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ $page.props.flash.success }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="space-y-2">
                            <label for="email" class="text-sm font-medium">Email address</label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="admin@pms.test"
                                autofocus
                            />
                            <p v-if="form.errors.email" class="text-xs text-rose-600">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label for="password" class="text-sm font-medium">Password</label>
                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                placeholder="********"
                            />
                            <p v-if="form.errors.password" class="text-xs text-rose-600">
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <label class="flex items-center gap-2 text-sm text-muted-foreground">
                            <input v-model="form.remember" type="checkbox" class="h-4 w-4" />
                            Remember me
                        </label>

                        <Button type="submit" :disabled="form.processing" class="w-full">
                            {{ form.processing ? 'Signing in...' : 'Sign in' }}
                        </Button>
                    </form>

                    <div class="text-sm text-muted-foreground">
                        <Link :href="route('central.home')" class="font-medium text-primary">
                            Back to home
                        </Link>
                    </div>

                    <div class="rounded-md border border-border bg-muted/40 p-4 text-xs text-muted-foreground">
                        <div class="font-semibold text-foreground">Demo admin credentials</div>
                        <div>Email: superadmin@pms.test</div>
                        <div>Password: password</div>
                    </div>
                </CardContent>
            </Card>

            <Card class="hidden lg:block">
                <CardHeader>
                    <CardTitle>Operate with confidence</CardTitle>
                    <CardDescription>
                        Central SAAS gives you a single view of tenant health, approvals, and subscription readiness.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <ul class="space-y-3 text-sm text-muted-foreground">
                        <li>Instant provisioning visibility</li>
                        <li>Real time tenant status</li>
                        <li>Secure admin workflows</li>
                    </ul>
                </CardContent>
            </Card>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

function submit() {
    form.post(route("central.login"), {
        preserveScroll: true,
    });
}
</script>

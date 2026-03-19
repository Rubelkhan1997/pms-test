<template>
    <div class="min-h-screen bg-muted/30 px-6 py-12">
        <div class="mx-auto max-w-2xl">
            <Card>
                <CardHeader>
                    <div class="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                        Free trial access
                    </div>
                    <CardTitle>Start your free trial</CardTitle>
                    <CardDescription>14 days free. No credit card required.</CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <form @submit.prevent="submit" class="space-y-8">
                        <div class="space-y-4">
                            <h2 class="text-base font-semibold">Tenant information</h2>

                            <div class="space-y-2">
                                <label for="tenant_name" class="text-sm font-medium">Hotel or property name</label>
                                <Input id="tenant_name" v-model="form.tenant_name" placeholder="Grand Hotel" />
                                <p v-if="form.errors.tenant_name" class="text-xs text-rose-600">
                                    {{ form.errors.tenant_name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label for="tenant_email" class="text-sm font-medium">Business email</label>
                                <Input id="tenant_email" v-model="form.tenant_email" type="email" placeholder="hotel@example.com" />
                                <p v-if="form.errors.tenant_email" class="text-xs text-rose-600">
                                    {{ form.errors.tenant_email }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label for="tenant_subdomain" class="text-sm font-medium">Subdomain</label>
                                <div class="flex">
                                    <span class="flex items-center rounded-l-md border border-input bg-muted px-3 text-xs text-muted-foreground">
                                        https://
                                    </span>
                                    <Input
                                        id="tenant_subdomain"
                                        v-model="form.tenant_subdomain"
                                        class="rounded-none"
                                        placeholder="grandhotel"
                                    />
                                    <span class="flex items-center rounded-r-md border border-input bg-muted px-3 text-xs text-muted-foreground">
                                        .pms.test
                                    </span>
                                </div>
                                <p v-if="form.errors.tenant_subdomain" class="text-xs text-rose-600">
                                    {{ form.errors.tenant_subdomain }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Your unique subdomain for accessing the system.
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4 border-t pt-6">
                            <h2 class="text-base font-semibold">Admin user information</h2>

                            <div class="space-y-2">
                                <label for="admin_name" class="text-sm font-medium">Full name</label>
                                <Input id="admin_name" v-model="form.admin_name" placeholder="John Doe" />
                                <p v-if="form.errors.admin_name" class="text-xs text-rose-600">
                                    {{ form.errors.admin_name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label for="admin_email" class="text-sm font-medium">Email address</label>
                                <Input id="admin_email" v-model="form.admin_email" type="email" placeholder="admin@grandhotel.com" />
                                <p v-if="form.errors.admin_email" class="text-xs text-rose-600">
                                    {{ form.errors.admin_email }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label for="admin_password" class="text-sm font-medium">Password</label>
                                <Input id="admin_password" v-model="form.admin_password" type="password" placeholder="********" />
                                <p v-if="form.errors.admin_password" class="text-xs text-rose-600">
                                    {{ form.errors.admin_password }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label for="admin_password_confirmation" class="text-sm font-medium">Confirm password</label>
                                <Input id="admin_password_confirmation" v-model="form.admin_password_confirmation" type="password" placeholder="********" />
                            </div>
                        </div>

                        <label class="flex items-start gap-2 text-sm text-muted-foreground">
                            <input v-model="form.accept_terms" type="checkbox" class="mt-1 h-4 w-4" required />
                            <span>
                                I agree to the <a href="#" class="font-medium text-primary">Terms of Service</a> and
                                <a href="#" class="font-medium text-primary">Privacy Policy</a>
                            </span>
                        </label>

                        <Button type="submit" :disabled="form.processing" class="w-full">
                            {{ form.processing ? 'Creating account...' : 'Create account' }}
                        </Button>
                    </form>

                    <div class="border-t pt-6 text-sm text-muted-foreground">
                        Already have an account?
                        <Link :href="route('central.login')" class="font-medium text-primary">Sign in</Link>
                    </div>

                    <div class="rounded-md border border-border bg-muted/40 p-4 text-xs text-muted-foreground">
                        <div class="font-semibold text-foreground">Trial includes</div>
                        <div>14 day free trial</div>
                        <div>Full access to all features</div>
                        <div>Requires admin approval</div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useForm, Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";

const form = useForm({
    tenant_name: "",
    tenant_email: "",
    tenant_subdomain: "",
    admin_name: "",
    admin_email: "",
    admin_password: "",
    admin_password_confirmation: "",
    accept_terms: false,
});

function submit() {
    form.post(route("central.register"), {
        preserveScroll: true,
    });
}
</script>

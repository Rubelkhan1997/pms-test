<template>
    <div class="space-y-6">
        <Card>
            <CardHeader class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                        Tenant onboarding
                    </div>
                    <CardTitle>Create a new tenant</CardTitle>
                    <CardDescription>
                        Register a new property and secure its first administrator.
                    </CardDescription>
                </div>
                <Button :as="Link" :href="route('central.tenants.index')" variant="outline">
                    Back to tenants
                </Button>
            </CardHeader>
        </Card>

        <Card v-if="showProgress">
            <CardHeader class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <CardTitle>Provisioning status</CardTitle>
                    <CardDescription>We are preparing the tenant workspace.</CardDescription>
                </div>
                <Badge variant="secondary">{{ progressLabel }}</Badge>
            </CardHeader>
            <CardContent class="space-y-4">
                <Progress :value="progressPercent" />
                <div class="grid gap-2 text-sm">
                    <div
                        v-for="(step, index) in progressSteps"
                        :key="step"
                        class="flex items-center gap-3"
                    >
                        <div
                            class="flex h-6 w-6 items-center justify-center rounded-full border text-xs font-semibold"
                            :class="index + 1 <= progressStep ? 'border-primary bg-primary text-primary-foreground' : 'border-border text-muted-foreground'"
                        >
                            {{ index + 1 }}
                        </div>
                        <span :class="index + 1 <= progressStep ? 'text-foreground' : 'text-muted-foreground'">
                            {{ step }}
                        </span>
                    </div>
                </div>
                <p class="text-xs text-muted-foreground">
                    Database provisioning begins after approval. This confirms that the request is being processed.
                </p>
            </CardContent>
        </Card>

        <Card>
            <CardContent class="space-y-6">
                <div v-if="$page.props.flash?.success" class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $page.props.flash.error }}
                </div>
                <div v-if="form.errors.general" class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ form.errors.general }}
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="space-y-4">
                            <div>
                                <h2 class="text-base font-semibold">Property details</h2>
                                <p class="text-sm text-muted-foreground">
                                    Core property identity and the public endpoint.
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium" for="tenant_name">Property name</label>
                                <Input
                                    id="tenant_name"
                                    v-model="form.tenant_name"
                                    placeholder="Grand Hotel"
                                />
                                <p v-if="form.errors.tenant_name" class="text-xs text-rose-600">
                                    {{ form.errors.tenant_name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium" for="tenant_email">Business email</label>
                                <Input
                                    id="tenant_email"
                                    v-model="form.tenant_email"
                                    type="email"
                                    placeholder="hotel@example.com"
                                />
                                <p v-if="form.errors.tenant_email" class="text-xs text-rose-600">
                                    {{ form.errors.tenant_email }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium" for="tenant_subdomain">System subdomain</label>
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
                                    The tenant will access the platform using this address.
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <h2 class="text-base font-semibold">Initial administrator</h2>
                                <p class="text-sm text-muted-foreground">
                                    Primary owner credentials for the new tenant.
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium" for="admin_name">Full name</label>
                                <Input
                                    id="admin_name"
                                    v-model="form.admin_name"
                                    placeholder="John Doe"
                                />
                                <p v-if="form.errors.admin_name" class="text-xs text-rose-600">
                                    {{ form.errors.admin_name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium" for="admin_email">Email address</label>
                                <Input
                                    id="admin_email"
                                    v-model="form.admin_email"
                                    type="email"
                                    placeholder="admin@grandhotel.com"
                                />
                                <p v-if="form.errors.admin_email" class="text-xs text-rose-600">
                                    {{ form.errors.admin_email }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium" for="admin_password">Password</label>
                                <Input
                                    id="admin_password"
                                    v-model="form.admin_password"
                                    type="password"
                                    placeholder="********"
                                />
                                <p v-if="form.errors.admin_password" class="text-xs text-rose-600">
                                    {{ form.errors.admin_password }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium" for="admin_password_confirmation">
                                    Confirm password
                                </label>
                                <Input
                                    id="admin_password_confirmation"
                                    v-model="form.admin_password_confirmation"
                                    type="password"
                                    placeholder="********"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 border-t pt-6">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Registering tenant...' : 'Register tenant' }}
                        </Button>
                        <p class="text-xs text-muted-foreground">
                            The tenant will be created in a pending state and requires your approval before database provisioning.
                        </p>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import CentralLayout from "../../../Layouts/CentralLayout.vue";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import { Progress } from "@/components/ui/progress";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";

defineOptions({ layout: CentralLayout });

const form = useForm({
    tenant_name: "",
    tenant_email: "",
    tenant_subdomain: "",
    admin_name: "",
    admin_email: "",
    admin_password: "",
    admin_password_confirmation: "",
    accept_terms: true,
});

const progressStep = ref(0);
const progressSteps = [
    "Request submitted",
    "Awaiting approval",
    "Provisioning queued",
];

const progressPercent = computed(() => {
    if (progressStep.value === 0) {
        return 0;
    }
    return (progressStep.value / progressSteps.length) * 100;
});

const progressLabel = computed(() => {
    if (progressStep.value === 0) {
        return "Idle";
    }
    return progressSteps[Math.min(progressStep.value - 1, progressSteps.length - 1)];
});

const showProgress = computed(() => progressStep.value > 0);

let progressTimer: ReturnType<typeof setTimeout> | null = null;

function advanceProgress() {
    if (progressTimer) {
        clearTimeout(progressTimer);
    }

    if (progressStep.value < progressSteps.length) {
        progressTimer = setTimeout(() => {
            progressStep.value += 1;
            if (progressStep.value < progressSteps.length) {
                advanceProgress();
            }
        }, 700);
    }
}

function submit() {
    progressStep.value = 1;
    advanceProgress();

    form.post(route("central.tenants.store"), {
        preserveScroll: true,
        onFinish: () => {
            progressStep.value = progressSteps.length;
        },
    });
}
</script>

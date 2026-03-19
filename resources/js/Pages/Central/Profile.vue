<template>
    <div class="space-y-6">
        <Card>
            <CardHeader>
                <div class="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                    Account profile
                </div>
                <CardTitle>Central profile</CardTitle>
                <CardDescription>Manage your central admin details.</CardDescription>
            </CardHeader>
        </Card>

        <div v-if="$page.props.flash?.success" class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ $page.props.flash.success }}
        </div>

        <Card>
            <CardContent class="space-y-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <label for="name" class="text-sm font-medium">Full name</label>
                            <Input id="name" v-model="form.name" placeholder="Full name" />
                            <p v-if="form.errors.name" class="text-xs text-rose-600">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label for="email" class="text-sm font-medium">Email address</label>
                            <Input id="email" v-model="form.email" type="email" placeholder="you@company.com" />
                            <p v-if="form.errors.email" class="text-xs text-rose-600">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label for="phone" class="text-sm font-medium">Phone</label>
                            <Input id="phone" v-model="form.phone" placeholder="+1 555 123 4567" />
                            <p v-if="form.errors.phone" class="text-xs text-rose-600">
                                {{ form.errors.phone }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Role</label>
                            <Input :model-value="roleLabel" disabled />
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3 border-t pt-6">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : 'Save changes' }}
                        </Button>
                        <Button type="button" variant="outline" @click="resetForm">
                            Reset
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import CentralLayout from "../../Layouts/CentralLayout.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

defineOptions({ layout: CentralLayout });

const form = useForm({
    name: user.value?.name ?? "",
    email: user.value?.email ?? "",
    phone: user.value?.phone ?? "",
});

const roleLabel = computed(() => "Super Admin");

function submit() {
    form.put(route("central.profile"), {
        preserveScroll: true,
    });
}

function resetForm() {
    form.defaults({
        name: user.value?.name ?? "",
        email: user.value?.email ?? "",
        phone: user.value?.phone ?? "",
    });
    form.reset();
}
</script>

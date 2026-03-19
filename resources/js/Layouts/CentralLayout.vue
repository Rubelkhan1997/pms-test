<template>
    <div class="min-h-screen bg-muted/30 font-sans text-foreground">
        <div
            class="fixed inset-0 z-40 bg-black/30 transition-opacity lg:hidden"
            :class="isSidebarOpen ? 'opacity-100' : 'pointer-events-none opacity-0'"
            @click="isSidebarOpen = false"
        ></div>

        <aside
            class="fixed inset-y-0 left-0 z-50 w-64 border-r border-border bg-background px-4 py-6 shadow-sm transition-transform lg:translate-x-0"
            :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex items-center gap-3 px-2">
                <span class="h-2 w-2 rounded-full bg-primary"></span>
                <div>
                    <div class="text-base font-semibold">PMS Central</div>
                    <div class="text-[0.65rem] uppercase tracking-[0.35em] text-muted-foreground">
                        Control Center
                    </div>
                </div>
            </div>

            <nav class="mt-8 flex flex-col gap-1 text-sm">
                <div class="px-2 text-[0.65rem] font-semibold uppercase tracking-[0.35em] text-muted-foreground">
                    Main
                </div>
                <Link
                    :href="route('central.dashboard')"
                    class="flex items-center gap-3 rounded-md px-3 py-2 font-medium transition"
                    :class="
                        $page.component === 'Central/Dashboard'
                            ? 'bg-primary/10 text-foreground'
                            : 'text-muted-foreground hover:bg-muted'
                    "
                >
                    <span class="flex h-7 w-7 items-center justify-center rounded-md bg-muted text-xs font-semibold">
                        D
                    </span>
                    Dashboard
                </Link>

                <Link
                    :href="route('central.tenants.index')"
                    class="flex items-center gap-3 rounded-md px-3 py-2 font-medium transition"
                    :class="
                        $page.component.startsWith('Central/Tenants')
                            ? 'bg-primary/10 text-foreground'
                            : 'text-muted-foreground hover:bg-muted'
                    "
                >
                    <span class="flex h-7 w-7 items-center justify-center rounded-md bg-muted text-xs font-semibold">
                        T
                    </span>
                    Tenants
                </Link>

                <Link
                    :href="route('central.tenants.create')"
                    class="flex items-center gap-3 rounded-md px-3 py-2 font-medium transition"
                    :class="
                        $page.component === 'Central/Tenants/AdminCreate'
                            ? 'bg-primary/10 text-foreground'
                            : 'text-muted-foreground hover:bg-muted'
                    "
                >
                    <span class="flex h-7 w-7 items-center justify-center rounded-md bg-muted text-xs font-semibold">
                        +
                    </span>
                    New Tenant
                </Link>

                <div class="mt-6 px-2 text-[0.65rem] font-semibold uppercase tracking-[0.35em] text-muted-foreground">
                    System
                </div>
                <Link
                    :href="route('central.profile')"
                    class="flex items-center gap-3 rounded-md px-3 py-2 font-medium text-muted-foreground transition hover:bg-muted"
                >
                    <span class="flex h-7 w-7 items-center justify-center rounded-md bg-muted text-xs font-semibold">
                        P
                    </span>
                    Profile
                </Link>
            </nav>

            <div class="mt-auto px-2 pt-6" v-if="$page.props.auth?.user">
                <div class="flex items-center gap-3 rounded-md border border-border bg-muted/40 p-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-xs font-semibold text-primary-foreground">
                        {{ $page.props.auth.user.name.charAt(0) }}
                    </div>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-medium">
                            {{ $page.props.auth.user.name }}
                        </div>
                        <div class="text-[0.65rem] uppercase tracking-[0.2em] text-muted-foreground">
                            Super Admin
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="lg:ml-64">
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-border bg-background/95 px-6 backdrop-blur">
                <div class="flex items-center gap-4">
                    <Button
                        variant="outline"
                        size="sm"
                        class="lg:hidden"
                        @click="toggleSidebar"
                    >
                        Menu
                    </Button>
                    <div class="hidden w-64 lg:block">
                        <Input placeholder="Search tenants, users" />
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Badge variant="secondary">Central live</Badge>
                    <Button variant="outline" size="icon">
                        N
                    </Button>
                    <div class="h-6 w-px bg-border"></div>
                    <Button
                        v-if="$page.props.auth?.user"
                        :as="Link"
                        :href="route('central.logout')"
                        method="post"
                        variant="default"
                    >
                        Logout
                    </Button>
                </div>
            </header>

            <main class="px-6 py-8">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";

const isSidebarOpen = ref(false);

function toggleSidebar() {
    isSidebarOpen.value = !isSidebarOpen.value;
}
</script>

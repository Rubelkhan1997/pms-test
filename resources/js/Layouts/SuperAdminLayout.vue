<template>
    <div class="min-h-screen bg-white">
        <!-- Sidebar -->
        <aside class="fixed left-0 top-0 h-screen w-64 bg-[#1B1E51] shadow-xl z-40">
            <!-- Logo -->
            <div class="flex items-center gap-3 px-6 py-5 border-b border-[#1B1E51]/30">
                <div class="w-10 h-10 bg-[#F47F20] rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-white">PMS</h1>
                    <p class="text-xs text-white/60">Super Admin</p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="px-4 py-6 space-y-2">
                <Link
                    v-for="item in navigation"
                    :key="item.href"
                    :href="item.href"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200"
                    :class="isActive(item.href)
                        ? 'bg-white/10 text-white'
                        : 'text-white/70 hover:bg-white/5 hover:text-white'"
                >
                    <component :is="item.icon" class="w-5 h-5" />
                    {{ item.label }}
                </Link>
            </nav>

            <!-- User Info -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-[#1B1E51]/30">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-[#F47F20]/20 rounded-full flex items-center justify-center">
                        <span class="text-sm font-semibold text-[#F47F20]">{{ userInitials }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ userName }}</p>
                        <p class="text-xs text-white/60">Super Admin</p>
                    </div>
                </div>
                <button
                    @click="handleLogout"
                    :disabled="isLoggingOut"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg text-sm font-medium transition disabled:opacity-50"
                >
                    <svg v-if="isLoggingOut" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    {{ isLoggingOut ? 'Signing out...' : 'Sign Out' }}
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="ml-64 min-h-screen">
            <!-- Top Header -->
            <header class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-30">
                <div class="px-8 py-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-[#1B1E51]">{{ pageTitle }}</h2>
                        <p class="text-sm text-gray-500 mt-0.5">{{ pageDescription }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <button class="relative p-2 text-gray-400 hover:text-[#1B1E51] transition">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-[#F47F20] rounded-full"></span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-8">
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { computed, inject, onMounted, ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import type { PageProps } from '@/Types';
import type { confirm as ConfirmType } from '@/Plugins/confirm';

const confirm = inject('confirm') as typeof ConfirmType;

// Navigation Icons as components
const DashboardIcon = {
    template: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
    </svg>`
};

const TenantsIcon = {
    template: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
    </svg>`
};

const PlansIcon = {
    template: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
    </svg>`
};

const navigation = [
    { label: 'Dashboard', href: '/dashboard', icon: DashboardIcon },
    { label: 'Tenants', href: '/tenants', icon: TenantsIcon },
    { label: 'Subscription Plans', href: '/plans', icon: PlansIcon },
];

const page = usePage<PageProps>();

const userName = computed(() => page.props.auth?.user?.name ?? 'Admin');
const userInitials = computed(() => {
    const name = userName.value;
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

const pageTitle = computed(() => {
    const path = page.url;
    if (path.includes('/tenants')) return 'Tenants';
    if (path.includes('/plans')) return 'Subscription Plans';
    return 'Dashboard';
});

const pageDescription = computed(() => {
    const path = page.url;
    if (path.includes('/tenants')) return 'Manage all tenant accounts';
    if (path.includes('/plans')) return 'Manage subscription plans';
    return 'Platform overview and statistics';
});

function isActive(href: string): boolean {
    return page.url.startsWith(href);
}

const isLoggingOut = ref(false);

async function handleLogout() {
    const confirmed = await confirm.show({
        title: 'Sign Out?',
        message: 'Are you sure you want to sign out?',
        confirmText: 'Yes, Sign Out',
        cancelText: 'Cancel',
        variant: 'danger',
    });

    if (!confirmed) return;

    isLoggingOut.value = true;
    router.post('/logout', {}, {
        onFinish: () => isLoggingOut.value = false,
    });
}
</script>

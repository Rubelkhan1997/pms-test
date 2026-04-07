<template>
    <Head :title="hotel.name" />
    <div v-if="canView" class="max-w-4xl mx-auto">
        <section class="space-y-6">

            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-800">{{ hotel.name }}</h1>
                    <p class="text-sm text-slate-500 mt-1">Hotel Details</p>
                </div>
                <div class="flex gap-2">
                    <Link
                        v-if="canEdit"
                        :href="`/hotels/${hotel.id}/edit`"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                    >
                        Edit
                    </Link>
                    <Link 
                        href="/hotels"
                        class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition"
                    >
                        ← Back
                    </Link>
                </div>
            </div>

            <!-- Details Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Hotel Name</label>
                        <p class="text-slate-900">{{ hotel.name }}</p>
                    </div>

                    <!-- Code -->
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Hotel Code</label>
                        <p class="text-slate-900">{{ hotel.code }}</p>
                    </div>

                    <!-- Timezone -->
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Timezone</label>
                        <p class="text-slate-900">{{ hotel.timezone || 'N/A' }}</p>
                    </div>

                    <!-- Currency -->
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Currency</label>
                        <p class="text-slate-900">{{ hotel.currency || 'N/A' }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Email</label>
                        <p class="text-slate-900">{{ hotel.email || 'N/A' }}</p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Phone</label>
                        <p class="text-slate-900">{{ hotel.phone || 'N/A' }}</p>
                    </div>
                </div>

                <!-- Address (Full Width) -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-slate-500 mb-1">Address</label>
                    <p class="text-slate-900">{{ hotel.address || 'N/A' }}</p>
                </div>

                <!-- Timestamps -->
                <div class="mt-6 pt-6 border-t border-slate-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-slate-500">Created At:</span>
                            <span class="ml-2 text-slate-900">{{ formatDate(hotel.createdAt) }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500">Updated At:</span>
                            <span class="ml-2 text-slate-900">{{ formatDate(hotel.updatedAt) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup lang="ts">
    import { computed, onMounted } from 'vue';
    import { router } from '@inertiajs/vue3';
    import { formatDate } from '@/Utils/date';
    import type { Hotel } from '@/Types/FrontDesk/hotel';
    import { mapToHotel } from '@/Utils/Mappers/hotel';
    import { usePermissionService } from '@/Composables/usePermissionService';
    
    // ─── Permissions ─────────────────────────────────────────
    const permission = usePermissionService();
    const canView = computed(() => permission.check('view hotels'));
    const canEdit = computed(() => permission.check('edit hotels')); 

    // ─── Props ───────────────────────────────────────────────
    const props = defineProps<{
        hotel: Record<string, any>;
    }>();

    // ─── Computed ────────────────────────────────────────────
    const hotel: Hotel = mapToHotel(props.hotel);

    // Load reservation on mount
    onMounted(() => {
        if (!canView.value) {
            router.visit('/hotels');
            return;
        } 
    });
</script>

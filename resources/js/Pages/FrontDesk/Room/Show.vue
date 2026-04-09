<template>
    <Head :title="`${t('rooms.room_number')} ${room.number}`" />

    <div v-if="canView" class="max-w-4xl mx-auto">
        <section class="space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-800">
                        {{ t('rooms.room_number') }} {{ room.number }}
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">{{ t('rooms.room_details') }}</p>
                </div>

                <div class="flex gap-2">
                    <FormButton
                        v-if="canEdit"
                        type="button"
                        color="primary"
                        :name="t('actions.edit')"
                        @click="router.visit(`/rooms/${room.id}/edit`)"
                    />
                    <FormButton
                        type="button"
                        color="secondary"
                        :name="t('actions.back')"
                        @click="router.visit('/rooms')"
                    />
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">{{ t('rooms.room_number') }}</label>
                        <p class="text-slate-900">{{ room.number }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">{{ t('navigation.hotels') }}</label>
                        <p class="text-slate-900">{{ room.hotel?.name || t('na') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">{{ t('rooms.room_type') }}</label>
                        <p class="text-slate-900">{{ room.type }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">{{ t('rooms.floor') }}</label>
                        <p class="text-slate-900">{{ room.floor || t('na') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">{{ t('rooms.base_rate') }}</label>
                        <p class="text-slate-900">{{ room.baseRate }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">{{ t('rooms.status') }}</label>
                        <p class="text-slate-900">
                            <span
                                :class="[
                                    'px-2 py-1 text-xs font-semibold rounded-full',
                                    statusBadgeClass(room.status)
                                ]"
                            >
                                {{ room.statusLabel || room.status }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div v-else class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <h1 class="text-xl font-semibold text-slate-800">{{ t('messages.access_denied') }}</h1>
            <p class="text-sm text-slate-500 mt-2">{{ t('messages.no_permission') }}</p>
            <FormButton
                type="button"
                color="secondary"
                :name="t('actions.back')"
                button-class="mt-4"
                @click="router.visit('/rooms')"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
    import { computed, onMounted } from 'vue';
    import { router } from '@inertiajs/vue3';
    import type { Room } from '@/Types/FrontDesk/room';
    import { mapToRoom } from '@/Utils/Mappers/room';
    import { useI18n } from '@/Composables/useI18n';
    import { usePermissionService } from '@/Composables/usePermissionService';

    const { t } = useI18n();

    const permission = usePermissionService();
    const canView = computed(() => permission.check('view rooms'));
    const canEdit = computed(() => permission.check('edit rooms'));

    onMounted(() => {
        if (!canView.value) {
            router.visit('/rooms');
            return;
        }
    });

    const props = defineProps<{
        room: Record<string, any>;
    }>();

    const room: Room = mapToRoom(props.room);

    function statusBadgeClass(status: string): string {
        const map: Record<string, string> = {
            available: 'bg-green-100 text-green-800',
            occupied: 'bg-blue-100 text-blue-800',
            dirty: 'bg-yellow-100 text-yellow-800',
            out_of_order: 'bg-red-100 text-red-800',
        };

        return map[status] ?? 'bg-slate-100 text-slate-600';
    }
</script>

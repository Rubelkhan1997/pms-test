<template>
    <Head :title="t('rooms.new_room')" />

    <div v-if="canCreate" class="max-w-4xl mx-auto">
        <section class="space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-800">{{ t('rooms.new_room') }}</h1>
                    <p class="text-sm text-slate-500 mt-1">{{ t('rooms.create_hint') }}</p>
                </div>

                <Link
                    href="/rooms"
                    class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition"
                >
                    {{ t('rooms.back_to_rooms') }}
                </Link>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormSelect
                            id="hotel_id"
                            v-model="form.hotelId"
                            :label="t('navigation.hotels')"
                            :required="true"
                            :placeholder="t('rooms.select_hotel')"
                            :options="hotelSelectOptions"
                            option-label="label"
                            option-value="value"
                            :error="form.errors.hotelId"
                            wrapper-class="mb-0"
                        />

                        <FormInput
                            id="number"
                            v-model="form.number"
                            :label="t('rooms.room_number')"
                            :placeholder="t('rooms.number_placeholder')"
                            :required="true"
                            :error="form.errors.number"
                            wrapper-class="mb-0"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormInput
                            id="type"
                            v-model="form.type"
                            :label="t('rooms.room_type')"
                            :placeholder="t('rooms.type_placeholder')"
                            :required="true"
                            :error="form.errors.type"
                            wrapper-class="mb-0"
                        />

                        <FormInput
                            id="floor"
                            v-model="form.floor"
                            :label="t('rooms.floor')"
                            :placeholder="t('rooms.floor_placeholder')"
                            :error="form.errors.floor"
                            wrapper-class="mb-0"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormInput
                            id="base_rate"
                            v-model="form.baseRate"
                            type="number"
                            :label="t('rooms.base_rate')"
                            :placeholder="t('rooms.base_rate_placeholder')"
                            :required="true"
                            :error="form.errors.baseRate"
                            wrapper-class="mb-0"
                        />

                        <FormSelect
                            id="status"
                            v-model="form.status"
                            :label="t('rooms.status')"
                            :required="true"
                            :options="statusOptions"
                            option-label="label"
                            option-value="value"
                            :error="form.errors.status"
                            wrapper-class="mb-0"
                        />
                    </div>

                    <div class="flex gap-4 pt-4">
                        <FormButton
                            type="submit"
                            color="primary"
                            :name="submitLabel"
                            :disabled="isSaving"
                            button-class="px-6"
                        />

                        <Link
                            href="/rooms"
                            class="px-6 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition"
                        >
                            {{ t('actions.cancel') }}
                        </Link>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <div v-else class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <h1 class="text-xl font-semibold text-slate-800">{{ t('messages.access_denied') }}</h1>
            <p class="text-sm text-slate-500 mt-2">{{ t('messages.no_permission') }}</p>
            <Link
                href="/rooms"
                class="inline-flex mt-4 px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition"
            >
                {{ t('actions.back') }}
            </Link>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { computed, onMounted } from 'vue';
    import { useForm, router } from '@inertiajs/vue3';
    import { useRooms } from '@/Composables/FrontDesk/useRooms';
    import { useI18n } from '@/Composables/useI18n';
    import { usePermissionService } from '@/Composables/usePermissionService';
    import { required, minValue, validateInertiaForm } from '@/Utils/validation';
    import type { RoomHotelOption, RoomStatus } from '@/Types/FrontDesk/room';
    import { mapRoomHotelOptionApi } from '@/Utils/Mappers/room';

    const { t } = useI18n();

    const permission = usePermissionService();
    const canCreate = computed(() => permission.check('create rooms'));

    const { create: createRoom, saving } = useRooms();

    onMounted(() => {
        if (!canCreate.value) {
            router.visit('/rooms');
            return;
        }
    });

    const props = defineProps<{
        hotels: Array<Record<string, any>>;
    }>();

    const hotelOptions = computed<RoomHotelOption[]>(() => props.hotels.map(mapRoomHotelOptionApi));
    const hotelSelectOptions = computed(() =>
        hotelOptions.value.map((hotel) => ({
            value: hotel.id,
            label: hotel.name,
        }))
    );

    const statusOptions = computed(() => [
        { value: 'available', label: t('enums.room_status.available') },
        { value: 'occupied', label: t('enums.room_status.occupied') },
        { value: 'dirty', label: t('enums.room_status.dirty') },
        { value: 'out_of_order', label: t('enums.room_status.out_of_order') },
    ]);

    const form = useForm({
        hotelId: '' as number | string,
        number: '',
        floor: '',
        type: '',
        status: 'available' as RoomStatus,
        baseRate: '' as number | string,
    });

    const isSaving = computed(() => form.processing || saving.value);
    const submitLabel = computed(() => isSaving.value ? t('actions.creating') : t('rooms.new_room'));

    async function submit(): Promise<void> {
        form.clearErrors();

        if (!validateForm()) {
            scrollToFirstError();
            return;
        }

        try {
            const result = await createRoom({
                hotelId: Number(form.hotelId),
                number: form.number,
                floor: form.floor || undefined,
                type: form.type,
                status: form.status,
                baseRate: Number(form.baseRate),
            });

            if (Number(result.status) === 1) {
                form.reset();
                router.visit('/rooms');
            }
        } catch (err: unknown) {
            const apiErr = err as Record<string, any>;

            if (apiErr?.response?.data?.errors) {
                const backendErrors: Record<string, string[]> = apiErr.response.data.errors;
                Object.entries(backendErrors).forEach(([key, messages]) => {
                    form.setError(mapBackendField(key) as any, messages[0]);
                });
                scrollToFirstError();
            }
        }
    }

    function validateForm(): boolean {
        return validateInertiaForm(form, {
            hotelId: [required],
            number: [required],
            type: [required],
            status: [required],
            baseRate: [required, minValue(0)],
        });
    }

    function mapBackendField(field: string): string {
        const map: Record<string, string> = {
            hotel_id: 'hotelId',
            base_rate: 'baseRate',
        };

        return map[field] ?? field;
    }

    function scrollToFirstError(): void {
        setTimeout(() => {
            const firstError = document.querySelector('.border-red-500');
            firstError?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    }
</script>

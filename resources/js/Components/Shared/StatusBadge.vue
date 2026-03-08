<template>
    <span :class="badgeClasses">
        {{ statusLabel }}
    </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

// ─────────────────────────────────────────────────────────────────────
// Props
// ─────────────────────────────────────────────────────────────────────
const props = defineProps<{
    status: PMS.ReservationStatus;
}>();

// ─────────────────────────────────────────────────────────────────────
// Computed
// ─────────────────────────────────────────────────────────────────────
const badgeClasses = computed(() => {
    const baseClasses = 'px-2 py-1 text-xs font-medium rounded-full';
    
    const statusClasses: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-green-100 text-green-800',
        checked_in: 'bg-blue-100 text-blue-800',
        checked_out: 'bg-slate-100 text-slate-800',
        cancelled: 'bg-red-100 text-red-800',
        no_show: 'bg-purple-100 text-purple-800'
    };
    
    return `${baseClasses} ${statusClasses[props.status] || 'bg-gray-100 text-gray-800'}`;
});

const statusLabel = computed(() => {
    const labels: Record<string, string> = {
        pending: 'Pending',
        confirmed: 'Confirmed',
        checked_in: 'Checked In',
        checked_out: 'Checked Out',
        cancelled: 'Cancelled',
        no_show: 'No Show'
    };
    
    return labels[props.status] || props.status;
});
</script>

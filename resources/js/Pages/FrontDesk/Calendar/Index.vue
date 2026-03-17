<template>
    <div class="availability-calendar">
        <div class="calendar-header">
            <h1>Availability Calendar</h1>
            <div class="controls">
                <button @click="previousMonth" class="btn-icon">←</button>
                <span class="current-month">{{
                    currentMonth.toFormat("MMMM yyyy")
                }}</span>
                <button @click="nextMonth" class="btn-icon">→</button>
            </div>
        </div>

        <div class="calendar-grid">
            <!-- Dates Header -->
            <div class="dates-header">
                <div
                    v-for="date in calendarDates"
                    :key="date.date"
                    class="date-col"
                    :class="{
                        'is-weekend': date.isWeekend,
                        'is-today': date.isToday,
                    }"
                >
                    <div class="day-name">{{ date.dayName }}</div>
                    <div class="day-number">{{ date.dayNumber }}</div>
                </div>
            </div>

            <!-- Availability Grid (placeholder) -->
            <div class="availability-grid">
                <div class="room-type-row">
                    <div
                        v-for="date in calendarDates"
                        :key="date.date"
                        class="availability-cell available"
                    >
                        <div class="available-count">10</div>
                        <div class="cell-date">{{ date.dayNumber }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { DateTime } from "luxon";

const currentMonth = ref(DateTime.now());
const availabilityData = ref({});

const calendarDates = computed(() => {
    const dates = [];
    const startOfMonth = currentMonth.value.startOf("month");
    const endOfMonth = currentMonth.value.endOf("month");

    let date = startOfMonth;
    while (date <= endOfMonth) {
        dates.push({
            date: date.toISODate(),
            dayName: date.toFormat("EEE"),
            dayNumber: date.day,
            isWeekend: date.weekday > 5,
            isToday: date.hasSame(DateTime.now(), "day"),
        });
        date = date.plus({ days: 1 });
    }

    return dates;
});

onMounted(async () => {
    await loadAvailability();
});

async function loadAvailability() {
    // Placeholder - will be implemented with real API
    availabilityData.value = {};
}

function previousMonth() {
    currentMonth.value = currentMonth.value.minus({ months: 1 });
    loadAvailability();
}

function nextMonth() {
    currentMonth.value = currentMonth.value.plus({ months: 1 });
    loadAvailability();
}
</script>

<style scoped>
.availability-calendar {
    padding: 2rem;
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.controls {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.current-month {
    font-size: 1.5rem;
    font-weight: bold;
    min-width: 200px;
    text-align: center;
}

.btn-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    border: 1px solid #ddd;
    background: white;
    cursor: pointer;
    font-size: 1.25rem;
}

.calendar-grid {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.dates-header {
    display: flex;
    background: #fafafa;
    border-bottom: 1px solid #ddd;
}

.date-col {
    flex: 1;
    padding: 0.5rem;
    text-align: center;
    border-right: 1px solid #eee;
}

.is-weekend {
    background: #fef3c7;
}

.is-today {
    background: #dbeafe;
    font-weight: bold;
}

.availability-grid {
    display: flex;
    flex-direction: column;
}

.room-type-row {
    display: flex;
    border-bottom: 1px solid #eee;
}

.availability-cell {
    flex: 1;
    padding: 0.75rem;
    text-align: center;
    border-right: 1px solid #eee;
    cursor: pointer;
    transition: all 0.2s;
}

.availability-cell:hover {
    transform: scale(1.05);
    z-index: 1;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.available {
    background: #d1fae5;
    color: #065f46;
}

.cell-date {
    font-size: 0.75rem;
    opacity: 0.7;
}
</style>

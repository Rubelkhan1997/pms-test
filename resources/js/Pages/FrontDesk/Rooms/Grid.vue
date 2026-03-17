<template>
  <div class="room-status-grid">
    <div class="grid-header">
      <h1>Room Status</h1>
      <div class="filters">
        <select v-model="filterFloor" @change="applyFilters">
          <option value="">All Floors</option>
          <option v-for="floor in floors" :key="floor" :value="floor">
            Floor {{ floor }}
          </option>
        </select>
        <select v-model="filterStatus" @change="applyFilters">
          <option value="">All Statuses</option>
          <option value="available">Available</option>
          <option value="occupied">Occupied</option>
          <option value="dirty">Dirty</option>
          <option value="out_of_order">Out of Order</option>
        </select>
      </div>
    </div>
    
    <!-- Status Summary -->
    <div class="status-summary">
      <div class="status-card available">
        <div class="status-count">{{ summary.available }}</div>
        <div class="status-label">Available</div>
      </div>
      <div class="status-card occupied">
        <div class="status-count">{{ summary.occupied }}</div>
        <div class="status-label">Occupied</div>
      </div>
      <div class="status-card dirty">
        <div class="status-count">{{ summary.dirty }}</div>
        <div class="status-label">Dirty</div>
      </div>
      <div class="status-card out_of_order">
        <div class="status-count">{{ summary.out_of_order }}</div>
        <div class="status-label">Out of Order</div>
      </div>
    </div>
    
    <!-- Room Grid -->
    <div class="rooms-grid">
      <div 
        v-for="room in filteredRooms" 
        :key="room.id"
        class="room-card"
        :class="getStatusClass(room.status)"
        @click="openRoomDetails(room)"
      >
        <div class="room-header">
          <span class="room-number">{{ room.number }}</span>
          <span class="room-floor">Floor {{ room.floor }}</span>
        </div>
        <div class="room-status">
          <span class="status-badge" :class="room.status">
            {{ formatStatus(room.status) }}
          </span>
        </div>
        <div class="room-details">
          <div class="room-type">{{ room.type }}</div>
          <div class="guest-info" v-if="room.current_guest">
            👤 {{ room.current_guest }}
          </div>
        </div>
        <div class="room-actions">
          <button 
            v-if="room.status === 'dirty'" 
            @click.stop="markClean(room)"
            class="action-btn"
          >
            ✓ Mark Clean
          </button>
          <button 
            v-if="room.status === 'available'" 
            @click.stop="markDirty(room)"
            class="action-btn"
          >
            🧹 Mark Dirty
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const rooms = ref([]);
const filterFloor = ref('');
const filterStatus = ref('');
const floors = ref([]);

const filteredRooms = computed(() => {
  return rooms.value.filter(room => {
    if (filterFloor.value && room.floor !== filterFloor.value) {
      return false;
    }
    if (filterStatus.value && room.status !== filterStatus.value) {
      return false;
    }
    return true;
  });
});

const summary = computed(() => {
  return {
    available: rooms.value.filter(r => r.status === 'available').length,
    occupied: rooms.value.filter(r => r.status === 'occupied').length,
    dirty: rooms.value.filter(r => r.status === 'dirty').length,
    out_of_order: rooms.value.filter(r => r.status === 'out_of_order').length,
  };
});

onMounted(async () => {
  await loadRooms();
});

async function loadRooms() {
  const response = await fetch('/api/v1/front-desk/rooms');
  const data = await response.json();
  rooms.value = data.data;
  
  // Extract unique floors
  floors.value = [...new Set(rooms.value.map(r => r.floor))].sort();
}

function applyFilters() {
  // Filters are applied automatically via computed property
}

function getStatusClass(status) {
  return `status-${status}`;
}

function formatStatus(status) {
  const statusMap = {
    available: 'Available',
    occupied: 'Occupied',
    dirty: 'Dirty',
    out_of_order: 'Out of Order',
  };
  return statusMap[status] || status;
}

function openRoomDetails(room) {
  router.visit(`/front-desk/rooms/${room.id}`);
}

async function markClean(room) {
  if (confirm(`Mark room ${room.number} as clean?`)) {
    await updateRoomStatus(room.id, 'available');
  }
}

async function markDirty(room) {
  if (confirm(`Mark room ${room.number} as dirty?`)) {
    await updateRoomStatus(room.id, 'dirty');
  }
}

async function updateRoomStatus(roomId, status) {
  const response = await fetch(`/api/v1/front-desk/rooms/${roomId}/status`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify({ status }),
  });
  
  if (response.ok) {
    await loadRooms();
  }
}
</script>

<style scoped>
.room-status-grid {
  padding: 2rem;
}

.grid-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.filters {
  display: flex;
  gap: 1rem;
}

.filters select {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  border: 1px solid #ddd;
  min-width: 150px;
}

.status-summary {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 2rem;
}

.status-card {
  padding: 1.5rem;
  border-radius: 8px;
  text-align: center;
  color: white;
}

.status-card.available { background: #10b981; }
.status-card.occupied { background: #3b82f6; }
.status-card.dirty { background: #f59e0b; }
.status-card.out_of_order { background: #6b7280; }

.status-count {
  font-size: 2rem;
  font-weight: bold;
}

.status-label {
  font-size: 0.875rem;
  opacity: 0.9;
}

.rooms-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
}

.room-card {
  background: white;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  cursor: pointer;
  transition: all 0.2s;
  border-left: 4px solid transparent;
}

.room-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.room-card.status-available { border-left-color: #10b981; }
.room-card.status-occupied { border-left-color: #3b82f6; }
.room-card.status-dirty { border-left-color: #f59e0b; }
.room-card.status-out_of_order { border-left-color: #6b7280; }

.room-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.room-number {
  font-size: 1.5rem;
  font-weight: bold;
}

.room-floor {
  color: #666;
  font-size: 0.875rem;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.available { background: #d1fae5; color: #065f46; }
.status-badge.occupied { background: #dbeafe; color: #1e40af; }
.status-badge.dirty { background: #fef3c7; color: #92400e; }
.status-badge.out_of_order { background: #e5e7eb; color: #6b7280; }

.room-details {
  margin: 1rem 0;
}

.room-type {
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.guest-info {
  color: #666;
  font-size: 0.875rem;
}

.room-actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  flex: 1;
  padding: 0.5rem;
  border-radius: 4px;
  border: none;
  background: #f5f5f5;
  cursor: pointer;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.action-btn:hover {
  background: #e5e5e5;
}
</style>

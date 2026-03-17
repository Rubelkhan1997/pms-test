<template>
  <div class="dashboard">
    <!-- Today's Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-content">
          <div class="stat-value">{{ stats.arrivals }}</div>
          <div class="stat-label">Today's Arrivals</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">🚪</div>
        <div class="stat-content">
          <div class="stat-value">{{ stats.departures }}</div>
          <div class="stat-label">Today's Departures</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">🏨</div>
        <div class="stat-content">
          <div class="stat-value">{{ stats.inHouse }}</div>
          <div class="stat-label">In-House Guests</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">📊</div>
        <div class="stat-content">
          <div class="stat-value">{{ stats.occupancy }}%</div>
          <div class="stat-label">Occupancy Rate</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
          <div class="stat-value">${{ stats.revenue }}</div>
          <div class="stat-label">Today's Revenue</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">📈</div>
        <div class="stat-content">
          <div class="stat-value">{{ stats.adr }}</div>
          <div class="stat-label">ADR</div>
        </div>
      </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions">
      <h2>Quick Actions</h2>
      <div class="action-buttons">
        <Link :href="route('front-desk.reservations.create')" class="action-btn primary">
          ➕ New Reservation
        </Link>
        <Link :href="route('front-desk.rooms.index')" class="action-btn">
          🏨 Room Status
        </Link>
        <Link :href="route('guests.profiles.index')" class="action-btn">
          👤 Guest List
        </Link>
        <Link :href="route('housekeeping.tasks.index')" class="action-btn">
          🧹 Housekeeping
        </Link>
      </div>
    </div>
    
    <!-- Arrivals & Departures -->
    <div class="grid-2">
      <div class="card">
        <div class="card-header">
          <h2>Today's Arrivals</h2>
          <Link :href="route('front-desk.reservations.index', { status: 'confirmed' })" class="view-all">
            View All →
          </Link>
        </div>
        <div class="card-content">
          <div v-if="arrivals.length === 0" class="empty-state">
            No arrivals today
          </div>
          <div v-else class="reservation-list">
            <div v-for="reservation in arrivals" :key="reservation.id" class="reservation-item">
              <div class="reservation-info">
                <div class="guest-name">{{ reservation.guest.full_name }}</div>
                <div class="reservation-details">
                  Room {{ reservation.room.number }} • {{ reservation.adults }} guests
                </div>
              </div>
              <div class="reservation-actions">
                <Link :href="route('front-desk.reservations.show', reservation.id)" class="btn-sm">
                  View
                </Link>
                <button @click="checkIn(reservation)" class="btn-sm primary">
                  Check In
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="card">
        <div class="card-header">
          <h2>Today's Departures</h2>
          <Link :href="route('front-desk.reservations.index', { status: 'checked_in' })" class="view-all">
            View All →
          </Link>
        </div>
        <div class="card-content">
          <div v-if="departures.length === 0" class="empty-state">
            No departures today
          </div>
          <div v-else class="reservation-list">
            <div v-for="reservation in departures" :key="reservation.id" class="reservation-item">
              <div class="reservation-info">
                <div class="guest-name">{{ reservation.guest.full_name }}</div>
                <div class="reservation-details">
                  Room {{ reservation.room.number }} • {{ reservation.nights }} nights
                </div>
              </div>
              <div class="reservation-actions">
                <Link :href="route('front-desk.reservations.show', reservation.id)" class="btn-sm">
                  View
                </Link>
                <button @click="checkOut(reservation)" class="btn-sm primary">
                  Check Out
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const stats = ref({
  arrivals: 0,
  departures: 0,
  inHouse: 0,
  occupancy: 0,
  revenue: 0,
  adr: 0,
});

const arrivals = ref([]);
const departures = ref([]);

onMounted(async () => {
  await loadDashboardData();
});

async function loadDashboardData() {
  // Load stats
  const statsResponse = await fetch(route('api.dashboard.stats'));
  const statsData = await statsResponse.json();
  stats.value = statsData;
  
  // Load arrivals
  const arrivalsResponse = await fetch(route('api.front-desk.reservations.arrivals'));
  const arrivalsData = await arrivalsResponse.json();
  arrivals.value = arrivalsData.data.slice(0, 5); // Show first 5
  
  // Load departures
  const departuresResponse = await fetch(route('api.front-desk.reservations.departures'));
  const departuresData = await departuresResponse.json();
  departures.value = departuresData.data.slice(0, 5); // Show first 5
}

function checkIn(reservation) {
  if (confirm(`Check in ${reservation.guest.full_name}?`)) {
    router.post(route('front-desk.reservations.check-in', reservation.id));
  }
}

function checkOut(reservation) {
  if (confirm(`Check out ${reservation.guest.full_name}?`)) {
    router.post(route('front-desk.reservations.check-out', reservation.id));
  }
}
</script>

<style scoped>
.dashboard {
  padding: 2rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.stat-icon {
  font-size: 2rem;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: bold;
  color: #1a1a1a;
}

.stat-label {
  color: #666;
  font-size: 0.875rem;
}

.quick-actions {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.action-buttons {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

.action-btn {
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  text-decoration: none;
  color: #333;
  background: #f5f5f5;
  transition: all 0.2s;
}

.action-btn.primary {
  background: #3b82f6;
  color: white;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.grid-2 {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.card-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e5e5;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-content {
  padding: 1.5rem;
}

.reservation-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0;
  border-bottom: 1px solid #f0f0f0;
}

.reservation-item:last-child {
  border-bottom: none;
}

.btn-sm {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  font-size: 0.875rem;
  text-decoration: none;
  background: #f5f5f5;
  color: #333;
  border: none;
  cursor: pointer;
}

.btn-sm.primary {
  background: #3b82f6;
  color: white;
}

.empty-state {
  text-align: center;
  color: #999;
  padding: 2rem;
}
</style>

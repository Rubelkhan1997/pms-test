<template>
  <div class="ota-dashboard">
    <h1>Channel Manager</h1>
    
    <!-- Connection Status -->
    <div class="connections-grid">
      <div 
        v-for="connection in connections" 
        :key="connection.id"
        class="connection-card"
        :class="connection.status"
      >
        <div class="connection-header">
          <h3>{{ connection.ota_provider.name }}</h3>
          <span class="status-badge" :class="connection.status">
            {{ connection.status }}
          </span>
        </div>
        <div class="connection-details">
          <div class="detail-row">
            <span>Last Sync:</span>
            <span>{{ formatDateTime(connection.last_sync_at) }}</span>
          </div>
          <div class="detail-row">
            <span>Rooms Mapped:</span>
            <span>{{ connection.room_mappings_count }}/{{ totalRooms }}</span>
          </div>
          <div class="detail-row">
            <span>Rates Mapped:</span>
            <span>{{ connection.rate_mappings_count }}/{{ totalRates }}</span>
          </div>
        </div>
        <div class="connection-actions">
          <button @click="syncNow(connection)" class="btn-primary">
            Sync Now
          </button>
          <button @click="configureConnection(connection)" class="btn-secondary">
            Configure
          </button>
        </div>
      </div>
    </div>
    
    <!-- Sync Queue -->
    <div class="sync-queue">
      <h2>Sync Queue</h2>
      <div class="queue-stats">
        <div class="queue-stat">
          <div class="stat-value">{{ queueStats.pending }}</div>
          <div class="stat-label">Pending</div>
        </div>
        <div class="queue-stat">
          <div class="stat-value">{{ queueStats.processing }}</div>
          <div class="stat-label">Processing</div>
        </div>
        <div class="queue-stat">
          <div class="stat-value">{{ queueStats.failed }}</div>
          <div class="stat-label">Failed</div>
        </div>
      </div>
    </div>
    
    <!-- Recent Sync Logs -->
    <div class="sync-logs">
      <h2>Recent Sync Activity</h2>
      <table class="logs-table">
        <thead>
          <tr>
            <th>Provider</th>
            <th>Type</th>
            <th>Direction</th>
            <th>Status</th>
            <th>Time</th>
            <th>Duration</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="log in recentLogs" :key="log.id">
            <td>{{ log.ota_provider.name }}</td>
            <td>{{ log.sync_type }}</td>
            <td>{{ log.direction }}</td>
            <td>
              <span :class="log.success ? 'success' : 'error'">
                {{ log.success ? '✓ Success' : '✗ Failed' }}
              </span>
            </td>
            <td>{{ formatDateTime(log.synced_at) }}</td>
            <td>{{ log.execution_time }}ms</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const connections = ref([]);
const queueStats = ref({ pending: 0, processing: 0, failed: 0 });
const recentLogs = ref([]);
const totalRooms = ref(0);
const totalRates = ref(0);

onMounted(async () => {
  await loadDashboard();
});

async function loadDashboard() {
  // Load connections
  const connResponse = await fetch('/api/v1/channel-manager/connections');
  const connData = await connResponse.json();
  connections.value = connData.data;
  
  // Load queue stats
  const queueResponse = await fetch('/api/v1/channel-manager/queue/stats');
  const queueData = await queueResponse.json();
  queueStats.value = queueData;
  
  // Load recent logs
  const logsResponse = await fetch('/api/v1/channel-manager/logs?limit=10');
  const logsData = await logsResponse.json();
  recentLogs.value = logsData.data;
  
  // Load room/rate counts
  const roomsResponse = await fetch('/api/v1/front-desk/room-types');
  const roomsData = await roomsResponse.json();
  totalRooms.value = roomsData.data.length;
}

function formatDateTime(date) {
  if (!date) return 'Never';
  return new Date(date).toLocaleString();
}

async function syncNow(connection) {
  await fetch(`/api/v1/channel-manager/connections/${connection.id}/sync`, {
    method: 'POST',
  });
  
  await loadDashboard();
}

function configureConnection(connection) {
  // Navigate to configuration page
  window.location.href = `/channel-manager/connections/${connection.id}/edit`;
}
</script>

<style scoped>
.ota-dashboard {
  padding: 2rem;
}

.connections-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.connection-card {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  border-left: 4px solid #ddd;
}

.connection-card.active { border-left-color: #10b981; }
.connection-card.pending { border-left-color: #f59e0b; }
.connection-card.error { border-left-color: #ef4444; }

.connection-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.active { background: #d1fae5; color: #065f46; }
.status-badge.pending { background: #fef3c7; color: #92400e; }
.status-badge.error { background: #fee2e2; color: #991b1b; }

.connection-details {
  margin-bottom: 1rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f0f0f0;
}

.connection-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-primary {
  flex: 1;
  padding: 0.5rem 1rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.btn-secondary {
  flex: 1;
  padding: 0.5rem 1rem;
  background: #f5f5f5;
  color: #333;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.sync-queue, .sync-logs {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  margin-bottom: 1.5rem;
}

.queue-stats {
  display: flex;
  gap: 2rem;
  margin-top: 1rem;
}

.queue-stat {
  text-align: center;
}

.stat-value {
  font-size: 2rem;
  font-weight: bold;
}

.stat-label {
  color: #666;
  font-size: 0.875rem;
}

.logs-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
}

.logs-table th, .logs-table td {
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid #f0f0f0;
}

.logs-table th {
  background: #f9f9f9;
  font-weight: 600;
}

.logs-table .success { color: #10b981; }
.logs-table .error { color: #ef4444; }
</style>

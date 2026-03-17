<template>
    <div class="reports-dashboard">
        <h1>Reports & Analytics</h1>

        <!-- Date Range Selector -->
        <div class="date-range-selector">
            <input type="date" v-model="startDate" @change="loadReports" />
            <span>to</span>
            <input type="date" v-model="endDate" @change="loadReports" />
        </div>

        <!-- Key Metrics -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-label">Occupancy Rate</div>
                <div class="metric-value">{{ metrics.occupancyRate }}%</div>
                <div
                    class="metric-change"
                    :class="
                        metrics.occupancyChange >= 0 ? 'positive' : 'negative'
                    "
                >
                    {{ metrics.occupancyChange >= 0 ? "+" : ""
                    }}{{ metrics.occupancyChange }}%
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-label">ADR (Average Daily Rate)</div>
                <div class="metric-value">${{ metrics.adr }}</div>
                <div
                    class="metric-change"
                    :class="metrics.adrChange >= 0 ? 'positive' : 'negative'"
                >
                    {{ metrics.adrChange >= 0 ? "+" : ""
                    }}{{ metrics.adrChange }}%
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-label">
                    RevPAR (Revenue Per Available Room)
                </div>
                <div class="metric-value">${{ metrics.revpar }}</div>
                <div
                    class="metric-change"
                    :class="metrics.revparChange >= 0 ? 'positive' : 'negative'"
                >
                    {{ metrics.revparChange >= 0 ? "+" : ""
                    }}{{ metrics.revparChange }}%
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-label">Total Revenue</div>
                <div class="metric-value">${{ metrics.totalRevenue }}</div>
                <div
                    class="metric-change"
                    :class="
                        metrics.revenueChange >= 0 ? 'positive' : 'negative'
                    "
                >
                    {{ metrics.revenueChange >= 0 ? "+" : ""
                    }}{{ metrics.revenueChange }}%
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
            <div class="chart-card">
                <h2>Occupancy Trend</h2>
                <canvas ref="occupancyChart"></canvas>
            </div>

            <div class="chart-card">
                <h2>Revenue Breakdown</h2>
                <canvas ref="revenueChart"></canvas>
            </div>
        </div>

        <!-- Detailed Reports -->
        <div class="reports-section">
            <h2>Detailed Reports</h2>
            <div class="report-links">
                <a href="/reports/occupancy" class="report-link">
                    📊 Occupancy Report
                </a>
                <a href="/reports/revenue" class="report-link">
                    💰 Revenue Report
                </a>
                <a href="/reports/arrivals" class="report-link">
                    📅 Arrivals/Departures
                </a>
                <a href="/reports/source" class="report-link">
                    📈 Source/Market Report
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { Chart, registerables } from "chart.js";

Chart.register(...registerables);

const startDate = ref(
    new Date(new Date().setMonth(new Date().getMonth() - 1))
        .toISOString()
        .split("T")[0],
);
const endDate = ref(new Date().toISOString().split("T")[0]);

const metrics = ref({
    occupancyRate: 0,
    occupancyChange: 0,
    adr: 0,
    adrChange: 0,
    revpar: 0,
    revparChange: 0,
    totalRevenue: 0,
    revenueChange: 0,
});

onMounted(() => {
    loadReports();
});

async function loadReports() {
    // Load metrics
    const metricsResponse = await fetch(
        `/api/v1/reports/metrics?start=${startDate.value}&end=${endDate.value}`,
    );
    const metricsData = await metricsResponse.json();
    metrics.value = metricsData;

    // Load charts
    loadOccupancyChart();
    loadRevenueChart();
}

async function loadOccupancyChart() {
    const response = await fetch(
        `/api/v1/reports/occupancy-trend?start=${startDate.value}&end=${endDate.value}`,
    );
    const data = await response.json();

    const ctx = this.$refs.occupancyChart.getContext("2d");
    new Chart(ctx, {
        type: "line",
        data: {
            labels: data.dates,
            datasets: [
                {
                    label: "Occupancy Rate (%)",
                    data: data.rates,
                    borderColor: "#3b82f6",
                    tension: 0.4,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
            },
        },
    });
}

async function loadRevenueChart() {
    const response = await fetch(
        `/api/v1/reports/revenue-breakdown?start=${startDate.value}&end=${endDate.value}`,
    );
    const data = await response.json();

    const ctx = this.$refs.revenueChart.getContext("2d");
    new Chart(ctx, {
        type: "doughnut",
        data: {
            labels: data.labels,
            datasets: [
                {
                    data: data.values,
                    backgroundColor: [
                        "#3b82f6",
                        "#10b981",
                        "#f59e0b",
                        "#ef4444",
                    ],
                },
            ],
        },
        options: {
            responsive: true,
        },
    });
}
</script>

<style scoped>
.reports-dashboard {
    padding: 2rem;
}

.date-range-selector {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
}

.date-range-selector input {
    padding: 0.5rem 1rem;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.metric-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.metric-label {
    color: #666;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.metric-value {
    font-size: 2rem;
    font-weight: bold;
    color: #1a1a1a;
}

.metric-change {
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

.metric-change.positive {
    color: #10b981;
}
.metric-change.negative {
    color: #ef4444;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.chart-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.reports-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.report-links {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-top: 1rem;
}

.report-link {
    padding: 1rem;
    background: #f5f5f5;
    border-radius: 6px;
    text-decoration: none;
    color: #333;
    transition: all 0.2s;
}

.report-link:hover {
    background: #e5e5e5;
    transform: translateX(4px);
}
</style>

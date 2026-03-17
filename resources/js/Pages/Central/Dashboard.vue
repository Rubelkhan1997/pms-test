<template>
    <div class="central-dashboard">
        <div class="dashboard-header">
            <div class="header-left">
                <h1>Central Admin Dashboard</h1>
                <nav class="dashboard-nav">
                    <Link
                        :href="route('central.dashboard')"
                        class="nav-link"
                        :class="{
                            active: $component.name === 'Central/Dashboard',
                        }"
                        >Dashboard</Link
                    >
                    <Link
                        :href="route('central.tenants.index')"
                        class="nav-link"
                        :class="{
                            active: $component.name === 'Central/Tenants/Index',
                        }"
                        >Tenants</Link
                    >
                </nav>
            </div>
            <div class="user-menu">
                <span v-if="$page.props.auth?.user" class="user-name">{{
                    $page.props.auth.user.name
                }}</span>
                <Link
                    v-if="$page.props.auth?.user"
                    :href="route('central.profile')"
                    class="btn-sm"
                    >Profile</Link
                >
                <Link
                    v-if="$page.props.auth?.user"
                    :href="route('central.logout')"
                    method="post"
                    as="button"
                    class="btn-sm btn-outline"
                >
                    Logout
                </Link>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🏢</div>
                <div class="stat-content">
                    <div class="stat-value">{{ stats.total_tenants }}</div>
                    <div class="stat-label">Total Tenants</div>
                </div>
            </div>

            <div class="stat-card pending">
                <div class="stat-icon">⏳</div>
                <div class="stat-content">
                    <div class="stat-value">{{ stats.pending_tenants }}</div>
                    <div class="stat-label">Pending Approval</div>
                </div>
            </div>

            <div class="stat-card active">
                <div class="stat-icon">✅</div>
                <div class="stat-content">
                    <div class="stat-value">{{ stats.active_tenants }}</div>
                    <div class="stat-label">Active Tenants</div>
                </div>
            </div>

            <div class="stat-card suspended">
                <div class="stat-icon">⚠️</div>
                <div class="stat-content">
                    <div class="stat-value">{{ stats.suspended_tenants }}</div>
                    <div class="stat-label">Suspended</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <div class="stat-value">{{ stats.total_users }}</div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
        </div>

        <!-- Recent Tenants -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Recent Tenants</h2>
                <Link :href="route('central.tenants.index')" class="view-all">
                    View All →
                </Link>
            </div>

            <div class="table-container">
                <table class="recent-table">
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Subdomain</th>
                            <th>Admin</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="tenant in recentTenants" :key="tenant.id">
                            <td>
                                <div class="tenant-name">{{ tenant.name }}</div>
                                <div class="tenant-email">
                                    {{ tenant.email }}
                                </div>
                            </td>
                            <td>
                                <code>{{ tenant.subdomain }}.pms.test</code>
                            </td>
                            <td>
                                <div
                                    v-if="
                                        tenant.owners &&
                                        tenant.owners.length > 0
                                    "
                                >
                                    {{ tenant.owners[0].name }}
                                </div>
                            </td>
                            <td>
                                <span
                                    class="status-badge"
                                    :class="tenant.status"
                                >
                                    {{ tenant.status }}
                                </span>
                            </td>
                            <td>{{ formatDate(tenant.created_at) }}</td>
                        </tr>
                        <tr v-if="recentTenants.length === 0">
                            <td colspan="5" class="empty-state">
                                No tenants yet
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-section">
            <h2>Quick Actions</h2>
            <div class="quick-actions">
                <Link
                    :href="route('central.tenants.index')"
                    class="action-card"
                >
                    <div class="action-icon">🏢</div>
                    <div class="action-title">Manage Tenants</div>
                    <div class="action-desc">View and approve tenants</div>
                </Link>

                <Link :href="route('central.profile')" class="action-card">
                    <div class="action-icon">👤</div>
                    <div class="action-title">Profile Settings</div>
                    <div class="action-desc">Update your profile</div>
                </Link>

                <a href="/central/tenants?status=pending" class="action-card">
                    <div class="action-icon">⏳</div>
                    <div class="action-title">Pending Approvals</div>
                    <div class="action-desc">
                        {{ stats.pending_tenants }} pending
                    </div>
                </a>

                <a href="#" class="action-card">
                    <div class="action-icon">📊</div>
                    <div class="action-title">System Reports</div>
                    <div class="action-desc">View analytics</div>
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    recentTenants: {
        type: Array,
        required: true,
    },
    auth: {
        type: Object,
        default: () => ({ user: null }),
    },
});

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}
</script>

<style scoped>
.central-dashboard {
    padding: 2rem;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    gap: 2rem;
}

.dashboard-header h1 {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.user-menu {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-name {
    font-weight: 600;
    color: #374151;
}

.btn-sm {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-outline {
    background: transparent;
    color: #374151;
    border: 1px solid #d1d5db;
}

.btn-outline:hover {
    background: #f9fafb;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon {
    font-size: 2.5rem;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: bold;
    color: #1a1a1a;
}

.stat-label {
    color: #6b7280;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.stat-card.pending {
    border-left: 4px solid #f59e0b;
}

.stat-card.active {
    border-left: 4px solid #10b981;
}

.stat-card.suspended {
    border-left: 4px solid #ef4444;
}

.dashboard-section {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.section-header h2 {
    font-size: 1.5rem;
    font-weight: 600;
}

.view-all {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
}

.view-all:hover {
    text-decoration: underline;
}

.table-container {
    overflow-x: auto;
}

.recent-table {
    width: 100%;
    border-collapse: collapse;
}

.recent-table th {
    background: #f9fafb;
    padding: 0.75rem 1rem;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #e5e7eb;
    font-size: 0.875rem;
}

.recent-table td {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.tenant-name {
    font-weight: 600;
    color: #1a1a1a;
}

.tenant-email {
    color: #6b7280;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

code {
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
    color: #6b7280;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.pending {
    background: #fef3c7;
    color: #92400e;
}

.status-badge.active {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.suspended {
    background: #fee2e2;
    color: #991b1b;
}

.empty-state {
    text-align: center;
    color: #9ca3af;
    padding: 2rem;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.action-card {
    background: #f9fafb;
    padding: 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    color: #1a1a1a;
    transition: all 0.2s;
    text-align: center;
}

.action-card:hover {
    background: #f3f4f6;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.action-icon {
    font-size: 2.5rem;
    margin-bottom: 0.75rem;
}

.action-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.action-desc {
    color: #6b7280;
    font-size: 0.875rem;
}

.header-left {
    flex: 1;
}

.dashboard-nav {
    display: flex;
    gap: 1.5rem;
    margin-top: 1rem;
}

.nav-link {
    color: #6b7280;
    text-decoration: none;
    font-weight: 500;
    padding: 0.5rem 0;
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
}

.nav-link:hover {
    color: #667eea;
}

.nav-link.active {
    color: #667eea;
    border-bottom-color: #667eea;
}
</style>

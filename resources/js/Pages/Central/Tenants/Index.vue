<template>
    <div class="tenants-list">
        <div class="page-header-new">
            <div class="page-title">
                <h1>Tenant Management</h1>
                <p>Manage all your property tenants, view their status, and approve new sign-ups.</p>
            </div>
            <div class="header-actions">
                <Link
                    :href="route('central.tenants.create')"
                    class="btn-primary"
                >
                    + Add Tenant
                </Link>
            </div>
        </div>

        <div class="stats">
            <div class="stat-badge pending">
                <span class="stat-value">{{ stats.pending }}</span>
                <span class="stat-label">Pending</span>
            </div>
            <div class="stat-badge active">
                <span class="stat-value">{{ stats.active }}</span>
                <span class="stat-label">Active</span>
            </div>
            <div class="stat-badge suspended">
                <span class="stat-value">{{ stats.suspended }}</span>
                <span class="stat-label">Suspended</span>
            </div>
        </div>

        <div v-if="$page.props.flash?.success" class="alert alert-success">
            {{ $page.props.flash.success }}
        </div>

        <div v-if="$page.props.flash?.error" class="alert alert-error">
            {{ $page.props.flash.error }}
        </div>

        <div class="table-container">
            <table class="tenants-table">
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>Subdomain</th>
                        <th>Admin</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="tenant in tenants.data" :key="tenant.id">
                        <td>
                            <div class="tenant-info">
                                <div class="tenant-name">{{ tenant.name }}</div>
                                <div class="tenant-email">
                                    {{ tenant.email }}
                                </div>
                            </div>
                        </td>
                        <td>
                            <code>{{ tenant.subdomain }}.pms.test</code>
                        </td>
                        <td>
                            <div
                                v-if="tenant.owners && tenant.owners.length > 0"
                            >
                                <div class="admin-name">
                                    {{ tenant.owners[0].name }}
                                </div>
                                <div class="admin-email">
                                    {{ tenant.owners[0].email }}
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge" :class="tenant.status">
                                {{ tenant.status }}
                            </span>
                        </td>
                        <td>{{ formatDate(tenant.created_at) }}</td>
                        <td>
                            <div class="action-buttons">
                                <Link
                                    :href="
                                        route('central.tenants.show', tenant.id)
                                    "
                                    class="btn-sm btn-info"
                                >
                                    View
                                </Link>

                                <template v-if="tenant.status === 'pending'">
                                    <button
                                        @click="approveTenant(tenant)"
                                        class="btn-sm btn-success"
                                        :disabled="processing"
                                    >
                                        Approve
                                    </button>
                                    <button
                                        @click="rejectTenant(tenant)"
                                        class="btn-sm btn-danger"
                                        :disabled="processing"
                                    >
                                        Reject
                                    </button>
                                </template>

                                <template
                                    v-else-if="tenant.status === 'active'"
                                >
                                    <button
                                        @click="suspendTenant(tenant)"
                                        class="btn-sm btn-warning"
                                        :disabled="processing"
                                    >
                                        Suspend
                                    </button>
                                </template>

                                <template
                                    v-else-if="tenant.status === 'suspended'"
                                >
                                    <button
                                        @click="reactivateTenant(tenant)"
                                        class="btn-sm btn-success"
                                        :disabled="processing"
                                    >
                                        Reactivate
                                    </button>
                                </template>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <Link
                v-for="(link, index) in tenants.links"
                :key="index"
                :href="link.url"
                v-html="link.label"
                class="pagination-link"
                :class="{ active: link.active, disabled: !link.url }"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import CentralLayout from "../../../Layouts/CentralLayout.vue";

defineOptions({ layout: CentralLayout });

const props = defineProps({
    tenants: {
        type: Object,
        required: true,
    },
});

const processing = ref(false);

const stats = computed(() => ({
    pending: props.tenants.data.filter((t) => t.status === "pending").length,
    active: props.tenants.data.filter((t) => t.status === "active").length,
    suspended: props.tenants.data.filter((t) => t.status === "suspended")
        .length,
}));

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}

function approveTenant(tenant) {
    if (
        confirm(
            `Approve tenant "${tenant.name}"? This will provision their database.`,
        )
    ) {
        processing.value = true;
        router.post(route("central.tenants.approve", tenant.id));
    }
}

function rejectTenant(tenant) {
    const reason = prompt("Rejection reason (optional):");
    if (reason !== null) {
        processing.value = true;
        router.post(route("central.tenants.reject", tenant.id), {
            rejection_reason: reason,
        });
    }
}

function suspendTenant(tenant) {
    if (confirm(`Suspend tenant "${tenant.name}"?`)) {
        processing.value = true;
        router.post(route("central.tenants.suspend", tenant.id));
    }
}

function reactivateTenant(tenant) {
    if (confirm(`Reactivate tenant "${tenant.name}"?`)) {
        processing.value = true;
        router.post(route("central.tenants.reactivate", tenant.id));
    }
}
</script>

<style scoped>
.tenants-list {
    padding: 2rem;
}

.page-header-new {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    gap: 2rem;
}

.page-title h1 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.5rem;
    letter-spacing: -0.025em;
}

.page-title p {
    color: #6b7280;
    font-size: 0.95rem;
    margin: 0;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    background-color: #4f46e5;
    color: white;
    font-weight: 600;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
}

.btn-primary:hover {
    background-color: #4338ca;
    transform: translateY(-2px);
    box-shadow: 0 6px 8px -1px rgba(79, 70, 229, 0.3);
}

.stats {
    display: flex;
    gap: 1rem;
}

.stat-badge {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    text-align: center;
}

.stat-badge.pending {
    background: #fef3c7;
    color: #92400e;
}

.stat-badge.active {
    background: #d1fae5;
    color: #065f46;
}

.stat-badge.suspended {
    background: #fee2e2;
    color: #991b1b;
}

.stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: bold;
}

.stat-label {
    font-size: 0.85rem;
    text-transform: uppercase;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #10b981;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #ef4444;
}

.table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.tenants-table {
    width: 100%;
    border-collapse: collapse;
}

.tenants-table th {
    background: #f9fafb;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #e5e7eb;
}

.tenants-table td {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.tenant-info .tenant-name {
    font-weight: 600;
    color: #1a1a1a;
}

.tenant-info .tenant-email {
    color: #666;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

code {
    background: #f5f5f5;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
    color: #666;
}

.admin-name {
    font-weight: 600;
    color: #1a1a1a;
}

.admin-email {
    color: #666;
    font-size: 0.875rem;
    margin-top: 0.25rem;
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

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-sm {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-info {
    background: #3b82f6;
    color: white;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-warning {
    background: #f59e0b;
    color: white;
}

.btn-sm:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.btn-sm:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    margin-top: 2rem;
}

.pagination-link {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    text-decoration: none;
    color: #374151;
    background: white;
    border: 1px solid #e5e7eb;
    transition: all 0.2s;
}

.pagination-link:hover:not(.disabled) {
    background: #f9fafb;
}

.pagination-link.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.pagination-link.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

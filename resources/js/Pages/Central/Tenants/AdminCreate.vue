<template>
    <div class="tenant-create-admin">
        <div class="page-header-new">
            <div class="page-title">
                <h1>Create New Tenant</h1>
                <p>Register a new hotel or property tenant manually.</p>
            </div>
            <div class="header-actions">
                <Link
                    :href="route('central.tenants.index')"
                    class="btn-secondary"
                >
                    Back to Tenants
                </Link>
            </div>
        </div>

        <div class="form-card">
            <div v-if="$page.props.flash?.success" class="alert alert-success">
                {{ $page.props.flash.success }}
            </div>

            <div v-if="$page.props.flash?.error" class="alert alert-error">
                {{ $page.props.flash.error }}
            </div>

            <div v-if="form.errors.general" class="alert alert-error">
                {{ form.errors.general }}
            </div>

            <form @submit.prevent="submit">
                <div class="form-grid">
                    <!-- Tenant Information -->
                    <div class="form-column">
                        <div class="form-section">
                            <h2>Property Details</h2>
                            <p class="section-desc">
                                Basic details about the hotel property and its
                                system endpoint.
                            </p>

                            <div class="form-group">
                                <label for="tenant_name">Property Name</label>
                                <input
                                    id="tenant_name"
                                    v-model="form.tenant_name"
                                    type="text"
                                    required
                                    placeholder="e.g. Grand Hotel"
                                    :class="{ error: form.errors.tenant_name }"
                                />
                                <span
                                    v-if="form.errors.tenant_name"
                                    class="error-message"
                                    >{{ form.errors.tenant_name }}</span
                                >
                            </div>

                            <div class="form-group">
                                <label for="tenant_email">Business Email</label>
                                <input
                                    id="tenant_email"
                                    v-model="form.tenant_email"
                                    type="email"
                                    required
                                    placeholder="hotel@example.com"
                                    :class="{ error: form.errors.tenant_email }"
                                />
                                <span
                                    v-if="form.errors.tenant_email"
                                    class="error-message"
                                    >{{ form.errors.tenant_email }}</span
                                >
                            </div>

                            <div class="form-group">
                                <label for="tenant_subdomain"
                                    >System Subdomain</label
                                >
                                <div class="input-with-prefix">
                                    <span class="prefix">https://</span>
                                    <input
                                        id="tenant_subdomain"
                                        v-model="form.tenant_subdomain"
                                        type="text"
                                        required
                                        placeholder="grandhotel"
                                        :class="{
                                            error: form.errors.tenant_subdomain,
                                        }"
                                    />
                                    <span class="suffix">.pms.test</span>
                                </div>
                                <span
                                    v-if="form.errors.tenant_subdomain"
                                    class="error-message"
                                    >{{ form.errors.tenant_subdomain }}</span
                                >
                                <small
                                    >The unique endpoint they will use to access
                                    the system.</small
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Admin User Information -->
                    <div class="form-column">
                        <div class="form-section">
                            <h2>Initial Administrator</h2>
                            <p class="section-desc">
                                Details for the tenant's primary owner account.
                            </p>

                            <div class="form-group">
                                <label for="admin_name">Full Name</label>
                                <input
                                    id="admin_name"
                                    v-model="form.admin_name"
                                    type="text"
                                    required
                                    placeholder="e.g. John Doe"
                                    :class="{ error: form.errors.admin_name }"
                                />
                                <span
                                    v-if="form.errors.admin_name"
                                    class="error-message"
                                    >{{ form.errors.admin_name }}</span
                                >
                            </div>

                            <div class="form-group">
                                <label for="admin_email">Email Address</label>
                                <input
                                    id="admin_email"
                                    v-model="form.admin_email"
                                    type="email"
                                    required
                                    placeholder="admin@grandhotel.com"
                                    :class="{ error: form.errors.admin_email }"
                                />
                                <span
                                    v-if="form.errors.admin_email"
                                    class="error-message"
                                    >{{ form.errors.admin_email }}</span
                                >
                            </div>

                            <div class="form-group">
                                <label for="admin_password">Password</label>
                                <input
                                    id="admin_password"
                                    v-model="form.admin_password"
                                    type="password"
                                    required
                                    minlength="8"
                                    placeholder="••••••••"
                                    :class="{
                                        error: form.errors.admin_password,
                                    }"
                                />
                                <span
                                    v-if="form.errors.admin_password"
                                    class="error-message"
                                    >{{ form.errors.admin_password }}</span
                                >
                            </div>

                            <div class="form-group">
                                <label for="admin_password_confirmation"
                                    >Confirm Password</label
                                >
                                <input
                                    id="admin_password_confirmation"
                                    v-model="form.admin_password_confirmation"
                                    type="password"
                                    required
                                    minlength="8"
                                    placeholder="••••••••"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button
                        type="submit"
                        class="btn-primary"
                        :disabled="form.processing"
                    >
                        <span v-if="!form.processing">+ Register Tenant</span>
                        <span v-else>Registering Provider Data...</span>
                    </button>
                    <p class="form-note">
                        Upon creation, this tenant will be placed in a
                        <strong>pending</strong> state and will require your
                        approval before their database is provisioned.
                    </p>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import CentralLayout from "../../../Layouts/CentralLayout.vue";

defineOptions({ layout: CentralLayout });

const form = useForm({
    tenant_name: "",
    tenant_email: "",
    tenant_subdomain: "",
    admin_name: "",
    admin_email: "",
    admin_password: "",
    admin_password_confirmation: "",
    accept_terms: true, // Auto accept for admin creation
});

function submit() {
    form.post(route("central.tenants.store"), {
        preserveScroll: true,
        onSuccess: () => {
            // Form submitted successfully, will redirect
        },
        onError: (errors) => {
            console.error("Form submission errors:", errors);
        },
    });
}
</script>

<style scoped>
.tenant-create-admin {
    padding: 0 0 2rem 0;
}

.alert {
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-weight: 500;
}

.alert-success {
    background-color: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.alert-error {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
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
    justify-content: center;
    padding: 0.75rem 1.5rem;
    background-color: #4f46e5;
    color: white;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
}

.btn-primary:hover:not(:disabled) {
    background-color: #4338ca;
    transform: translateY(-2px);
    box-shadow: 0 6px 8px -1px rgba(79, 70, 229, 0.3);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-secondary {
    display: inline-flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    background-color: white;
    color: #374151;
    font-weight: 500;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-secondary:hover {
    background-color: #f9fafb;
}

.form-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 2.5rem;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}

.form-section h2 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
}

.section-desc {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-group input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.2s;
    background-color: #f9fafb;
}

.form-group input:focus {
    outline: none;
    border-color: #4f46e5;
    background-color: white;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.form-group input.error {
    border-color: #ef4444;
}

.error-message {
    display: block;
    color: #ef4444;
    font-size: 0.85rem;
    margin-top: 0.4rem;
}

.form-group small {
    display: block;
    color: #9ca3af;
    font-size: 0.8rem;
    margin-top: 0.4rem;
}

.input-with-prefix {
    display: flex;
    align-items: center;
}

.input-with-prefix .prefix,
.input-with-prefix .suffix {
    padding: 0.75rem 1rem;
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    color: #6b7280;
    font-size: 0.9rem;
    white-space: nowrap;
}

.input-with-prefix .prefix {
    border-right: none;
    border-radius: 8px 0 0 8px;
}

.input-with-prefix .suffix {
    border-left: none;
    border-radius: 0 8px 8px 0;
}

.input-with-prefix input {
    border-radius: 0;
    background-color: white;
}

.form-actions {
    margin-top: 2.5rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
}

.form-note {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}
</style>

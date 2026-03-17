<template>
    <div class="tenant-register">
        <div class="register-card">
            <div class="register-header">
                <h1>Start Your Free Trial</h1>
                <p>14 days free • No credit card required</p>
            </div>

            <form @submit.prevent="submit">
                <!-- Tenant Information -->
                <div class="form-section">
                    <h2>Tenant Information</h2>

                    <div class="form-group">
                        <label for="tenant_name">Hotel/Property Name</label>
                        <input
                            id="tenant_name"
                            v-model="form.tenant_name"
                            type="text"
                            required
                            placeholder="Grand Hotel"
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
                        <label for="tenant_subdomain">Subdomain</label>
                        <div class="input-with-prefix">
                            <span class="prefix">https://</span>
                            <input
                                id="tenant_subdomain"
                                v-model="form.tenant_subdomain"
                                type="text"
                                required
                                placeholder="grandhotel"
                                :class="{ error: form.errors.tenant_subdomain }"
                            />
                            <span class="suffix">.pms.test</span>
                        </div>
                        <span
                            v-if="form.errors.tenant_subdomain"
                            class="error-message"
                            >{{ form.errors.tenant_subdomain }}</span
                        >
                        <small
                            >Your unique subdomain for accessing the
                            system</small
                        >
                    </div>
                </div>

                <!-- Admin User Information -->
                <div class="form-section">
                    <h2>Admin User Information</h2>

                    <div class="form-group">
                        <label for="admin_name">Full Name</label>
                        <input
                            id="admin_name"
                            v-model="form.admin_name"
                            type="text"
                            required
                            placeholder="John Doe"
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
                            :class="{ error: form.errors.admin_password }"
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

                <!-- Terms -->
                <div class="form-options">
                    <label class="checkbox-label">
                        <input
                            v-model="form.accept_terms"
                            type="checkbox"
                            required
                        />
                        <span
                            >I agree to the <a href="#">Terms of Service</a> and
                            <a href="#">Privacy Policy</a></span
                        >
                    </label>
                </div>

                <button
                    type="submit"
                    class="btn-primary"
                    :disabled="form.processing"
                >
                    <span v-if="!form.processing">Create Account</span>
                    <span v-else>Creating account...</span>
                </button>
            </form>

            <div class="auth-footer">
                <p>
                    Already have an account?
                    <Link :href="route('central.login')" class="link"
                        >Sign in</Link
                    >
                </p>
            </div>

            <div class="trial-info">
                <div class="trial-feature">
                    <span class="icon">✓</span>
                    <span>14-day free trial</span>
                </div>
                <div class="trial-feature">
                    <span class="icon">✓</span>
                    <span>Full access to all features</span>
                </div>
                <div class="trial-feature">
                    <span class="icon">✓</span>
                    <span>Requires admin approval</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const form = useForm({
    tenant_name: "",
    tenant_email: "",
    tenant_subdomain: "",
    admin_name: "",
    admin_email: "",
    admin_password: "",
    admin_password_confirmation: "",
    accept_terms: false,
});

function submit() {
    form.post(route("central.register"), {
        preserveScroll: true,
    });
}
</script>

<style scoped>
.tenant-register {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem;
}

.register-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    padding: 3rem;
    width: 100%;
    max-width: 600px;
}

.register-header {
    text-align: center;
    margin-bottom: 2rem;
}

.register-header h1 {
    font-size: 2rem;
    font-weight: bold;
    color: #1a1a1a;
    margin-bottom: 0.5rem;
}

.register-header p {
    color: #666;
    font-size: 0.95rem;
}

.form-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #e5e5e5;
}

.form-section h2 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-group input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e5e5;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s;
}

.form-group input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group input.error {
    border-color: #ef4444;
}

.error-message {
    display: block;
    color: #ef4444;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

.form-group small {
    display: block;
    color: #666;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

.input-with-prefix {
    display: flex;
    align-items: center;
    gap: 0;
}

.input-with-prefix .prefix,
.input-with-prefix .suffix {
    padding: 0.75rem 1rem;
    background: #f5f5f5;
    border: 2px solid #e5e5e5;
    color: #666;
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
    flex: 1;
}

.input-with-prefix input.error {
    border-color: #ef4444;
}

.form-options {
    margin-bottom: 1.5rem;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: #666;
    cursor: pointer;
}

.checkbox-label a {
    color: #667eea;
    text-decoration: none;
}

.checkbox-label a:hover {
    text-decoration: underline;
}

.btn-primary {
    width: 100%;
    padding: 0.875rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.auth-footer {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e5e5;
}

.auth-footer p {
    color: #666;
    font-size: 0.95rem;
}

.auth-footer .link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
}

.auth-footer .link:hover {
    text-decoration: underline;
}

.trial-info {
    margin-top: 2rem;
    padding: 1.5rem;
    background: #f0f9ff;
    border-radius: 8px;
}

.trial-feature {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
    color: #0369a1;
}

.trial-feature:last-child {
    margin-bottom: 0;
}

.trial-feature .icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    background: #10b981;
    color: white;
    border-radius: 50%;
    font-size: 0.75rem;
    font-weight: bold;
}
</style>

<template>
    <div class="central-auth">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Central Admin Login</h1>
                <p>Sign in to manage tenants</p>
            </div>

            <div v-if="$page.props.flash?.success" class="alert alert-success">
                {{ $page.props.flash.success }}
            </div>

            <form @submit.prevent="submit">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        placeholder="admin@pms.test"
                        :class="{ error: form.errors.email }"
                    />
                    <span v-if="form.errors.email" class="error-message">{{
                        form.errors.email
                    }}</span>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        placeholder="••••••••"
                        :class="{ error: form.errors.password }"
                    />
                    <span v-if="form.errors.password" class="error-message">{{
                        form.errors.password
                    }}</span>
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input v-model="form.remember" type="checkbox" />
                        <span>Remember me</span>
                    </label>
                </div>

                <button
                    type="submit"
                    class="btn-primary"
                    :disabled="form.processing"
                >
                    <span v-if="!form.processing">Sign In</span>
                    <span v-else>Signing in...</span>
                </button>
            </form>

            <div class="auth-footer">
                <Link :href="route('central.home')" class="back-link">
                    ← Back to Home
                </Link>
            </div>

            <div class="demo-credentials">
                <p><strong>Demo Admin Credentials:</strong></p>
                <p>Email: superadmin@pms.test</p>
                <p>Password: password</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

function submit() {
    form.post(route("central.login"), {
        preserveScroll: true,
    });
}
</script>

<style scoped>
.central-auth {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem;
}

.auth-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    padding: 3rem;
    width: 100%;
    max-width: 450px;
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-header h1 {
    font-size: 2rem;
    font-weight: bold;
    color: #1a1a1a;
    margin-bottom: 0.5rem;
}

.auth-header p {
    color: #666;
    font-size: 0.95rem;
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

.form-group {
    margin-bottom: 1.5rem;
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

.form-options {
    margin-bottom: 1.5rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #666;
    cursor: pointer;
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

.back-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
}

.back-link:hover {
    text-decoration: underline;
}

.demo-credentials {
    margin-top: 2rem;
    padding: 1rem;
    background: #f5f5f5;
    border-radius: 8px;
    text-align: center;
    font-size: 0.85rem;
    color: #666;
}

.demo-credentials p {
    margin: 0.25rem 0;
}
</style>

<template>
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <h1>Create Account</h1>
        <p>Start your 14-day free trial</p>
      </div>

      <form @submit.prevent="submit">
        <div class="form-group">
          <label for="name">Full Name</label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            autofocus
            placeholder="John Doe"
            :class="{ error: form.errors.name }"
          />
          <span v-if="form.errors.name" class="error-message">{{ form.errors.name }}</span>
        </div>

        <div class="form-group">
          <label for="email">Email Address</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            placeholder="you@example.com"
            :class="{ error: form.errors.email }"
          />
          <span v-if="form.errors.email" class="error-message">{{ form.errors.email }}</span>
        </div>

        <div class="form-group">
          <label for="company_name">Company/Hotel Name (Optional)</label>
          <input
            id="company_name"
            v-model="form.company_name"
            type="text"
            placeholder="Grand Hotel"
            :class="{ error: form.errors.company_name }"
          />
          <span v-if="form.errors.company_name" class="error-message">{{ form.errors.company_name }}</span>
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
          <span v-if="form.errors.password" class="error-message">{{ form.errors.password }}</span>
        </div>

        <div class="form-group">
          <label for="password_confirmation">Confirm Password</label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            required
            placeholder="••••••••"
          />
        </div>

        <div class="form-options">
          <label class="checkbox-label">
            <input v-model="form.accept_terms" type="checkbox" required />
            <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></span>
          </label>
        </div>

        <button type="submit" class="btn-primary" :disabled="form.processing">
          <span v-if="!form.processing">Create Account</span>
          <span v-else>Creating account...</span>
        </button>
      </form>

      <div class="auth-footer">
        <p>
          Already have an account?
          <Link :href="route('login')" class="link">Sign in</Link>
        </p>
      </div>

      <div class="trial-info">
        <div class="trial-feature">
          <span class="icon">✓</span>
          <span>14-day free trial</span>
        </div>
        <div class="trial-feature">
          <span class="icon">✓</span>
          <span>No credit card required</span>
        </div>
        <div class="trial-feature">
          <span class="icon">✓</span>
          <span>Full access to all features</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const form = useForm({
  name: '',
  email: '',
  company_name: '',
  password: '',
  password_confirmation: '',
  accept_terms: false,
});

function submit() {
  form.post(route('register'), {
    preserveScroll: true,
  });
}
</script>

<style scoped>
.auth-container {
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
  max-width: 500px;
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

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"] {
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

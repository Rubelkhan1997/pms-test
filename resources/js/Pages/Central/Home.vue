<template>
  <div class="central-home">
    <!-- Hero Section -->
    <section class="hero">
      <div class="container">
        <h1>Hotel Property Management System</h1>
        <p class="subtitle">Complete hotel management solution for modern hospitality</p>
        <div class="cta-buttons">
          <Link :href="route('central.register')" class="btn btn-primary">
            Start Free Trial
          </Link>
          <Link :href="route('central.login')" class="btn btn-secondary">
            Login
          </Link>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features">
      <div class="container">
        <h2>Everything You Need</h2>
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">🏨</div>
            <h3>Property Management</h3>
            <p>Complete room and reservation management</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">📅</div>
            <h3>Booking Engine</h3>
            <p>Direct bookings from your website</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">🌐</div>
            <h3>Channel Manager</h3>
            <p>Connect to Booking.com, Expedia & more</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">💰</div>
            <h3>Accounting</h3>
            <p>Invoicing, payments & financial reports</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">🧹</div>
            <h3>Housekeeping</h3>
            <p>Task management & room status tracking</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">📊</div>
            <h3>Analytics</h3>
            <p>Occupancy, revenue & performance metrics</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing">
      <div class="container">
        <h2>Simple, Transparent Pricing</h2>
        <div class="pricing-grid">
          <div class="pricing-card">
            <h3>Starter</h3>
            <div class="price">$49<span>/month</span></div>
            <ul class="features-list">
              <li>✓ Up to 20 rooms</li>
              <li>✓ 1 property</li>
              <li>✓ Basic reporting</li>
              <li>✓ Email support</li>
              <li>✗ Channel manager</li>
            </ul>
            <Link :href="route('central.register')" class="btn btn-outline">
              Get Started
            </Link>
          </div>
          <div class="pricing-card featured">
            <div class="badge">Most Popular</div>
            <h3>Professional</h3>
            <div class="price">$99<span>/month</span></div>
            <ul class="features-list">
              <li>✓ Up to 100 rooms</li>
              <li>✓ 5 properties</li>
              <li>✓ Advanced reporting</li>
              <li>✓ Priority support</li>
              <li>✓ Channel manager</li>
            </ul>
            <Link :href="route('central.register')" class="btn btn-primary">
              Get Started
            </Link>
          </div>
          <div class="pricing-card">
            <h3>Enterprise</h3>
            <div class="price">$249<span>/month</span></div>
            <ul class="features-list">
              <li>✓ Unlimited rooms</li>
              <li>✓ Unlimited properties</li>
              <li>✓ Custom reporting</li>
              <li>✓ 24/7 support</li>
              <li>✓ Full integration</li>
            </ul>
            <Link :href="route('central.register')" class="btn btn-outline">
              Contact Sales
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="container">
        <p>&copy; 2026 Hotel PMS. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
</script>

<style scoped>
.central-home {
  min-height: 100vh;
}

.hero {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 8rem 2rem;
  text-align: center;
}

.hero h1 {
  font-size: 3rem;
  font-weight: bold;
  margin-bottom: 1rem;
}

.subtitle {
  font-size: 1.25rem;
  opacity: 0.9;
  margin-bottom: 2rem;
}

.cta-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.btn {
  padding: 1rem 2rem;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-primary {
  background: white;
  color: #667eea;
}

.btn-secondary {
  background: transparent;
  color: white;
  border: 2px solid white;
}

.btn-outline {
  background: transparent;
  color: #667eea;
  border: 2px solid #667eea;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.features {
  padding: 6rem 2rem;
  background: #f9fafb;
}

.features h2 {
  text-align: center;
  font-size: 2.5rem;
  margin-bottom: 3rem;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.feature-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  text-align: center;
}

.feature-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.feature-card h3 {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.pricing {
  padding: 6rem 2rem;
}

.pricing h2 {
  text-align: center;
  font-size: 2.5rem;
  margin-bottom: 3rem;
}

.pricing-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
  max-width: 1000px;
  margin: 0 auto;
}

.pricing-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  text-align: center;
  position: relative;
}

.pricing-card.featured {
  border: 2px solid #667eea;
  transform: scale(1.05);
}

.badge {
  position: absolute;
  top: -12px;
  left: 50%;
  transform: translateX(-50%);
  background: #667eea;
  color: white;
  padding: 0.25rem 1rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: bold;
}

.price {
  font-size: 3rem;
  font-weight: bold;
  color: #1a1a1a;
  margin: 1.5rem 0;
}

.price span {
  font-size: 1rem;
  color: #666;
}

.features-list {
  list-style: none;
  padding: 0;
  margin: 2rem 0;
  text-align: left;
}

.features-list li {
  padding: 0.5rem 0;
  border-bottom: 1px solid #f0f0f0;
}

.footer {
  background: #1a1a1a;
  color: white;
  padding: 2rem;
  text-align: center;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
}
</style>

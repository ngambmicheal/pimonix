<template>
  <div class="auth-container">
    <div class="row g-0 h-100">
      <!-- Left Side - Branding -->
      <div class="col-lg-6 d-none d-lg-flex auth-branding">
        <div class="branding-content">
          <div class="brand-logo mb-4">
            <i class="bi bi-wallet2"></i>
          </div>
          <h1 class="brand-title">Pimonix</h1>
          <p class="brand-tagline">Your Digital Wallet, Simplified</p>
          <div class="brand-features mt-5">
            <div class="feature-item">
              <i class="bi bi-lightning-charge-fill"></i>
              <span>Instant Transfers</span>
            </div>
            <div class="feature-item">
              <i class="bi bi-shield-check"></i>
              <span>Secure & Reliable</span>
            </div>
            <div class="feature-item">
              <i class="bi bi-graph-up"></i>
              <span>Track Your Spending</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Side - Login Form -->
      <div class="col-lg-6 d-flex align-items-center justify-content-center auth-form-section">
        <div class="auth-form-container">
          <!-- Mobile Logo -->
          <div class="text-center mb-4 d-lg-none">
            <i class="bi bi-wallet2 text-primary" style="font-size: 3rem;"></i>
            <h3 class="mt-2">Pimonix</h3>
          </div>

          <div class="auth-card">
            <div class="text-center mb-4">
              <h2 class="auth-title">Welcome Back</h2>
              <p class="text-muted">Login to your account to continue</p>
            </div>

            <div v-if="authStore.error" class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              {{ authStore.error }}
              <button type="button" class="btn-close" @click="authStore.error = null"></button>
            </div>

            <form @submit.prevent="handleLogin">
              <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email Address</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-envelope text-muted"></i>
                  </span>
                  <input
                    type="email"
                    class="form-control border-start-0 ps-0"
                    id="email"
                    v-model="form.email"
                    placeholder="Enter your email"
                    required
                    autofocus
                  />
                </div>
              </div>

              <div class="mb-4">
                <label for="password" class="form-label fw-semibold">Password</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-lock text-muted"></i>
                  </span>
                  <input
                    type="password"
                    class="form-control border-start-0 ps-0"
                    id="password"
                    v-model="form.password"
                    placeholder="Enter your password"
                    required
                  />
                </div>
              </div>

              <button
                type="submit"
                class="btn btn-primary w-100 py-2 mb-3"
                :disabled="authStore.loading"
              >
                <span v-if="authStore.loading">
                  <span class="spinner-border spinner-border-sm me-2"></span>
                  Logging in...
                </span>
                <span v-else>
                  <i class="bi bi-box-arrow-in-right me-2"></i>
                  Login
                </span>
              </button>

              <div class="text-center">
                <p class="text-muted mb-0">
                  Don't have an account?
                  <router-link to="/register" class="text-primary fw-semibold text-decoration-none">
                    Create Account
                  </router-link>
                </p>
              </div>
            </form>

            <!-- Test Accounts Info -->
            <div class="mt-4 pt-4 border-top">
              <p class="text-muted small mb-2"><strong>Test Accounts:</strong></p>
              <div class="test-accounts">
                <code class="small">john@example.com</code>
                <code class="small">jane@example.com</code>
                <code class="small">alice@example.com</code>
              </div>
              <p class="text-muted small mt-2 mb-0">Password: <code>password</code></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  email: '',
  password: ''
})

async function handleLogin() {
  try {
    await authStore.login(form.value)
    router.push('/dashboard')
  } catch (error) {
    console.error('Login failed:', error)
  }
}
</script>

<style scoped>
.auth-container {
  min-height: 100vh;
  background: #f8f9fa;
}

.auth-branding {
  background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
  color: white;
  padding: 3rem;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

.auth-branding::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
}

.branding-content {
  position: relative;
  z-index: 1;
  max-width: 500px;
  text-align: center;
}

.brand-logo {
  font-size: 5rem;
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-20px);
  }
}

.brand-title {
  font-size: 3.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.brand-tagline {
  font-size: 1.25rem;
  opacity: 0.9;
}

.brand-features {
  text-align: left;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 0;
  font-size: 1.1rem;
}

.feature-item i {
  font-size: 1.5rem;
  opacity: 0.9;
}

.auth-form-section {
  padding: 2rem;
  background: #f8f9fa;
}

.auth-form-container {
  width: 100%;
  max-width: 480px;
}

.auth-card {
  background: white;
  padding: 2.5rem;
  border-radius: 16px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 10px 20px rgba(0, 0, 0, 0.05);
}

.auth-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #212529;
}

.input-group-text {
  background: #f8f9fa;
  border: 1px solid #dee2e6;
}

.form-control {
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  padding: 0.625rem 0.75rem;
}

.form-control:focus {
  background: white;
  border-color: #0d6efd;
  box-shadow: none;
}

.input-group:focus-within .input-group-text {
  background: white;
  border-color: #0d6efd;
}

.btn-primary {
  background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
  border: none;
  font-weight: 600;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
}

.btn-primary:active:not(:disabled) {
  transform: translateY(0);
}

.test-accounts {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.test-accounts code {
  background: #f8f9fa;
  color: #0d6efd;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
}

@media (max-width: 991.98px) {
  .auth-card {
    padding: 2rem 1.5rem;
  }
}
</style>

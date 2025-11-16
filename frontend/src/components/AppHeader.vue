<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
      <router-link to="/" class="navbar-brand fw-bold">
        <i class="bi bi-wallet2"></i> Pimonix
      </router-link>

      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <router-link
              to="/dashboard"
              class="nav-link"
              active-class="active"
            >
              <i class="bi bi-speedometer2"></i> Dashboard
            </router-link>
          </li>
          <li class="nav-item">
            <router-link
              to="/transactions"
              class="nav-link"
              active-class="active"
            >
              <i class="bi bi-arrow-left-right"></i> Transactions
            </router-link>
          </li>
        </ul>

        <div class="d-flex align-items-center">
          <div class="text-white me-3">
            <i class="bi bi-person-circle"></i>
            <span class="ms-2">{{ authStore.user?.name }}</span>
          </div>
          <div class="text-white me-3">
            <small class="text-white-50">Balance:</small>
            <strong class="ms-1">${{ walletStore.balance }}</strong>
          </div>
          <button @click="handleLogout" class="btn btn-outline-light btn-sm">
            <i class="bi bi-box-arrow-right"></i> Logout
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useWalletStore } from '../stores/wallet'

const router = useRouter()
const authStore = useAuthStore()
const walletStore = useWalletStore()

async function handleLogout() {
  await authStore.logout()
  walletStore.reset()
  router.push('/login')
}
</script>

<style scoped>
.navbar {
  width: 100%;
  margin: 0;
  padding: 0;
}

.container-fluid {
  width: 80%;
  max-width: 1400px;
  margin: 0 auto;
  padding: 0.5rem 1rem;
}

.navbar-brand {
  font-size: 1.5rem;
}

.nav-link {
  transition: all 0.3s ease;
}

.nav-link:hover {
  transform: translateY(-2px);
}

.nav-link.active {
  font-weight: bold;
  border-bottom: 2px solid white;
}

@media (max-width: 992px) {
  .container-fluid {
    width: 90%;
  }
}

@media (max-width: 768px) {
  .container-fluid {
    width: 95%;
  }
}
</style>

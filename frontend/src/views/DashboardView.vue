<template>
  <AppLayout>
    <!-- Welcome Section -->
      <div class="row mb-4">
        <div class="col">
          <h2 class="mb-1">Welcome back, {{ authStore.user?.name }}! 👋</h2>
          <p class="text-muted">Here's an overview of your wallet activity</p>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="row g-4 mb-4">
        <div class="col-md-4">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                  <i class="bi bi-wallet2"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                  <p class="text-muted mb-0 small">Current Balance</p>
                  <h3 class="mb-0 fw-bold">${{ walletStore.balance }}</h3>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                  <i class="bi bi-arrow-left-right"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                  <p class="text-muted mb-0 small">Total Transactions</p>
                  <h3 class="mb-0 fw-bold">{{ walletStore.transactions.length }}</h3>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                  <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                  <p class="text-muted mb-0 small">Total Spent</p>
                  <h3 class="mb-0 fw-bold">${{ totalSpent }}</h3>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions & Recent Transactions -->
      <div class="row g-4">
        <div class="col-lg-5">
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <h5 class="card-title mb-4">
                <i class="bi bi-send text-primary"></i> Quick Transfer
              </h5>
              <TransferForm @transfer-sent="handleTransferSent" compact />
            </div>
          </div>
        </div>

        <div class="col-lg-7">
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0">
                  <i class="bi bi-clock-history text-primary"></i> Recent Activity
                </h5>
                <router-link to="/transactions" class="btn btn-sm btn-outline-primary">
                  View All <i class="bi bi-arrow-right"></i>
                </router-link>
              </div>

              <div v-if="recentTransactions.length === 0" class="text-center text-muted py-4">
                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                <p class="mt-2">No recent transactions</p>
              </div>

              <div v-else class="transaction-list">
                <div
                  v-for="transaction in recentTransactions"
                  :key="transaction.tuuid"
                  class="transaction-item d-flex align-items-center py-3 border-bottom"
                >
                  <div
                    class="transaction-icon me-3"
                    :class="getTransactionIconClass(transaction)"
                  >
                    <i :class="getTransactionIcon(transaction)"></i>
                  </div>
                  <div class="flex-grow-1">
                    <p class="mb-0 fw-semibold">{{ getTransactionTitle(transaction) }}</p>
                    <small class="text-muted">
                      {{ formatDate(transaction.created_at) }}
                    </small>
                  </div>
                  <div class="text-end">
                    <p class="mb-0 fw-bold" :class="getAmountClass(transaction)">
                      {{ getAmountPrefix(transaction) }}${{ transaction.amount }}
                    </p>
                    <small class="text-muted">Fee: ${{ transaction.commission_fee }}</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
  </AppLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useWalletStore } from '../stores/wallet'
import AppLayout from '../components/AppLayout.vue'
import TransferForm from '../components/TransferForm.vue'

const authStore = useAuthStore()
const walletStore = useWalletStore()

const recentTransactions = computed(() => {
  return walletStore.transactions.slice(0, 5)
})

const totalSpent = computed(() => {
  const spent = walletStore.transactions
    .filter(t => t.sender?.uid === authStore.user?.uid)
    .reduce((sum, t) => sum + parseFloat(t.amount) + parseFloat(t.commission_fee), 0)
  return spent.toFixed(2)
})

onMounted(() => {
  if (walletStore.transactions.length === 0) {
    walletStore.fetchTransactions()
  }
})

function handleTransferSent() {
  walletStore.fetchTransactions()
}

function getTransactionTitle(transaction) {
  if (transaction.sender?.uid === authStore.user?.uid) {
    return `Sent to ${transaction.receiver?.name}`
  } else {
    return `Received from ${transaction.sender?.name}`
  }
}

function getTransactionIcon(transaction) {
  if (transaction.type === 'commission') return 'bi bi-percent'
  if (transaction.sender?.uid === authStore.user?.uid) {
    return 'bi bi-arrow-up-right'
  }
  return 'bi bi-arrow-down-left'
}

function getTransactionIconClass(transaction) {
  if (transaction.type === 'commission') return 'bg-warning bg-opacity-10 text-warning'
  if (transaction.sender?.uid === authStore.user?.uid) {
    return 'bg-danger bg-opacity-10 text-danger'
  }
  return 'bg-success bg-opacity-10 text-success'
}

function getAmountClass(transaction) {
  if (transaction.sender?.uid === authStore.user?.uid) {
    return 'text-danger'
  }
  return 'text-success'
}

function getAmountPrefix(transaction) {
  if (transaction.sender?.uid === authStore.user?.uid) {
    return '- '
  }
  return '+ '
}

function formatDate(dateString) {
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)

  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`
  return date.toLocaleDateString()
}
</script>

<style scoped>
.stat-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.transaction-item {
  transition: background-color 0.2s ease;
}

.transaction-item:hover {
  background-color: #f8f9fa;
}

.transaction-item:last-child {
  border-bottom: none !important;
}

.transaction-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>

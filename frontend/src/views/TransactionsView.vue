<template>
  <AppLayout>
    <div class="row mb-4">
        <div class="col">
          <h2 class="mb-1">
            <i class="bi bi-arrow-left-right text-primary"></i> Transaction History
          </h2>
          <p class="text-muted">View all your wallet transactions</p>
        </div>
      </div>

      <div class="row">
        <div class="col">
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <div v-if="walletStore.loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Loading transactions...</p>
              </div>

              <div v-else-if="walletStore.transactions.length === 0" class="text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                <h5 class="mt-3 text-muted">No transactions yet</h5>
                <p class="text-muted">Start by sending money to someone!</p>
                <router-link to="/dashboard" class="btn btn-primary mt-3">
                  Go to Dashboard
                </router-link>
              </div>

              <div v-else>
                <!-- Filters -->
                <div class="row mb-4">
                  <div class="col-md-6">
                    <div class="input-group">
                      <span class="input-group-text">
                        <i class="bi bi-search"></i>
                      </span>
                      <input
                        type="text"
                        class="form-control"
                        placeholder="Search transactions..."
                        v-model="searchQuery"
                      />
                    </div>
                  </div>
                  <div class="col-md-3">
                    <select class="form-select" v-model="filterType">
                      <option value="">All Types</option>
                      <option value="transfer">Transfers</option>
                      <option value="commission">Commissions</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <select class="form-select" v-model="filterDirection">
                      <option value="">All Transactions</option>
                      <option value="sent">Sent</option>
                      <option value="received">Received</option>
                    </select>
                  </div>
                </div>

                <!-- Transactions Table -->
                <div class="table-responsiv">
                  <table class="table table-hover align-middle">
                    <thead class="table-light">
                      <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>From/To</th>
                        <th>Amount</th>
                        <th>Fee</th>
                        <th>Status</th>
                        <th>ID</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        v-for="transaction in filteredTransactions"
                        :key="transaction.tuuid"
                        class="transaction-row"
                      >
                        <td>
                          <div>
                            <div class="fw-semibold">{{ formatDate(transaction.created_at) }}</div>
                            <small class="text-muted">{{ formatTime(transaction.created_at) }}</small>
                          </div>
                        </td>
                        <td>
                          <span :class="getTypeBadgeClass(transaction.type)" class="badge">
                            {{ transaction.type }}
                          </span>
                        </td>
                        <td>
                          <div v-if="transaction.sender?.uid === authStore.user?.uid">
                            <small class="text-muted d-block">To:</small> <strong>{{ transaction.receiver?.name }}</strong>
                            <br />
                            <small class="text-muted">{{ transaction.receiver?.uid }}</small>
                          </div>
                          <div v-else>
                            <small class="text-muted d-block">From:</small>
                            <strong>{{ transaction.sender?.name }}</strong>
                            <br />
                            <small class="text-muted">{{ transaction.sender?.uid }}</small>
                          </div>
                        </td>
                        <td>
                          <span
                            class="fw-bold"
                            :class="getAmountClass(transaction)"
                          >
                            {{ getAmountPrefix(transaction) }}${{ transaction.amount }}
                          </span>
                        </td>
                        <td>
                          <span class="text-muted">${{ transaction.commission_fee }}</span>
                        </td>
                        <td>
                          <span :class="getStatusBadgeClass(transaction.status)" class="badge">
                            {{ transaction.status }}
                          </span>
                        </td>
                        <td>
                          <small class="text-muted font-monospace">
                            {{ transaction.tuuid.substring(0, 8) }}...
                          </small>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Pagination -->
                <div v-if="walletStore.totalPages > 1" class="d-flex justify-content-center mt-4">
                  <nav>
                    <ul class="pagination">
                      <li class="page-item" :class="{ disabled: walletStore.currentPage === 1 }">
                        <button
                          class="page-link"
                          @click="changePage(walletStore.currentPage - 1)"
                          :disabled="walletStore.currentPage === 1"
                        >
                          Previous
                        </button>
                      </li>
                      <li
                        v-for="page in walletStore.totalPages"
                        :key="page"
                        class="page-item"
                        :class="{ active: walletStore.currentPage === page }"
                      >
                        <button class="page-link" @click="changePage(page)">
                          {{ page }}
                        </button>
                      </li>
                      <li
                        class="page-item"
                        :class="{ disabled: walletStore.currentPage === walletStore.totalPages }"
                      >
                        <button
                          class="page-link"
                          @click="changePage(walletStore.currentPage + 1)"
                          :disabled="walletStore.currentPage === walletStore.totalPages"
                        >
                          Next
                        </button>
                      </li>
                    </ul>
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useWalletStore } from '../stores/wallet'
import AppLayout from '../components/AppLayout.vue'

const authStore = useAuthStore()
const walletStore = useWalletStore()

const searchQuery = ref('')
const filterType = ref('')
const filterDirection = ref('')

const filteredTransactions = computed(() => {
  let transactions = walletStore.transactions

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    transactions = transactions.filter(t =>
      t.sender?.name.toLowerCase().includes(query) ||
      t.receiver?.name.toLowerCase().includes(query) ||
      t.tuuid.toLowerCase().includes(query)
    )
  }

  // Filter by type
  if (filterType.value) {
    transactions = transactions.filter(t => t.type === filterType.value)
  }

  // Filter by direction
  if (filterDirection.value) {
    transactions = transactions.filter(t => {
      if (filterDirection.value === 'sent') {
        return t.sender?.uid === authStore.user?.uid
      } else if (filterDirection.value === 'received') {
        return t.receiver?.uid === authStore.user?.uid
      }
      return true
    })
  }

  return transactions
})

onMounted(() => {
  walletStore.fetchTransactions()
})

function changePage(page) {
  if (page >= 1 && page <= walletStore.totalPages) {
    walletStore.fetchTransactions(page)
  }
}

function getTypeBadgeClass(type) {
  const classes = {
    transfer: 'bg-primary',
    commission: 'bg-warning',
    deposit: 'bg-success',
    withdrawal: 'bg-danger'
  }
  return classes[type] || 'bg-secondary'
}

function getStatusBadgeClass(status) {
  const classes = {
    completed: 'bg-success',
    pending: 'bg-warning',
    failed: 'bg-danger'
  }
  return classes[status] || 'bg-secondary'
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
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

function formatTime(dateString) {
  const date = new Date(dateString)
  return date.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<style scoped>
.transaction-row {
  transition: background-color 0.2s ease;
}

.transaction-row:hover {
  background-color: rgba(13, 110, 253, 0.05);
}

.font-monospace {
  font-family: 'Courier New', monospace;
}
</style>

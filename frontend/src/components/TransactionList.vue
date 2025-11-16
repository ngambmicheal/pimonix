<template>
  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Transaction History</h5>

      <div v-if="transactions.length === 0" class="text-center text-muted py-4">
        No transactions yet
      </div>

      <div v-else class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>Type</th>
              <th>From/To</th>
              <th>Amount</th>
              <th>Fee</th>
              <th>Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="transaction in transactions" :key="transaction.tuuid">
              <td>
                <small class="text-muted">{{ transaction.tuuid.substring(0, 8) }}...</small>
              </td>
              <td>
                <span :class="getTypeBadgeClass(transaction.type)">
                  {{ transaction.type }}
                </span>
              </td>
              <td>
                <div v-if="transaction.sender">
                  <small class="text-muted">From:</small>
                  <strong>{{ transaction.sender.name }}</strong>
                  <small>({{ transaction.sender.uid }})</small>
                </div>
                <div v-if="transaction.receiver">
                  <small class="text-muted">To:</small>
                  <strong>{{ transaction.receiver.name }}</strong>
                  <small>({{ transaction.receiver.uid }})</small>
                </div>
              </td>
              <td>
                <span :class="getAmountClass(transaction)">
                  ${{ transaction.amount }}
                </span>
              </td>
              <td>
                <small class="text-muted">${{ transaction.commission_fee }}</small>
              </td>
              <td>
                <small>{{ formatDate(transaction.created_at) }}</small>
              </td>
              <td>
                <span :class="getStatusBadgeClass(transaction.status)">
                  {{ transaction.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  transactions: {
    type: Array,
    default: () => []
  }
})

function getTypeBadgeClass(type) {
  const classes = {
    transfer: 'badge bg-primary',
    commission: 'badge bg-warning',
    deposit: 'badge bg-success',
    withdrawal: 'badge bg-danger'
  }
  return classes[type] || 'badge bg-secondary'
}

function getStatusBadgeClass(status) {
  const classes = {
    completed: 'badge bg-success',
    pending: 'badge bg-warning',
    failed: 'badge bg-danger'
  }
  return classes[status] || 'badge bg-secondary'
}

function getAmountClass(transaction) {
  // If current user is receiver, it's incoming (green)
  // If current user is sender, it's outgoing (red)
  return transaction.type === 'transfer' ? 'text-primary fw-bold' : 'text-muted'
}

function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleString()
}
</script>

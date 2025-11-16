<template>
  <div class="container my-4">
    <div class="row mb-3">
      <div class="col">
        <div class="d-flex justify-content-between align-items-center">
          <h2>Pimonix Wallet</h2>
          <div>
            <span class="me-3">{{ authStore.user?.name }}</span>
            <button @click="handleLogout" class="btn btn-outline-danger btn-sm">
              Logout
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-md-6">
        <BalanceCard :balance="walletStore.balance" />
      </div>
      <div class="col-md-6">
        <TransferForm @transfer-sent="handleTransferSent" />
      </div>
    </div>

    <div class="row">
      <div class="col">
        <TransactionList :transactions="walletStore.transactions" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useWalletStore } from '../stores/wallet'
import BalanceCard from '../components/BalanceCard.vue'
import TransferForm from '../components/TransferForm.vue'
import TransactionList from '../components/TransactionList.vue'
import { initEcho, disconnectEcho } from '../services/echo'

const router = useRouter()
const authStore = useAuthStore()
const walletStore = useWalletStore()

let echoInstance = null

onMounted(async () => {
  await walletStore.fetchTransactions()

  // Initialize Laravel Echo for real-time updates
  echoInstance = initEcho(authStore.token)
  if (echoInstance && authStore.user) {
    echoInstance.private(`user.${authStore.user.id}`)
      .listen('TransactionCreated', (e) => {
        walletStore.addTransaction(e.transaction)
        walletStore.fetchTransactions() // Refresh to update balance
      })
  }
})

onUnmounted(() => {
  if (echoInstance) {
    disconnectEcho(echoInstance)
  }
})

async function handleLogout() {
  await authStore.logout()
  walletStore.reset()
  router.push('/login')
}

function handleTransferSent() {
  // Refresh transactions after successful transfer
  walletStore.fetchTransactions()
}
</script>

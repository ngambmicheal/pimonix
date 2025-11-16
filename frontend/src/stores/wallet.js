import { defineStore } from 'pinia'
import { ref } from 'vue'
import { transactionAPI } from '../services/api'

export const useWalletStore = defineStore('wallet', () => {
  const balance = ref('0.00')
  const transactions = ref([])
  const loading = ref(false)
  const error = ref(null)
  const currentPage = ref(1)
  const totalPages = ref(1)

  async function fetchTransactions(page = 1) {
    loading.value = true
    error.value = null
    try {
      const response = await transactionAPI.getTransactions(page)
      balance.value = response.data.balance
      transactions.value = response.data.transactions
      currentPage.value = response.data.pagination.current_page
      totalPages.value = response.data.pagination.last_page
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch transactions'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function sendMoney(receiverId, amount) {
    loading.value = true
    error.value = null
    try {
      const response = await transactionAPI.createTransfer({
        receiver_id: receiverId,
        amount: parseFloat(amount)
      })

      // Update balance
      balance.value = response.data.new_balance

      // Add new transaction to the list
      transactions.value.unshift(response.data.transaction)

      return response.data
    } catch (err) {
      // Handle validation errors
      if (err.response?.data?.errors) {
        const errors = err.response.data.errors
        error.value = Object.values(errors).flat().join(', ')
      } else {
        error.value = err.response?.data?.error || err.response?.data?.message || 'Transfer failed'
      }
      throw err
    } finally {
      loading.value = false
    }
  }

  function addTransaction(transaction) {
    // Add transaction from real-time event
    transactions.value.unshift(transaction)

    // Remove duplicates
    const seen = new Set()
    transactions.value = transactions.value.filter(t => {
      const duplicate = seen.has(t.tuuid)
      seen.add(t.tuuid)
      return !duplicate
    })
  }

  function updateBalance(newBalance) {
    balance.value = newBalance
  }

  function reset() {
    balance.value = '0.00'
    transactions.value = []
    currentPage.value = 1
    totalPages.value = 1
    error.value = null
  }

  return {
    balance,
    transactions,
    loading,
    error,
    currentPage,
    totalPages,
    fetchTransactions,
    sendMoney,
    addTransaction,
    updateBalance,
    reset
  }
})

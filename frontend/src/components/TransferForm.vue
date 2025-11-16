<template>
  <div :class="compact ? '' : 'card'">
    <div :class="compact ? '' : 'card-body'">
      <h5 v-if="!compact" class="card-title">Send Money</h5>

      <div v-if="walletStore.error" class="alert alert-danger alert-dismissible fade show">
        {{ walletStore.error }}
        <button type="button" class="btn-close" @click="walletStore.error = null"></button>
      </div>

      <div v-if="successMessage" class="alert alert-success alert-dismissible fade show">
        {{ successMessage }}
        <button type="button" class="btn-close" @click="successMessage = ''"></button>
      </div>

      <form @submit.prevent="handleTransfer">
        <div class="mb-3">
          <label for="receiver_id" class="form-label">Receiver ID</label>
          <input
            type="number"
            class="form-control"
            id="receiver_id"
            v-model.number="form.receiver_id"
            placeholder="Enter user ID"
            required
          />
          <small class="form-text text-muted">Enter the numeric ID of the receiver</small>
        </div>

        <div class="mb-3">
          <label for="amount" class="form-label">Amount</label>
          <div class="input-group">
            <span class="input-group-text">$</span>
            <input
              type="number"
              class="form-control"
              id="amount"
              v-model.number="form.amount"
              step="0.01"
              min="0.01"
              placeholder="0.00"
              required
            />
          </div>
          <small class="form-text text-muted">
            Commission: ${{ commission }} (1.5%) | Total: ${{ total }}
          </small>
        </div>

        <button
          type="submit"
          class="btn btn-primary w-100"
          :disabled="walletStore.loading"
        >
          {{ walletStore.loading ? 'Sending...' : 'Send Money' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useWalletStore } from '../stores/wallet'

const props = defineProps({
  compact: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['transfer-sent'])
const walletStore = useWalletStore()

const form = ref({
  receiver_id: null,
  amount: null
})

const successMessage = ref('')

const commission = computed(() => {
  if (!form.value.amount) return '0.00'
  return (form.value.amount * 0.015).toFixed(2)
})

const total = computed(() => {
  if (!form.value.amount) return '0.00'
  return (parseFloat(form.value.amount) + parseFloat(commission.value)).toFixed(2)
})

async function handleTransfer() {
  successMessage.value = ''

  try {
    await walletStore.sendMoney(form.value.receiver_id, form.value.amount)
    successMessage.value = `Successfully sent $${form.value.amount}!`

    // Reset form
    form.value = {
      receiver_id: null,
      amount: null
    }

    emit('transfer-sent')
  } catch (error) {
    console.error('Transfer failed:', error)
  }
}
</script>

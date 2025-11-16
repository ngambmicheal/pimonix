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
        <div class="mb-3 position-relative">
          <label for="receiver_search" class="form-label">Receiver</label>
          <input
            type="text"
            class="form-control"
            id="receiver_search"
            v-model="searchQuery"
            @input="handleSearch"
            @focus="showSuggestions = true"
            @blur="handleBlur"
            placeholder="Search by email, name, or UID"
            autocomplete="off"
            required
          />
          <small class="form-text text-muted">
            {{ selectedUser ? `Selected: ${selectedUser.name} (${selectedUser.email})` : 'Type at least 2 characters to search' }}
          </small>

          <!-- Autocomplete suggestions -->
          <div
            v-if="showSuggestions && searchResults.length > 0"
            class="autocomplete-suggestions"
          >
            <div
              v-for="user in searchResults"
              :key="user.id"
              class="suggestion-item"
              @mousedown="selectUser(user)"
            >
              <div class="d-flex align-items-center">
                <div class="suggestion-icon">
                  <i class="bi bi-person-circle"></i>
                </div>
                <div class="flex-grow-1">
                  <div class="fw-semibold">{{ user.name }}</div>
                  <small class="text-muted">{{ user.email }}</small>
                </div>
                <small class="text-muted">{{ user.uid }}</small>
              </div>
            </div>
          </div>

          <!-- Loading indicator -->
          <div v-if="searching" class="search-loading">
            <div class="spinner-border spinner-border-sm text-primary"></div>
          </div>
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
          :disabled="walletStore.loading || !selectedUser"
        >
          <span v-if="walletStore.loading">
            <span class="spinner-border spinner-border-sm me-2"></span>
            Sending...
          </span>
          <span v-else>
            <i class="bi bi-send me-2"></i>
            Send Money
          </span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useWalletStore } from '../stores/wallet'
import { userAPI } from '../services/api'

const props = defineProps({
  compact: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['transfer-sent'])
const walletStore = useWalletStore()

const searchQuery = ref('')
const searchResults = ref([])
const selectedUser = ref(null)
const showSuggestions = ref(false)
const searching = ref(false)
const searchTimeout = ref(null)

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

function handleSearch() {
  const query = searchQuery.value.trim()

  // Clear selected user if search query changes
  if (selectedUser.value && searchQuery.value !== selectedUser.value.email) {
    selectedUser.value = null
    form.value.receiver_id = null
  }

  // Minimum 2 characters to search
  if (query.length < 2) {
    searchResults.value = []
    return
  }

  // Debounce search
  clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(async () => {
    searching.value = true
    try {
      const response = await userAPI.searchUsers(query)
      console.log('Search response:', response.data)
      searchResults.value = response.data.data || response.data
      showSuggestions.value = true
      console.log('Search results:', searchResults.value)
    } catch (error) {
      console.error('Search failed:', error)
      searchResults.value = []
    } finally {
      searching.value = false
    }
  }, 300) // 300ms debounce
}

function selectUser(user) {
  selectedUser.value = user
  form.value.receiver_id = user.id
  searchQuery.value = user.email
  searchResults.value = []
  showSuggestions.value = false
}

function handleBlur() {
  // Delay to allow click on suggestion
  setTimeout(() => {
    showSuggestions.value = false
  }, 200)
}

async function handleTransfer() {
  if (!selectedUser.value) {
    walletStore.error = 'Please select a receiver from the suggestions'
    return
  }

  successMessage.value = ''

  try {
    await walletStore.sendMoney(form.value.receiver_id, form.value.amount)
    successMessage.value = `Successfully sent $${form.value.amount} to ${selectedUser.value.name}!`

    // Reset form
    searchQuery.value = ''
    selectedUser.value = null
    searchResults.value = []
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

<style scoped>
.autocomplete-suggestions {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  max-height: 300px;
  overflow-y: auto;
  z-index: 1000;
  margin-top: 0.25rem;
}

.suggestion-item {
  padding: 0.75rem 1rem;
  cursor: pointer;
  transition: background-color 0.2s ease;
  border-bottom: 1px solid #f0f0f0;
}

.suggestion-item:last-child {
  border-bottom: none;
}

.suggestion-item:hover {
  background-color: #f8f9fa;
}

.suggestion-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #e7f1ff;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 0.75rem;
  color: #0d6efd;
  font-size: 1.25rem;
}

.search-loading {
  position: absolute;
  right: 12px;
  top: 38px;
}
</style>

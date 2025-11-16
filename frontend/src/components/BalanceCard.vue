<template>
  <div class="card">
    <div class="card-body text-center">
      <h5 class="card-title text-muted">Current Balance</h5>
      <h2 class="display-4 text-success">${{ balance }}</h2>
    </div>
  </div>
</template>

<script setup>
defineProps({
  balance: {
    type: String,
    default: '0.00'
  }
})
</script>

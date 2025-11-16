<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from './stores/auth'

const authStore = useAuthStore()

onMounted(() => {
  authStore.initAuth()
})
</script>

<template>
  <div id="app">
    <router-view />
  </div>
</template>

<style>
body {
  background-color: #f8f9fa;
}

#app {
  min-height: 100vh;
}
</style>

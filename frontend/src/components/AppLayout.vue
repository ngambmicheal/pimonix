<template>
  <div class="app-layout">
    <AppHeader />
    <main class="main-content">
      <div class="content-wrapper">
        <slot />
      </div>
    </main>
    <footer class="app-footer">
      <div class="footer-wrapper">
        <small class="text-muted">
          &copy; 2025 Pimonix. Built with Laravel & Vue.js
        </small>
      </div>
    </footer>
  </div>
</template>

<script setup>
import AppHeader from './AppHeader.vue'
</script>

<style scoped>
.app-layout {
  min-height: 100vh;
  width: 100%;
  display: flex;
  flex-direction: column;
  margin: 0;
  padding: 0;
}

.main-content {
  width: 100%;
  padding: 2rem 0;
  background-color: #f8f9fa;
  justify-content: center;
}

.content-wrapper {
  width: 90%;
  max-width: 1400px;
  margin: 0 auto;
}

.app-footer {
  width: 100%;
  padding: 1.5rem 0;
  background-color: white;
  border-top: 1px solid #dee2e6;
  margin-top: auto;
  display: flex;
  justify-content: center;
}

.footer-wrapper {
  width: 80%;
  max-width: 1400px;
  text-align: center;
}

@media (max-width: 992px) {
  .content-wrapper,
  .footer-wrapper {
    width: 90%;
  }
}

@media (max-width: 768px) {
  .content-wrapper,
  .footer-wrapper {
    width: 95%;
  }
}
</style>

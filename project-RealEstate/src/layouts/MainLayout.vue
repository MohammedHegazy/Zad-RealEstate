<script setup>
import AppFooter from '@/components/layout/AppFooter.vue'
import AppNavbar from '@/components/layout/AppNavbar.vue'
</script>

<template>
  <div class="main-layout">
    <AppNavbar />
    <main class="main-layout__content">
      <slot />
    </main>
    <AppFooter />
  </div>
</template>

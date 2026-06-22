<script setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'

import BuyerSidebar from '@/components/buyer/BuyerSidebar.vue'

const route = useRoute()
const sidebarOpen = ref(false)

function closeSidebar() {
  sidebarOpen.value = false
}

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value
}

watch(
  () => route.fullPath,
  () => closeSidebar(),
)
</script>

<template>
  <div class="admin-layout" :class="{ 'admin-layout--sidebar-open': sidebarOpen }">
    <button
      type="button"
      class="admin-sidebar-backdrop"
      aria-label="إغلاق القائمة"
      :tabindex="sidebarOpen ? 0 : -1"
      @click="closeSidebar"
    />

    <BuyerSidebar :open="sidebarOpen" @close="closeSidebar" />

    <main class="admin-layout__main">
      <header class="admin-mobile-bar">
        <button
          type="button"
          class="admin-mobile-bar__toggle"
          aria-label="فتح القائمة"
          :aria-expanded="sidebarOpen"
          @click="toggleSidebar"
        >
          <i class="bi bi-list"></i>
        </button>
        <span class="admin-mobile-bar__title">زاد — المستثمر</span>
      </header>

      <div class="admin-layout__content">
        <slot />
      </div>
    </main>
  </div>
</template>

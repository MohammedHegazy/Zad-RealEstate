<script setup>
defineProps({
  icon: {
    type: String,
    default: 'bi-building',
  },
  title: {
    type: String,
    required: true,
  },
  message: {
    type: String,
    default: '',
  },
})
</script>

<template>
  <div class="empty-state">
    <i :class="['bi', icon, 'empty-state__icon']"></i>
    <h3 class="empty-state__title">{{ title }}</h3>
    <p v-if="message" class="empty-state__message">{{ message }}</p>
    <slot />
  </div>
</template>

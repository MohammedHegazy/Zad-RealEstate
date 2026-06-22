<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    default: '',
  },
})
</script>

<template>
  <section class="admin-stats-section">
    <header class="admin-stats-section__header">
      <h2 class="admin-stats-section__title">{{ title }}</h2>
      <p v-if="description" class="admin-stats-section__description">{{ description }}</p>
    </header>
    <slot />
  </section>
</template>

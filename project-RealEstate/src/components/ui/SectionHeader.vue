<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    default: '',
  },
  to: {
    type: [String, Object],
    default: null,
  },
  linkText: {
    type: String,
    default: 'عرض الكل',
  },
})
</script>

<template>
  <div class="section-header">
    <div class="section-header__content">
      <span v-if="subtitle" class="section-header__overline text-overline">{{ subtitle }}</span>
      <h2 class="section-header__title">{{ title }}</h2>
    </div>

    <RouterLink v-if="to" :to="to" class="section-header__link">
      {{ linkText }}
      <i class="bi bi-arrow-left"></i>
    </RouterLink>
  </div>
</template>

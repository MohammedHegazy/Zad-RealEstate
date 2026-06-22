<script setup>
import AppButton from '@/components/ui/AppButton.vue'

defineProps({
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    default: '',
  },
  primary: {
    type: Object,
    default: null,
    // { label, to }
  },
  secondary: {
    type: Object,
    default: null,
  },
})
</script>

<template>
  <section class="cta-banner">
    <div class="cta-banner__content">
      <h2 class="cta-banner__title">{{ title }}</h2>
      <p v-if="description" class="cta-banner__description">{{ description }}</p>
    </div>
    <div v-if="primary || secondary" class="cta-banner__actions">
      <AppButton v-if="primary" :to="primary.to" variant="primary" size="lg">
        {{ primary.label }}
      </AppButton>
      <AppButton v-if="secondary" :to="secondary.to" variant="outline" size="lg">
        {{ secondary.label }}
      </AppButton>
    </div>
  </section>
</template>

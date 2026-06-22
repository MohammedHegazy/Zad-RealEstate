<script setup>
defineProps({
  label: {
    type: String,
    required: true,
  },
  value: {
    type: [Number, String],
    default: 0,
  },
  icon: {
    type: String,
    default: 'bi-bar-chart',
  },
  variant: {
    type: String,
    default: 'default',
    validator: (v) => ['default', 'primary', 'warning', 'success', 'danger'].includes(v),
  },
  to: {
    type: String,
    default: '',
  },
})
</script>

<template>
  <component
    :is="to ? 'RouterLink' : 'article'"
    :to="to || undefined"
    class="admin-stat-card"
    :class="[`admin-stat-card--${variant}`, { 'admin-stat-card--link': to }]"
  >
    <div class="admin-stat-card__icon">
      <i :class="['bi', icon]"></i>
    </div>
    <div class="admin-stat-card__body">
      <p class="admin-stat-card__value">{{ value }}</p>
      <p class="admin-stat-card__label">{{ label }}</p>
    </div>
  </component>
</template>

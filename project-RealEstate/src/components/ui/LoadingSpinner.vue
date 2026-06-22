<script setup>
defineProps({
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v),
  },
  label: {
    type: String,
    default: 'جاري التحميل…',
  },
})
</script>

<template>
  <div class="loading-spinner" :class="`loading-spinner--${size}`" role="status">
    <div class="loading-spinner__ring"></div>
    <span class="loading-spinner__label">{{ label }}</span>
  </div>
</template>

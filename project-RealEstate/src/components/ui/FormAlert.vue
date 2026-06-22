<script setup>
defineProps({
  message: {
    type: String,
    required: true,
  },
  variant: {
    type: String,
    default: 'error',
    validator: (v) => ['error', 'success', 'info'].includes(v),
  },
})
</script>

<template>
  <div class="form-alert" :class="`form-alert--${variant}`" role="alert">
    <i
      class="bi"
      :class="{
        'bi-exclamation-circle-fill': variant === 'error',
        'bi-check-circle-fill': variant === 'success',
        'bi-info-circle-fill': variant === 'info',
      }"
    ></i>
    <p>{{ message }}</p>
  </div>
</template>

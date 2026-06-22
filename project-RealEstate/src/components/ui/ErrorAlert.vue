<script setup>
defineProps({
  message: {
    type: String,
    required: true,
  },
  retryLabel: {
    type: String,
    default: 'إعادة المحاولة',
  },
})

const emit = defineEmits(['retry'])
</script>

<template>
  <div class="error-alert" role="alert">
    <i class="bi bi-exclamation-triangle-fill error-alert__icon"></i>
    <p class="error-alert__message">{{ message }}</p>
    <button type="button" class="error-alert__retry" @click="emit('retry')">
      {{ retryLabel }}
    </button>
  </div>
</template>

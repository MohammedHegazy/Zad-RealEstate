<script setup>
defineProps({
  label: {
    type: String,
    default: '',
  },
  labelFor: {
    type: String,
    default: '',
  },
  error: {
    type: String,
    default: '',
  },
  hint: {
    type: String,
    default: '',
  },
  required: {
    type: Boolean,
    default: false,
  },
})
</script>

<template>
  <div class="app-form-group" :class="{ 'app-form-group--error': error }">
    <label v-if="label" :for="labelFor" class="app-form-group__label">
      {{ label }}
      <span v-if="required" class="app-form-group__required" aria-hidden="true">*</span>
    </label>

    <slot />

    <p v-if="error" class="app-form-group__error" role="alert">{{ error }}</p>
    <p v-else-if="hint" class="app-form-group__hint">{{ hint }}</p>
  </div>
</template>

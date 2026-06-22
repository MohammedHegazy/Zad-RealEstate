<script setup>
const model = defineModel({ type: Boolean, default: false })

defineProps({
  id: {
    type: String,
    default: '',
  },
  label: {
    type: String,
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})
</script>

<template>
  <label class="field-checkbox" :for="id">
    <input
      :id="id"
      v-model="model"
      type="checkbox"
      class="field-checkbox__input"
      :disabled="disabled"
    />
    <span class="field-checkbox__box">
      <i class="bi bi-check-lg"></i>
    </span>
    <span v-if="label || $slots.default" class="field-checkbox__label">
      <slot>{{ label }}</slot>
    </span>
  </label>
</template>

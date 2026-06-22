<script setup>
const model = defineModel({ type: String, default: '' })

defineProps({
  id: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: 'ابحث...',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  hasError: {
    type: Boolean,
    default: false,
  },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v),
  },
  rounded: {
    type: Boolean,
    default: true,
  },
})
</script>

<template>
  <div class="field-shell field-shell--search app-search-input">
    <i class="bi bi-search field-shell__icon field-shell__icon--start"></i>
    <input
      :id="id"
      v-model="model"
      type="search"
      class="field-control field-control--icon-start"
      :class="[
        `field-control--${size}`,
        { 'field-control--error': hasError, 'field-control--search': rounded },
      ]"
      :placeholder="placeholder"
      :disabled="disabled"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'

const model = defineModel({ type: String, default: '' })

const props = defineProps({
  id: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: '',
  },
  rows: {
    type: Number,
    default: 4,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  readonly: {
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
})

const fieldClasses = computed(() => [
  'field-control',
  'field-control--textarea',
  `field-control--${props.size}`,
  { 'field-control--error': props.hasError },
])
</script>

<template>
  <textarea
    :id="id"
    v-model="model"
    :class="fieldClasses"
    :placeholder="placeholder"
    :rows="rows"
    :disabled="disabled"
    :readonly="readonly"
  ></textarea>
</template>

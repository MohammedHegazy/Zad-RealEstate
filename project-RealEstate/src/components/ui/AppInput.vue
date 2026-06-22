<script setup>
import { computed, ref } from 'vue'

const model = defineModel({ type: [String, Number], default: '' })

const props = defineProps({
  id: {
    type: String,
    default: '',
  },
  type: {
    type: String,
    default: 'text',
  },
  placeholder: {
    type: String,
    default: '',
  },
  autocomplete: {
    type: String,
    default: '',
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
  icon: {
    type: String,
    default: '',
  },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v),
  },
  showPasswordToggle: {
    type: Boolean,
    default: false,
  },
})

const showPassword = ref(false)

const inputType = computed(() => {
  if (props.type !== 'password' || !props.showPasswordToggle) return props.type
  return showPassword.value ? 'text' : 'password'
})

const iconClass = computed(() => {
  if (!props.icon) return ''
  return props.icon.startsWith('bi-') ? props.icon : `bi-${props.icon}`
})

const fieldClasses = computed(() => [
  'field-control',
  `field-control--${props.size}`,
  {
    'field-control--error': props.hasError,
    'field-control--icon-start': props.icon,
    'field-control--action-end': props.type === 'password' && props.showPasswordToggle,
  },
])
</script>

<template>
  <div class="field-shell app-input">
    <i v-if="icon" :class="[iconClass, 'field-shell__icon', 'field-shell__icon--start']"></i>

    <input
      :id="id"
      v-model="model"
      :type="inputType"
      :class="fieldClasses"
      :placeholder="placeholder"
      :autocomplete="autocomplete"
      :disabled="disabled"
      :readonly="readonly"
    />

    <button
      v-if="type === 'password' && showPasswordToggle"
      type="button"
      class="field-shell__action"
      :aria-label="showPassword ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور'"
      @click="showPassword = !showPassword"
    >
      <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
    </button>
  </div>
</template>

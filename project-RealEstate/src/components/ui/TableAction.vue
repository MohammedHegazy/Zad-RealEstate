<script setup>
import { computed } from 'vue'

const props = defineProps({
  tone: {
    type: String,
    default: 'neutral',
    validator: (v) => ['view', 'success', 'warning', 'danger', 'reject', 'neutral'].includes(v),
  },
  icon: {
    type: String,
    default: '',
  },
  label: {
    type: String,
    default: '',
  },
  title: {
    type: String,
    default: '',
  },
  to: {
    type: [String, Object],
    default: null,
  },
  href: {
    type: String,
    default: null,
  },
  type: {
    type: String,
    default: 'button',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  divider: {
    type: Boolean,
    default: false,
  },
})

const TONE_ICONS = {
  view: 'bi-eye',
  success: 'bi-check-circle',
  warning: 'bi-pause-circle',
  danger: 'bi-trash',
  reject: 'bi-x-circle',
  neutral: 'bi-sliders',
}

const iconClass = computed(() => {
  const raw = props.icon || TONE_ICONS[props.tone] || TONE_ICONS.neutral
  return raw.startsWith('bi-') ? raw : `bi-${raw}`
})

const ariaLabel = computed(() => props.title || props.label)

const actionClasses = computed(() => [
  'table-action',
  `table-action--${props.tone}`,
  { 'table-action--divider': props.divider },
])
</script>

<template>
  <RouterLink
    v-if="to"
    :to="to"
    class="table-action"
    :class="actionClasses"
    :aria-label="ariaLabel || undefined"
    :title="title || label || undefined"
  >
    <i :class="[iconClass, 'table-action__icon']" aria-hidden="true"></i>
    <span v-if="label || $slots.default" class="table-action__label">
      <slot>{{ label }}</slot>
    </span>
  </RouterLink>

  <a
    v-else-if="href"
    :href="href"
    class="table-action"
    :class="actionClasses"
    :aria-label="ariaLabel || undefined"
    :title="title || label || undefined"
  >
    <i :class="[iconClass, 'table-action__icon']" aria-hidden="true"></i>
    <span v-if="label || $slots.default" class="table-action__label">
      <slot>{{ label }}</slot>
    </span>
  </a>

  <button
    v-else
    :type="type"
    class="table-action"
    :class="actionClasses"
    :disabled="disabled"
    :aria-label="ariaLabel || undefined"
    :title="title || label || undefined"
  >
    <i :class="[iconClass, 'table-action__icon']" aria-hidden="true"></i>
    <span v-if="label || $slots.default" class="table-action__label">
      <slot>{{ label }}</slot>
    </span>
  </button>
</template>

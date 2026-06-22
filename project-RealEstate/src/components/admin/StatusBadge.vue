<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
  labels: {
    type: Object,
    default: () => ({}),
  },
})

const label = computed(() => props.labels[props.status] ?? props.status)

const variant = computed(() => {
  const map = {
    active: 'success',
    approved: 'success',
    pending: 'warning',
    inactive: 'muted',
    rejected: 'danger',
    suspended: 'danger',
  }
  return map[props.status] ?? 'default'
})
</script>

<template>
  <span class="status-badge" :class="`status-badge--${variant}`">{{ label }}</span>
</template>

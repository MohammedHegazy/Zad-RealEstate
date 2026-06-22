<script setup>
defineProps({
  align: {
    type: String,
    default: 'start',
    validator: (v) => ['start', 'end', 'center'].includes(v),
  },
  compactMobile: {
    type: Boolean,
    default: false,
  },
})
</script>

<template>
  <div
    class="table-action-group"
    :class="[
      `table-action-group--${align}`,
      { 'table-action-group--compact-mobile': compactMobile },
    ]"
  >
    <slot />
  </div>
</template>

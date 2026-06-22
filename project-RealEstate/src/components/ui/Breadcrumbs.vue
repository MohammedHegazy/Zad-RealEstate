<script setup>
defineOptions({ name: 'UiBreadcrumbs' })
defineProps({
  items: {
    type: Array,
    required: true,
    // { label: string, to?: string }
  },
})
</script>

<template>
  <nav class="breadcrumbs" aria-label="مسار التنقل">
    <ol class="breadcrumbs__list">
      <li v-for="(item, index) in items" :key="index" class="breadcrumbs__item">
        <RouterLink v-if="item.to && index < items.length - 1" :to="item.to">
          {{ item.label }}
        </RouterLink>
        <span v-else aria-current="page">{{ item.label }}</span>
        <i v-if="index < items.length - 1" class="bi bi-chevron-left breadcrumbs__sep"></i>
      </li>
    </ol>
  </nav>
</template>

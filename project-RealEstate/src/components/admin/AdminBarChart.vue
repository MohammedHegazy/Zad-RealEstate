<script setup>
import { computed } from 'vue'

const props = defineProps({
  data: {
    type: Array,
    default: () => [],
    // { label, value, variant? }
  },
  emptyMessage: {
    type: String,
    default: 'لا توجد بيانات.',
  },
})

const maxValue = computed(() => {
  const values = props.data.map((item) => Number(item.value) || 0)
  return Math.max(...values, 1)
})

function barWidth(value) {
  return `${((Number(value) || 0) / maxValue.value) * 100}%`
}
</script>

<template>
  <div class="admin-bar-chart">
    <p v-if="!data.length" class="admin-bar-chart__empty">{{ emptyMessage }}</p>

    <ul v-else class="admin-bar-chart__list">
      <li v-for="item in data" :key="item.label" class="admin-bar-chart__item">
        <div class="admin-bar-chart__meta">
          <span class="admin-bar-chart__label">{{ item.label }}</span>
          <span class="admin-bar-chart__value">{{ item.value }}</span>
        </div>
        <div class="admin-bar-chart__track">
          <div
            class="admin-bar-chart__fill"
            :class="item.variant ? `admin-bar-chart__fill--${item.variant}` : ''"
            :style="{ width: barWidth(item.value) }"
          ></div>
        </div>
      </li>
    </ul>
  </div>
</template>

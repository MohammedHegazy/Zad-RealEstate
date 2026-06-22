<script setup>
import { computed } from 'vue'

const props = defineProps({
  data: {
    type: Array,
    default: () => [],
    // { date, count }
  },
  title: {
    type: String,
    default: '',
  },
})

const maxValue = computed(() => {
  const values = props.data.map((item) => Number(item.count) || 0)
  return Math.max(...values, 1)
})

function barHeight(count) {
  return `${((Number(count) || 0) / maxValue.value) * 100}%`
}

function formatDate(date) {
  if (!date) return ''
  const parsed = new Date(date)
  return parsed.toLocaleDateString('ar-SY', { weekday: 'short', day: 'numeric' })
}
</script>

<template>
  <div class="admin-trend-chart">
    <p v-if="title" class="admin-trend-chart__title">{{ title }}</p>

    <div class="admin-trend-chart__bars">
      <div v-for="point in data" :key="point.date" class="admin-trend-chart__column">
        <span class="admin-trend-chart__count">{{ point.count }}</span>
        <div class="admin-trend-chart__bar-wrap">
          <div class="admin-trend-chart__bar" :style="{ height: barHeight(point.count) }"></div>
        </div>
        <span class="admin-trend-chart__date">{{ formatDate(point.date) }}</span>
      </div>
    </div>
  </div>
</template>

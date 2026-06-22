<script setup>
import { computed } from 'vue'

import { formatPrice, formatRoi } from '@/composables/useFormatters.js'

const props = defineProps({
  estate: {
    type: Object,
    required: true,
  },
})

const metrics = computed(() => {
  const e = props.estate
  const items = []

  const roi = formatRoi(e.roi)
  if (roi) items.push({ label: 'العائد على الاستثمار', value: roi, icon: 'bi-graph-up-arrow' })

  if (e.expected_annual_income) {
    items.push({
      label: 'الدخل السنوي المتوقع',
      value: formatPrice(e.expected_annual_income),
      icon: 'bi-cash-stack',
    })
  }

  if (e.payback_period) {
    items.push({
      label: 'فترة استرداد رأس المال',
      value: `${Number(e.payback_period).toFixed(1)} سنة`,
      icon: 'bi-hourglass-split',
    })
  }

  if (e.monthly_rent) {
    items.push({
      label: 'الإيجار الشهري',
      value: formatPrice(e.monthly_rent),
      icon: 'bi-calendar-month',
    })
  }

  if (e.occupancy_rate) {
    items.push({
      label: 'نسبة الإشغال',
      value: `${Number(e.occupancy_rate).toFixed(0)}%`,
      icon: 'bi-pie-chart',
    })
  }

  if (e.price_of_meter) {
    items.push({
      label: 'سعر المتر',
      value: formatPrice(e.price_of_meter),
      icon: 'bi-rulers',
    })
  }

  return items
})

const hasMetrics = computed(() => metrics.value.length > 0)
</script>

<template>
  <section class="estate-investment">
    <div class="estate-investment__header">
      <div>
        <h3 class="estate-investment__title">
          <i class="bi bi-bar-chart-line"></i>
          تحليل استثماري
        </h3>
        <p class="estate-investment__subtitle">
          احسب العائد المتوقع على استثمارك في هذا العقار
        </p>
      </div>
      <RouterLink
        :to="`/estates/${estate.id}/analyze`"
        class="estate-investment__cta"
      >
        <i class="bi bi-calculator"></i>
        <span>حساب العائد</span>
        <i class="bi bi-arrow-left-short"></i>
      </RouterLink>
    </div>

    <template v-if="hasMetrics">
      <div class="estate-investment__grid">
        <div v-for="metric in metrics" :key="metric.label" class="estate-investment__card">
          <i :class="['bi', metric.icon]"></i>
          <span class="estate-investment__card-label">{{ metric.label }}</span>
          <strong class="estate-investment__card-value">{{ metric.value }}</strong>
        </div>
      </div>
    </template>

    <p v-else class="estate-investment__empty">
      <i class="bi bi-info-circle"></i>
      لا توجد بيانات استثمارية متاحة بعد. استخدم "حساب العائد" لإنشاء تحليل خاص بك بناءً على معطياتك.
    </p>
  </section>
</template>

<style scoped>
.estate-investment {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 1rem;
  padding: 1.5rem;
}

.estate-investment__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.25rem;
  flex-wrap: wrap;
}

.estate-investment__title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin: 0;
  font-size: var(--text-xl);
  color: var(--color-text-primary);
}

.estate-investment__title i {
  color: var(--color-primary);
}

.estate-investment__subtitle {
  margin: 0.25rem 0 0;
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
}

.estate-investment__cta {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  background: var(--color-card-dark-accent-bg);
  color: var(--color-text-primary);
  border-radius: 0.75rem;
  font-weight: 600;
  font-size: var(--text-sm);
  text-decoration: none;
  transition: all 0.2s ease;
  box-shadow: 0 4px 14px var(--color-focus-ring);
  white-space: nowrap;
  border: none;
  line-height: 1.4;
}

.estate-investment__cta:hover {
  filter: brightness(1.08);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px var(--color-focus-ring);
  color: var(--color-text-primary);
}

.estate-investment__cta i:first-child {
  font-size: 1.1rem;
}

.estate-investment__cta i:last-child {
  transition: transform 0.2s ease;
}

.estate-investment__cta:hover i:last-child {
  transform: translateX(-3px);
}

.estate-investment__grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 1rem;
}

.estate-investment__card {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  padding: 1.25rem;
  border-radius: 0.75rem;
  background: var(--color-surface-sunken);
  border: 1px solid var(--color-border-subtle);
}

.estate-investment__card i {
  color: var(--color-primary);
  font-size: 1.25rem;
}

.estate-investment__card-label {
  color: var(--color-text-secondary);
  font-size: var(--text-xs);
}

.estate-investment__card-value {
  font-family: var(--font-family-price);
  font-size: var(--text-lg);
  color: var(--color-text-primary);
}

.estate-investment__empty {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--color-text-muted);
  font-size: var(--text-sm);
  margin: 0;
  padding: 0.75rem 0;
}

.estate-investment__empty i {
  color: var(--color-primary);
  font-size: 1rem;
}
</style>

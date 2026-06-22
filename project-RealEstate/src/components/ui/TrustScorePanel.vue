<script setup>
import { computed } from 'vue'

import { TRUST_FACTOR_LABELS } from '@/config/agents.js'

const props = defineProps({
  trust: {
    type: Object,
    default: null,
  },
  title: {
    type: String,
    default: 'تفاصيل درجة الثقة',
  },
})

const factors = computed(() => {
  if (!props.trust?.factors) return []
  return Object.entries(props.trust.factors).map(([key, value]) => ({
    key,
    label: TRUST_FACTOR_LABELS[key] ?? key,
    value: Number(value) || 0,
  }))
})

const maxFactorValue = computed(() => {
  const values = factors.value.map((f) => f.value)
  return Math.max(...values, 1)
})
</script>

<template>
  <section v-if="trust" class="trust-panel">
    <h3 class="trust-panel__title">
      <i class="bi bi-shield-check"></i>
      {{ title }}
    </h3>

    <div class="trust-panel__score">
      <span class="trust-panel__score-value">{{ trust.trust_score }}%</span>
      <span class="trust-panel__score-label">درجة الثقة الإجمالية</span>
    </div>

    <ul v-if="factors.length" class="trust-panel__factors">
      <li v-for="factor in factors" :key="factor.key" class="trust-panel__factor">
        <div class="trust-panel__factor-header">
          <span>{{ factor.label }}</span>
          <span>{{ factor.value }}</span>
        </div>
        <div class="trust-panel__bar">
          <div
            class="trust-panel__bar-fill"
            :style="{ width: `${(factor.value / maxFactorValue) * 100}%` }"
          ></div>
        </div>
      </li>
    </ul>
  </section>
</template>

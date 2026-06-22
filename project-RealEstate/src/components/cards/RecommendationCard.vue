<script setup>
import PropertyCard from '@/components/cards/PropertyCard.vue'

defineProps({
  recommendation: {
    type: Object,
    required: true,
  },
})

function matchLabel(value) {
  const num = Number(value)
  if (Number.isNaN(num)) return '—'
  return `${Math.round(num)}%`
}
</script>

<template>
  <article class="recommendation-card">
    <div class="recommendation-card__badge">
      <i class="bi bi-stars"></i>
      تطابق {{ matchLabel(recommendation.matching_percentage) }}
    </div>

    <PropertyCard v-if="recommendation.estate" :estate="recommendation.estate" />

    <ul v-if="recommendation.why_recommended?.length" class="recommendation-card__reasons">
      <li v-for="reason in recommendation.why_recommended.slice(0, 3)" :key="reason">
        {{ reason }}
      </li>
    </ul>
  </article>
</template>

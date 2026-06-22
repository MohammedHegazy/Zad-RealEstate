<script setup>
import { computed } from 'vue'

const props = defineProps({
  rating: {
    type: Number,
    default: 0,
  },
  max: {
    type: Number,
    default: 5,
  },
  showValue: {
    type: Boolean,
    default: false,
  },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v),
  },
})

const stars = computed(() => {
  const list = []
  const rounded = Math.round(props.rating * 2) / 2

  for (let i = 1; i <= props.max; i += 1) {
    if (rounded >= i) {
      list.push('full')
    } else if (rounded >= i - 0.5) {
      list.push('half')
    } else {
      list.push('empty')
    }
  }

  return list
})
</script>

<template>
  <div class="star-rating" :class="`star-rating--${size}`" :aria-label="`التقييم ${rating} من ${max}`">
    <span v-for="(star, index) in stars" :key="index" class="star-rating__star">
      <i v-if="star === 'full'" class="bi bi-star-fill"></i>
      <i v-else-if="star === 'half'" class="bi bi-star-half"></i>
      <i v-else class="bi bi-star"></i>
    </span>
    <span v-if="showValue" class="star-rating__value">{{ rating.toFixed(1) }}</span>
  </div>
</template>
